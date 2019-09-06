<?php

namespace Lavary\Menu;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;

class Builder
{
    /**
     * The items container.
     *
     * @var array
     */
    protected $items;

    /**
     * The Menu name.
     *
     * @var string
     */
    protected $name;

    /**
     * The Menu configuration data.
     *
     * @var array
     */
    protected $conf;

    /**
     * The route group attribute stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * The reserved attributes.
     *
     * @var array
     */
    protected $reserved = ['route', 'action', 'url', 'prefix', 'parent', 'secure', 'raw'];

    /**
     * Initializing the menu manager.
     */
    public function __construct($name, $conf)
    {
        $this->name = $name;

        // creating a laravel collection for storing menu items
        $this->items = new Collection();

        $this->conf = $conf;
    }

    /**
     * Adds an item to the menu.
     *
     * @param string       $title
     * @param string|array $options
     *
     * @return Item $item
     */
    public function add($title, $options = '')
    {
        $id = isset($options['id']) ? $options['id'] : $this->id();

        $item = new Item($this, $id, $title, $options);

        $this->items->push($item);

        return $item;
    }

    /**
     * Generate an integer identifier for each new item.
     *
     * @return string
     */
    protected function id()
    {
        // Issue #170: Use more_entropy otherwise usleep(1) is called.
        // Issue #197: The ID was not a viable document element ID value due to the period.
        return str_replace('.', '', uniqid('id-', true));
    }

    /**
     * Add raw content.
     *
     * @param $title
     * @param array $options
     *
     * @return Item
     */
    public function raw($title, array $options = [])
    {
        $options['raw'] = true;

        return $this->add($title, $options);
    }

    /**
     * Returns menu item by name.
     *
     * @return Item
     */
    public function get($title)
    {
        return $this->whereNickname($title)->first();
    }

    /**
     * Returns menu item by Id.
     *
     * @return Item
     */
    public function find($id)
    {
        return $this->whereId($id)->first();
    }

    /**
     * Return all items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Return the first item in the collection.
     *
     * @return Item
     */
    public function first()
    {
        return $this->items->first();
    }

    /**
     * Return the last item in the collection.
     *
     * @return Item
     */
    public function last()
    {
        return $this->items->last();
    }

    /**
     * Returns menu item by name.
     *
     * @param string $title
     *
     * @return Item
     */
    public function item($title)
    {
        return $this->whereNickname($title)->first();
    }

    /**
     * Returns the first item marked as active.
     *
     * @return Item
     */
    public function active()
    {
        return $this->whereActive(true)->first();
    }

    /**
     * Insert a separator after the item.
     *
     * @param array $attributes
     */
    public function divide(array $attributes = [])
    {
        $attributes['class'] = self::formatGroupClass(array('class' => 'divider'), $attributes);

        $this->items->last()->divider = $attributes;
    }

    /**
     * Create a menu group with shared attributes.
     *
     * @param array    $attributes
     * @param callable $closure
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
     * @param array $attributes
     */
    protected function updateGroupStack(array $attributes = [])
    {
        if (count($this->groupStack) > 0) {
            $attributes = $this->mergeWithLastGroup($attributes);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Merge the given array with the last group stack.
     *
     * @param array $new
     *
     * @return array
     */
    protected function mergeWithLastGroup($new)
    {
        return self::mergeGroup($new, last($this->groupStack));
    }

    /**
     * Merge the given group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    protected static function mergeGroup($new, $old)
    {
        $new['prefix'] = self::formatGroupPrefix($new, $old);

        $new['class'] = self::formatGroupClass($new, $old);

        return array_merge(Arr::except($old, array('prefix', 'class')), $new);
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return string
     */
    public static function formatGroupPrefix($new, $old)
    {
        if (isset($new['prefix'])) {
            return trim(Arr::get($old, 'prefix'), '/').'/'.trim($new['prefix'], '/');
        }

        return Arr::get($old, 'prefix');
    }

    /**
     * Get the prefix from the last group on the stack.
     *
     * @return string
     */
    public function getLastGroupPrefix()
    {
        if (count($this->groupStack) > 0) {
            return Arr::get(last($this->groupStack), 'prefix', '');
        }

        return null;
    }

    /**
     * Prefix the given URI with the last prefix.
     *
     * @param string $uri
     *
     * @return string
     */
    protected function prefix($uri)
    {
        return trim(trim($this->getLastGroupPrefix(), '/').'/'.trim($uri, '/'), '/') ?: '/';
    }

    /**
     * Get the valid attributes from the options.
     *
     * @param array $new
     * @param array $old
     *
     * @return string
     */
    public static function formatGroupClass($new, $old)
    {
        if (isset($new['class'])) {
            $classes = trim(trim(Arr::get($old, 'class')).' '.trim(Arr::get($new, 'class')));

            return implode(' ', array_unique(explode(' ', $classes)));
        }

        return Arr::get($old, 'class');
    }

    /**
     * Get the valid attributes from the options.
     *
     * @param array $options
     *
     * @return array
     */
    public function extractAttributes($options = [])
    {
        if (!is_array($options)) {
            $options = [];
        }

        if (count($this->groupStack) > 0) {
            $options = $this->mergeWithLastGroup($options);
        }

        return Arr::except($options, $this->reserved);
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
        if (isset($options['url'])) {
            return $this->getUrl($options);
        } elseif (isset($options['route'])) {
            return $this->getRoute($options['route']);
        }

        // If an action is available, we are attempting to point the link to controller
        // action route. So, we will use the URL generator to get the path to these
        // actions and return them from the method. Otherwise, we'll use current.
        elseif (isset($options['action'])) {
            return $this->getControllerAction($options['action']);
        }

        return null;
    }

    /**
     * Get the action for a "url" option.
     *
     * @param array|string $options
     *
     * @return string
     */
    protected function getUrl($options)
    {
        foreach ($options as $key => $value) {
            $$key = $value;
        }

        $secure = null;
        if (isset($options['secure'])) {
            $secure = true === $options['secure'] ? true : false;
        }

        if (is_array($url)) {
            if (self::isAbs($url[0])) {
                return $url[0];
            }

            return URL::to($prefix.'/'.$url[0], array_slice($url, 1), $secure);
        }

        if (self::isAbs($url)) {
            return $url;
        }

        return URL::to($prefix.'/'.$url, [], $secure);
    }

    /**
     * Check if the given url is an absolute url.
     *
     * @param string $url
     *
     * @return bool
     */
    public static function isAbs($url)
    {
        return parse_url($url, PHP_URL_SCHEME) or false;
    }

    /**
     * Get the action for a "route" option.
     *
     * @param array|string $options
     *
     * @return string
     */
    protected function getRoute($options)
    {
        if (is_array($options)) {
            return URL::route($options[0], array_slice($options, 1));
        }

        return URL::route($options);
    }

    /**
     * Get the action for an "action" option.
     *
     * @param array|string $options
     *
     * @return string
     */
    protected function getControllerAction($options)
    {
        if (is_array($options)) {
            return URL::action($options[0], array_slice($options, 1));
        }

        return URL::action($options);
    }

    /**
     * Returns items with no parent.
     *
     * @return \Illuminate\Support\Collection
     */
    public function roots()
    {
        return $this->whereParent();
    }

    /**
     * Filter menu items by user callbacks.
     *
     * @param callable $callback
     *
     * @return Builder
     */
    public function filter($callback)
    {
        if (is_callable($callback)) {
            $this->items = $this->items->filter($callback);
        }

        return $this;
    }

    /**
     * Sorts the menu based on user's callable.
     *
     * @param string|callable $sort_type
     *
     * @return Builder
     */
    public function sortBy($sort_by, $sort_type = 'asc')
    {
        if (is_callable($sort_by)) {
            $rslt = call_user_func($sort_by, $this->items->toArray());

            if (!is_array($rslt)) {
                $rslt = array($rslt);
            }

            $this->items = new Collection($rslt);
            return $this;
        }

        // running the sort proccess on the sortable items
        $this->items = $this->items->sort(function ($f, $s) use ($sort_by, $sort_type) {
            $f = $f->$sort_by;
            $s = $s->$sort_by;

            if ($f == $s) {
                return 0;
            }

            if ('asc' == $sort_type) {
                return $f > $s ? 1 : -1;
            }

            return $f < $s ? 1 : -1;
        });

        return $this;
    }

    /**
     * Creates a new Builder instance with the given name and collection.
     *
     * @param $name
     * @param Collection $collection
     *
     * @return Builder
     */
    public function spawn($name, Collection $collection)
    {
        $nb = new self($name, $this->conf);
        $nb->takeCollection($collection);

        return $nb;
    }

    /**
     * Takes an entire collection and stores it as the items.
     *
     * @param Collection $collection
     */
    public function takeCollection(Collection $collection)
    {
        $this->items = $collection;
    }

    /**
     * Returns a new builder of just the top level menu items.
     *
     * @return Builder
     */
    public function topMenu()
    {
        return $this->spawn('topLevel', $this->roots());
    }

    /**
     * Returns a new builder with the active items children.
     *
     * @return Builder
     */
    public function subMenu()
    {
        $nb = $this->spawn('subMenu', new Collection());

        $subs = $this->active()->children();
        foreach ($subs as $s) {
            $nb->add($s->title, $s->url());
        }

        return $nb;
    }

    /**
     * Returns a new builder with siblings of the active item.
     *
     * @return Builder
     */
    public function siblingMenu()
    {
        $nb = $this->spawn('siblingMenu', new Collection());

        $parent = $this->active()->parent();
        if ($parent) {
            $siblings = $parent->children();
        } else {
            $siblings = $this->roots();
        }

        if ($siblings->count() > 1) {
            foreach ($siblings as $s) {
                $nb->add($s->title, $s->url());
            }
        }

        return $nb;
    }

    /**
     * Returns a new builder with all of the parents of the active item.
     *
     * @return Builder
     */
    public function crumbMenu()
    {
        $nb = $this->spawn('crumbMenu', new Collection());

        $item = $this->active();
        $items = [$item];
        while ($item->hasParent()) {
            $item = $item->parent();
            array_unshift($items, $item);
        }

        foreach ($items as $item) {
            $nb->add($item->title, $item->url());
        }

        return $nb;
    }

    /**
     * Generate the menu items as list items using a recursive function.
     *
     * @param string   $type
     * @param int      $parent
     * @param array    $children_attributes
     * @param array    $item_attributes
     * @param callable $item_after_calback
     * @param array    $item_after_calback_params
     *
     * @return string
     */
    public function render($type = 'ul', $parent = null, $children_attributes = [], $item_attributes = [], $item_after_calback = null, $item_after_calback_params = [])
    {
        $items = '';

        $item_tag = in_array($type, array('ul', 'ol')) ? 'li' : $type;

        foreach ($this->whereParent($parent) as $item) {
            if ($item->link) {
                $link_attr = $item->link->attr();
                if (is_callable($item_after_calback)) {
                    call_user_func_array($item_after_calback, [
                        $item,
                        &$children_attributes,
                        &$item_attributes,
                        &$link_attr,
                        &$item_after_calback_params,
                    ]);
                }
            }
            $items .= '<'.$item_tag.self::attributes($item->attr() + $item_attributes).'>';

            if ($item->link) {
                $items .= $item->beforeHTML.'<a'.self::attributes($link_attr).(!empty($item->url()) ? ' href="'.$item->url().'"' : '').'>'.$item->title.'</a>'.$item->afterHTML;
            } else {
                $items .= $item->title;
            }

            if ($item->hasChildren()) {
                $items .= '<'.$type.self::attributes($children_attributes).'>';
                // Recursive call to children.
                $items .= $this->render($type, $item->id, $children_attributes, $item_attributes, $item_after_calback, $item_after_calback_params);
                $items .= "</{$type}>";
            }

            $items .= "</{$item_tag}>";

            if ($item->divider) {
                $items .= '<'.$item_tag.self::attributes($item->divider).'></'.$item_tag.'>';
            }
        }

        return $items;
    }

    /**
     * Returns the menu as an unordered list.
     *
     * @param array    $attributes
     * @param array    $children_attributes
     * @param array    $item_attributes
     * @param callable $item_after_calback
     * @param array    $item_after_calback_params
     *
     * @return string
     */
    public function asUl($attributes = [], $children_attributes = [], $item_attributes = [], $item_after_calback = null, $item_after_calback_params = [])
    {
        return '<ul'.self::attributes($attributes).'>'.$this->render('ul', null, $children_attributes, $item_attributes, $item_after_calback, $item_after_calback_params).'</ul>';
    }

    /**
     * Returns the menu as an ordered list.
     *
     * @param array    $attributes
     * @param array    $children_attributes
     * @param array    $item_attributes
     * @param callable $item_after_calback
     * @param array    $item_after_calback_params
     *
     * @return string
     */
    public function asOl($attributes = [], $children_attributes = [], $item_attributes = [], $item_after_calback = null, $item_after_calback_params = [])
    {
        return '<ol'.self::attributes($attributes).'>'.$this->render('ol', null, $children_attributes, $item_attributes, $item_after_calback, $item_after_calback_params).'</ol>';
    }

    /**
     * Returns the menu as div containers.
     *
     * @param array    $attributes
     * @param array    $children_attributes
     * @param array    $item_attributes
     * @param callable $item_after_calback
     * @param array    $item_after_calback_params
     *
     * @return string
     */
    public function asDiv($attributes = [], $children_attributes = [], $item_attributes = [], $item_after_calback = null, $item_after_calback_params = [])
    {
        return '<div'.self::attributes($attributes).'>'.$this->render('div', null, $children_attributes, $item_attributes, $item_after_calback, $item_after_calback_params).'</div>';
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param array $attributes
     *
     * @return string
     */
    public static function attributes($attributes)
    {
        $html = [];

        foreach ((array) $attributes as $key => $value) {
            $element = self::attributeElement($key, $value);
            if (!is_null($element)) {
                $html[] = $element;
            }
        }

        return count($html) > 0 ? ' '.implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    protected static function attributeElement($key, $value)
    {
        if (is_numeric($key)) {
            $key = $value;
        }
        if (!is_null($value)) {
            return $key.'="'.e($value).'"';
        }

        return null;
    }

    /**
     * Return configuration value by key.
     *
     * @param string $key
     *
     * @return string
     */
    public function conf($key)
    {
        return $this->conf[$key];
    }

    /**
     * Merge item's attributes with a static string of attributes.
     *
     * @param null  $new
     * @param array $old
     *
     * @return string
     */
    public static function mergeStatic($new = null, array $old = [])
    {
        // Parses the string into an associative array
        parse_str(preg_replace('/\s*([\w-]+)\s*=\s*"([^"]+)"/', '$1=$2&', $new), $attrs);

        // Merge classes
        $attrs['class'] = self::formatGroupClass($attrs, $old);

        // Merging new and old array and parse it as a string
        return self::attributes(array_merge(Arr::except($old, array('class')), $attrs));
    }

    /**
     * Filter items recursively.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return Collection
     */
    public function filterRecursive($attribute, $value)
    {
        $collection = new Collection();

        // Iterate over all the items in the main collection
        $this->items->each(function ($item) use ($attribute, $value, &$collection) {
            if (!$this->hasProperty($attribute)) {
                return false;
            }

            if ($item->$attribute == $value) {
                $collection->push($item);

                // Check if item has any children
                if ($item->hasChildren()) {
                    $collection = $collection->merge($this->filterRecursive($attribute, $item->id));
                }
            }
        });

        return $collection;
    }

    /**
     * Search the menu based on an attribute.
     *
     * @param string $method
     * @param array  $args
     *
     * @return bool|Builder|Collection
     */
    public function __call($method, $args)
    {
        preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $method, $matches);

        if ($matches) {
            $attribute = strtolower($matches[1]);
        } else {
            return false;
        }

        $value = $args ? $args[0] : null;
        $recursive = isset($args[1]) ? $args[1] : false;

        if ($recursive) {
            return $this->filterRecursive($attribute, $value);
        }

        return $this->items->filter(function ($item) use ($attribute, $value) {
            if (!$item->hasProperty($attribute)) {
                return false;
            }

            if ($item->$attribute == $value) {
                return true;
            }

            return false;
        })->values();
    }

    /**
     * Returns menu item by name.
     *
     * @return Item
     */
    public function __get($prop)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }

        return $this->whereNickname($prop)->first();
    }
}
