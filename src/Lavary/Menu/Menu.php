<?php namespace Lavary\Menu;

use View;
use Config;
use Response;
use Illuminate\Html\HtmlBuilder;
use Illuminate\Routing\UrlGenerator;
use Illuminate\View\Environment as ViewEnvironment;

class Menu {
	
	/**
	 * The Menu container
	 *
	 * @var array
	 */
	public $menu = array();

	/**
	 * The Menu name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The route group attribute stack.
	 *
	 * @var array
	 */
	protected $groupStack = array();
	
	/**
	* The reserved attributes.
	*
	* @var array
	*/
	protected $reserved = array('route', 'action', 'url', 'prefix', 'pid');

	/**
	* Tags for specifying lists of information in HTML and their childeren
	*
	* @var array
	*/
	protected $htmlLists = array('ul' => 'li', 'ol' => 'li');

	/**
	* The filter callback
	*
	* @var callable
	*/
	protected   $filter;


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
	* @var Illuminate\View\Environment
	*/
	private $environment;
	
	/**
	 * Initializing the menu builder
	 *
	 * @param  \Illuminate\Html\HtmlBuilder  $html
	 * @param  \Illuminate\Routing\UrlGenerator  $url
	 * @param  \Illuminate\View\Environment  $environment
	 * @return void
	 */
	public function __construct(HtmlBuilder $html, UrlGenerator $url, ViewEnvironment $environment)
	{
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
			call_user_func($callback, $this);
			$this->name = $name;		
			
			// We make the menu available in all views
			View::composer('*', function($view){
				$view->with($this->name, $this);
			});

			return $this;
		}
	}

	/**
	 * Adds an item to the menu
	 *
	 * @param  string  $title
	 * @param  string|array  $acion
	 * @return Lavary\Menu\MenuItem $item
	 */
	public function add($title, $action)
	{
		$title      = $title;
		
		$url        = $this->dispatch($action);		
		
		if( is_array($action) )
		{
			$attributes = $this->getAttributes($action); 

		} else {
			
			$attributes = $this->getAttributes();
		}		
		
		$pid        = ( isset($action['pid']) ) ? $action['pid'] : null;
		
		$item       = new MenuItem($this, $title, $url, $attributes, $pid);
	
		array_push($this->menu, $item);
		
		return $item;
	}

	/**
	 * Create a menu group with shared attributes.
	 *
	 * @param  array  $attributes
	 * @param  callable  $closure
	 * @return void
	 */
	public function group($attributes, $closure, $obj = null)
	{
		$this->updateGroupStack($attributes);

		$obj = ( is_null($obj) ) ? $this : $obj;

		// Once we have updated the group stack, we will execute the user Closure and
		// merge in the groups attributes when the route is created. After we have
		// run the callback, we will pop the attributes off of this group stack.
		call_user_func($closure, $obj);

		array_pop($this->groupStack);
	}

	/**
	 * Update the group stack with the given attributes.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	protected function updateGroupStack(array $attributes)
	{
		if (count($this->groupStack) > 0)
		{
			$attributes = $this->mergeWithLastGroup($attributes);
		}

		$this->groupStack[] = $attributes;
	}

	/**
	 * Merge the given array with the last group stack.
	 *
	 * @param  array  $new
	 * @return array
	 */
	protected function mergeWithLastGroup($new)
	{
		return $this->mergeGroup($new, last($this->groupStack));
	}

	/**
	 * Merge the given group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return array
	 */
	protected function mergeGroup($new, $old)
	{
		$new['prefix'] = $this->formatGroupPrefix($new, $old);

		return array_merge_recursive(array_except($old, array('prefix')), $new);
	}

	/**
	 * Format the prefix for the new group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return string
	 */
	protected function formatGroupPrefix($new, $old)
	{
		if (isset($new['prefix']))
		{
			return trim(array_get($old, 'prefix'), '/').'/'.trim($new['prefix'], '/');
		}
		return array_get($old, 'prefix');
	}

	/**
	 * Get the prefix from the last group on the stack.
	 *
	 * @return string
	 */
	protected function getLastGroupPrefix()
	{
		if (count($this->groupStack) > 0)
		{
			return array_get(last($this->groupStack), 'prefix', '');
		}

		return '';
	}

	/**
	 * Prefix the given URI with the last prefix.
	 *
	 * @param  string  $uri
	 * @return string
	 */
	protected function prefix($uri)
	{
		return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
	}

	/**
	 * Get the valid attributes from the options.
	 *
	 * @param  array   $options
	 * @return string
	 */
	protected function getAttributes($options = array())
	{
		if( count($this->groupStack) > 0 ) {
			$options = $this->mergeWithLastGroup($options);
		}

		$attributes = array_except($options, $this->reserved);

		return $attributes;
	}

	/**
	 * Get the form action from the options.
	 *
	 * @param  array|string   $options
	 * @return string|null
	 */
	protected function dispatch($options)
	{
		// We will also check for a "route" or "action" parameter on the array so that
		// developers can easily specify a route or controller action when creating the
		// menus.
		if(!is_array($options))
		{
			return $this->getUrl($options);
		}

		if (isset($options['url']))
		{
			return $this->getUrl($options['url']);
		}

		if (isset($options['route']))
		{
			return $this->getRoute($options['route']);
		}

		// If an action is available, we are attempting to point the link to controller
		// action route. So, we will use the URL generator to get the path to these
		// actions and return them from the method. Otherwise, we'll use current.
		elseif (isset($options['action']))
		{
			return $this->getControllerAction($options['action']);
		}

		return null;
	}

	/**
	 * Get the action for a "url" option.
	 *
	 * @param  array|string  $options
	 * @return string
	 */
	protected function getUrl($options)
	{
		if (is_array($options))
		{
			return $this->url->to($this->getLastGroupPrefix() . '/' . $options[0], array_slice($options, 1));
		}

		return $this->url->to($this->getLastGroupPrefix() . '/' . $options);
	}

	/**
	 * Get the action for a "route" option.
	 *
	 * @param  array|string  $options
	 * @return string
	 */
	protected function getRoute($options)
	{
		if (is_array($options))
		{
			return $this->url->route($options[0], array_slice($options, 1));
		}

		return $this->url->route($options);
	}

	/**
	 * Get the action for an "action" option.
	 *
	 * @param  array|string  $options
	 * @return string
	 */
	protected function getControllerAction($options)
	{
		if (is_array($options))
		{
			return $this->url->action($options[0], array_slice($options, 1));
		}

		return $this->url->action($options);
	}

	/**
	 * Returns items with no parent
	 *
	 * @return array
	 */
	public function roots()
	{
		return $this->whereParent();
	}

	/**
	 * Returns items with the specified parent id
	 *
	 * @param  int $parent
	 * @return array
	 */
	public function whereParent($parent = 0)
	{
		return array_filter($this->menu, function($item) use ($parent){
			if( $item->get_pid() == $parent )
			{
				return true;		
			}
			return false;
		});
	}

	/**
	 * Filter menu items by user callbacks
	 *
	 * @param  callable $callback
	 * @return void
	 */
	public function filter($callback)
	{
		if( is_callable($callback) )
		{
			$this->menu = array_filter($this->menu, $callback);
		}

		return $this;
	}

	/**
	 * Generate the menu items as list items using a recursive function
	 *
	 * @param string $type
	 * @param int $pid
	 * @return string
	 */
	public function render($type = 'ul', $pid = null)
	{
		$items = '';
		$item_tag = ( isset($this->htmlLists[$type]) ) ? $this->htmlLists[$type] : $type;
		
		foreach ($this->whereParent($pid) as $item)
		{
			$items .= "<{$item_tag}{$this->html->attributes($item->attributes)}>" . $item->link();

			if( $item->hasChilderen() )
			{
				$items .= "<{$type}>";
				$items .= $this->render($type, $item->get_id());
				$items .= "</{$type}>";
			}
			
			$items .= "</{$item_tag}>";
		}

		return $items;
	}
		
	/**
	 * Returns the menu as an unordered list.
	 *
	 * @return string
	 */
	public function asUl($attributes = array())
	{
		return "<ul{$this->html->attributes($attributes)}>{$this->render('ul')}</ul>";
	}

	/**
	 * Returns the menu as an ordered list.
	 *
	 * @return string
	 */
	public function asOl($attributes = array())
	{
		return "<ol{$this->html->attributes($attributes)}>{$this->render('ol')}</ol>";
	}

	/**
	 * Returns the menu as div containers
	 *
	 * @return string
	 */
	public function asDiv($attributes = array())
	{
		return "<div{$this->html->attributes($attributes)}>{$this->render('div')}</div>";
	}

	/**
	 * Returns the menu as Bootstrap navbar
	 *
	 * @return string
	 */
	public function asBootstrap($options = array())
	{
		$theme = ( isset($options['inverse']) && $options['inverse'] == true ) ? '-inverse' : null;
		
		$view  = Config::get('laravel-menu::bootstrap-navbar' . $theme);

		return $this->asView($view, 'navbar');		
	}

	/**
	 * Returns the menu as view
	 *
	 * @param string $view
	 * @param string $menu
	 * @return string
	 */
	public function asView($view, $name = 'menu')
	{
		return $this->environment->make($view, array($name => $this));
	}

}
