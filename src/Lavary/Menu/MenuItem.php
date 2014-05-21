<?php namespace Lavary\Menu;

class MenuItem {
	
	/**
	 * Reference to the Menu
	 *
	 * @var Lavary\Menu\Menu
	 */
	private $ref;

	/**
	 * The ID of the menu item
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Parent Id of the menu item
	 *
	 * @var int
	 */
	private $pid;

	/**
	 * The menu item title
	 *
	 * @var string
	 */
	public $title;
	
	/**
	 * Extra information attached to the menu item
	 *
	 * @var array
	 */
	public $meta;
	
	/**
	 * Attributes of menu item
	 *
	 * @var array
	 */
	public $attributes = array();

	/**
	 * Creates a new Lavary\Menu\MenuItem instance.
	 *
	 * @param  string  $title
	 * @param  string  $url
     * @param  array  $attributes
     * @param  int  $pid
	 * @param  \Lavary\Menu\Menu  $ref
	 * @return void
	 */
	public function __construct($ref, $title, $url, $attributes = array(), $pid = 0)
	{
		$this->ref   = $ref;
		$this->id    = $this->id();
		$this->pid   = $pid;
		$this->title = $title;
		$this->link   = new Link($title, $url);
		$this->attributes  = $attributes;
	}

	/**
	 * Creates a sub Item
	 *
	 * @param  string  $title
	 * @param  string|array  $action
	 * @return void
	 */
	public function add($title, $action)
	{
		if( !is_array($action) ) {
			$url = $action;
			$action = array();
			$action['url'] = $url;
		}
		
		$action['pid'] = $this->id;
				
		return $this->ref->add( $title, $action );
	}

	/**
	 * Group childeren of the item
	 *
	 * @param  array $attributes
	 * @param  callable $closure
	 * @return void
	 */
	public function group($attributes, $closure)
	{
		$this->ref->group($attributes, $closure, $this);
	}

	/**
	 * Returns Item attributes
	 *
	 * @return array
	 */
	public function get_attributes()
	{
		return $this->attributes;
	}

	/**
	 * Returns Item attributes as string
	 *
	 * @return string
	 */
	public function attributes()
	{
		return $this->ref->html->attributes($this->attributes);
	}

	/**
	 * Generate an integer identifier for the item
	 *
	 * @return int
	 */
	protected function id()
	{
		return count($this->ref->menu) + 1;
	}

	/**
	 * Returns Item's identifier
	 *
	 * @return int
	 */
	public function get_id()
	{
		return $this->id;
	}

	/**
	 * Returns Item's link text
	 *
	 * @return string
	 */
	public function get_title()
	{
		return $this->link->get_text();
	}

	/**
	 * Returns Item's link url
	 *
	 * @return string
	 */
	public function get_url()
	{
		return $this->link->get_url();
	}

	/**
	 * Returns Item's parent id
	 *
	 * @return int
	 */
	public function get_pid()
	{
		return $this->pid;
	}

	/**
	 * Checks if the item has childeren
	 *
	 * @return boolean
	 */
	public function hasChilderen()
	{
		return (count($this->ref->whereParent($this->id))) ? true : false;
	}

	/**
	 * Returns childeren of the item
	 *
	 * @return array
	 */
	public function childeren()
	{
		return $this->ref->whereParent($this->id);
	}

	/**
	 * Creates a hyper link for the item
	 *
	 * @return string
	 */
	public function link()
	{
		return "<a href=\"{$this->link->get_url()}\"{$this->ref->html->attributes($this->link->attributes)}>{$this->link->get_text()}</a>";
	}

	/**
	 * Set or get items's meta data
	 *
	 * @return string|MenuItem
	 */
	public function meta($key, $value = null)
	{
		if( !is_null($value) ) {
			$this->meta[$key] = $value;
			
			return $this;
		}

		return $this->meta[$key];
	}

}