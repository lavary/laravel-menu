<?php

namespace Lavary\Menu;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;

class Item
{
    /**
     * Reference to the menu builder.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * The ID of the menu item.
     *
     * @var int
     */
    protected $id;

    /**
     * Item's title.
     *
     * @var string
     */
    public $title;

    /**
     * Item's html before.
     *
     * @var string
     */
    public $beforeHTML;

    /**
     * Item's html after.
     *
     * @var string
     */
    public $afterHTML;

    /**
     * Item's title in camelCase.
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
     * Parent Id of the menu item.
     *
     * @var int
     */
    protected $parent;

    /**
     * Holds link element.
     *
     * @var Link|null
     */
    protected $link;

    /**
     * Extra information attached to the menu item.
     *
     * @var array
     */
    protected $data = array();

    /**
     * If this is the currently active item, doesn't include parents.
     *
     * @var bool
     */
    protected $active = false;

    /**
     * Attributes of menu item.
     *
     * @var array
     */
    public $attributes = array();

    /**
     * Flag for active state.
     *
     * @var bool
     */
    public $isActive = false;

    /**
     * If true this prevents auto activation by matching URL
     * Activation by active children keeps working.
     *
     * @var bool
     */
    private $disableActivationByURL = false;

    /**
     * Creates a new Item instance.
     *
     * @param Builder $builder
     * @param int     $id
     * @param string  $title
     * @param array   $options
     */
    public function __construct($builder, $id, $title, $options)
    {
        $this->builder = $builder;
        $this->id = $id;
        $this->title = $title;
        $this->nickname = isset($options['nickname']) ? $options['nickname'] : Str::camel(Str::ascii($title));

        $this->attributes = $this->builder->extractAttributes($options);
        $this->parent = (is_array($options) && isset($options['parent'])) ? $options['parent'] : null;

        // Storing path options with each link instance.
        if (!is_array($options)) {
            $path = array('url' => $options);
        } elseif (isset($options['raw']) && true == $options['raw']) {
            $path = null;
        } else {
            $path = Arr::only($options, array('url', 'route', 'action', 'secure'));
        }
        if (isset($options['disableActivationByURL']) && true == $options['disableActivationByURL']) {
            $this->disableActivationByURL = true;
        }

        if (!is_null($path)) {
            $path['prefix'] = $this->builder->getLastGroupPrefix();
        }

        $this->link = $path ? new Link($path, $this->builder) : null;

        // Activate the item if items's url matches the request uri
        if (true === $this->builder->conf('auto_activate')) {
            $this->checkActivationStatus();
        }
    }

    /**
     * Creates a sub Item.
     *
     * @param string       $title
     * @param string|array $options
     * @return Item
     */
    public function add($title, $options = '')
    {
        if (!is_array($options)) {
            $url = $options;
            $options = array();
            $options['url'] = $url;
        }

        $options['parent'] = $this->id;

        return $this->builder->add($title, $options);
    }

    /**
     * Add a plain text item.
     *
     * @param $title
     * @param array $options
     * @return Item
     */
    public function raw($title, array $options = array())
    {
        $options['parent'] = $this->id;

        return $this->builder->raw($title, $options);
    }

    /**
     * Insert a separator after the item.
     *
     * @param array $attributes
     *
     * @return Item
     */
    public function divide($attributes = array())
    {
        $attributes['class'] = Builder::formatGroupClass($attributes, array('class' => 'divider'));

        $this->divider = $attributes;

        return $this;
    }

    /**
     * Group children of the item.
     *
     * @param array    $attributes
     * @param callable $closure
     */
    public function group($attributes, $closure)
    {
        $this->builder->group($attributes, $closure, $this);
    }

    /**
     * Add attributes to the item.
     *
     * @param  mixed
     *
     * @return string|Item|array
     */
    public function attr()
    {
        $args = func_get_args();

        if (isset($args[0]) && is_array($args[0])) {
            $this->attributes = array_merge($this->attributes, $args[0]);

            return $this;
        } elseif (isset($args[0]) && isset($args[1])) {
            $this->attributes[$args[0]] = $args[1];

            return $this;
        } elseif (isset($args[0])) {
            return isset($this->attributes[$args[0]]) ? $this->attributes[$args[0]] : null;
        }

        return $this->attributes;
    }

    /**
     * Generate URL for link.
     *
     * @return string
     */
    public function url()
    {
        // If the item has a link proceed:
        if (!is_null($this->link)) {
            // If item's link has `href` property explicitly defined
            // return it
            if ($this->link->href) {
                return $this->link->href;
            }

            // Otherwise dispatch to the proper address
            return $this->builder->dispatch($this->link->path);
        }
    }

    /**
     * Prepends text or html to the item.
     *
     * @param $html
     * @return Item
     */
    public function prepend($html)
    {
        $this->title = $html.$this->title;

        return $this;
    }

    /**
     * Appends text or html to the item.
     *
     * @param $html
     * @return Item
     */
    public function append($html)
    {
        $this->title .= $html;

        return $this;
    }

    /**
     * Before text or html to the item.
     *
     * @param $html
     * @return Item
     */
    public function before($html)
    {
        $this->beforeHTML = $html.$this->beforeHTML;

        return $this;
    }

    /**
     * After text or html to the item.
     *
     * @param $html
     * @return Item
     */
    public function after($html)
    {
        $this->afterHTML .= $html;

        return $this;
    }

    /**
     * Checks if the item has any children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->builder->whereParent($this->id)) or false;
    }

    /**
     * Returns children of the item.
     *
     * @return Collection
     */
    public function children()
    {
        return $this->builder->whereParent($this->id);
    }

    /**
     * Checks if this item has a parent.
     *
     * @return bool
     */
    public function hasParent()
    {
        return isset($this->parent);
    }

    /**
     * Returns the parent item.
     *
     * @return Item
     */
    public function parent()
    {
        return $this->builder->whereId($this->parent)->first();
    }

    /**
     * Returns all childeren of the item.
     *
     * @return Collection
     */
    public function all()
    {
        return $this->builder->whereParent($this->id, true);
    }

    /**
     * Decide if the item should be active.
     */
    public function checkActivationStatus()
    {
        if (true === $this->disableActivationByURL) {
            return;
        }
        if (true == $this->builder->conf['restful']) {
            $path = ltrim(parse_url($this->url(), PHP_URL_PATH), '/');
            $rpath = ltrim(parse_url(Request::path(), PHP_URL_PATH), '/');

            if ($this->builder->conf['rest_base']) {
                $base = (is_array($this->builder->conf['rest_base'])) ? implode('|', $this->builder->conf['rest_base']) : $this->builder->conf['rest_base'];

                list($path, $rpath) = preg_replace('@^('.$base.')/@', '', [$path, $rpath], 1);
            }

            if (preg_match("@^{$path}(/.+)?\z@", $rpath)) {
                $this->activate();
            }
        } else {
            // We should consider a $strict config. If $strict then only match against fullURL.
            if ($this->url() == Request::url() || $this->url() == Request::fullUrl()) {
                $this->activate();
            }
        }
    }

    /**
     * Set nickname for the item manually.
     *
     * @param string $nickname
     *
     * @return Item
     */
    public function nickname($nickname = null)
    {
        if (is_null($nickname)) {
            return $this->nickname;
        }

        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Set id for the item manually.
     *
     * @param mixed $id
     *
     * @return Item|int
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id;
        }

        $this->id = $id;

        return $this;
    }

    /**
     * Activate the item.
     *
     * @param Item $item
     * @param bool $recursion
     */
    public function activate(Item $item = null, $recursion = false)
    {
        $item = is_null($item) ? $this : $item;

        // Check to see which element should have class 'active' set.
        if ('item' == $this->builder->conf('active_element')) {
            $item->active();
        } else {
            $item->link->active();
        }

        if (false === $recursion) {
            $item->active = true;
        }

        // If parent activation is enabled:
        if (true === $this->builder->conf('activate_parents')) {
            // Moving up through the parent nodes, activating them as well.
            if ($item->parent) {
                $this->activate($this->builder->whereId($item->parent)->first(), true);
            }
        }
    }

    /**
     * Make the item active.
     *
     * @param null|string $pattern
     * @return Item
     */
    public function active($pattern = null)
    {
        if (!is_null($pattern)) {
            $pattern = ltrim(preg_replace('/\/\*/', '(/.*)?', $pattern), '/');
            if (preg_match("@^{$pattern}\z@", Request::path())) {
                $this->activate();
            }

            return $this;
        }

        $this->attributes['class'] = Builder::formatGroupClass(array('class' => $this->builder->conf('active_class')), $this->attributes);
        $this->isActive = true;

        return $this;
    }

    /**
     * Set or get items's meta data.
     *
     * @param  mixed
     *
     * @return string|Item|array
     */
    public function data()
    {
        $args = func_get_args();

        if (isset($args[0]) && is_array($args[0])) {
            $this->data = array_merge($this->data, array_change_key_case($args[0]));

            // Cascade data to item's children if cascade_data option is enabled
            if ($this->builder->conf['cascade_data']) {
                $this->cascade_data($args);
            }

            return $this;
        } elseif (isset($args[0]) && isset($args[1])) {
            $this->data[strtolower($args[0])] = $args[1];

            // Cascade data to item's children if cascade_data option is enabled
            if ($this->builder->conf['cascade_data']) {
                $this->cascade_data($args);
            }

            return $this;
        } elseif (isset($args[0])) {
            return isset($this->data[$args[0]]) ? $this->data[$args[0]] : null;
        }

        return $this->data;
    }

    /**
     * Cascade data to children.
     *
     * @param array $args
     *
     * @return bool
     */
    public function cascade_data($args = array())
    {
        if (!$this->hasChildren()) {
            return false;
        }

        if (count($args) >= 2) {
            $this->children()->data($args[0], $args[1]);
        } else {
            $this->children()->data($args[0]);
        }

        return true;
    }

    /**
     * Check if propery exists either in the class or the meta collection.
     *
     * @param string $property
     *
     * @return bool
     */
    public function hasProperty($property)
    {
        if (property_exists($this, $property) || !is_null($this->data($property))) {
            return true;
        }

        return false;
    }

    /**
     * Search in meta data if a property doesn't exist otherwise return the property.
     *
     * @param  string
     *
     * @return string
     */
    public function __get($prop)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }

        return $this->data($prop);
    }
}
