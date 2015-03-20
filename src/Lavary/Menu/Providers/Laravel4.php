<?php namespace Lavary\Menu\Providers;

use Lavary\Menu\Menu;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class Laravel4 extends IlluminateServiceProvider
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
        // As guessPackagePath() in Illuminate\Support\ServiceProvider
        // has a fixed number of directories to traverse up and this
        // provider exists in a Providers directory, we need to manually
        // pass the path to the package() method instead.
        $path = realpath(dirname(__FILE__).'/../../../');

        $this->package('lavary/laravel-menu', null, $path);

        // Extending Blade engine
        require_once __DIR__.'/../Extensions/BladeExtension.php';
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $app = $this->app;

        $app['menu'] = $app->share(function ($app) {
            return new Menu($app['config']->get('laravel-menu::settings'));
        });
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
