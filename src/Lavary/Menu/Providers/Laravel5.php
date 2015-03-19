<?php namespace Lavary\Menu;

use Illuminate\Support\ServiceProvider;

class Laravel5 extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes(array(
      __DIR__.'/../../../config/settings.php' => config_path('laravel-menu.php'),
		));

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

		// merge default configs
    $this->mergeConfigFrom(__DIR__.'/../../../config/settings.php', 'laravel-menu');
    
		$app['menu'] = $app->share(function ($app) {
			return new Menu;
		});
	}
}
