# Laravel Menu

A Simple Laravel way of making menus.


## Installation

In the `require` key of `composer.json` file add `lavary/laravel-menu": "dev-master`:

```
...
"require": {
	"laravel/framework": "4.1.*",
	"lavary/laravel-menu": "dev-master"
  }  
```
  
Run the composer update command:

```bash
composer update
```

Now append Laravel Menu service provider to  `providers` array in `app/config/app.php`.

```php
<?php

'providers' => array(

    'Illuminate\Foundation\Providers\ArtisanServiceProvider',
    'Illuminate\Auth\AuthServiceProvider',
    ...
    'Lavary\Menu\ServiceProvider',

),
?>
```

At the end of `config/app.php` add `'Menu'    => 'Lavary\Menu\Facade'` to the `$aliases` array:

```php
<?php

'aliases' => array(

    'App'        => 'Illuminate\Support\Facades\App',
    'Artisan'    => 'Illuminate\Support\Facades\Artisan',
    ...
    'Menu'       => 'Lavary\Menu\Facade',

),
?>
```

This registers the package with Laravel and creates an alias called `Menu`.


## Basic Usage


Menus can be defined in `app/routes.php` or `start/global.php` or any other place you wish as long as it is auto loaded when a request hits your application.


Here is a basic usage:


```php
<?php
Menu::make('MyNavBar', function($menu){
  
  $menu->add('Home',     '');
  $menu->add('About',    'about');
  $menu->add('services', 'services');
  $menu->add('Contact',  'contact');
  
});
?>
```

**Attention** `$MyNavBar` is just a hypothetical name I used in these examples.

`Menu::make()` creates a menu named `MyNavBar` and makes `$myNavBar` object available in all views.

This method accepts a callable where you can define your items in there using the `add` method. First parameter in `add` method is the *item title* and second one is the *url*. 

`add` adds a new item to the menu and returns an instance of `MenuItem`.

To render the menu in your view:

```html
{{ $MyNavBar->asUl() }}
```

This will render your menu as below:

```html
<ul>
  <li><a href="http://yourdomain.com">Home</a></li>
  <li><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ul>
```

## Named Routs and Controller Actions

You can also define named routes or controller actions as item url:

```php
<?php
Menu::make('MyNavBar', function($menu){
  
  // the second parameter can be string or an array containing options 
  $menu->add('Home',     array('route'  => 'home.page'));
  $menu->add('About',    array('route'  => 'page.about'));
  $menu->add('services', array('action' => 'ServicesController@index'));
  $menu->add('Contact',  'contact');

});
?>
```

## Sub-menus

Items can have subitems too:

```php
<?php
Menu::make('MyNavBar', function($m){

  ...
  
  $about = $menu->add('About',    array('route'  => 'page.about'));
  
  // these items will go under $about MenuItem
  $about->add('Who are we?', 'who-we-are');
  $about->add('What we do?', 'what-we-do');
  
  ...

});
?>
```

You can also chain the item definitions and go as deep as you wish:

```php  
<?php

  ...
  
  $about = $menu->add('About',    array('route'  => 'page.about'))
		     ->add('Level2', 'link address')
		          ->add('level3', 'Link address')
		               ->add('level4', 'Link address');
        
  ...      
?>
```  

You can also add sub items directly using `pid` key:

```php  
<?php
...
$about = $menu->add('About',    array('route'  => 'page.about'));
$menu->add('Level2', array('url' => 'Link address', 'pid' => $about->get_id()));
...
?>
```  

## HTML attributes

Since all menu items would be rendered as html entities like lists or divs, you can define as many properties as you need for each menu item:


```php
<?php
Menu::make('MyNavBar', function($menu){

  $menu->add('Home',     array('route'  => 'home.page',  'class' => 'navbar navbar-home', 'id' => 'home'));
  $menu->add('About',    array('route'  => 'page.about', 'class' => 'navbar navbar-about dropdown'));
  $menu->add('services', array('action' => 'ServicesController@index'));
  $menu->add('Contact',  'contact');

});
?>
```

If we choose html lists as our rendering format like `ul` or `ol`, the result would be something similiar to this:

```html
<ul>
  <li class="navbar navbar-home" id="home"><a href="http://yourdomain.com">Home</a></li>
  <li class="navbar navbar-about dropdown"><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ul>
```

## Links

All the html attributes will go to `<li>` tags. How about the attributes for `<a>` tags?

Each `MenuItem` instance has an attribute holding an instance of the `Link` class. This class is provided for you to manipulate `<a>` tags.

To add some attributes to the link you can use the `attributes()` method of `Link` object:

```php
<?php
Menu::make('MyNavBar', function($menu){

  ...
  
  $about = $menu->add('About',    array('route'  => 'page.about', 'class' => 'navbar navbar-about dropdown'));
  
  $about->link->attributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
  
  ...

});
?>
```

You can also `append` or `prepend` html or plain text content to the link text:

```php
<?php
Menu::make('MyNavBar', function($menu){

  ...
  
  $about = $menu->add('About',    array('route'  => 'page.about', 'class' => 'navbar navbar-about dropdown'));
  
  $about->link->attributes(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'))
              ->append('<b classs="caret"></b>')
              ->prepend('<span classs="glyphicon glyphicon-user"></span> ');
              
  ...            

});
?>
```

The above code will result:

```html
<ul>
  ...
  
  <li class="navbar navbar-about dropdown">
   <a href="about" class="dropdown-toggle" data-toggle="dropdown">
     <span class="glyphicon glyphicon-user"></span> About <b classs="caret"></b>
   </a>
  </li>
</ul>

```

## Menu Groups

Sometimes you may need to share attributes to a group of items. Instead of specifying the attributes on each menu, you may use a menu group:

**PS:** This feture works exactly like Laravel group routes. 


```php
<?php
Menu::make('MyNavBar', function($menu){

  $menu->add('Home',     array('route'  => 'home.page', 'class' => 'navbar navbar-home', 'id' => 'home'));
  
  $menu->group(array('style' => 'padding: 0', 'data-role' => 'navigation') function($m){
    
        $m->add('About',    array('route'  => 'page.about', 'class' => 'navbar navbar-about dropdown'));
        $m->add('services', array('action' => 'ServicesController@index'));
  }
  
  $menu->add('Contact',  'contact');

});
?>
```

Attributes `style` and `data-role` would be applied to both `About` and `Services` items:

```html
<ul>
    <li class="navbar navbar-home" id="home"><a href="http://yourdomain.com">Home</a></li>
    <li style="padding: 0" data-role="navigation" class="navbar navbar-about dropdown"><a href="http://yourdomain.com/about"About</a></li>
    <li style="padding: 0" data-role="navigation"><a href="http://yourdomain.com/services">Services</a></li>
</ul>
```


## Item Prefixing

Just like Laravel route prefixing feature, a group of menu items may be prefixed by using the prefix option in the attributes array of a group:

**Attention:** Prefixing only works on the menu items addressed with `url` but not `route` or `action`. 

```php
<?php
Menu::make('MyNavBar', function($menu){

  $menu->add('Home',     array('route'  => 'home.page', 'class' => 'navbar navbar-home', 'id' => 'home'));
  
  $about = $menu->add('About', array('url'  => 'about', 'class' => 'navbar navbar-about dropdown'));  // URL: /about 
  $about->group(array('prefix' => 'about') function($m){
  
  	$about->add('Who we are?', 'who-we-are');   // URL: about/who-we-are
  	$about->add('What we do?', 'what-we-do');   // URL: about/what-we-do
  	
  }
  
  $menu->add('Contact',  'contact');

});
?>
```