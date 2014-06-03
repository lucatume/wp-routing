<?php

namespace tad\wrappers\Illuminate\Http;


use \Illuminate\Http\Request as IRequest;
use \Symfony\Component\HttpFoundation\Request as SRequest;

/**
 * A wrapping class around the original one to bootstrap request handling
 */
class Request extends IRequest
{
    private static $instance = null;
    
    public static function init()
    {
        
        // maybe initiliaze the instance
        if (is_null(self::$instance)) {
            self::$instance = IRequest::createFromBase(SRequest::createFromGlobals());
        }
        return self::$instance;
    }
}
