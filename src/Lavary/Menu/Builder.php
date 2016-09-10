<?php namespace Lavary\Menu;

class Builder {
	
	/**
	 * The items container
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The Menu name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The Menu configuration data
	 *
	 * @var array
	 */
	protected $conf;

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
	protected $reserved = array('route', 'action', 'url', 'prefix', 'parent', 'secure', 'raw');
	
	/**
	 * Initializing the menu manager
	 *
	 * @return void
	 */
	public function __construct($name, $conf)
	{
		$this->name = $name;

		// creating a laravel collection ofr storing enu items
		$this->items = new Collection;

		$this->conf = $conf;

	}

	/**
	 * Adds an item to the menu
	 *
	 * @param  string  $title
	 * @param  string|array  $acion
	 * @return Lavary\Menu\Item $item
	 */
	public function add($title, $options = '')
	{
	
		$id = isset($options['id']) ? $options['id'] : $this->id();

		$item = new Item($this, $id, $title, $options);
                      
		$this->items->push($item);
		
		return $item;
	}

	/**
	 * Generate an integer identifier for each new item
	 *
	 * @return int
	 */
	protected function id()
	{
		return uniqid(rand());
	}

	/**
	 * Add raw content
	 *
	 * @return Lavary\Menu\Item
	 */
	public function raw($title, array $options = array())
	{
		$options['raw'] = true;
		
		return $this->add($title, $options);
	}

	/**
	 * Returns menu item by name
	 *
	 * @return Lavary\Menu\Item
	 */
	public function get($title){
		
		return $this->whereNickname($title)
		
					->first();		
	}

	/**
	 * Returns menu item by Id
	 *
	 * @return Lavary\Menu\Item
	 */
	public function find($id){
		
		return $this->whereId($id)
		
					->first();		
	}

	
	/**
	 * Return all items in the collection
	 *
	 * @return array
	 */
	public function all(){
		
		return $this->items;
	
	}

	/**
	 * Return the first item in the collection
	 *
	 * @return Lavary\Menu\Item
	 */
	public function first(){
		
		return $this->items->first();
	
	}

	/**
	 * Return the last item in the collection
	 *
	 * @return Lavary\Menu\Item
	 */
	public function last(){
		
		return $this->items->last();	
	}

	/**
	 * Returns menu item by name
	 *
	 * @return Lavary\Menu\Item
	 */
	public function item($title){
		
		return $this->whereNickname($title)
		
					->first();		
	}

	/**
	 * Insert a separator after the item
	 *
	 * @param  array $attributes
	 * @return void
	 */
	public function divide(array $attributes = array()){
		
		$attributes['class'] = self::formatGroupClass(array('class' => 'divider'), $attributes);
		
		$this->items->last()->divider = $attributes;

	}

	/**
	 * Create a menu group with shared attributes.
	 *
	 * @param  array  $attributes
	 * @param  callable  $closure
	 * @return void
	 */
	public function group($attributes, $closure)
	{
		$this->updateGroupStack($attributes);

		// Once we have updated the group stack, we will execute the user Closure and
		// merge in the groups attributes when the item is created. After we have
		// run the callback, we will pop the attributes off of this group stack.
		call_user_func($closure, $this);

		array_pop($this->groupStack);
	}

	/**
	 * Update the group stack with the given attributes.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	protected function updateGroupStack(array $attributes = array())
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
		return self::mergeGroup($new, last($this->groupStack));
	}

	/**
	 * Merge the given group attributes.	
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return array
	 */
	protected static function mergeGroup($new, $old)
	{
		$new['prefix'] = self::formatGroupPrefix($new, $old);
		
		$new['class']  = self::formatGroupClass($new, $old);
		
		return array_merge(array_except($old, array('prefix', 'class')), $new);
	}

	/**
	 * Format the prefix for the new group attributes.
	 *
	 * @param  array  $new
	 * @param  array  $old
	 * @return string
	 */
	public static function formatGroupPrefix($new, $old)
	{
		if (isset($new['prefix']))
		{
			return trim(array_get($old, 'prefix'), '/') . '/' . trim($new['prefix'], '/');
		}
		return array_get($old, 'prefix');
	}

	/**
	 * Get the prefix from the last group on the stack.
	 *
	 * @return string
	 */
	public function getLastGroupPrefix()
	{
		if (count($this->groupStack) > 0)
		{

			return array_get(last($this->groupStack), 'prefix', '');
		}

		return null;
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
	public static function formatGroupClass($new, $old) {
		
		if(isset($new['class'])) {
			
			$classes = trim(trim(array_get($old, 'class')) . ' ' . trim(array_get($new, 'class')));
			
			return implode(' ', array_unique(explode(' ', $classes)));
		}
		return array_get($old, 'class');

	}

	/**
	 * Get the valid attributes from the options.
	 *
	 * @param  array   $options
	 * @return array
	 */
	public function extractAttributes($options = array())
	{
		if(!is_array($options)) {
			$options = array();
		}
			
		if( count($this->groupStack) > 0 ) {
			$options = $this->mergeWithLastGroup($options);
		}

		return array_except($options, $this->reserved);
		
	}

	/**
	 * Get the form action from the options.
	 *
	 * @return string
	 */
	public function dispatch($options)
	{
		// We will also check for a "route" or "action" parameter on the array so that
		// developers can easily specify a route or controller action when creating the
		// menus.
		if (isset($options['url']))
		{
			return $this->getUrl($options);
		}

		elseif (isset($options['route']))
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
		foreach($options as $key => $value) {
			$$key = $value;
		}
		
		$secure = (isset($options['secure']) && $options['secure'] === true) ? true : false;

		if (is_array($url))
		{
			if( self::isAbs($url[0]) ){

				return $url[0];

			}

			return \URL::to($prefix . '/' . $url[0], array_slice($url, 1), $secure);
		}
		
		if( self::isAbs($url) ){

			return $url;

		}
		return \URL::to($prefix . '/' . $url, array(), $secure);
	}

	/**
	 * Check if the given url is an absolute url.
	 *
	 * @param  string  $url
	 * @return boolean
	 */
	public static function isAbs($url)
	{
		return parse_url($url, PHP_URL_SCHEME) or false;		
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
			return \URL::route($options[0], array_slice($options, 1));
		}

		return \URL::route($options);
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
			return \URL::action($options[0], array_slice($options, 1));
		}

		return \URL::action($options);
	}

	/**
	 * Returns items with no parent
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function roots()
	{
		return $this->whereParent();
	}

	/**
	 * Filter menu items by user callbacks
	 *
	 * @param  callable $callback
	 *
	 * @return Lavary\Menu\Builder
	 */
	public function filter($callback)
	{
		if( is_callable($callback) ) {
	
			$this->items = $this->items->filter($callback);
		}

		return $this;
	}

	/**
	 * Sorts the menu based on user's callable
	 *
	 * @param string|callable $sort_type
	 * @return Lavary\Menu\Builder
	 */
	public function sortBy($sort_by, $sort_type = 'asc'){

		if(is_callable($sort_by)) {

			$rslt = call_user_func($sort_by, $this->items->toArray());

			if(!is_array($rslt)) {
				$rslt = array($rslt);
			}

			$this->items = new Collection($rslt);

		}
		
		// running the sort proccess on the sortable items
		$this->items	= $this->items->sort(function ($f, $s) use ($sort_by, $sort_type) {
			
			$f = $f->$sort_by;
			$s = $s->$sort_by;
			
			if( $f == $s ) {
				return 0;
			}

			if( $sort_type == 'asc' ) { 
				return $f > $s ? 1 : -1;
			}
			
			return $f < $s ? 1 : -1;	

		});

		return $this;

	}

	
	/**
	 * Generate the menu items as list items using a recursive function
	 *
	 * @param string $type
	 * @param int $parent
	 * @return string
	 */
	public function render($type = 'ul', $parent = null, $childrenAttributes = array())
	{
		$items = '';
		
		$item_tag = in_array($type, array('ul', 'ol')) ? 'li' : $type;
		
		foreach ($this->whereParent($parent) as $item)
		{
			$items  .= '<' . $item_tag . self::attributes($item->attr()) . '>';

			if($item->link) {
				$items .= '<a' . self::attributes($item->link->attr()) . ' href="' . $item->url() . '">' . $item->title . '</a>';
			} else {
				$items .= $item->title;
			}
					
			if( $item->hasChildren() ) {
				$items .= '<' . $type . self::attributes($childrenAttributes) . '>';
				$items .= $this->render($type, $item->id);
				$items .= "</{$type}>";
			}
			
			$items .= "</{$item_tag}>";

			if($item->divider) {
				$items .= '<' . $item_tag . self::attributes($item->divider) . '></' . $item_tag . '>';
			}
		}

		return $items;
	}
		
	/**
	 * Returns the menu as an unordered list.
	 *
	 * @return string
	 */
	public function asUl($attributes = array(), $childrenAttributes = array())
	{
		return '<ul' . self::attributes($attributes) . '>' . $this->render('ul', null, $childrenAttributes) . '</ul>';
	}

	/**
	 * Returns the menu as an ordered list.
	 *
	 * @return string
	 */
	public function asOl($attributes = array(), $childrenAttributes = array())
	{
		return '<ol' . self::attributes($attributes) . '>' . $this->render('ol', null, $childrenAttributes) . '</ol>';
	}

	/**
	 * Returns the menu as div containers
	 *
	 * @return string
	 */
	public function asDiv($attributes = array(), $childrenAttributes = array())
	{
		return '<div' . self::attributes($attributes) . '>' . $this->render('div', null, $childrenAttributes) . '</div>';
	}

	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param  array  $attributes
	 * @return string
	 */
	public static function attributes($attributes)
	{
		$html = array();
		
		foreach ((array) $attributes as $key => $value)
		{
			$element = self::attributeElement($key, $value);
			if ( ! is_null($element)) $html[] = $element;
		}
		return count($html) > 0 ? ' ' . implode(' ', $html) : '';
	}
	
	/**
	 * Build a single attribute element.
	 *
	 * @param  string  $key
	 * @param  string  $value
	 * @return string
	 */
	protected static function attributeElement($key, $value)
	{
		if (is_numeric($key)) $key = $value;
		if ( ! is_null($value)) return $key . '="' . e($value). '"';
	}

	/**
	 * Return configuration value by key
	 * @param string $key
	 *
	 * @return string
	 */
	public function conf($key) {

		return $this->conf[$key];
	}

	

	/**
	 * Merge item's attributes with a static string of attributes
	 *
	 * @param string $attributes
	 *
	 * @return string
	 */
	public static function mergeStatic($new = null, array $old = array()) {
		
		// Parses the string into an associative array
		parse_str(preg_replace('/\s*([\w-]+)\s*=\s*"([^"]+)"/', '$1=$2&',  $new), $attrs);

        // Merge classes
		$attrs['class']  = self::formatGroupClass($attrs, $old);

		// Merging new and old array and parse it as a string
		return self::attributes(array_merge(array_except($old, array('class')), $attrs));
	}

	/**
	 * Filter items recursively
	 *
	 * @param string $attribute
	 * @param mixed  $value
	 *
	 * @return Lavary\Menu\Collection
	 */
	public function filterRecursive($attribute, $value){

		$collection = new Collection;
		
		// Iterate over all the items in the main collection
		$this->items->each( function ($item) use ($attribute, $value, &$collection) {
			
			if (!$this->hasProperty($attribute)) {
				return false;
			}
			
			if( $item->$attribute == $value ) {
				
				$collection->push($item);
				
				// Check if item has any children
				if( $item->hasChildren() ) {
					
					$collection = $collection->merge( $this->filterRecursive($attribute, $item->id) );
				}
			}

		});

		return $collection;
	}

	/**
	 * Search the menu based on an attribute
	 *
	 * @param string $method
	 * @param array  $args
	 *
	 * @return Lavary\Menu\Item
	 */
	public function __call($method, $args)
	{
		preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $method, $matches);
		
		if($matches) {
			$attribute = strtolower($matches[1]);
		} else {
			return false;
		}

		$value     = $args ? $args[0] : null;
		$recursive = isset($args[1]) ? $args[1] : false;
		
		if( $recursive ) {
			return $this->filterRecursive($attribute, $value);
		} 

		return $this->items->filter(function($item) use ($attribute, $value) {

			if (!$item->hasProperty($attribute)) {
				return false;
			}
			
			if($item->$attribute == $value) {
				return true;
			} 
			
			return false;

		})->values();

	}

	/**
	 * Returns menu item by name
	 *
	 * @return Lavary\Menu\Item
	 */
	public function __get($prop){
		
		if(property_exists($this, $prop)) {

			return $this->$prop;
		}
		
		return $this->whereNickname($prop)
		
					->first();		
	}

}
