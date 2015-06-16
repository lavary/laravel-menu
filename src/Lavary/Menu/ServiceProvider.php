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
		$this->publishes([
            __DIR__.'/../../config/settings.php' => config_path('lavary-menu.php'),
            __DIR__.'/../../config/views.php' => config_path('lavary-menu-views.php'),
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
		 $this->app['menu'] = $this->app->share(function($app){

		 		return new Menu();
		 });
         

		$this->mergeConfigFrom(
			__DIR__.'/../../config/settings.php', 'lavary-menu'
		);

		$this->mergeConfigFrom(
			__DIR__.'/../../config/views.php', 'lavary-menu-views'
		);
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
