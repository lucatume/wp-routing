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
            dirname(__FILE__).DIRECTORY_SEPARATOR.'sample-page.php'
            );

## WPRouting_PersistableRoute
An extension of the base `WPRouting_Route` class that allows persisting route meta information to the WordPress database.

### Persisting a route meta information
The class defines a `shouldBePersisted` method accepting a boolean that will trigger route meta information when set to `true`; **by default routes will not be persisted**.  
At a bare minimum a route must define a title and a path to be eligible for persistence; it's a fluent method to be used like

        // file my-routes-plugin.php

    WPRouting_Route::get('wp_router/{word}', function($word){
            echo "Hello $word";
        })->where('word', '.*?')
          ->withTitle('Wp Router Sample Page')
          ->withTemplate(array(
            'sample-page',
            dirname(__FILE__).DIRECTORY_SEPARATOR.'sample-page.php'
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