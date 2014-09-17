# WordPress Wrappers

A set of wrapping classes that should allow Laravel-like routing in WordPress thanks to [WP Router](https://wordpress.org/plugins/wp-router/) plugin and some packages from [Laravel](http://laravel.com/).

## Including the library in a theme or plugin
The library is meant to be used with [Composer](https://getcomposer.org/) in a Composer-managed project and to include in the project the following line should be added to <code>composer.json</code> require entry

    {
        "require": {
            "lucatume/wp-routing": "dev-master"
        }
    } 

## WPRouting_Route

### Setting routes
While the inner workings of the wrapper classes will build on WP Router code and hence any example applies the library will wrap the methods required to set up routes in a Laravel-like interface to go from this

    // file my-routes-plugin.php

    add_action('wp_router_generate_routes', 'generateMyRoutes');

    function generateMyRoutes(WP_Router $router)
    {
        $router->add_route('wp-router-sample', array(
            'path' => '^wp_router/(.*?)$',
            'query_vars' => array(
                'sample_argument' => 1,
            ),
            'page_callback' => array(get_class(), 'sample_callback'),
            'page_arguments' => array('sample_argument'),
            'access_callback' => TRUE,
            'title' => 'WP Router Sample Page',
            'template' => array('sample-page.php', dirname(__FILE__).DIRECTORY_SEPARATOR.'sample-page.php')
        ));
    }

to this

    // file my-routes-plugin.php

    WPRouting_Route::get('wp_router/{word}', function($word){
            echo "Hello $word";
        })->where('word', '.*?')
          ->withTitle('Wp Router Sample Page')
          ->withTemplate(array(
            'sample-page',
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sample-page.php'
            );

### Route methods
The entry point of any route generation is always one of 4 methods:

* `WPRouting_Route::get` to handle `GET` requests
* `WPRouting_Route::post` to handle `POST` requests
* `WPRouting_Route::put` to handle `PUT` requests
* `WPRouting_Route::delete` to handle `DELETE` requests
* `WPRouting_Route::all` to handle all requests

each one of the methods above will take two arguments

* a path relative to the root URL
* a filters and callback parameters parameter

The path can contain placeholders strings like `{word}` in the example above that will require a definition using the `where` method later in the fluent chain.  
The second parameter can be a simple callback function or be an array containing:

* an array or a pipe char separated list of filters
* a callback function

Any filter needs to be registered using the `filter` static method.

* `withTitle`: sets a route page title, can be a string or a callback function.
* `withTemplate`: sets the template to be used to display the route, follows the same mechanic [WP Router follows.](https://wordpress.org/plugins/wp-router/other_notes/)
* `with`: sets a key/value pair of meta information to be attached to a route.
* `withId`: explicitly sets the route id, normally the route id would be constructed dash-separating-the-path.
* `where`: sets a pattern to be used for the route path.
* `filter`: static, sets a filter to be used for the route access callback.
* `pattern`: static, sets a pattern to be used for all routes.

#### Examples
Please note that path variables are passed in their appearance order to the page, access and title  callback methods/functions.  
I want to add a `/posts` page displaying the posts archive

    WPRouting_Route::get('posts', function(){
        echo 'My post archive';
    })->withTitle('Archive');

I wand to add a `/secret-posts` page accessible to admins alone
    
    WPRouting_Route::filter('admin', function(){
            return current_user_can('activate_plugins');
        }) ;

    WPRouting_Route::get('secret-posts', array('admin', function(){
            echo 'Secret posts';
        }))->withTitle('Secret page');

I want to add PUT and POST endpoints for editors to edit posts

    WPRouting_Route::filter('editor', function($id){
            return current_user_can('edit_posts', $id);
        });

    WPRouting_Route::pattern('id', '\d+');

    WPRouting_Route::put('posts/{id}', array('editor', function($id){
            echo 'Doing some post updating';
        }));

    WPRouting_Route::post('posts/{id}', array('editor', function($id){
            echo 'Adding a post';
        }));

## WPRouting_PersistableRoute
An extension of the base `WPRouting_Route` class that allows persisting route meta information to the WordPress database.

### Persisting a route meta information
The class defines a `shouldBePersisted` method accepting a boolean that will trigger route meta information when set to `true`; **by default routes will not be persisted**.  
At a bare minimum a route must define a title and a path to be eligible for persistence; it's a fluent method to be used like

        // file my-routes-plugin.php

    WPRouting_PersistableRoute::get('hello', function(){
            echo "Hello there";
        })->withTitle('Wp Router Sample Page')
          ->withTemplate(array(
            'sample-page',
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sample-page.php'
            )
          ->shouldBePersisted();

The `shouldBePersisted` method will default to `true` if no value is passed to it and hence 

    ->shouldBePersisted();
    ->shouldBePersisted(true);

If the persistence of the route meta is set to `true`  then any route argument not related to WP Router (see the `WPRouting_Route::$WPRouterArgs` variable) and not excluded from persistence (see `WPRouting_PersistableRoute::$nonPersistingArgs` variable) will be persisted as route meta information.
All routes meta will be stored in a single option in WordPress database (see `WPRouting_PersistableRoute::OPTION_ID` constant for its value) in an array using the structure
    
    [
        'route-one' :
            ['title' => 'Route One', 'permalink' => 'route-one', 'some-meta' => 'some meta value']
        'route-two' :
            ['title' => 'Route Two', 'permalink' => 'route-two', 'some-meta' => 'some meta value']
    ]

where 1st level array keys are the routes ids.

### Hooking to alter the route meta information
**Before** route meta information is persisted to the database and **after** the argument pruning has been made the `WPRouting_PersistableRoute` class offers a filter hook (see <code>WPRouting_PersistableRoute::ROUTE_PERSISTED_VALUES_FILTER</code> for its tag) to alter the route meta before it's persisted to the database.  
The arguments for the filter function are the route arguments and the route id, **the filter will trigger one time for each route**.
