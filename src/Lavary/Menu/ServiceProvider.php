<?php namespace Lavary\Menu;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		 $this->mergeConfigFrom(__DIR__ . '/../../config/settings.php', 'laravel-menu.settings');
		 $this->mergeConfigFrom(__DIR__ . '/../../config/views.php'   , 'laravel-menu.views');
		 
		 $this->app->singleton('menu', function($app) {
		 	return new Menu;
		 });            
	}

	/**
	 * Bootstrap the application events.
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
