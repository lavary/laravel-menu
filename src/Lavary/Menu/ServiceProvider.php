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
		if (method_exists($this->app['config'], 'package')) {
			$this->app['config']->package('lavary/laravel-menu', __DIR__ . '/../../config');
		} else {       
			$settings   = $this->app['files']->getRequire(__DIR__ .'/../../config/settings.php');
			$views      = $this->app['files']->getRequire(__DIR__ .'/../../config/views.php');

			$config     = array_merge($settings, $views);

            $this->app['config']->set('lavary/laravel-menu::config', $config);
        }

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
