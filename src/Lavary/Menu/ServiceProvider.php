<?php

namespace Lavary\Menu;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Support\Facades\Blade;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/settings.php', 'laravel-menu.settings');
        $this->mergeConfigFrom(__DIR__.'/../../config/views.php', 'laravel-menu.views');

        $this->app->singleton(Menu::class, function ($app) {
            return new Menu();
        });
    }

    // Patterns and Replace string for lm-attr
    // Remove with next major version
    const LM_ATTRS_PATTERN = '/(\s*)@lm-attrs\s*\((\$[^)]+)\)/';
    const LM_ATTRS_REPLACE = '$1<?php $lm_attrs = $2->attr(); ob_start(); ?>';

    // Patterns and Replace string for lm-endattr
    // Remove with next major version
    const LM_ENDATTRS_PATTERN = '/(?<!\w)(\s*)@lm-endattrs(\s*)/';
    const LM_ENDATTRS_REPLACE = '$1<?php echo \Lavary\Menu\Builder::mergeStatic(ob_get_clean(), $lm_attrs); ?>$2';

    /*
     * Extending Blade engine. Remove with next major version
     *
     * @deprecated
     * @return void
     */
    protected function bladeExtensions()
    {
        Blade::extend(function ($view, $compiler) {
            if (preg_match(self::LM_ATTRS_PATTERN, $view)) {
              \Log::debug("laravel-menu: @lm-attrs/@lm-endattrs is deprecated. Please switch to @lm_attrs and @lm_endattrs");
            }
            return preg_replace(self::LM_ATTRS_PATTERN, self::LM_ATTRS_REPLACE, $view);
        });

        Blade::extend(function ($view, $compiler) {
            return preg_replace(self::LM_ENDATTRS_PATTERN, self::LM_ENDATTRS_REPLACE, $view);
        });
    }

    /*
     * Adding custom Blade directives.
     */
    protected function bladeDirectives()
    {
        /*
         * Buffers the output if there's any.
         * The output will be passed to mergeStatic()
         * where it is merged with item's attributes
         */
        Blade::directive('lm_attrs', function ($expression) {
            return '<?php $lm_attrs = ' . $expression . '->attr(); ob_start(); ?>';
        });

        /*
         * Reads the buffer data using ob_get_clean()
         * and passes it to MergeStatic().
         * mergeStatic() takes the static string,
         * converts it into a normal array and merges it with others.
         */
        Blade::directive('lm_endattrs', function ($expression) {
            return '<?php echo \Lavary\Menu\Builder::mergeStatic(ob_get_clean(), $lm_attrs); ?>';
        });
    }

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->bladeDirectives();
        $this->bladeExtensions();

        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-menu');

        $this->publishes([
            __DIR__.'/resources/views' => base_path('resources/views/vendor/laravel-menu'),
            __DIR__.'/../../config/settings.php' => config_path('laravel-menu/settings.php'),
            __DIR__.'/../../config/views.php' => config_path('laravel-menu/views.php'),
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Menu::class];
    }
}
