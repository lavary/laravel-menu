<?php namespace Lavary\Menu\Providers;

use Lavary\Menu\Menu;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class Laravel4 extends IlluminateServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('lavary/laravel-menu');

		// Extending Blade engine
		require_once(__DIR__.'/../Extensions/BladeExtension.php');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$app = $this->app;

		$app['menu'] = $app->share(function($app){
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
