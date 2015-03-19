<?php namespace Lavary\Menu;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Provider for version
	 *
	 * @var \Illuminate\Support\ServiceProvider
	 */
	protected $provider;

	/**
	 * Create a new service provider instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 * @return void
	 */
	public function __construct(Application $app)
	{
		parent::__construct($app);

		$this->provider = $this->getProvider();
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		return $this->provider->boot();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		return $this->provider->register();
	}

	/**
	 * Return ServiceProvider suitable for Laravel version
	 *
	 * @return \Lavary\Menu\Providers\ProviderInterface
	 */
	private function getProvider()
	{
		$provider = version_compare(Application::VERSION, '5.0', '<')
			? '\Lavary\Menu\Providers\Laravel4'
			: '\Lavary\Menu\Providers\Laravel5';

		return new $provider($this->app);
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