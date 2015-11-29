<?php
/* dynamic_route schema generated on: 2011-11-21 08:44:09 : 1321865049*/
class DynamicRouteSchema extends CakeSchema {

	public $name = 'DynamicRoute';

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $dynamic_routes = array(
		'slug' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'spec' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
		'indexes' => array('PRIMARY' => array('column' => 'slug', 'unique' => 1), 'spec' => array('column' => 'spec', 'unique' => 0)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);
}
