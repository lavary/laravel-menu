<?php
/**
 * Facade class
 */
namespace Lavary\Menu;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * Class Facade
 *
 * This provides a laravel facade to the Menu class.
 *
 * Facades provide a "static" interface to classes that are
 * available in the application's service container.
 *
 * ```php
 * Menu::make('MyNavBar', function($menu){
 *     $menu->add('Home');
 *     $menu->add('About',    'about');
 *     $menu->add('services', 'services');
 *     $menu->add('Contact',  'contact');
 * });
 * ```
 *
 * @link https://github.com/lavary/laravel-menu
 * @link https://laravel.com/docs/5.2/facades
 */
class Facade extends BaseFacade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'menu';
    }
}
