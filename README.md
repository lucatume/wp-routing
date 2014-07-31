# WordPress Wrappers

A set of wrapping classes that should allow Laravel-like routing in WordPress thanks to [WP Router](https://wordpress.org/plugins/wp-router/) plugin and some packages from [Laravel](http://laravel.com/).

## Including the library in a theme or plugin
The library is meant to be used with [Composer](https://getcomposer.org/) in a Composer-managed project and to include in the project the following line should be added to <code>composer.json</code> require entry

    {
        "require": {
            "lucatume/wp-routing": "dev-master"
        }
    } 

## Setting routes
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

    use tad\wrappers\WP_Router\WP_Routing_Route;

    WP_Routing_Route::get('wp_router/{word}', function($word){
            echo "Hello $word";
        })->where('word', '.*?')
          ->withTitle('Wp Router Sample Page')
          ->withTemplate(array(
            'sample-page',
            dirname(__FILE__).DIRECTORY_SEPARATOR.'sample-page.php'
            );

## WP_Routing_Route path arguments
To allow for a more flexible route arguments handling the [<code>Illuminate\Http</code> package](https://github.com/illuminate/http) is pulled in along with other library requirements; this allows using the classes there defined. Some of those classes are wrapped to allow some degree of static access to them. Among those wrapped classes is <code>Illuminate\Http\WP_Routing_Request</code>; no argument will be passed to callback functions when using the <code>tad\wrapper\WP_Router\WP_Routing_Route</code> class and route arguments will have to be fetched insided the callback function like

    WP_Routing_Route::get('hello/{name}', function ()
    {
        $name = WP_Routing_Request::init()->segment(2);
        echo "Hello $name";
    })->where('name', '\w+?');
