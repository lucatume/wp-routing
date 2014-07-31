<?php

/**
 * A wrapping class around the original
 */
class WP_Routing_Request
{
    private static $instance = null;
    private static $globals = null;
    
    public static function __callStatic($name, $arguments)
    {
        
        // maybe initiliaze the instance
        if (is_null(self::$instance)) {
            self::$instance = IRequest::createFromBase(SRequest::createFromGlobals());
        }
        if (method_exists(self::$instance, $name)) {
            return call_user_func_array(array(self::$instance, $name), $arguments);
        }
        $className = '\Illuminate\Http\Request';
        throw new \BadMethodCallException("Method $name is not a $className method.", 1);
    }
    public static function query($key = null, $default = null)
    {
        $vars = self::$globals->wp('query_vars');
        if (!$vars) {
            return $default;
        }
        if (is_array($vars) and isset($vars[$key])) {
            return $vars[$key];
        }
        if (!$key) {
            return $vars;
        }
        return $default;
    }
    public static function _setInstance($instance = null)
    {
        self::$instance = $instance;
    }
    public static function _setGlobals($globals = null)
    {
        self::$globals = $globals;
    }
}
