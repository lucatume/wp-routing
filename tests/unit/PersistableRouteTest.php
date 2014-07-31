<?php
use tad\adapters\Globals;
use tad\interfaces\GlobalsAdapter;
use tad\wrappers\Option;
use tad\wrappers\WP_Router\PersistableRoute;

class PersistableRouteTest extends \tad\test\cases\TadLibTestCase
{
    protected $sut = null;

    public function setUp()
    {
        $this->f = $this->getMockFunctions(array('add_action', 'do_action'));
        $this->router = $this->getMock('\WP_Router', array('add_route'));
        $this->option = $this->getMock('\tad\wrappers\Option', array('setValue'));

        // reset the WP_Routing_PersistableRoute
        PersistableRoute::set('routes', array());
        PersistableRoute::set('patterns', array());
        PersistableRoute::set('option', $this->option);

        // set up the subject under test
        $this->sut = new PersistableRoute($this->f, $this->option);
    }

    public function testItShouldBeInstantiatable()
    {
        $this->assertInstanceOf('\tad\wrappers\WP_Router\PersistableRoute', $this->sut);
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
        PersistableRoute::generateRoutes($this->router);
    }

    public function testItShouldAddRouteMetaToTheWpRouterRoutesMetaOption()
    {
        $path = 'hello';
        $id = 'hello';
        $callback = function () {
            echo 'Hello there';
        };
        $this->option->expects($this->once())
            ->method('setValue')
            ->with($id, array('title' => 'Hello route', 'permalink' => 'hello'));
        $this->sut->hook();
        $this->sut->_get($path, $callback)->shouldBePersisted()->withTitle('Hello route');
        $this->sut->__destruct();
        PersistableRoute::generateRoutes($this->router);
    }
}
