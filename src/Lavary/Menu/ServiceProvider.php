<?php
/**
 * Lavary Menu ServiceProvider
 */
namespace Lavary\Menu;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Lavary Menu ServiceProvider
 *
 * Service providers are the central place of all Laravel application bootstrapping.
 * Your own application, as well as all of Laravel's core services are bootstrapped
 * via service providers.
 *
 * ### Functionality
 *
 * * Registers the settings and views.
 * * Creates a menu singleton that refers to the Menu class.
 * * Provides an extension to he blade engine.
 *
 * @see  Illuminate\Support\ServiceProvider
 * @link http://laravel.com/docs/5.1/providers
 */
class ServiceProvider extends BaseServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * Within the register method, you should only bind things into the
     * service container. You should never attempt to register any event
     * listeners, routes, or any other piece of functionality within the
     * register method.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/settings.php', 'laravel-menu.settings');
        $this->mergeConfigFrom(__DIR__ . '/../../config/views.php', 'laravel-menu.views');

        $this->app->singleton('menu', function ($app) {
            return new Menu;
         });
    }

    /**
     * Boot the service provider.
     *
     * This method is called after all other service providers have
     * been registered, meaning you have access to all other services
     * that have been registered by the framework.
     *
     * @return void
     */
    public function boot()
    {
        // Extending Blade engine
        require_once('blade/lm-attrs.php');

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-menu');

        $this->publishes([
            __DIR__ . '/resources/views'           => base_path('resources/views/vendor/laravel-menu'),
            __DIR__ . '/../../config/settings.php' => config_path('laravel-menu/settings.php'),
            __DIR__ . '/../../config/views.php'    => config_path('laravel-menu/views.php'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('menu');
    }
}
