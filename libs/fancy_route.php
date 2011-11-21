<?php
/**
 * Custom Route class enables database-backed, dynamic routes
 * Enables you to add new pages from the database without having to
 * manually specify a shortcut route in your routes.php file
 *
 * To use, install the page_route plugin and add
 * the following to the top of app/config/routes.php:
 *
 * App::import('Lib', 'FancyRoute.FancyRoute');
 *
 * To trigger it, simply call the connectFancyRoutes() static method:
 *
 * FancyRoute::connectFancyRoutes();
 *
 * Note that this hack works by calling Router::connect() on the actual
 * DynamicRoute records.
 *
 * @author Jose Gonzalez (support@savant.be)
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @see CakeRoute
 */
class FancyRoute {

/**
 * An array of additional parameters for the Route.
 *
 * @var array
 * @access public
 */
	static $options = array(
		'model' => 'DynamicRoute.DynamicRoute',
		'cacheKey' => 'dynamic_routes',
		'cache' => true,
	);

	static $_routes = array();

	static $model = null;

/**
 * Constructor for a Route
 *
 * @param string $template Template string with parameter placeholders
 * @param array $defaults Array of defaults for the route.
 * @param string $params Array of parameters and additional options for the Route
 * @return void
 * @access public
 */
	public static function connectFancyRoutes($options = array()) {
		static::$options = array_merge(static::$options, (array) $options);
		Configure::write('DynamicRoute.cacheKey', static::$options['cacheKey']);

		self::_loadDynamicRoutes();
	}

/**
 * Loads routes in from the cache or database
 *
 * @return boolean
 */
	public static function _loadDynamicRoutes() {
		if (static::$options['cache']) {
			static::$_routes = Cache::read(static::$options['cacheKey']);
			if (static::$_routes) {
				return self::_loadRoutes();
			}
		}

		if (is_object(static::$options['model'])) {
			static::$model = static::$options['model'];
		} else {
			static::$model = ClassRegistry::init(static::$options['model']);
		}

		if (!is_object(static::$model)) {
			if (!class_exists('CakeLog')) {
				App::import('Core', 'CakeLog');
			}
			CakeLog::write('dynamic_route', 'Unable to load dynamic_route model');
			return false;
		}

		static::$_routes = static::$model->find('load');
		if (static::$_routes) {
			if (static::$options['cache']) {
				Cache::write(static::$options['cacheKey'], static::$_routes);
			}
			return self::_loadRoutes();
		}

		if (!class_exists('CakeLog')) {
			App::import('Core', 'CakeLog');
		}
		CakeLog::write('dynamic_route', 'No routes available');
		return false;
	}

	public static function _loadRoutes() {
		foreach (static::$_routes as $slug => $spec) {
			Router::connect($slug, $spec);
		}
		return true;
	}

}