<?php

namespace Easy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class EasyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'easy');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'easy');
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/easy'),
        ]);
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('easy.php'),
            ], 'config');
        }
    }

    protected function registerRoutes()
    {
        Route::group($this->routeApiConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    protected function routeApiConfiguration(): array
    {
        return [
            'prefix' => config('easy.api_prefix'),
            'middleware' => config('easy.api_middleware'),
        ];
    }
}
