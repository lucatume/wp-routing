<?php

namespace tad\wrappers\Illuminate\Http;


use \Illuminate\Http\Request as IRequest;
use \Symfony\Component\HttpFoundation\Request as SRequest;

/**
 * A wrapping class around the original one to bootstrap request handling
 */
class Request
{
    private static $instance = null;
    
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
    public static function _setInstance($instance = null)
    {
        self::$instance = $instance;
    }
}
