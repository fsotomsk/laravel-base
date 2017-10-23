<?php

namespace CDeep\Providers;

use CDeep\Helpers\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->publishes([
            __DIR__ . '/../../config/'          => $this->app->configPath() . "/",
            __DIR__ . '/../../public/'          => $this->app->publicPath() . "/",
            __DIR__ . '/../../database/'        => $this->app->basePath("database"),
            __DIR__ . '/../../resources/'       => $this->app->resourcePath(),
        ], 'base');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('view.finder', function (Application $app) {
            return new FileViewFinder($app['files'], [
                $app->resourcePath('default/views'),
                realpath(__DIR__ . '/../../resources/default/views'),
            ]);
        });
    }
}
