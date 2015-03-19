<?php namespace Lavary\Menu\Providers;

interface ProviderInterface {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot();

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register();

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides();

}
