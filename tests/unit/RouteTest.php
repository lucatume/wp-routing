<?php
use tad\wrappers\WP_Router\Route;

class RouteTest extends \tad\test\cases\TadLibTestCase
{
    protected $sut = null;
    public function setUp()
    {
        $this->f = $this->getMockFunctions(array('add_action', 'do_action'));
        $this->router = $this->getMock('\WP_Router', array('add_route'));
        
        // reset the Route
        Route::set('routes', array());
        Route::set('patterns', array());
        $this->sut = new Route($this->f);
    }
    public function tearDown()
    {
    }
    public function testItShouldHook()
    {
        $this->f->expects($this->once())->method('add_action')->with('wp_router_generate_routes', array('tad\wrappers\WP_Router\Route', 'generateRoutes'));
        $this->sut->hook();
    }
    public function testGetRoutesCanBeAddedUsingGetMethod()
    {
        $path = 'some/path';
        $id = 'some-path';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/path$', 'page_callback' => array('GET' => $callback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback);
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testPostRoutesCanBeAddedUsingPostMethod()
    {
        $path = '/some/path';
        $id = 'some-path';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/path$', 'page_callback' => array('POST' => $callback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_post($path, $callback);
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testPutRoutesCanBeAddedUsingPutMethod()
    {
        $path = '/some/path';
        $id = 'some-path';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/path$', 'page_callback' => array('PUT' => $callback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_put($path, $callback);
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testDeleteRoutesCanBeAddedUsingDeleteMethod()
    {
        $path = '/some/path';
        $id = 'some-path';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/path$', 'page_callback' => array('DELETE' => $callback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_delete($path, $callback);
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItAllowsSettingQueryVars()
    {
        $path = '/some/{type}/{id}';
        $id = 'some-type-id';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/(event|post)/([0-9]+)$', 'page_callback' => array('GET' => $callback), 'query_vars' => array('type' => 1, 'id' => 2), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->where('type', '(event|post)')->where('id', '[0-9]+');
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItAllowsSettingQueryVarsUsingArray()
    {
        $path = '/some/{type}/{id}/{foo}';
        $id = 'some-type-id-foo';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/(event|post)/([0-9]+)/([\w]{3})$', 'page_callback' => array('GET' => $callback), 'query_vars' => array('type' => 1, 'id' => 2, 'foo' => 3), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->where(array('type' => '(event|post)', 'id' => '[0-9]+', 'foo' => '[\w]{3}'));
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItAllowsSettingTheId()
    {
        $path = '/some/{type}/{id}/{foo}';
        $id = 'some-type-id-foo';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/(event|post)/([0-9]+)/([\w]{3})$', 'page_callback' => array('GET' => $callback), 'query_vars' => array('type' => 1, 'id' => 2, 'foo' => 3), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->where(array('type' => '(event|post)', 'id' => '[0-9]+', 'foo' => '[\w]{3}'))->withId('some random route');
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItAllowsSettingPatterns()
    {
        Route::pattern('type', '(event|post)');
        Route::pattern('id', '[0-9]+');
        
        $path = '/some/{type}/{id}';
        $id = 'some-type-id';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/(event|post)/([0-9]+)$', 'page_callback' => array('GET' => $callback), 'query_vars' => array('type' => 1, 'id' => 2), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback);
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItAllowsSettingFiltersToControlAccess()
    {
        $path = '/posts/{category}/{category-id}';
        $id = 'posts-category-category-id';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $accessCallback = function ()
        {
            echo 'Hello admin';
        };
        
        // set the filter
        Route::filter('auth', $accessCallback);
        $args = array('path' => '^posts/([\w]+)/([0-9]+)$', 'page_callback' => array('GET' => $callback), 'query_vars' => array('category' => 1, 'category-id' => 2), 'access_callback' => array('GET' => $accessCallback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, array('auth', $callback))->where('category', '[\w]+')->where('category-id', '[0-9]+');
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowUsingStaticMethodGetToAddGetRoute()
    {
        $path = 'some/path';
        $id = 'some-path';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/path$', 'page_callback' => array('GET' => $callback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        Route::get($path, $callback);
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowUsingStaticMethodPutToAddPutRoute()
    {
        $path = 'some/path';
        $id = 'some-path';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/path$', 'page_callback' => array('PUT' => $callback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        Route::put($path, $callback);
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowUsingStaticMethodPostToAddPostRoute()
    {
        $path = 'some/path';
        $id = 'some-path';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/path$', 'page_callback' => array('POST' => $callback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        Route::post($path, $callback);
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowUsingStaticMethodDeleteToAddDeleteRoute()
    {
        $path = 'some/path';
        $id = 'some-path';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^some/path$', 'page_callback' => array('DELETE' => $callback), 'template' => false);
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        Route::delete($path, $callback);
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowSettingTheTitle()
    {
        $path = '/posts/{category}/{category-id}';
        $id = 'posts-category-category-id';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $titleCallback = function ()
        {
            echo 'Hello page';
        };
        $args = array('path' => '^posts/([\w]+)/([0-9]+)$', 'page_callback' => array('GET' => $callback), 'query_vars' => array('category' => 1, 'category-id' => 2), 'template' => false, 'title_callback' => array('GET' => $titleCallback));
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->where('category', '[\w]+')->where('category-id', '[0-9]+')->withTitle(function ()
        {
            echo 'Hello page';
        });
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowSettingTheTitleAsAString()
    {
        $path = '/posts/{category}/{category-id}';
        $id = 'posts-category-category-id';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^posts/([\w]+)/([0-9]+)$', 'page_callback' => array('GET' => $callback), 'query_vars' => array('category' => 1, 'category-id' => 2), 'template' => false, 'title' => 'Page Title');
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->where('category', '[\w]+')->where('category-id', '[0-9]+')->withTitle('Page Title');
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowSettingATemplate()
    {
        $path = '/posts/{category}/{category-id}';
        $id = 'posts-category-category-id';
        $callback = function ()
        {
            echo 'Hello there';
        };
        $args = array('path' => '^posts/([\w]+)/([0-9]+)$', 'page_callback' => array('GET' => $callback), 'query_vars' => array('category' => 1, 'category-id' => 2), 'template' => array('category-term.php'));
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->where('category', '[\w]+')->where('category-id', '[0-9]+')->withTemplate('category-term');
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowSettingMoreTemplatesUsingAnArray()
    {
        $path = 'hello';
        $id = 'hello';
        $callback = function ()
        {
            echo 'Hello there';
        };
        
        $args = array('path' => '^hello$', 'page_callback' => array('GET' => $callback), 'template' => array('single.php', 'page.php'));
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->withTemplate(array('single', 'page'));
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowSettingATemplatePassingTheBasenameAndTheExtension()
    {
        $path = 'hello';
        $id = 'hello';
        $callback = function ()
        {
            echo 'Hello there';
        };
        
        $args = array('path' => '^hello$', 'page_callback' => array('GET' => $callback), 'template' => array('single.php', 'page.php'));
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->withTemplate(array('single.php', 'page.php'));
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowSettingATemplatePassingARelativePath()
    {
        $path = 'hello';
        $id = 'hello';
        $callback = function ()
        {
            echo 'Hello there';
        };
        
        $args = array('path' => '^hello$', 'page_callback' => array('GET' => $callback), 'template' => array('templates/single.php', 'templates/page.php'));
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->withTemplate(array('templates/single.php', 'templates/page'));
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShouldAllowSettingATemplatePassingAnAbsolutePath()
    {
        $path = 'hello';
        $id = 'hello';
        $callback = function ()
        {
            echo 'Hello there';
        };
        
        $args = array('path' => '^hello$', 'page_callback' => array('GET' => $callback), 'template' => array('/some/folder/templates/single.php', '/some/folder/templates/page.php'));
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)->withTemplate(array('/some/folder/templates/single.php', '/some/folder/templates/page'));
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShouldCallActionsWhenAddingRoutes()
    {
        $this->f->expects($this->at(0))
            ->method('do_action')
            ->with('route_before_adding_routes', $this->isType('array'));
        $this->f->expects($this->at(1))
            ->method('do_action')
            ->with('route_after_adding_routes', $this->isType('array'));
        Route::generateRoutes($this->router, $this->f);
    }
    public function testItShouldAllowAddingAnOptionalInformationToTheRoute()
    {
        $path = 'hello';
        $id = 'hello';
        $callback = function ()
        {
            echo 'Hello there';
        };
        
        $args = array(
            'path' => '^hello$',
            'page_callback' => array('GET' => $callback),
            'template' => array('/some/folder/templates/single.php', '/some/folder/templates/page.php'),
            'description' => 'The hello route'
            );
        $this->router->expects($this->once())->method('add_route')->with($id, $args);
        $this->sut->hook();
        $this->sut->_get($path, $callback)
            ->withTemplate(array('/some/folder/templates/single.php', '/some/folder/templates/page'))
            ->with('description', 'The hello route');
        $this->sut->__destruct();
        Route::generateRoutes($this->router);
    }
    public function testItShoulgThrowIfUsingWithMethodWithKeysThatAreMethodAccessible()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->sut->_get('some/path', function(){ echo 'some callback';})
            ->with('path', 'hacky/path');
    }
}
