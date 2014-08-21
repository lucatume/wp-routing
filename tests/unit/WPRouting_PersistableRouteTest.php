<?php
class WP_Routing_PersistableRouteTest extends tad_TestCase
{
    protected $sut = null;
    protected $f;
    protected $router;
    protected $option;
    
    public function setUp()
    {
        $this->f = $this->getMockFunctions(array(
            'add_action',
            'do_action'
        ));
        $this->router = $this->getMock('WP_Router', array(
            'add_route'
        ));
        $this->option = $this->getMock('tad_Option', array(
            'setValue'
        ));
        
        // reset the WPRouting_PersistableRoute
        WPRouting_PersistableRoute::set('routes', array());
        WPRouting_PersistableRoute::set('patterns', array());
        WPRouting_PersistableRoute::set('option', $this->option);
        
        // set up the subject under test
        $this->sut = new WPRouting_PersistableRoute($this->f, $this->option);
    }
    
    public function testItShouldBeInstantiatable()
    {
        $this->assertInstanceOf('WPRouting_PersistableRoute', $this->sut);
    }
    
    public function testItShouldAllowTriggerRoutePersistenceUsingTheShouldBePersistedMethod()
    {
        $path = 'hello';
        $id = 'hello';
        $callback = function ()
        {
            echo 'Hello there';
        };
        
        $args = array(
            'path' => '^hello$',
            'page_callback' => array(
                'GET' => $callback
            ) ,
            'shouldBePersisted' => true,
            'template' => false,
            'permalink' => 'hello'
        );
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->shouldBePersisted();
        $this->sut->__destruct();
        WPRouting_PersistableRoute::generateRoutes($this->router);
    }
    
    public function testItShouldAddRouteMetaToTheWpRouterRoutesMetaOption()
    {
        $path = 'hello';
        $id = 'hello';
        
        // what the route will return
        $callback = function ()
        {
            echo 'Hello there';
        };
        
        // it should store the route meta using the option
        $this->option->expects($this->once())->method('setValue')->with($id, array(
            'title' => 'Hello route',
            'permalink' => 'hello'
        ));
        $this->sut->hook();
        
        // add a GET method route to the /hello path with the above callback
        $this->sut->_get($path, $callback)->shouldBePersisted()->withTitle('Hello route');
        
        // destroy the instance to trigger WPRouting_Route::replacePatterns
        $this->sut->__destruct();
        WPRouting_PersistableRoute::generateRoutes($this->router);
    }
    
    /**
     * @test
     * it should allow setting the persistence using the shouldBePersisted method
     */
    public function it_should_allow_setting_the_persistence_using_the_shouldBePersisted_method()
    {
        $sut = new WPRouting_PersistableRoute();
        $this->assertFalse($sut->willBePersisted());
        $sut->shouldBePersisted(false);
        $this->assertFalse($sut->willBePersisted());
        $sut->shouldBePersisted();
        $this->assertTrue($sut->willBePersisted());
        $sut->shouldBePersisted(false);
        $this->assertFalse($sut->willBePersisted());
        $sut->shouldBePersisted(true);
        $this->assertTrue($sut->willBePersisted());
    }
    
    /**
     * @test
     * it should persist all the route args
     */
    public function it_should_persist_all_the_route_args()
    {
        $path = 'hello';
        $id = 'hello';
        
        // what the route will return
        $callback = function ()
        {
            echo 'Hello there';
        };
        
        // it should store the route meta using the option
        $this->option->expects($this->once())->method('setValue')->with($id, array(
            'title' => 'Hello route',
            'permalink' => 'hello',
            'someMeta' => 'foo',
            'anotherMeta' => 23
        ));
        $this->sut->hook();
        
        // add a GET method route to the /hello path with the above callback
        $this->sut->_get($path, $callback)->shouldBePersisted()->withTitle('Hello route')->with('someMeta', 'foo')->with('anotherMeta', 23);

        // destroy the instance to trigger WPRouting_Route::replacePatterns
        $this->sut->__destruct();
        WPRouting_PersistableRoute::generateRoutes($this->router);
    }

    /**
     * @test
     * it should not allow setting the permalink using the with method
     */
    public function it_should_not_allow_setting_the_permalink_using_the_with_method()
    {
        $path = 'hello';
        $id = 'hello';

        // what the route will return
        $callback = function () {
            echo 'Hello there';
        };
        // add a GET method route to the /hello path with the above callback
        $this->setExpectedException('InvalidArgumentException', null, 3);
        $this->sut->_get($path, $callback)->shouldBePersisted()->withTitle('Hello route')->with('permalink', 'No good permalink');
    }
}
