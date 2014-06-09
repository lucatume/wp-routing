<?php

namespace tad\wrappers\WP_Router;


use tad\adapters\Functions;
use tad\interfaces\FunctionsAdapter;

/**
 * A wrapper around WP Router method to allow a Laravel-like interface.
 * WP Router plugin by Flightless (http://flightless.us/)
 */
class Route
{
    protected static $routes = array();
    protected static $patterns = array();
    protected static $filters = array();
    protected $f = null;
    protected $path = '';
    protected $id = '';
    protected $args = array();
    protected $routePatterns = array();

    public function __construct(FunctionsAdapter $f = null)
    {
        if (is_null($f)) {
            $f = new Functions();
        }
        $this->f = $f;
    }

    /**
     * The static method that will actually call WP Router to generate the routes.
     *
     * This is the method that will be called by the 'wp_router_generate_routes' action.
     *
     * @param  WP_router $router A WP Router instance.
     * @param  \tad\interfaces\FunctionsAdapter $f An optionally injected functions adapter.
     *
     * @return void
     */
    public static function generateRoutes(\WP_router $router, FunctionsAdapter $f = null)
    {
        if (is_null($f)) {
            $f = new Functions();
        }
        // action hook for plugins and themes to act on the route
        $f->do_action('route_before_adding_routes', self::$routes);
        foreach (static::$routes as $routeId => $args) {
            $router->add_route($routeId, $args);

            // class hook to allow for extending classes to act on the route
            static::actOnRoute($routeId, $args);
        }
        // action hook for plugins and themes to act on the route
        $f->do_action('route_after_adding_routes', self::$routes);
    }

    /**
     * A class-level hook to allow for extending classes to act on each route.
     *
     * @param  string $routeId The route id
     * @param  Array $args The args associated with the route.
     *
     * @return void
     */
    protected static function actOnRoute($routeId, Array $args)
    {
    }

    public static function set($key, $value = null)
    {
        static::${$key} = $value;
    }

    /**
     * Adds a pattern to be used in the routes without having to specify it every time.
     *
     * @param  string $key The slug for the pattern
     * @param  string $pattern The regex pattern.
     *
     * @return void
     */
    public static function pattern($key, $pattern)
    {
        if (!is_string($key)) {
            throw new \BadMethodCallException("Key must be a string", 1);
        }
        if (!is_string($pattern)) {
            throw new \BadMethodCallException("Pattern must be a string", 2);
        }
        self::$patterns[$key] = $pattern;
    }

    /**
     * Adds a filter, access callback, to be used in the routes.
     *
     * @param  string $filterSlug The slug for the filter.
     * @param  callable $filterCallback The callback function for the filter, return TRUE to allow, FALSE to redirect to login.
     *
     * @return void
     */
    public static function filter($filterSlug, $filterCallback)
    {
        if (!is_string($filterSlug) or !preg_match('/[\w]+/', $filterSlug)) {
            throw new \BadMethodCallException("Filter slug mus be a strin with letters, numbers and underscores alone", 1);
        }
        if (!is_callable($filterCallback)) {
            throw new \BadMethodCallException("Filter callback must be a callable", 2);
        }
        self::$filters[$filterSlug] = $filterCallback;
    }

    /**
     * Allows accessing get, post, put and delete method statically.
     *
     * This method offers the convenient static entry point to the class like
     *
     *     Route::get('hello', $callback)->...
     *
     * @param  string $func The method name
     * @param  array $args The method arguments
     *
     * @return Route       A new instance of this class
     */
    public static function __callStatic($func, $args)
    {

        // get defined public methods
        $publicMethods = array('get', 'post', 'put', 'delete');

        // if not $func in defined public method throw
        if (!in_array($func, $publicMethods)) {
            throw new \InvalidArgumentException("$func is not a defined class method", 1);
        }

        // create and set an instance of the class
        $instance = new self();
        $instance->hook();
        call_user_func_array(array($instance, "_$func"), $args);
        return $instance;
    }

    /**
     * Make the route hook into the generate routes action.
     *
     * @return Route The calling instance of the class
     */
    public function hook()
    {
        $this->f->add_action('wp_router_generate_routes', array(__CLASS__, 'generateRoutes'));
        return $this;
    }

    /**
     * Adds a GET method route.
     *
     * @param  string $path The pattern for the route.
     * @param  callable /array $callbackAndFilters Either a function that will return/echo the page content or an array containing filter(s) slug(s) to control the page access like
     *
     *     $route->_get(array('admin', function(){echo 'some';});
     *
     * @return Route                     The calling instance of this class.
     */
    public function _get($path, $callbackAndFilters)
    {
        return $this->base('GET', $path, $callbackAndFilters);
    }

    protected function base($method, $path, $callbackAndFilters)
    {
        $routeFilters = array();
        $pageCallback = null;

        // what is the third argument?
        if (is_callable($callbackAndFilters)) {

            // it's the page callback
            $pageCallback = $callbackAndFilters;
        } else if (is_array($callbackAndFilters)) {

            // it's the filters and callback array
            // filters can be in string or array form
            if (!is_array($callbackAndFilters[0]) and !is_string($callbackAndFilters[0])) {
                throw new \BadMethodCallException("Filters are missing", 4);
            }
            if (!is_callable($callbackAndFilters[1])) {
                throw new \BadMethodCallException("Callback function is missing", 4);
            }

            // filters are in array or pipe-separated form?
            if (is_array($callbackAndFilters[0])) {
                $routeFilters = $callbackAndFilters[0];
            } else {
                $routeFilters = explode('|', $callbackAndFilters[0]);
            }
            $pageCallback = $callbackAndFilters[1];
        } else {
            throw new \BadMethodCallException("Proper call is path and either a page callback function or an array containing filters and then the callback function", 3);
        }

        // create an id from the path like
        // 'hello/some/{path}' to 'hello-some-path'
        $this->id = trim(preg_replace("/-+/ui", '-', preg_replace("/[^\\w-]/ui", "-", $path)), '-');
        $this->method = $method;
        $this->args['path'] = $path;

        // by default do not use the theme template
        $this->args['template'] = false;
        $this->args['page_callback'] = array($method => $pageCallback);

        // how many filters?
        if (count($routeFilters) == 1) {

            // then set it as the access callback
            $this->args['access_callback'] = array($method => self::$filters[$routeFilters[0]]);
        } else if (count($routeFilters) > 1) {

            // if there is more than one filter
            // create a closure to call them in sequence
            $accessCallback = function () use ($routeFilters) {
                foreach ($routeFilters as $routeFilter) {

                    // call the filter
                    self::$filters[$routeFilter]();
                }
            };
            $this->args['access_callback'] = array($method => $accessCallback);
        }
        return $this;
    }

    /**
     * Adds a POST method route.
     *
     * @param  string $path The pattern for the route.
     * @param  callable /array $callbackAndFilters Either a function that will return/echo the page content or an array containing filter(s) slug(s) to control the page access like
     *
     *     $route->_post(array('admin', function(){echo 'some';});
     *
     * @return Route                     The calling instance of this class.
     */
    public function _post($path, $callbackAndFilters)
    {
        return $this->base('POST', $path, $callbackAndFilters);
    }

    /**
     * Adds a PUT method route.
     *
     * @param  string $path The pattern for the route.
     * @param  callable /array $callbackAndFilters Either a function that will return/echo the page content or an array containing filter(s) slug(s) to control the page access like
     *
     *     $route->_put(array('admin', function(){echo 'some';});
     *
     * @return Route                     The calling instance of this class.
     */
    public function _put($path, $callbackAndFilters)
    {
        return $this->base('PUT', $path, $callbackAndFilters);
    }

    /**
     * Adds a DELETE method route.
     *
     * @param  string $path The pattern for the route.
     * @param  callable /array $callbackAndFilters Either a function that will return/echo the page content or an array containing filter(s) slug(s) to control the page access like
     *
     *     $route->_delete(array('admin', function(){echo 'some';});
     *
     * @return Route                     The calling instance of this class.
     */
    public function _delete($path, $callbackAndFilters)
    {
        return $this->base('DELETE', $path, $callbackAndFilters);
    }

    /**
     * Allows setting key/regex pattern couples.
     *
     * Allows writing paths in a more legible way like
     *
     *     Route::get('hello/{name}', $callback)->where('name', '\w+');
     *
     * @param  string $keyOrArray The slug for the path component
     * @param  string $pattern The corresponding regex pattern
     *
     * @return Route             The calling instance of the class.
     */
    public function where($keyOrArray, $pattern = null)
    {
        if (!is_array($keyOrArray) and !is_string($keyOrArray)) {
            throw new \BadMethodCallException("Key must be a string or an array of key/patterns", 1);
        }
        if (!is_null($pattern) and !is_string($pattern)) {
            throw new \BadMethodCallException("Group must be a regex pattern", 2);
        }
        $couples = $keyOrArray;
        if (is_string($keyOrArray)) {
            $couples = array($keyOrArray => $pattern);
        }

        // save the patterns local to the route
        $this->routePatterns = array_merge($this->routePatterns, $couples);
        return $this;
    }

    /**
     * Allows specifying the id for the route.
     *
     * By default the id will be set based on the path, as an example
     *
     *     Route::get('hello/{name}', $callback);
     *
     * would have an id of 'hello-name'. This method will override that.
     *
     * @param  string $id The new id
     *
     * @return Route     The calling instance of this class
     */
    public function withId($id)
    {
        if (!is_string($id)) {
            throw new \BadMethodCallException("Id must be a string", 1);
        }
        if (!preg_match('/[\w_]*/', $id)) {
            throw new \BadMethodCallException("Id must contain only letters, numbers and underscores", 2);
        }
        $this->$id = $id;
        return $this;
    }

    /**
     * Allows adding additional information to a route.
     *
     * Additional arguments will be ignored by WP Router.
     *
     * @param  string $key The key for the information to add.
     * @param  mixed $value The value for the information to add.
     *
     * @return Route         The calling instance.
     */
    public function with($key, $value)
    {
        if (!is_string($key)) {
            throw new \BadMethodCallException("Key must be a string", 1);
        }
        $methodAccessibleArgs = array('template', 'query_vars', 'id', 'page_arguments', 'page_callback', 'access_arguments', 'access_callback', 'title_arguments', 'title_callback', 'title', 'path');
        if (in_array($key, $methodAccessibleArgs)) {
            throw new \InvalidArgumentException("Argument $key should be set with its dedicated method.", 2);
        }
        $this->args[$key] = $value;
        return $this;
    }

    /**
     * Allows specifying which templates to use.
     *
     * The method allows passing templates using their basename minus the .php
     * file extension like
     *
     *     ->withTemplate('page');
     *     ->withTemplate(array('page', 'some-template', 'single'));
     *
     * @param  string /array $templateOrArray The basename of the template or an array of basenames, will respect paths.
     *
     * @return Route                  The calling instance of the Route.
     */
    public function withTemplate($templateOrArray)
    {
        if (!is_string($templateOrArray) and !is_array($templateOrArray)) {
            throw new \BadMethodCallException("Template must either be a string or an array of strings", 1);
        }
        $templates = $templateOrArray;

        // if it's a string make it an array
        if (is_string($templateOrArray)) {
            $templates = array($templateOrArray);
        }

        // add .php extension where needed
        foreach ($templates as & $template) {
            $template = str_replace('.php', '', $template) . '.php';
        }

        // set the template in the route
        $this->args['template'] = $templates;
        return $this;
    }

    /**
     * Allows setting the title that will be returned in functions like the_title.
     *
     * @param  callable /string $callbackOrString Either a function to generate the title or a string to be returned as is.
     *
     * @return Route                   The calling instance of the Route.
     */
    public function withTitle($callbackOrString)
    {
        if (!is_callable($callbackOrString) and !is_string($callbackOrString)) {
            throw new \BadMethodCallException("Title must be a callback function or a string", 1);
        }
        if (is_callable($callbackOrString)) {
            $this->args['title_callback'] = array($this->method => $callbackOrString);
        } else {

            // is a string
            $this->args['title'] = $callbackOrString;
        }
        return $this;
    }

    /**
     * The method will close the fluent chain effectively registering the route.
     * Please note that
     *
     *     title_callback
     *     page_callback
     *     access_callback
     *
     * all will not receive any argument.
     */
    public function __destruct()
    {

        // replace the registered patterns
        $this->replacePatterns(self::$patterns);

        // replace the patterns local to the route
        $this->replacePatterns($this->routePatterns);
        self::$routes[$this->id] = $this->args;
    }

    protected function replacePatterns($patterns)
    {
        foreach ($patterns as $key => $pattern) {

            // convert the pattern in the path
            $match = '~\{' . $key . '\}~';
            $replacement = '(' . trim($pattern, '()') . ')';
            $this->args['path'] = preg_replace($match, $replacement, $this->args['path']);
            if (!isset($this->args['query_vars'])) {
                $this->args['query_vars'] = array();
            }
            $this->args['query_vars'][$key] = count($this->args['query_vars']) + 1;
        }

        // take care of initial caret and ending dollar sign
        $this->args['path'] = '^' . rtrim(ltrim($this->args['path'], '^/'), '$/') . '$';
        // set the permalink to something like /path
        $this->args['permalink'] = '/' . rtrim(ltrim($this->args['path'], '^/'), '$/');
    }
}
