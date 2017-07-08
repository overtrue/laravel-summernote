<?php

/*
 * This file is part of the overtrue/laravel-summernote.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Hinet\LaravelSummernote;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

/**
 * Class SummernoteServiceProvider.
 */
class SummernoteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router)
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'summernote');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'summernote');

        $this->publishes([
            __DIR__.'/config/summernote.php' => config_path('summernote.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/resources/assets/summernote' => public_path('vendor/summernote'),
        ], 'assets');

        $this->publishes([
            __DIR__.'/resources/views' => base_path('resources/views/vendor/summernote'),
            __DIR__.'/resources/lang' => base_path('resources/lang/vendor/summernote'),
        ], 'resources');

        if (!app()->runningInConsole()) {
            $this->registerRoute($router);
        }
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/summernote.php', 'summernote');
        $this->app->singleton('summernote.storage', function ($app) {
            return new StorageManager(Storage::disk($app['config']->get('summernote.disk', 'public')));
        });
    }

    /**
     * Register routes.
     *
     * @param $router
     */
    protected function registerRoute($router)
    {
        if (!$this->app->routesAreCached()) {
            $router->group(array_merge(['namespace' => __NAMESPACE__], config('summernote.route.options', [])), function ($router) {
                $router->any(config('summernote.route.url', '/summernote/server'), 'UploadController@serve');
            });
        }
    }
}
