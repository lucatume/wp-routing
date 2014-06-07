<?php
namespace tad\wrappers\Wp_Router;

use \tad\wrappers\WP_Router\Route;
use \tad\wrappers\Option;

class PersistableRoute extends Route
{
    public function __construct(FunctionsAdapter $f = null, Option $option = null){
        if (is_null($option)) {
            $option = Option::on('WP_Router_routes_information');
        }
        $this->option = $option;
        parent::__construct($f);
    }
    private static function actOnRoute()
    {

    }
    public function shouldBePersisted(){
        $this->args['shouldBePersisted'] = true;
        return $this; 
    }
}
