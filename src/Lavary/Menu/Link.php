<?php namespace Lavary\Menu;

class Link {
	
	/**
	 * Link text
	 *
	 * @var array
	 */
	public $text;
	
	/**
	 * Link URL
	 *
	 * @var array
	 */
	public $url;
	
	/**
	 * Link attributes
	 *
	 * @var array
	 */
	public $attributes;
	
	/**
	 * Creates a hyper link instance
	 *
	 * @param  string $title
	 * @param  string  $url
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct($text, $url, $attributes = array())
	{
		$this->text = $text;
		$this->url = $url;
		$this->attributes = $attributes;
	}

	/**
	 * Returns the link URL
	 *
	 * @return string $url
	 */
	public function get_url()
	{
		return $this->url;
	}

	/**
	 * Returns the link title
	 *
	 * @return string $title
	 */
	public function get_text()
	{
		return $this->text;
	}

	/**
	 * Prepends text or html to the link
	 *
	 * @return Lavary\Menu\Link
	 */
	public function prepend($html)
	{
		$this->text = $html . $this->text;
	
		return $this;
	}

	/**
	 * Appends text or html to the link
	 *
	 * @return Lavary\Menu\Link
	 */
	public function append($html)
	{
		$this->text .= $html;
		
		return $this;
	}

	/**
	 * Add attributes to the link
	 *
	 * @param array $attributes
	 * @return Lavary\Menu\Link
	 */
	public function attributes($attributes = array())
	{
		$this->attributes = $attributes;
		
		return $this;
	}

}