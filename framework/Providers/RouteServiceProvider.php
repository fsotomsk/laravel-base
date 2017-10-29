<?php

namespace CDeep\Providers;


use CDeep\Http\Controllers\Api\Main  as ApiMain;
use CDeep\Http\Controllers\Page\Main as WebMain;
use CDeep\Models\Page;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = null;

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    /**
     * @param Router $router
     * @param $classes
     * @param null $trap404
     */
    public function mapControllers(Router $router, $namespace, $classes, $trap404=null)
    {
        $routes = [];

        foreach ($classes as $class) {

            $class = new \ReflectionClass($class);

            $name = $namespace
                ? str_replace($namespace, '', $class->name)
                : $class->name;

            $methods = $class->getMethods();

            foreach ($methods as $method) {
                $methodName = $method->name;
                if ($method->isPublic()) {
                    $description = [];
                    $docs = preg_split('/([\s*]*)\n([\s*]*)/', $method->getDocComment(), -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($docs as $d) {

                        $value = preg_split('/[\s,;]+/', $d, -1, PREG_SPLIT_NO_EMPTY);
                        $p = array_shift($value);
                        switch ($p) {
                            case '@route':
                                $routes[ $name ][ $methodName ] = [
                                    'url'        => $value[0],
                                    'as'         => $value[1] ?? null,
                                    'title'      => null,
                                    'page'       => null,
                                    'example'    => null,
                                    'urn'        => preg_replace('/\{(.+?)\?*\}/u', '$1', $value[0]),
                                    'uses'       => $name . '@' . $methodName,
                                    'class'      => $name,
                                    'method'     => ['GET'],
                                    'with'       => [],
                                    'link'       => [],
                                    'cache'      => [0, []],
                                    'params'     => [],
                                    'middleware' => [],
                                    'arg'        => [],
                                    'args'       => array_filter(
                                        array_map(function(\ReflectionParameter $p){
                                            return $p->name;
                                        }, $method->getParameters() ?: []),
                                        function($n){
                                            return !in_array($n, ['request', 'response']);
                                        }
                                    ),
                                    'description' => null,
                                ];
                                break;
                            case '@with':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['with'] =
                                        array_merge(
                                            $routes[ $name ][ $methodName ]['with'],
                                            $value
                                        );
                                }
                                break;
                            case '@link':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['link'][]
                                        = [array_shift($value), implode(' ', $value)];
                                }
                                break;
                            case '@as':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['as'] = array_shift($value);
                                }
                                break;
                            case '@example':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['example'] = array_shift($value);
                                }
                                break;
                            case '@cache':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['cache'] = [array_shift($value), $value];
                                }
                                break;
                            case '@middleware':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['middleware'] =
                                        array_merge(
                                            $routes[ $name ][ $methodName ]['middleware'],
                                            $value
                                        );
                                }
                                break;
                            case '@method':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['method'] = $value;
                                }
                                break;
                            case '@data':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['params'][] = $value;
                                }
                                break;
                            case '@param':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['arg'][] = $value;
                                }
                                break;
                            case '@return':
                                if (isset($routes[ $name ][ $methodName ])) {
                                    $routes[ $name ][ $methodName ]['return'] = $value;
                                }
                                break;
                            default:
                                if ($p) {
                                    if($p{0} == '@') {
                                        if (isset($routes[ $name ][ $methodName ])) {
                                            $routes[ $name ][ $methodName ][ substr($p, 1) ][] = $value;
                                        }
                                    }
                                    elseif ($p{0} != '/') {
                                        $description[] = $d;
                                    }
                                }
                                break;
                        }
                    }
                    if (isset($routes[ $name ][ $methodName ]) && $description) {
                        $routes[$name][$methodName]['title'] = array_shift($description);
                        $routes[$name][$methodName]['description'] = implode('<br>', $description);
                    }
                }
            }
        }

        foreach($routes as $class => $route) {
            $_route = array_reverse($route, true);
            foreach ($_route as $method => $params) {
                if ($method == 'help') {
                    $params['routes']     = $route;
                    $params['routes_all'] = $routes;
                    unset($params['routes_all'][$class]);
                }
                $router->match($params['method'], $params['url'], $params);
            }
        }

        if ($trap404) {
            $router->match(['GET', 'POST', 'PUT', 'DELETE'],  '{any?}',    $trap404)
                ->where('any', '.+');
        }
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace'  => $this->namespace,
        ], function (Router $router) {
            try {

                $classes = array_unique(
                    array_merge(
                        require __DIR__ . '/../../routes/web.php',
                        require base_path('routes/web.php')
                    )
                );
                $this->mapControllers($router, $this->namespace, $classes);

                $pages = Page::indexed();
                foreach($pages as $page) {
                    $params = [
                        'as'         => $page->id,
                        'uses'       => $page->controller ?: WebMain::class . '@view',
                        'page'       => $page,
                        'middleware' => [],
                    ];
                    $router->match(['GET', 'POST'], $page->http_link, $params);
                }

            } catch (\Exception $e) {}
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace'  => $this->namespace,
            'prefix'     => 'api',
        ], function (Router $router) {
            try {
                $classes = array_unique(
                    array_merge(
                        require __DIR__ . '/../../routes/api.php',
                        require base_path('routes/api.php')
                    )
                );

                $this->mapControllers($router, $this->namespace, $classes, ApiMain::class . '@error404');
            } catch (\Exception $e) {}
        });
    }
}
