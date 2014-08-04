# Laravel Menu
[![Latest Stable Version](https://poser.pugx.org/lavary/laravel-menu/v/stable.svg)](https://packagist.org/packages/lavary/laravel-menu)
[![Total Downloads](https://poser.pugx.org/lavary/laravel-menu/downloads.svg)](https://packagist.org/packages/lavary/laravel-menu)
[![License](https://poser.pugx.org/lavary/laravel-menu/license.svg)](https://packagist.org/packages/lavary/laravel-menu)
[![Latest Unstable Version](https://poser.pugx.org/lavary/laravel-menu/v/unstable.svg)](https://packagist.org/packages/lavary/laravel-menu)

A quick way to create menus in [Laravel 4.x](http://laravel.com/)


## Installation

In the `require` key of `composer.json` file add `lavary/laravel-menu": "dev-master`:

```
...
"require": {
	"laravel/framework": "4.2.*",
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

**Attention** `$MyNavBar` is just a hypothetical name I used in these examples; You can name your menus whatever you please.

In the above example `Menu::make()` creates a menu named `MyNavBar`, Adds the menu instance to the `Menu::collection` and finally makes `$myNavBar` object available in the views.

This method accepts a callable inside which you can define your items by `add` method. `add` adds a new item to the menu and returns an instance of `Item`. `add()` receives two parameters, the first one is the item title and the second one is options.

*options* can be a simple string representing a URL or an associative array of options and HTML attributes which we'll discuss in a bit.



**To render the menu in your view:**

`Laravel-menu` provides three rendering methods out of the box. However you can create your own renderer using the right methods and attributes.

As noted earlier, `laravel-menu` provides three rendering formats out of the box, asUl(), asOl() and asDiv(). We'll talk about these methods in detail later.

```
{{ $MyNavBar->asUl() }}
```

You can also access the menu via the menu collection:

```
{{ Menu::get('MyNavBar')->asUl() }}
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
And that's all about it!

## Named Routs and Controller Actions

`laravel-menu` supports named routes or controller actions as item URL:

This time instead of passing a simple string to `add()`, we pass an associative array like so:

```php
<?php

// ...

/* Suppose we have these routes defined in our app/routes.php file */
Route::get('/',        array('as' => 'home.page',  function(){...}));
Route::get('about',    array('as' => 'page.about', function(){...}));
Route::get('services', 'ServiceController@index');

//...

// Now we make the menu:
Menu::make('MyNavBar', function($menu){
  
  $menu->add('Home',     array('route'  => 'home.page'));
  $menu->add('About',    array('route'  => 'page.about'));
  $menu->add('services', array('action' => 'ServicesController@index'));
  $menu->add('Contact',  'contact');

});
?>
```
if you need to send some data to routes, URLs or controller actions as a query string, you can simply include them in an array along with the route, action or URL value:

```php
<?php
Menu::make('MyNavBar', function($menu){
  
  $menu->add('Home',     array('route'  => 'home.page'));
  $menu->add('About',    array('route'  => array('page.about', 'template' => 1)));
  $menu->add('services', array('action' => array('ServicesController@index', 'id' => 12)));
 
  $menu->add('Contact',  'contact');

});
?>
```

## HTTPS

If you need to serve the route over HTTPS, you can add key `secure` to the options array and set it to `true` or alternatively call `secure()` on the item's `link` attribute:

```php
<?php
Menu::make('MyNavBar', function($menu){
	// ...
	
	$menu->add('Members', array('url' => 'members', 'secure' => true));
	
	// or alternatively use this shortcut
	
	$menu->add('Members', 'members')->link->secure();
	
	// ...
});
?>
```

The output as `<ul>` would be:

```html
<ul>
	...
	<li><a href="https://yourdomain.com/members">Members</a></li>
	...
</ul>
```

## Accessing Defined Items

You can access defined items throughout your code using the below methods along with item's title in *camel case*:

```php
<?php
	// ...
	
	$menu->itemTitleInCamelCase ...
	
	// or
	
	$menu->get('itemTitleInCamelCase') ...
	
	// or
	
	$menu->item('itemTitleInCamelCase') ...
	
	// ...
?>
```

As an example, let's insert a divider after `About us` item after we've defined it:

```php
<?php
    // ...
	
	$menu->add('About us', 'about-us')
	
	$menu->aboutUs->divide();
	
	// or
	
	$menu->get('aboutUs')->divide();
	
	// or
	
	$menu->item('aboutUs')->divide();
	
	// ...
?>
```

You can also get an item by Id if needed:

```php
<?php
	// ...
	$menu->find(12) ...
	// ...
?>
```

## Magic Where Methods

You can also search the items collection by magic where methods.
These methods are consisted of a `where` concatenated with a property (object property or even meta data)

For example to get an item with parent equal to 12, you can use it like so:

```php
<?php
	// ...
	$subs = $menu->whereParent(12);
	// ...
?>
```

Or to get item's with a specific meta data:

```php
<?php
	// ...
	$menu->add('Home',     '#')->data('color', 'red');
	$menu->add('About',    '#')->data('color', 'blue');
	$menu->add('Services', '#')->data('color', 'red');
	$menu->add('Contact',  '#')->data('color', 'green');
	// ...
	
	// Fetch all the items with color set to red:
	$reds = $menu->whereColor('red');
	
?>
```

This method returns a *Laravel collection*.

## Sub-items

Items can have sub-items too: 

```php
<?php
Menu::make('MyNavBar', function($menu){

  //...
  
  $menu->add('About',    array('route'  => 'page.about'));
  
  // these items will go under Item 'About'
  
  // refer to about as a property of $menu object then call `add()` on it
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

  // ...
  
  $menu->add('About',    array('route'  => 'page.about'))
		     ->add('Level2', 'link address')
		          ->add('level3', 'Link address')
		               ->add('level4', 'Link address');
        
  // ...      
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

## HTML Attributes

Since all menu items would be rendered as HTML entities like list items or divs, you can define as many HTML attributes as you need for each item:


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

If we choose HTML lists as our rendering format like `ul`, the result would be something similar to this:

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
If you call it with two arguments, It will consider the first and second parameters as a key/value pair and sets the attribute. 
You can also pass an associative array of attributes if you need to add a group of HTML attributes in one step; Lastly if you call it without any arguments it will return all the attributes as an array.

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

All the HTML attributes will go to the wrapping tags(li, div, etc); You might encounter situations when you need to add some HTML attributes to `<a>` tags as well.

Each `Item` instance has an attribute which stores an instance of `Link` object. This object is provided for you to manipulate `<a>` tags.

Just like each item, `Link` also has an `attr()` method which functions exactly like item's:

```php
<?php
Menu::make('MyNavBar', function($menu){

 //  ...
  
  $about = $menu->add('About',    array('route'  => 'page.about', 'class' => 'navbar navbar-about dropdown'));
  
  $about->link->attr(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
  
 // ...
  
});
?>
```

### Link's Href Property

If you don't want to use the routing feature of `laravel-menu` or you don't want the builder to prefix your URL with anything (Your host address for example), you can explicitly set your link's href property:

```
<?php
// ...
$menu->add('About')->link->href('#');
// ...
?>
```

## Active Item

You can mark an item as activated using `active()` on that item:

```php
<?php
	// ...
	$menu->add('Home', '#')->active();
	// ...
	
	/* Output
	
	<li class="active"><a href="#">#</a></li>	
	
	*/
	
?>
```

You can also add class 'active' to the anchor element instead of the wrapping element:

```php
<?php
	// ...
	$menu->add('Home', '#')->link->active();
	// ...
	
	/* Output
	
	<li><a class="active" href="#">#</a></li>	
	
	*/
	
?>
```

Laravel Menu does this for you automatically according to the current **URI** the time you reigster the item.

You can also choose the element to be activated (item or the link) in `options.php` which resides in package's config directory:

```php

	// ...
	'active_element' => 'item',    // item|link
	// ...

```

## Inserting a Separator

You can insert a separator after each item using `divide()` method:

```php
<?php
	//...
	$menu->add('Separated Item', 'item-url')->divide()
	
	// You can also use it this way:
	
	$menu->('Another Separated Item', 'another-item-url');
	
	// This line will insert a divider after the last defined item
	$menu->divide()
	
	//...
	
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

?>
```

`divide()` also gets an associative array of attributes:

```php
<?php
	//...
	$menu->add('Separated Item', 'item-url')->divide( array('class' => 'my-divider') );
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


## Adding Content to Item's Title


You can `append` or `prepend` HTML or plain text to each item's title after it is defined:

```php
<?php
Menu::make('MyNavBar', function($menu){

  // ...
  
  $about = $menu->add('About',    array('route'  => 'page.about', 'class' => 'navbar navbar-about dropdown'));
  
  $menu->about->attr(array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'))
              ->append(' <b classs="caret"></b>')
              ->prepend('<span classs="glyphicon glyphicon-user"></span> ');
              
  // ...            

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

## Plain Text Items

To insert items as plain text instead of hyper-links you can user `text()`:

```php
<?php
    // ...
    $menu->text('Item Title', array('class' => 'some-class'));  
    
    $menu->add('About', 'about');
    $menu->About->text('Another Plain Text Item')
    // ...
    
    /* Output as an unordered list:
       <ul>
            ...
            <li class="some-class">Item's Title</li>
            <li>
                About
                <ul>
                    <li>Another Plain Text Item</li>
                </ul>
            </li>
            ...
        </ul>
    */
?>
```


## Menu Groups

Sometimes you may need to share attributes between a group of items. Instead of specifying the attributes and options for each item, you may use a menu group feature:

**PS:** This feature works exactly like Laravel group routes. 


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

Just like Laravel route prefixing feature, a group of menu items may be prefixed by using the `prefix` option in the passing array to group.

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

Laravel Menu supports nested grouping feature as well. A menu group merges its own attribute with its parent group then shares them between its wrapped items:

```php
<?php
Menu::make('MyNavBar', function($menu){

	// ...
	
	$menu->group(array('prefix' => 'pages', 'data-info' => 'test'), function($m){
		
		$m->add('About', 'about');
		
		$m->group(array('prefix' => 'about', 'data-role' => 'navigation'), function($a){
		
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

You might encounter situations when you need to attach some meta data to each item; This data can be anything from item placement order to permissions required for accessing the item; You can do this by using `data()` method.

`data()` method works exactly like `attr()` method:

If you call `data()` with one argument, it will return the data value for you.
If you call it with two arguments, It will consider the first and second parameters as a key/value pair and sets the data. 
You can also pass an associative array of data if you need to add a group of key/value pairs in one step; Lastly if you call it without any arguments it will return all data as an array.

```php
<?php
Menu::make('MyNavBar', function($menu){

  // ...
  
  $menu->add('Users', array('route'  => 'admin.users'))
       ->data('permission', 'manage_users');

});
?>
```

You can also access a data as if it's a property:

```php
<?php
	
	//...
	
	$menu->add('Users', '#');
	
	$menu->users->data('placement', 12);
	
	// you can refer to placement as if it's a public property of the item object
	echo $menu->users->placement;    // Output : 12
	
	//...
?>
```

Meta data don't do anything to the item and won't be rendered in HTML either. It is the developer who would decide what to do with them.


## Filtering Menu Items

We can filter menu items by a using `filter()` method. 
`Filter()` receives a closure which is defined by you.It then iterates over the items and run your closure on each of them.

You must return false for items you want to exclude and true for those you want to keep.


Let's proceed with a real world scenario:

I suppose your `User` model can check whether the user has an specific permission or not:

```php
<?php
Menu::make('MyNavBar', function($menu){

  // ...
  
  $menu->add('Users', array('route'  => 'admin.users'))
       ->data('permission', 'manage_users');

})->filter(function($item){
  if(User::get()->hasRole( $item->meta('permission'))) {
      return true;
  }
  return false;
});
?>
```
As you might have noticed we attached the required permission for each item using `data()`.

As result, `Users` item will be visible to those who has the `manage_users` permission.


## Sorting the Items

`laravel-menu` can sort the items based on a user defined function Or a key which can be item properties like id,parent,etc or meta data stored with each item.

Passing a closure:

```php
<?php
Menu::make('main', function($m){

	$m->add('About', '#')     ->data('order', 2);
	$m->add('Home', '#')      ->data('order', 1);
	$m->add('Services', '#')  ->data('order', 3);
	$m->add('Contact', '#')   ->data('order', 5);
	$m->add('Portfolio', '#') ->data('order', 4);

})->sortBy(function($items) {
	// Your sorting algorithm here...
	
});		
?>
```

The closure receives the items collection as an array.

You can also use available properties and meta data to sort the items:

```php
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

`sortBy()` also receives a second parameter which specifies the ordering direction: Ascending order(`asc`) and Descending Order(`dsc`). 

Default value is `asc`.


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

`asDiv()` method will render your menu as nested HTML divs. it also takes an optional parameter to define attributes for the parent `<div>` tag itself:

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

Laravel Menu provides a parital view out of the box which generates menu items in a bootstrap friendly format which you can **include** in your Bootstrap based navigation bars:

You can access the partial view using `Config`.

All you need to do is to pass the root level items to the partial view:

```
{{{...}}}

@include(Config::get('laravel-menu::views.bootstrap-items'), array('items' => $mainNav->roots()))

{{{...}}}

```

This how your Bootstrap code is going to look like:

```html
<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Brand</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">

       @include(Config::get('laravel-menu::views.bootstrap-items'), array('items' => $mainNav->roots()))

      </ul>
      <form class="navbar-form navbar-right" role="search">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Search">
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>
      <ul class="nav navbar-nav navbar-right">

        @include(Config::get('laravel-menu::views.bootstrap-items'), array('items' => $loginNav->roots()))

      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
```

I've prepared a tutorial about embedding several menu objects in a bootstrap navbar in case somebody is interested.
You can read all about it [here](https://gist.github.com/lavary/c9da317446e2e3b32779).

## Advanced Usage

As noted earlier you can create your own rendering formats.

If you'd like to render your menu(s) according to your own design, you should create two views.

* `View-1`  This view contains all the HTML codes like `nav` or `ul` or `div` tags wrapping your menu items.
* `View-2`  This view is actually a partial view responsible for rendering menu items (it is going to be included in `View-1`.)


The reason we use two view files here is that `View-2` calls itself recursively to render the items to the deepest level required in multi-level menus.

Let's make this easier with an example:

In our `routes.php`:

```php
<?php
Menu::make('MyNavBar', function($menu){
  
  $menu->add('Home');
  
   $menu->add('About',    array('route'  => 'page.about'));
   
   $menu->about->add('Who are we?', 'who-we-are');
   $menu->about->add('What we do?', 'what-we-do');

  $menu->add('services', 'services');
  $menu->add('Contact',  'contact');
  
});
?>
```

In this example we name View-1 `custom-menu.blade.php` and View-2 `custom-menu-items.blade.php`.

**custom-menu.blade.php**
```
<nav class="navbar">
  <ul class="horizontal-navbar">
    @include('custom-menu-items', array('items', $MyNavBar->roots()))
  </ul>
</nav><!--/nav-->
```

**custom-menu-items.blade.php**
```
@foreach($items as $item)
  <li @if($item->hasChildren()) class="dropdown" @endif>
      <a href="{{ $item->url }}">{{ $item->title }} </a>
      @if($item->hasChildren())
        <ul class="dropdown-menu">
              @include('custom-menu-items', array('items' => $item->children()))
        </ul> 
      @endif
  </li>
@endforeach
```

Let's describe what we did above, In `custom-menus.blade.php` we put whatever HTML boilerplate code we had according to our design, then we included `custom-menu-items.blade.php` and passed the menu items at *root level* to `custom-menu-items.blade.php`:

```php
...
@include('custom-menu-items', array('items' => $menu->roots()))
...
```

In `custom-menu-items.blade.php` we ran a `foreach` loop and called the file recursively in case the current item had any children.

To put the rendered menu in your application template, you can simply include `custom-menu` view in your master layout.

## Blade Control Structure

You might encounter situations when some of your HTML properties are explicitly written inside your view instead of dynamically being defined when adding the item; However you will need to merge these static attributes with your Item's attributes.

```
@foreach($items as $item)
  <li @if($item->hasChildren()) class="dropdown" @endif data-test="test">
      <a href="{{ $item->url }}">{{ $item->title }} </a>
      @if($item->hasChildren())
        <ul class="dropdown-menu">
              @include('custom-menu-items', array('items' => $item->children()))
        </ul> 
      @endif
  </li>
@endforeach
```

In the above snippet the `li` tag has class `dropdown` and `data-test` property explicitly defined in the view; Laravel Menu provides a control structure which takes care of this.

Suppose the item has also several attributes dynamically defined when being added:

```php
<?php
// ...
$menu->add('Dropdown', array('class' => 'item item-1', 'id' => 'my-item'));
// ...
```

The view:

```
@foreach($items as $item)
  <li@lm-attrs($item) @if($item->hasChildren()) class="dropdown" @endif data-test="test" @lm-endattrs>
      <a href="{{ $item->url }}">{{ $item->title }} </a>
      @if($item->hasChildren())
        <ul class="dropdown-menu">
              @include('custom-menu-items', array('items' => $item->children()))
        </ul> 
      @endif
  </li>
@endforeach
```

This control structure automatically merges the static HTML properties with the dynamically defined properties.

Here's the result:

```
...
<li class="item item-1 dropdown" id="my-item" data-test="test">...</li>
...
```



## If You Need Help

Please submit all issues and questions using GitHub issues and I will try to help you.


## License

*Laravel-Menu* is free software distributed under the terms of the MIT license.
