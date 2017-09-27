<?php

namespace Lavary\Menu;

class Link
{
    /**
     * Reference to the menu builder.
     *
     * @var Builder
     */
    protected $builder;

    /**
     * Path Information.
     *
     * @var array
     */
    protected $path = array();

    /**
     * Explicit href for the link.
     *
     * @var string
     */
    protected $href;

    /**
     * Link attributes.
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
     * Creates a hyper link instance.
     *
     * @param array   $path
     * @param Builder $builder
     */
    public function __construct($path = array(), $builder)
    {
        $this->path = $path;
        $this->builder = $builder;
    }

    /**
     * Make the anchor active.
     *
     * @return Link
     */
    public function active()
    {
        $this->attributes['class'] = Builder::formatGroupClass(array('class' => $this->builder->conf('active_class')), $this->attributes);
        $this->isActive = true;

        return $this;
    }

    /**
     * Set Anchor's href property.
     *
     * @return Link
     */
    public function href($href)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Make the url secure.
     *
     * @return Link
     */
    public function secure()
    {
        $this->path['secure'] = true;

        return $this;
    }

    /***
     * Add attributes to the link.
     *
     * @param mixed
     * @return $this|array|mixed|null
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

    /***
     * Check for a method of the same name if the attribute doesn't exist.
     *
     * @param $prop
     * @return Link|string
     */
    public function __get($prop)
    {
        if (property_exists($this, $prop)) {
            return $this->$prop;
        }

        return $this->attr($prop);
    }
}
