<?php


class WP_Routing_RouteTest extends tad_TestCase {

	protected $sut = null;

	public function setUp() {
		$this->f      = $this->getMockFunctions( array( 'add_action', 'do_action' ) );
		$this->router = $this->getMock( 'WP_Router', array( 'add_route' ) );

		// reset the WPRouting_Route
		WPRouting_Route::set( 'routes', array() );
		WPRouting_Route::set( 'patterns', array() );
		$this->sut = new WPRouting_Route( $this->f );
	}

	public function tearDown() {
	}

	public function testItShouldHook() {
		$this->f->expects( $this->once() )->method( 'add_action' )->with( 'wp_router_generate_routes', array(
				'WPRouting_Route',
				'generateRoutes'
			) );
		$this->sut->hook();
	}

	public function testGetRoutesCanBeAddedUsingGetMethod() {
		$path     = 'some/path';
		$id       = 'some-path';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/path$',
			'page_callback' => array( 'GET' => $callback ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testGetPutPostDeleteRoutesCanBeAddedUsingAllMethod() {
		$path      = 'some/path';
		$id        = 'some-path';
		$callback  = function () {
			echo 'Hello there';
		};
		$callbacks = array( 'GET' => $callback, 'POST' => $callback, 'PUT' => $callback, 'DELETE' => $callback );
		$args      = array( 'path' => '^some/path$', 'page_callback' => $callbacks, 'template' => false );
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_all( $path, $callback );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testPostRoutesCanBeAddedUsingPostMethod() {
		$path     = '/some/path';
		$id       = 'some-path';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/path$',
			'page_callback' => array( 'POST' => $callback ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_post( $path, $callback );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testPutRoutesCanBeAddedUsingPutMethod() {
		$path     = '/some/path';
		$id       = 'some-path';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/path$',
			'page_callback' => array( 'PUT' => $callback ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_put( $path, $callback );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testDeleteRoutesCanBeAddedUsingDeleteMethod() {
		$path     = '/some/path';
		$id       = 'some-path';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/path$',
			'page_callback' => array( 'DELETE' => $callback ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_delete( $path, $callback );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItAllowsSettingQueryVars() {
		$path     = '/some/{type}/{id}';
		$id       = 'some-type-id';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/(event|post)/([0-9]+)$',
			'page_callback' => array( 'GET' => $callback ),
			'query_vars'    => array( 'type' => 1, 'id' => 2 ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->where( 'type', '(event|post)' )->where( 'id', '[0-9]+' );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItAllowsSettingQueryVarsUsingArray() {
		$path     = '/some/{type}/{id}/{foo}';
		$id       = 'some-type-id-foo';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/(event|post)/([0-9]+)/([\w]{3})$',
			'page_callback' => array( 'GET' => $callback ),
			'query_vars'    => array( 'type' => 1, 'id' => 2, 'foo' => 3 ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->where( array(
				'type' => '(event|post)',
				'id'   => '[0-9]+',
				'foo'  => '[\w]{3}'
			) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItAllowsSettingTheId() {
		$path     = '/some/{type}/{id}/{foo}';
		$id       = 'some-type-id-foo';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/(event|post)/([0-9]+)/([\w]{3})$',
			'page_callback' => array( 'GET' => $callback ),
			'query_vars'    => array( 'type' => 1, 'id' => 2, 'foo' => 3 ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->where( array(
				'type' => '(event|post)',
				'id'   => '[0-9]+',
				'foo'  => '[\w]{3}'
			) )->withId( 'some random route' );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItAllowsSettingPatterns() {
		WPRouting_Route::pattern( 'type', '(event|post)' );
		WPRouting_Route::pattern( 'id', '[0-9]+' );

		$path     = '/some/{type}/{id}';
		$id       = 'some-type-id';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/(event|post)/([0-9]+)$',
			'page_callback' => array( 'GET' => $callback ),
			'query_vars'    => array( 'type' => 1, 'id' => 2 ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItAllowsSettingFiltersToControlAccess() {
		$path           = '/posts/{category}/{category-id}';
		$id             = 'posts-category-category-id';
		$callback       = function () {
			echo 'Hello there';
		};
		$accessCallback = function () {
			echo 'Hello admin';
		};

		// set the filter
		WPRouting_Route::filter( 'auth', $accessCallback );
		$args = array(
			'path'            => '^posts/([\w]+)/([0-9]+)$',
			'page_callback'   => array( 'GET' => $callback ),
			'query_vars'      => array( 'category' => 1, 'category-id' => 2 ),
			'access_callback' => array( 'GET' => $accessCallback ),
			'template'        => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, array(
				'auth',
				$callback
			) )->where( 'category', '[\w]+' )->where( 'category-id', '[0-9]+' );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowUsingStaticMethodGetToAddGetRoute() {
		$path     = 'some/path';
		$id       = 'some-path';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/path$',
			'page_callback' => array( 'GET' => $callback ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		WPRouting_Route::get( $path, $callback );
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowUsingStaticMethodPutToAddPutRoute() {
		$path     = 'some/path';
		$id       = 'some-path';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/path$',
			'page_callback' => array( 'PUT' => $callback ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		WPRouting_Route::put( $path, $callback );
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowUsingStaticMethodPostToAddPostRoute() {
		$path     = 'some/path';
		$id       = 'some-path';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/path$',
			'page_callback' => array( 'POST' => $callback ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		WPRouting_Route::post( $path, $callback );
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowUsingStaticMethodDeleteToAddDeleteRoute() {
		$path     = 'some/path';
		$id       = 'some-path';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^some/path$',
			'page_callback' => array( 'DELETE' => $callback ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		WPRouting_Route::delete( $path, $callback );
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowSettingTheTitle() {
		$path          = '/posts/{category}/{category-id}';
		$id            = 'posts-category-category-id';
		$callback      = function () {
			echo 'Hello there';
		};
		$titleCallback = function () {
			echo 'Hello page';
		};
		$args          = array(
			'path'           => '^posts/([\w]+)/([0-9]+)$',
			'page_callback'  => array( 'GET' => $callback ),
			'query_vars'     => array( 'category' => 1, 'category-id' => 2 ),
			'template'       => false,
			'title_callback' => array( 'GET' => $titleCallback )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->where( 'category', '[\w]+' )->where( 'category-id', '[0-9]+' )->withTitle( function () {
			echo 'Hello page';
		} );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowSettingTheTitleAsAString() {
		$path     = '/posts/{category}/{category-id}';
		$id       = 'posts-category-category-id';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^posts/([\w]+)/([0-9]+)$',
			'page_callback' => array( 'GET' => $callback ),
			'query_vars'    => array( 'category' => 1, 'category-id' => 2 ),
			'template'      => false,
			'title'         => 'Page Title'
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->where( 'category', '[\w]+' )->where( 'category-id', '[0-9]+' )->withTitle( 'Page Title' );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowSettingATemplate() {
		$path     = '/posts/{category}/{category-id}';
		$id       = 'posts-category-category-id';
		$callback = function () {
			echo 'Hello there';
		};
		$args     = array(
			'path'          => '^posts/([\w]+)/([0-9]+)$',
			'page_callback' => array( 'GET' => $callback ),
			'query_vars'    => array( 'category' => 1, 'category-id' => 2 ),
			'template'      => array( 'category-term.php' )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->where( 'category', '[\w]+' )->where( 'category-id', '[0-9]+' )->withTemplate( 'category-term' );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowSettingMoreTemplatesUsingAnArray() {
		$path     = 'hello';
		$id       = 'hello';
		$callback = function () {
			echo 'Hello there';
		};

		$args = array(
			'path'          => '^hello$',
			'page_callback' => array( 'GET' => $callback ),
			'template'      => array( 'single.php', 'page.php' )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->withTemplate( array( 'single', 'page' ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowSettingATemplatePassingTheBasenameAndTheExtension() {
		$path     = 'hello';
		$id       = 'hello';
		$callback = function () {
			echo 'Hello there';
		};

		$args = array(
			'path'          => '^hello$',
			'page_callback' => array( 'GET' => $callback ),
			'template'      => array( 'single.php', 'page.php' )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->withTemplate( array( 'single.php', 'page.php' ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowSettingATemplatePassingARelativePath() {
		$path     = 'hello';
		$id       = 'hello';
		$callback = function () {
			echo 'Hello there';
		};

		$args = array(
			'path'          => '^hello$',
			'page_callback' => array( 'GET' => $callback ),
			'template'      => array( 'templates/single.php', 'templates/page.php' )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->withTemplate( array( 'templates/single.php', 'templates/page' ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldAllowSettingATemplatePassingAnAbsolutePath() {
		$path     = 'hello';
		$id       = 'hello';
		$callback = function () {
			echo 'Hello there';
		};

		$args = array(
			'path'          => '^hello$',
			'page_callback' => array( 'GET' => $callback ),
			'template'      => array(
				'/some/folder/templates/single.php',
				'/some/folder/templates/page.php'
			)
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->withTemplate( array(
				'/some/folder/templates/single.php',
				'/some/folder/templates/page'
			) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShouldCallActionsWhenAddingRoutes() {
		$this->f->expects( $this->at( 0 ) )->method( 'do_action' )->with( 'route_before_adding_routes', $this->isType( 'array' ) );
		$this->f->expects( $this->at( 1 ) )->method( 'do_action' )->with( 'route_after_adding_routes', $this->isType( 'array' ) );
		WPRouting_Route::generateRoutes( $this->router, $this->f );
	}

	public function testItShouldAllowAddingAnOptionalInformationToTheRoute() {
		$path     = 'hello';
		$id       = 'hello';
		$callback = function () {
			echo 'Hello there';
		};

		$args = array(
			'path'          => '^hello$',
			'page_callback' => array( 'GET' => $callback ),
			'template'      => array( '/some/folder/templates/single.php', '/some/folder/templates/page.php' ),
			'description'   => 'The hello route'
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback )->withTemplate( array(
					'/some/folder/templates/single.php',
					'/some/folder/templates/page'
				) )->with( 'description', 'The hello route' );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	public function testItShoulgThrowIfUsingWithMethodWithKeysThatAreMethodAccessible() {
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->sut->_get( 'some/path', function () {
			echo 'some callback';
		} )->with( 'path', 'hacky/path' );
	}

	/**
	 * @test
	 * it should allow passing a function name as callback
	 */
	public function it_should_allow_passing_a_function_name_as_callback() {
		$path = 'some';
		$id   = 'some';
		$args = array( 'path' => '^some$', 'page_callback' => array( 'GET' => 'phpinfo' ), 'template' => false );
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, 'phpinfo' );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	/**
	 * @test
	 * it should allow passing an array of class and method as callback
	 */
	public function it_should_allow_passing_an_array_of_class_and_method_as_callback() {
		$path = 'some';
		$id   = 'some';
		$args = array(
			'path'          => '^some$',
			'page_callback' => array( 'GET' => array( 'WPRouting_Route', 'generateRoutes' ) ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, array( 'WPRouting_Route', 'generateRoutes' ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	/**
	 * @test
	 * it should allow passing an array of object and method as callbackj
	 */
	public function it_should_allow_passing_an_array_of_object_and_method_as_callback() {
		$path = 'some';
		$id   = 'some';
		$mock = $this->getMock( 'stdClass', array( 'someMethod' ) );
		$args = array(
			'path'          => '^some$',
			'page_callback' => array( 'GET' => array( $mock, 'someMethod' ) ),
			'template'      => false
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, array( $mock, 'someMethod' ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	/**
	 * @test
	 * it should allow passing an anonymous function as callback
	 */
	public function it_should_allow_passing_an_anonymous_function_as_callback() {
		$path     = 'some';
		$id       = 'some';
		$callback = function () {

		};
		$args     = array( 'path' => '^some$', 'page_callback' => array( 'GET' => $callback ), 'template' => false );
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		$this->sut->_get( $path, $callback );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	/**
	 * @test
	 * it should allow passing a filter and a function name as callback
	 */
	public function it_should_allow_passing_a_filter_and_a_function_name_as_callback() {
		$path = 'some';
		$id   = 'some';
		$args = array(
			'path'            => '^some$',
			'page_callback'   => array( 'GET' => 'phpinfo' ),
			'template'        => false,
			'access_callback' => array( 'GET' => 'phpcredits' )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		WPRouting_Route::filter( 'filter', 'phpcredits' );
		$this->sut->_get( $path, array( 'filter', 'phpinfo' ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	/**
	 * @test
	 * it should allow passing a filter and an array of class and method as callback
	 */
	public function it_should_allow_passing_a_filter_and_an_array_of_class_and_method_as_callback() {
		$path = 'some';
		$id   = 'some';
		$args = array(
			'path'            => '^some$',
			'page_callback'   => array( 'GET' => array( 'WPRouting_Route', 'generateRoutes' ) ),
			'template'        => false,
			'access_callback' => array( 'GET' => 'phpcredits' )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		WPRouting_Route::filter( 'filter', 'phpcredits' );
		$this->sut->_get( $path, array( 'filter', array( 'WPRouting_Route', 'generateRoutes' ) ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	/**
	 * @test
	 * it should allow passing a filter and an array of object and method as callback
	 */
	public function it_should_allow_passing_a_filter_and_an_array_of_object_and_method_as_callback() {
		$path = 'some';
		$id   = 'some';
		$mock = $this->getMock( 'stdClass', array( 'someMethod' ) );
		$args = array(
			'path'            => '^some$',
			'page_callback'   => array( 'GET' => array( $mock, 'someMethod' ) ),
			'template'        => false,
			'access_callback' => array( 'GET' => 'phpcredits' )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		WPRouting_Route::filter( 'filter', 'phpcredits' );
		$this->sut->_get( $path, array( 'filter', array( $mock, 'someMethod' ) ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}

	/**
	 * @test
	 * it should allow passing a filter and an anonymous function as callback
	 */
	public function it_should_allow_passing_a_filter_and_an_anonymous_function_as_callback() {
		$path     = 'some';
		$id       = 'some';
		$callback = function () {
		};
		$args     = array(
			'path'            => '^some$',
			'page_callback'   => array( 'GET' => $callback ),
			'template'        => false,
			'access_callback' => array( 'GET' => 'phpcredits' )
		);
		$this->router->expects( $this->once() )->method( 'add_route' )->with( $id, $args );
		$this->sut->hook();
		WPRouting_Route::filter( 'filter', 'phpcredits' );
		$this->sut->_get( $path, array( 'filter', $callback ) );
		$this->sut->__destruct();
		WPRouting_Route::generateRoutes( $this->router );
	}
}
