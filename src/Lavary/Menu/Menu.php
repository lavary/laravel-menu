<?php namespace Lavary\Menu;

use Illuminate\Support\Collection as Collection;

class Menu {

	/**
	* Menu collection
	*
	* @var Illuminate\Support\Collection
	*/
	public $collection;

	/**
	* HTML generator dependency
	*
	* @var Illuminate\Html\HtmlBuilder
	*/
	public $html;
	
	/**
	* The URL generator dependency
	*
	* @var Illuminate\Routing\UrlGenerator
	*/
	protected $url;	
	
	/**
	* The Environment instance
	*
	* @var Illuminate\View\Factory
	*/
	private $environment;

	/**
	 * Initializing the menu builder
	 *
	 * @param  \Illuminate\Html\HtmlBuilder      $html
	 * @param  \Illuminate\Routing\UrlGenerator  $url
	 * @param  \Illuminate\View\Factory          $environment
	 * @return void
	 */
	public function __construct($html, $url, $environment)
	{
		$this->collection = new Collection();
		$this->url  = $url;
		$this->html = $html;
		$this->environment = $environment;
	}


	/**
	 * Create a new menu instance
	 *
	 * @param  string  $name
	 * @param  callable  $callback
	 * @return \Lavary\Menu\Menu
	 */
	public function make($name, $callback)
	{
		if(is_callable($callback))
		{
			$menu = new Builder($this->html, $this->url, $this->environment);
			
			// Registering the items
			call_user_func($callback, $menu);
			
			// Storing each menu instance in the collection
			$this->collection->put($name, $menu);
			
			// Make the instance available in all views
			\View::share($name, $menu);

			return $menu;
		}
	}

	/**
	 * Return Menu instance from the collection by key
	 *
	 * @param  string  $key
	 * @return \Lavary\Menu\Item
	 */
	public function get($key) {
		
		return $this->collection->get($key);

	}

}
