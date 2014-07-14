# Laravel Menu
[![Latest Unstable Version](https://poser.pugx.org/lavary/laravel-menu/v/unstable.svg)](https://packagist.org/packages/lavary/laravel-menu)


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
  
  $menu->add('Home');
  $menu->add('About',    'about');
  $menu->add('services', 'services');
  $menu->add('Contact',  'contact');
  
});
?>
```

**Attention** `$MyNavBar` is just a hypothetical name I used in these examples.

In the above example `Menu::make()` creates a menu named `MyNavBar` and makes `$myNavBar` object available in your views.

This method accepts a callable where you can define your items in there using `add` method. First parameter in `add` method is the *item title* and second one is the *options*. 

*options* can be a simple string as URL or an associative array of options and attributes which we'll discuss later.

`add` adds a new item to the menu and returns an instance of `Item`.

**To render the menu in your view:**

```html
{{ $MyNavBar->asUl() }}
```

This will render your menu like so:

```html
<ul>
  <li><a href="http://yourdomain.com">Home</a></li>
  <li><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ul>
```

## Inserting a Seprator

You can insert a seprator after each item using `divider()` method.:

```php
<?php
	//...
	$menu->add('Separated Item', 'item-url')->divider()
	
	// You can also use it this way:
	
	$menu->('Another Separated Item', 'another-item-url');
	
	// This line will insert a divider after the last defined item
	$menu->divider()
	
	
	/*
	Output as <ul>:
	
		<ul>
			...
			<li><a href="item-url">Separated Item</a></li>
			<li class="divider"></li>
			
			<li><a href="another-item-url">Another Separated Item</a></li>
			<li class="divider"></li>
			...
		</ul>
		
	*/
	//...
?>
```

`divider()` also gets an associative array of attributes:

```php
<?php
	//...
	$menu->add('Separated Item', 'item-url')->divider( array('class' => 'my-divider') );
	//...
	
	/*
	Output as <ul>:
	
		<ul>
			...
			<li><a href="item-url">Separated Item</a></li>
			<li class="my-divider divider"></li>
		
			...
		</ul>
		
	*/
?>
```

## Named Routs and Controller Actions

You can also define named routes or controller actions as item url:

This time instead of passing a simple string to `add()`, we pass an associative array:

```php
<?php

...
// Suppose we have these routes defined in our app/routes.php file:
Route::get('/',        array('as' => 'home.page',  function(){...}));
Route::get('about',    array('as' => 'page.about', function(){...}));
Route::get('services', 'ServiceController@index');
...

// Now we make the menu:
Menu::make('MyNavBar', function($menu){
  
  // the second parameter can be string or an array containing options 
  $menu->add('Home',     array('route'  => 'home.page'));
  $menu->add('About',    array('route'  => 'page.about'));
  $menu->add('services', array('action' => 'ServicesController@index'));
  $menu->add('Contact',  'contact');

});
?>
```
if you need to send some data to routes, urls or controller actions as query string, you can simply include them in an array along with the route action or url value:

```php
<?php
Menu::make('MyNavBar', function($menu){
  
  // the second parameter can be string or an array containing options 
  $menu->add('Home',     array('route'  => 'home.page'));
  $menu->add('About',    array('route'  => array('page.about', 'template' => 1)));
  $menu->add('services', array('action' => array('ServicesController@index', 'id' => 12)));
  $menu->add('Contact',  'contact');

});
?>
```

## HTTPS

If you need to serve the route over HTTPS, you can add `secure` to the options array and set it to `true` or alternatively call `secure()` on the item `link` attribute:

```php
<?php
Menu::make('MyNavBar', function($menu){
	....
	$menu->add('Members', array('url' => 'members', 'secure' => true));
	
	// or alternatively use this shortcut
	
	$menu->add('Members', array('url' => 'members'))->secure();
	...
});
?>
```

The output as ul would be:

```html
<ul>
	...
	<li><a href="https://yourdomain.com/members">Members</a></li>
	...
</ul>
```

## Accessing Defined Items

You can access defined items throughout your code using the below methods along with item's title in camel case.

```php
<?php
	...
	$menu->itemTitleInCamelCase ...
	
	// or
	
	$menu->get('itemTitleInCamelCase') ...
	
	// or
	
	$menu->item('itemTitleInCamelCase') ...
?>
```

As an example, let's insert a divider after `About us` item:

```php
<?php
...
	$menu->aboutUs->divider();
	
	// or
	
	$menu->get('aboutUs')->divider();
	
	// or
	
	$menu->item('aboutUs')->divider();
?>
```

## Sub-menus

Items can have subitems too. You can access each item with the methods explained earlier:

```php
<?php
Menu::make('MyNavBar', function($menu){

  //...
  
  $menu->add('About',    array('route'  => 'page.about'));
  
  // these items will go under Item 'About'
  
  $menu->about->add('Who We are', 'who-we-are');

  // or
  
  $menu->get('about')->add('What We Do', 'what-we-do');
  
  // or
  
  $menu->item('about')->add('Our Goals', 'our-goals');
  
  //...

});
?>
```

You can also chain the item definitions and go as deep as you wish:

```php  
<?php

  ...
  
  $menu->add('About',    array('route'  => 'page.about'))
		     ->add('Level2', 'link address')
		          ->add('level3', 'Link address')
		               ->add('level4', 'Link address');
        
  ...      
?>
```  

It is possible to add sub items directly using `parent` attribute:

```php  
<?php
	//...
	$menu->add('About',    array('route'  => 'page.about'));
	$menu->add('Level2', array('url' => 'Link address', 'parent' => $menu->about->id));
	//...
?>
```  

## HTML attributes

Since all menu items would be rendered as html entities like list items or divs, you can define as many properties as you need for each menu item:


```php
<?php
Menu::make('MyNavBar', function($menu){

  // As you see, you need to pass the second parameter as an associative array:
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

It is also possible to set or get HTML attributes after the item has been defined using `attr()` method.


If you call `attr()` with one argument, it will return the attribute value for you.
If you call it with two arguments, It will consider the first and second parameters as an key/value pair and adds the attribute to item's attributes. 
You can also pass an associative array of items if you need to add a group of HTML attributes in one step; Lastly if you call it without any arguments it will return all the attributes as an array.

```php
<?php
	//...
	$menu->add('About', array('url' => 'about', 'class' => 'about-item'));
	
	echo $menu->about->attr('class');  // output:  about-item
	
	$menu->about->attr('class', 'another-class');
	echo $menu->about->attr('class');  // output:  about-item another-class

	$menu->about->attr(array('class' => 'yet-another', 'id' => 'about')); 
	
	echo $menu->about->attr('class');  // output:  about-item another-class yet-another
	echo $menu->about->attr('id');  // output:  id
	
	print_r($menu->about->attr());
	
	/* Output
	Array
	(
		[class] => about-item another-class yet-another
		[id] => id
	)
	*/
	
	//...
?>
```

## Maniuplating Links

All the HTML attributes will go to `<li>` tags. How about the attributes for `<a>` tags?

Each `Item` instance has an attribute storing an instance of the `Link` class. This class is provided for you to manipulate `<a>` tags.

Just like each item, `Link` also has an `attr()` which functions exactly like item's `attr()`.

```php
<?php
Menu::make('MyNavBar', function($menu){

  ...
  
  $about = $menu->add('About',    array('route'  => 'page.about', 'class' => 'navbar navbar-about dropdown'));
  
  $about->link->attr(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
  
  ...

});
?>
```


## Adding Content to Item's Title


You can `append` or `prepend` html or plain text to each item

```php
<?php
Menu::make('MyNavBar', function($menu){

  ...
  
  $about = $menu->add('About',    array('route'  => 'page.about', 'class' => 'navbar navbar-about dropdown'));
  
  $menu->about->attr(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'))
              ->append(' <b classs="caret"></b>')
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


## URL Prefixing

Just like Laravel route prefixing feature, a group of menu items may be prefixed by using the prefix option in the attributes array of a group:

**Attention:** Prefixing only works on the menu items addressed with `url` but not `route` or `action`. 

```php
<?php
Menu::make('MyNavBar', function($menu){

  $menu->add('Home',     array('route'  => 'home.page', 'class' => 'navbar navbar-home', 'id' => 'home'));
  
  $menu->add('About', array('url'  => 'about', 'class' => 'navbar navbar-about dropdown'));  // URL: /about 
  
  $menu->group(array('prefix' => 'about') function($m){
  
  	$about->add('Who we are?', 'who-we-are');   // URL: about/who-we-are
  	$about->add('What we do?', 'what-we-do');   // URL: about/what-we-do
  	
  }
  
  $menu->add('Contact',  'contact');

});
?>
```

This will generate:

```html
<ul>
    <li  class="navbar navbar-home" id="home"><a href="/">Home</a></li>
    
    <li  data-role="navigation" class="navbar navbar-about dropdown"><a href="http://yourdomain.com/about/summary"About</a>
    	<ul>
    	   <li><a href="http://yourdomain.com/about/who-we-are">Who we are?</a></li>
    	   <li><a href="http://yourdomain.com/about/who-we-are">What we do?</a></li>
    	</ul>
    </li>
    
    <li><a href="services">Services</a></li>
    <li><a href="contact">Contact</a></li>
</ul>
```

## Nested Groups

Laravel Menu supports nested grouping feature for the menu items. A menu group merges its own attribute with its parent group then shares them between its items:

```php
<?php
Menu::make('MyNavBar', function($menu){

	...
	
	$menu->group(array('prefix' => 'pages', 'data-info' => 'test'), function($m){
		
		$about = $m->add('About', 'about');
		
		$about->group(array('prefix' => 'about', 'data-role' => 'navigation'), function($a){
		
			$a->add('Who we are', 'who-we-are?');
			$a->add('What we do?', 'what-we-do');
			$a->add('Our Goals', 'our-goals');
		});
	});
	
});
?>
```

If we render it as a ul:

```html
<ul>
	...
	<li data-info="test">
		<a href="http://yourdomain.com/pages/about">About</a>
		<ul>
			<li data-info="test" data-role="navigation"><a href="http://yourdomain.com/pages/about/who-we-are"></a></li>
			<li data-info="test" data-role="navigation"><a href="http://yourdomain.com/pages/about/what-we-do"></a></li>
			<li data-info="test" data-role="navigation"><a href="http://yourdomain.com/pages/about/our-goals"></a></li>
		</ul>
	</li>
</ul>
```


## Meta Data

You might encounter situations when you need to attach some meta data to each item; This data can be anything from item placement order to permissions required for accessing the item. You can do this by using `data()` method.

`data()` method works exactly like `attr()` method:

If you call `data()` with one argument, it will return the data value for you.
If you call it with two arguments, It will consider the first and second parameters as an key/value pair and sets the data. 
You can also pass an associative array of data if you need to add a group of key/value pairs in one step; Lastly if you call it without any arguments it will return all data as an array.

```php
<?php
Menu::make('MyNavBar', function($menu){

  ...
  
  $menu->add('Users', array('route'  => 'admin.users'))
       ->data('permission', 'manage_users');

});
?>
```

You can also get a data value as an attribute of the item:

```
<?php
	//...
	$menu->add('Users', '#');
	
	$menu->users->data('placement', 12);
	
	echo $menu->users->placement;    // Output : 12
	//...
?>
```

This meta data don't do anything to the item and won't be rendered in HTML. It is the developer who would decides what to do with these data.


## Filtering Menu Items

We can filter menu items based on user type, permission or any other policy we may have in our application.


Let's proceed with an example:

We suppose our `User` model can check whether the user has a permisson or not:

```php
<?php
Menu::make('MyNavBar', function($menu){

  ...
  
  $menu->add('Users', array('route'  => 'admin.users'))
       ->meta('permission', 'manage_users');

})->filter(function($item){
  if(User::get()->hasRole( $item->meta('permission'))) {
      return true;
  }
  return false;
});
?>
```

`Users` item will be visible to those who has the `manage_users` permission.


## Sorting the Items

`laravel-menu` can sort the items based on a user defiend function or a key (item properties like id,parent,etc) or meta data stored along with the item.

You can pass a closure as the first argument to `SortBy()` method:

```
<?php
Menu::make('main', function($m){

	$m->add('About', '#')     ->data('order', 2);
	$m->add('Home', '#')      ->data('order', 1);
	$m->add('Services', '#')  ->data('order', 3);
	$m->add('Contact', '#')   ->data('order', 5);
	$m->add('Portfolio', '#') ->data('order', 4);

})->sortBy(function($items) {
	// Your sprting algorithm here...
});		
?>
```

The closure receives the items collection.

You can also use stored keys to sort the items:

```
<?php
Menu::make('main', function($m){

	$m->add('About', '#')     ->data('order', 2);
	$m->add('Home', '#')      ->data('order', 1);
	$m->add('Services', '#')  ->data('order', 3);
	$m->add('Contact', '#')   ->data('order', 5);
	$m->add('Portfolio', '#') ->data('order', 4);

})->sortBy('order');		
?>
```

`sortBy()` also recieves a second parameter which specifies the ordering direction: Ascending order(asc) or Descending Order(dsc). Default value is `asc`.


## Rendering Formats

Several rendering formats are available out of the box:

* **Menu as Unordered List**

```html
  {{ $MenuName->asUl() }}
```

`asUl()` will render your menu in an unordered list. it also takes an optional parameter to define attributes for the `<ul>` tag itself:

```php
{{ $MenuName->asUl( array('class' => 'awsome-ul') ) }}
```

Result:

```html
<ul class="awsome-ul">
  <li><a href="http://yourdomain.com">Home</a></li>
  <li><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ul>
```

* **Menu as Ordered List**


```php
  {{ $MenuName->asOl() }}
```

`asOl()` method will render your menu in an ordered list. it also takes an optional parameter to define attributes for the `<ol>` tag itself:

```php
{{ $MenuName->asOl( array('class' => 'awsome-ol') ) }}
```

Result:

```html
<ol class="awsome-ol">
  <li><a href="http://yourdomain.com">Home</a></li>
  <li><a href="http://yourdomain.com/about">About</a></li>
  <li><a href="http://yourdomain.com/services">Services</a></li>
  <li><a href="http://yourdomain.com/contact">Contact</a></li>
</ol>
```

* **Menu as Div**


```php
  {{ $MenuName->asDiv() }}
```

`asDiv()` method will render your menu as nested html divs. it also takes an optional parameter to define attributes for the parent `<div>` tag itself:

```php
{{ $MenuName->asDiv( array('class' => 'awsome-div') ) }}
```

Result:

```html
<div class="awsome-div">
  <div><a href="http://yourdomain.com">Home</a></div>
  <div><a href="http://yourdomain.com/about">About</a></div>
  <div><a href="http://yourdomain.com/services">Services</a></div>
  <div><a href="http://yourdomain.com/contact">Contact</a></div>
</div>
```

* **Menu as Bootstrap 3 Navbar**

```php
  {{ $MenuName->asBootstrap() }}
```

You can have your menu as a Bootstrap 3 `navbar`.

`asBootstrap` method also takes an optional array parameter to defines some configurations, like `inverse` mode.

To have your Bootstrap 3 navbar in `inverse` mode:


```php
  {{ $MenuName->asBootstrap(array('inverse' => true)  ) }}
```

I've prepared a tutorial about embedding several menu objects in a bootstrap navbar in case somebody is interested.
You can read all about it [here](https://gist.github.com/lavary/c9da317446e2e3b32779).

## View methods

**Menu**

* `roots()`  Returns menu items in root level (items with no parent)
* `whereParent(int $pid)`  Returns items with the given parent id($pid)
* `render(string $type, $integer $pid)` Renders menu items at a given level
* `asUl(array $attributes)` Renders menu in an unordered list
* `asOl(array $attributes)` Renders menu in an unordered list
* `asDiv(array $attributes)` Renders menu in html divs
* `asBootstrap(array $options)`Renders menu as Bootstrap 3 navbar

**MenuItem**

* `hasChilderen()` Checks whether the item has childeren and returns a boolean accordingly
* `childeren()` Returns all subitems of the item as an array of MenuItem objects
* `get_id()` Returns `id` of the item
* `get_pid()` Returns `pid` of the item
* `get_attributes()` Returns your item attributes as an array
* `get_title()` Returns item title
* `get_url()` Returns menu item url
* `link()` Generates an html link based on your settings
* `meta(string $name, string $value)` Sets or gets meta data of an item 


## Advanced Usage

It is also possible to render your menus in your own views.

If you'd like to render your menu(s) according to your own design, you should create two views.

* `View-1`  This view contains all the html codes like `nav` or `ul` or `div` tags wrapping your menu items.
* `View-2`  This view is responsible for rendering menu items and it is included in `View-1`.


The reason we use two view files here is that `View-2` calls itself recursively to render the items to the deepest level in multi-level menus.

Let's make this easier with an example:

In our `routes.php`:

```php
<?php
Menu::make('MyNavBar', function($menu){
  
  $menu->add('Home',     '');
  
  $about = $menu->add('About',    array('route'  => 'page.about'));
           $about->add('Who are we?', 'who-we-are');
           $about->add('What we do?', 'what-we-do');

  $menu->add('services', 'services');
  $menu->add('Contact',  'contact');
  
});
?>
```

In this example we name View-1 `custom-menu.blade.php` and View-2 `custom-menu-items.blade.php`.

**custom-menu.blade.php**
```html
<nav class="navbar">
  <ul class="horizontal-navbar">
    @include('custom-menu-items', array('items', $MyNavBar->roots()))
  </ul>
</nav><!--/nav-->
```

**custom-menu-items.blade.php**
```html
@foreach($items as $item)
  <li @if($item->hasChilderen()) class="dropdown" @endif>
      <a href="{{ $item->link->url }}">{{ $item->link->title }} </a>
      @if($item->hasChilderen())
        <ul class="dropdown-menu">
              @include('custom-menu-items', array('items' => $item->childeren()))
        </ul> 
      @endif
  </li>
@endforeach
```

Let's describe what we did above, In `custom-menus.blade.php` we put whatever html code we had according to our design, then we included `custom-menu-items.blade.php` and passed the menu items at *root level* to `custom-menu-items.blade.php`:

```php
...
@include('custom-menu-items', array('items' => $menu->roots()))
...
```

In `custom-menu-items.blade.php` we run a foreach loop control and call the file recursively for rendering the items to the deepest level required.

To put the rendered menu in your application template, you can simply include `custom-menu` view in your master layout.

I've prepared a tutorial about embedding several menu objects in a bootstrap navbar in case somebody is interested.
You can read all about it [here](https://gist.github.com/lavary/c9da317446e2e3b32779).

## If you need help

Please submit all issues and questions using GitHub issues and I will try to help you.


## License

Laravel Menu is free software distributed under the terms of the MIT license
