<?php

class WP_Routing_PersistableRouteTest extends tad_TestCase
{
    protected $sut = null;
    protected $f;
    protected $router;
    protected $option;

    public function setUp()
    {
        $this->f = $this->getMockFunctions(array('add_action', 'do_action'));
        $this->router = $this->getMock('WP_Router', array('add_route'));
        $this->option = $this->getMock('tad_Option', array('setValue'));

        // reset the WP_Routing_PersistableRoute
        WP_Routing_PersistableRoute::set('routes', array());
        WP_Routing_PersistableRoute::set('patterns', array());
        WP_Routing_PersistableRoute::set('option', $this->option);

        // set up the subject under test
        $this->sut = new WP_Routing_PersistableRoute($this->f, $this->option);
    }

    public function testItShouldBeInstantiatable()
    {
        $this->assertInstanceOf('WP_Routing_PersistableRoute', $this->sut);
    }

    public function testItShouldAllowTriggerRoutePersistenceUsingTheShouldBePersistedMethod()
    {
        $path = 'hello';
        $id = 'hello';
        $callback = function () {
            echo 'Hello there';
        };

        $args = array('path' => '^hello$', 'page_callback' => array('GET' => $callback), 'shouldBePersisted' => true, 'template' => false, 'permalink' => 'hello');
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->shouldBePersisted();
        $this->sut->__destruct();
        WP_Routing_PersistableRoute::generateRoutes($this->router);
    }

    public function testItShouldAddRouteMetaToTheWpRouterRoutesMetaOption()
    {
        $path = 'hello';
        $id = 'hello';
        // what the route will return
        $callback = function () {
            echo 'Hello there';
        };
        // it should store the route meta using the option
        $this->option->expects($this->once())
            ->method('setValue')
            ->with($id, array('title' => 'Hello route', 'permalink' => 'hello'));
        $this->sut->hook();
        // add a GET method route to the /hello path with the above callback
        $this->sut->_get($path, $callback)
            ->shouldBePersisted()
            ->withTitle('Hello route');
        // destroy the instance to trigger WP_Routing_Route::replacePatterns
        $this->sut->__destruct();
        WP_Routing_PersistableRoute::generateRoutes($this->router);
    }
}
