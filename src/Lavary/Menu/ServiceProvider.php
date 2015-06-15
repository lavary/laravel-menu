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
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadViewsFrom(
			__DIR__.'/../../views', 'laravel-menu'
		);

		$this->publishes([
			__DIR__ .'/../../config/settings.php' =>
				config_path('laravel-menu/settings.php'),
			__DIR__ .'/../../config/views.php' =>
				config_path('laravel-menu/views.php'),
			__DIR__ .'/../../views/bootstrap-navbar-items.blade.php' =>
				base_path('resources/views/vendor/laravel-menu/bootstrap-navbar-items.blade.php'),
		]);

		// Extending Blade engine
		require_once('Extensions/BladeExtension.php');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__ .'/../../config/settings.php', "laravel-menu.settings"
		);

		$this->mergeConfigFrom(
			__DIR__ .'/../../config/views.php', 'laravel-menu.views'
		);

		$this->app['menu'] = $this->app->share(function($app){

			return new Menu();
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
