<?php
/**
 * All DynamicRoute plugin tests
 */
class AllDynamicRouteTest extends CakeTestCase {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All DynamicRoute test');

		$path = CakePlugin::path('DynamicRoute') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
