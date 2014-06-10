<?php

namespace tad\wrappers\Wp_Router;


use tad\interfaces\FunctionsAdapter;
use tad\wrappers\Option;
use tad\wrappers\WP_Router\Route;

class PersistableRoute extends Route
{
    protected static $option = null;
    const OPTION_ID = 'WP_Router_routes_meta';

    /**
     * @param FunctionsAdapter $f
     * @param Option $option
     */
    public function __construct(FunctionsAdapter $f = null, Option $option = null)
    {
        if (is_null($option)) {
            $option = Option::on(self::OPTION_ID);
        }
        $this->option = $option;
        parent::__construct($f);
    }

    /**
     * @param string $routeId
     * @param array $args
     */
    protected  static function actOnRoute($routeId, Array $args)
    {
        // if the route should not be persisted return
        if (!isset($args['shouldBePersisted']) or !$args['shouldBePersisted']) {
            return;
        }

        // if the route title is not set return
        if (!isset($args['title']) or !is_string($args['title'])) {
            return;
        }

        // if the route permalink is not set return
        if (!isset($args['permalink']) or !is_string($args['permalink']) or !preg_match("/[\\/\\w]*/ui", $args['permalink'])) {
            return;
        }

        // persist the route using the id as the key and storing the title and the permalink
        self::$option->setVar($routeId, ['title' => $args['title'], 'permalink' => $args['permalink']]);
    }

    protected function replacePatterns($patterns){
        parent::replacePatterns($patterns);
        // set the permalink to something like /path
        $this->args['permalink'] = '/' . rtrim(ltrim($this->args['path'], '^/'), '$/');
    }

    public function shouldBePersisted()
    {
        $this->args['shouldBePersisted'] = true;
        return $this;
    }
}
