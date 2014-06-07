<?php

use tad\wrappers\WP_Router\PersistableRoute;
use tad\interfaces\GlobalsAdapter;
use tad\adapters\Globals;

class PersistableRouteTest extends \tad\test\cases\TadLibTestCase
{
    protected $sut = null;
    public function setUp()
    {
        $this->f = $this->getMockFunctions(array('add_action', 'do_action'));
        $this->router = $this->getMock('\WP_Router', array('add_route'));
        
        // reset the PersistableRoute
        PersistableRoute::set('routes', array());
        PersistableRoute::set('patterns', array());
        $this->sut = new PersistableRoute($this->f);
    }

    protected function tearDown()
    {
    }

    public function testItShouldBeInstantiatable()
    {
        $this->assertInstanceOf('\tad\wrappers\WP_Router\PersistableRoute', $this->sut);
    }

}