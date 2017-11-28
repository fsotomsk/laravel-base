<?php

namespace CDeep\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\FileViewFinder;

class ApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $request->isApi   = true;
        $request->isDebug = \App::environment('dev', 'testing', 'local');
        $action   = $request->route()->getAction();

        /**
         * @var \Illuminate\Http\Response $response
         */
        $response = null;

        if (!$request->isDebug && $request->getMethod() == 'GET' && ($action['cache'][0] ?? false)) {
            $key   = 'api:cache:' . md5($request->getRequestUri());
            $cache = \Cache::get($key, null);
            if ($cache) {
                $response = response($cache[1]);
                $response->withHeaders($cache[0]);
                $response->header('X-Cache-Key', $key);
            } else {
                $response = $next($request);
                \Cache::put(
                    $key,
                    [
                        $response->headers->all(),
                        $response->getContent(),
                    ],
                    ($action['cache'][0] / 60)
                );
            }
        } else {
            $response = $next($request);
        }

        $response->isApi = true;

        if ($request->get('pretty')) {
            $json = \json_decode($response->getContent());
            if ($json) {
                $response->withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8'
                ]);
                $response->setContent(\json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        return $response;
    }
}
