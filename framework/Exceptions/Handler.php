<?php

namespace CDeep\Exceptions;

use CDeep\Models\Page;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return mixed|\Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $debug = config('app.debug');

        if (property_exists($request, 'isApi') && $request->isApi) {

            $sanitizeMessage = function($message) use ($debug) {
                if (!$debug) {
                    if (strpos($message, 'SQL') !== false) {
                        return 'db error';
                    }
                    $message = preg_replace('#App([^:]+)::#ui', '', $message);
                }
                return str_replace(
                    [
                        base_path(),
                        '.php',
                    ],
                    '',
                    $message
                );
            };

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'error ' => [
#                        'url'   => $request->getRequestUri(),
                        'message' => $e->getMessage() ?: 'Model not found',
                        'status_code' => Response::HTTP_NOT_FOUND
                    ]
                ], Response::HTTP_NOT_FOUND);
            }
            elseif ($e instanceof NotFoundResourceException) {
                return response()->json([
                    'error' => [
#                        'url'   => $request->getRequestUri(),
                        'message' => $e->getMessage() ?: 'Resource not found',
                        'status_code' => Response::HTTP_NOT_FOUND
                    ]
                ], Response::HTTP_NOT_FOUND);
            }
            elseif ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'error' => [
                        'url'     => $request->getRequestUri(),
                        'method'  => $request->getMethod(),
                        'message' => $e->getMessage() ?: 'Endpoint not found',
                        'status_code' => Response::HTTP_NOT_FOUND,
                    ]
                ], Response::HTTP_NOT_FOUND);
            }
            elseif ($e instanceof HttpException) {
                return response()->json([
                    'error' => [
#                        'url'   => $request->getRequestUri(),
                        'message' => $sanitizeMessage($e->getMessage()),
                        'status_code' => $e->getStatusCode()
                    ]
                ], $e->getStatusCode(), $e->getHeaders());
            }
            elseif ($e instanceof ValidationException) {
                return response()->json([
                    'error' => [
#                        'url'   => $request->getRequestUri(),
                        'message'     => $sanitizeMessage($e->getMessage()),
                        'info'        => $e->validator->getMessageBag()->toArray(),
                        'status_code' => 400
                    ]
                ], 400);
            }
            elseif ($e instanceof \ReflectionException) {
                return response()->json([
                    'error' => [
#                        'url'   => $request->getRequestUri(),
                        'message'     => 'Not implemented',
                        'status_code' => 501
                    ]
                ], 400);
            }
            elseif ($e instanceof QueryException) {
                return response()->json([
                    'error' => [
#                        'url'   => $request->getRequestUri(),
                        'message'       => $sanitizeMessage($e->getMessage()),
                        'status_code'   => Response::HTTP_BAD_REQUEST,
                    ]
                ], 400);
            }
            elseif ($e instanceof FatalThrowableError) {
                return response()->json([
                    'error' => [
 #                       'url'   => $request->getRequestUri(),
                        'message'       => $sanitizeMessage($e->getMessage()),
                        'status_code'   => Response::HTTP_BAD_REQUEST,
                        'class'         => $sanitizeMessage(pathinfo($e->getFile(), PATHINFO_FILENAME)) . ':' . $e->getLine(),
                    ]
                ], 400);
            }
            elseif ($e instanceof AuthenticationException) {
                return response()->json([
                    'error' => [
#                        'url'   => $request->getRequestUri(),
                        'message'       => $sanitizeMessage($e->getMessage()),
                        'status_code'   => Response::HTTP_UNAUTHORIZED,
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
            elseif ($e instanceof AuthorizationException) {
                return response()->json([
                    'error' => [
#                        'url'   => $request->getRequestUri(),
                        'message'       => $sanitizeMessage($e->getMessage()),
                        'status_code'   => Response::HTTP_FORBIDDEN,
                    ]
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'error' => [
#                    'url'   => $request->getRequestUri(),
                    'message'       => $sanitizeMessage($e->getMessage()),
                    'status_code'   => Response::HTTP_BAD_REQUEST,
                    'class'         => $sanitizeMessage(pathinfo($e->getFile(), PATHINFO_FILENAME)) . ':' . $e->getLine(),
                    'type'          => array_reverse(explode('\\', get_class($e)))[0]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        switch (true) {
            case ($e instanceof TokenMismatchException):
                return response(view('errors.token'), 400);
                break;
            default:
                break;
        }

        if ( !$debug && !$this->isHttpException($e)) {
            return response(view('errors.500'), 500);
        }

        switch (true) {
            case ($e instanceof \ErrorException):
            case ($e instanceof HttpException):
            case ($e instanceof NotFoundHttpException):
            case ($e instanceof AuthorizationException):

                $status = method_exists($e, 'getStatusCode')
                    ? $e->getStatusCode()
                    : $e->getCode();

                /**
                 * @var Page $error
                 */
                $error = Page::indexed($status);
                if ($error) {
                    /**
                     *
                     */
                    $error->setup();

                    /**
                     * Woooo! Magick!
                     * ресолвится pathAttribute и env при этом
                     */
                    $error->http_link;

                    $params             = ['Page' => $error];
                    $params['_CONTENT'] = view()->exists($error->view) ? view($error->view, $params) : null;

                    return response(
                        view()->exists($error->env)
                            ? view($error->env, $params)
                            : $params['_CONTENT']
                        , $status);
                }
                break;
            default:
                break;
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
