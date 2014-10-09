<?php namespace Lavary\Menu;

class Menu {

	/**
	* Menu collection
	*
	* @var Illuminate\Support\Collection
	*/
	protected $collection;

	/**
	 * Initializing the menu builder
	 */
	public function __construct()
	{
		// creating a collection for storing menus
		$this->collection = new Collection();
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
			$menu = new Builder($name, $this->loadConf($name));
			
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
	 * Loads and merges configuration data
	 *
	 * @param  string  $name
	 * @return array
	 */
	public function loadConf($name) {
		
		$options = \Config::get('laravel-menu::settings');
		$name    = strtolower($name);
		
		if( isset($options[$name]) && is_array($options[$name]) ) {
			return array_merge($options['default'], $options[$name] );
		}

		return $options['default'];
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


	/**
	 * Return Menu collection 
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getCollection() {
		
		return $this->collection;

	}

	/**
	 * Alias for getCollection
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function all() {
		
		return $this->collection;

	}

}
