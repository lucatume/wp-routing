<?php


/**
 * Class WPRouting_PersistableRoute
 *
 * An extension of the WPRouting_Route class to allow meta information about a route to be persisted to the database.
 */
class WPRouting_PersistableRoute extends WPRouting_Route {

	const PARENT_CLASS = 'WPRouting_Route';
	/**
	 * All the meta information about all the routes will be stored to an array like value in the database. This is the `option_name`.
	 */
	const OPTION_ID                     = '__wp_routing_routes_meta';
	const ROUTE_PERSISTED_VALUES_FILTER = 'WP_Routing_PersistableRoute_persist_route';
	/**
	 * @var array An array of route arguments that are either set by internal
	 * methods or require the use of public set methods to be set.
	 */
	public static $protectedArgs = array( 'permalink' );
	/**
	 * @var null|tad_Option An instance of the options wrapper class.
	 */
	protected static $option = null;

	/**
	 * @param tad_FunctionsAdapterInterface $f
	 * @param tad_Option                    $option
	 */
	public function __construct( tad_FunctionsAdapterInterface $f = null, tad_Option $option = null ) {
		$this->maybeInitStaticHelper();
		if ( is_null( $option ) ) {
			$option = tad_Option::on( self::OPTION_ID );
		}
		$this->option = $option;
		parent::__construct( $f );
	}

	protected function maybeInitStaticHelper() {
		if ( is_null( tad_Static::getClassExtending( self::PARENT_CLASS ) ) ) {
			tad_Static::setClassExtending( self::PARENT_CLASS, __CLASS__ );
		}
	}

	public static function set( $key, $value = null ) {
		self::${$key} = $value;
	}

	/**
	 * Override of the parent method to hook in the route generation process at a class level (in place of using the WP hook).
	 *
	 * @param string $routeId
	 * @param array  $args
	 */
	protected static function actOnRoute( $routeId, Array $args ) {

		// if the route should not be persisted return
		if ( ! isset( $args['shouldBePersisted'] ) or ! $args['shouldBePersisted'] ) {
			return;
		}

		// if the route title is not set return
		if ( ! isset( $args['title'] ) or ! is_string( $args['title'] ) ) {
			return;
		}

		// if the route permalink is not set return
		if ( ! isset( $args['permalink'] ) or ! is_string( $args['permalink'] ) or ! preg_match( "/[\\/\\w]*/ui", $args['permalink'] ) ) {
			return;
		}

		// get additional meta that's not WP Router related
		$routeArgs = self::pruneArgs( $args );

		// allow plugins to hook into persisted arguments
		if ( function_exists( 'apply_filters' ) ) {
			/**
			 * The arguments for the route that will be persisted to the database.
			 *
			 * The route meta information will be stored if the returned
			 * value is an array.
			 * The route meta information will be saved in the option whose
			 * name is defined in the OPTION_ID constant.
			 * The option will be an associative array of arrays where each
			 * route meta is stored in an associative array under the route
			 * id key.
			 *
			 * @var array A key/value pairs array.
			 */
			$routeArgs = apply_filters( self::ROUTE_PERSISTED_VALUES_FILTER, $routeArgs, $routeId );
		}

		// persist the route using the id as the key and storing the title and the permalink
		if ( is_array( $routeArgs ) ) {
			self::$option->setValue( $routeId, $routeArgs );
		}
	}

	/**
	 * Removes WP Router related and the `shouldBePersisted` arguments from the route meta.
	 *
	 * @param  array $args The route meta arguments.
	 *
	 * @return array       The route meta arguments minus the WP Router related and the `shouldBePersisted` ones.
	 */
	protected static function pruneArgs( array $args ) {
		// set the basic ones
		$routeArgs   = array(
			'title'     => $args['title'],
			'permalink' => $args['permalink']
		);
		$toPruneArgs = array_flip( WPRouting_Route::$WPRouterArgs );
		// remove the shouldBePersisted argument too, any arg will do
		// the key is relevant
		$toPruneArgs['shouldBePersisted'] = - 1;
		$prunedArgs                       = array_diff_key( $args, $toPruneArgs );
		$routeArgs                        = array_merge( $prunedArgs, $routeArgs );

		return $routeArgs;
	}

	/**
	 * Allows setting a route additional arguments.
	 *
	 * @param  string $key   The key for the argument.
	 * @param  mixed  $value The argument value.
	 *
	 * @return WPRouting_PersistableRoute        The calling object instance.
	 */
	public function with( $key, $value ) {
		if ( in_array( $key, self::$protectedArgs ) ) {
			throw new InvalidArgumentException( "$key cannot be set using the with method.", 3 );
		}

		return parent::with( $key, $value );
	}

	/**
	 * Sugar method to set the `shouldBePersisted` meta for a route.
	 *
	 * @return WPRouting_PersistableRoute $this
	 */
	public function shouldBePersisted( $shouldBePersisted = null ) {
		$this->args['shouldBePersisted'] = is_bool( $shouldBePersisted ) ? $shouldBePersisted : true;

		return $this;
	}

	public function willBePersisted() {
		$shouldBePersisted = isset( $this->args['shouldBePersisted'] ) && ! empty( $this->args['shouldBePersisted'] );

		return $shouldBePersisted ? $shouldBePersisted : false;
	}

	/**
	 * Sets the `permalink` key for the route starting from the path.
	 *
	 * A `path` specified in the route like `/^hello$/` will set the route permalink to `hello`.
	 *
	 * @param $patterns
	 */
	protected function replacePatterns( $patterns ) {

		// call WPRouting_Route::replacePatterns
		parent::replacePatterns( $patterns );

		// set the permalink to something like path
		// do not use the '/'
		if ( ! isset( $this->args['path'] ) ) {
			return;
		}
		$this->args['permalink'] = rtrim( ltrim( $this->args['path'], '/^' ), '$/' );
	}
}
