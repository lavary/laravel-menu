<?php namespace Lavary\Menu;

class Item {
	
	/**
	 * Reference to the menu builder
	 *
	 * @var Lavary\Menu\Menu
	 */
	protected $builder;

	/**
	 * The ID of the menu item
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Item's title 
	 *
	 * @var string
	 */
	public $title;

	/**
	 * Item's title in camelCase
	 *
	 * @var string
	 */
	public $nickname;

	/**
	 * Item's seprator from the rest of the items, if it has any.
	 *
	 * @var array
	 */
	public $divider = array();

	/**
	 * Parent Id of the menu item
	 *
	 * @var int
	 */
	protected $parent;
	
	/**
	 * Extra information attached to the menu item
	 *
	 * @var array
	 */
	protected $data = array();
	
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
     * @param  int  $parent
	 * @param  \Lavary\Menu\Menu  $builder
	 * @return void
	 */
	public function __construct($builder, $id, $title, $options)
	{
		$this->builder     = $builder;
		$this->id          = $id;
		$this->title       = $title;
		$this->nickname    = camel_case($title);
		$this->attributes  = $this->builder->getAttributes($options); 
		$this->parent      = (is_array($options) && isset($options['parent'])) ? $options['parent'] : null;
		
		if(!is_array($options)) {
			
			$path = array('url' => $options);
		}
		elseif( isset($options['plaintext']) && 
				$options['plaintext'] === true ) {
			
			$path = null;

		}
		else {	
			
			$path = array_only($options, 
									 array('url', 'route', 'action', 'secure'));
		} 

		if(!is_null($path)) {
			$path['prefix'] = $this->builder->getLastGroupPrefix();
		}

		$this->link = $path ? new Link($path) : null;

	}

	/**
	 * Creates a sub Item
	 *
	 * @param  string  $title
	 * @param  string|array  $options
	 * @return void
	 */
	public function add($title, $options = '')
	{
		if( !is_array($options) ) {
			$url = $options;
			$options = array();
			$options['url'] = $url;
		}
		
		$options['parent'] = $this->id;
				
		return $this->builder->add( $title, $options );
	}

	/**
	 * Add a plain text item
	 *
	 * @return Lavary\Menu\Item
	 */
	public function text($title, array $options = array())
	{
		$options['parent'] = $this->id;
		
		return $this->builder->text($title, $options);
	}

	/**
	 * Insert a seprator after the item
	 *
	 * @param array $attributes
	 * @return void
	 */
	public function divide($attributes = array()){
		
		$attributes['class'] = Builder::formatGroupClass($attributes, array('class' => 'divider'));
		
		$this->divider = $attributes;
	}


	/**
	 * Group children of the item
	 *
	 * @param  array $attributes
	 * @param  callable $closure
	 * @return void
	 */
	public function group($attributes, $closure)
	{
		$this->builder->group($attributes, $closure, $this);
	}

	/**
	 * Add attributes to the item
	 *
	 * @param  mixed
	 * @return string|Lavary\Menu\Item
	 */
	public function attr()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0])) {
			$this->attributes = array_merge($this->attributes, $args[0]);
			return $this;
		}

		elseif(isset($args[0]) && isset($args[1])) {
			$this->attributes[$args[0]] = $args[1];
			return $this;
		} 

		elseif(isset($args[0])) {
			return isset($this->attributes[$args[0]]) ? $this->attributes[$args[0]] : null;
		}

		return $this->attributes;
	}

	/**
	 * Generate URL for link
	 *
	 * @return string
	 */
	public function url(){
			
			if( isset($this->link->href) ) {
				
				return $this->link->href;			
			}
			
			return $this->builder->dispatch($this->link->path);
	}

	/**
	 * Prepends text or html to the item
	 *
	 * @return Lavary\Menu\Item
	 */
	public function prepend($html)
	{
		$this->title = $html . $this->title;
	
		return $this;
	}

	/**
	 * Appends text or html to the item
	 *
	 * @return Lavary\Menu\Item
	 */
	public function append($html)
	{
		$this->title .= $html;
		
		return $this;
	}

	/**
	 * Checks if the item has any children
	 *
	 * @return boolean
	 */
	public function hasChildren()
	{
		return count($this->builder->whereParent($this->id)) or false;
	}

	/**
	 * Returns childeren of the item
	 *
	 * @return array
	 */
	public function children()
	{
		return $this->builder->whereParent($this->id);
	}

	/**
	 * Make the item active
	 *
	 * @return Lavary\Menu\Item
	 */
	public function active(){
	
		$this->attributes['class'] = Builder::formatGroupClass(array('class' => 'active'), $this->attributes);
		
		return $this;
	}

	/**
	 * Set or get items's meta data
	 *
	 * @param  mixed
	 * @return string|Lavary\Menu\Item
	 */
	public function data()
	{
		$args = func_get_args();

		if(isset($args[0]) && is_array($args[0])) {
			$this->data = array_merge($this->data, array_change_key_case($args[0]));
			return $this;
		}

		elseif(isset($args[0]) && isset($args[1])) {
			$this->data[strtolower($args[0])] = $args[1];
			return $this;
		} 

		elseif(isset($args[0])) {
			return isset($this->data[$args[0]]) ? $this->data[$args[0]] : null;
		}

		return $this->data;
	}

	/**
	 * Search in meta data if an attribute doesn't exist
	 *
	 * @param  string
	 * @return string
	 */
	public function __get($prop){

		if(property_exists($this, $prop)) {
			return $this->$prop;
		}
		
		return $this->data($prop);
	}

}