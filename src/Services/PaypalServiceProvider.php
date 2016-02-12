<?php

namespace ResultSystems\Paypal\Services;

use Illuminate\Support\ServiceProvider;

class PaypalServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     */
    public function boot()
    {
        $this->registerRoutes();
    }

    public function register()
    {
        $this->registerConfig();
        $this->publishMigrations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../config.php' => config_path('paypal.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config.php', 'paypal'
        );
    }

    protected function registerRoutes()
    {
        require __DIR__.'/../Http/routes.php';
    }

    /**
     * Publish migration file.
     */
    private function publishMigrations()
    {
        $this->publishes([__DIR__.'/../migrations/' => base_path('database/migrations')], 'migrations');
    }
}
