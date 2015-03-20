<?php namespace Lavary\Menu\Providers;

use Lavary\Menu\Menu;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class Laravel5 extends IlluminateServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes(array(
      __DIR__.'/../../../config/settings.php' => config_path('laravel-menu.php'),
        ));

        $this->loadViewsFrom(__DIR__.'/../../../views', 'laravel-menu');

    // Extending Blade engine
    require_once __DIR__.'/../Extensions/BladeExtension.php';
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $app = $this->app;

        // merge default configs
    $this->mergeConfigFrom(__DIR__.'/../../../config/views.php', 'laravel-menu.views');
        $this->mergeConfigFrom(__DIR__.'/../../../config/settings.php', 'laravel-menu');

        $app['menu'] = $app->share(function ($app) {
            return new Menu($app['config']->get('laravel-menu'));
        });
    }
}
