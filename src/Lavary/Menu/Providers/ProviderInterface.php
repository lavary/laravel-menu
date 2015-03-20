<?php namespace Lavary\Menu\Providers;

interface ProviderInterface
{
    /**
     * Bootstrap the application events.
     */
    public function boot();

    /**
     * Register the service provider.
     */
    public function register();

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides();
}
