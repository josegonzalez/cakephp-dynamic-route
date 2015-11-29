<?php
class DynamicRoute extends DynamicRouteAppModel {

	public $validate = array(
		'active' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				'message' => 'Route must be either active or inactive',
			),
		),
		'spec' => array(
			'required' => array(
				'rule' => array('validSpec'),
				'message' => 'Invalid specification',
			),
			'minLength' => array(
				'rule' => array('minLength', 3),
				'message' => 'Specification must be at least 3 characters long',
			),
			'maxLength' => array(
				'rule' => array('between', 255),
				'message' => 'Slug must be at most 255 characters',
			),
		),
		'slug' => array(
			'minLength' => array(
				'rule' => array('minLength', 2),
				'message' => 'Specification must be at least 2 characters long',
			),
			'maxLength' => array(
				'rule' => array('maxLength', 255),
				'message' => 'Slug must be at most 255 characters',
			),
			'beginsWith' => array(
				'rule' => array('custom', '/^[\/]/'),
				'message' => 'Slug must begin with a trailing slash',
			),
			'doesntEndWith' => array(
				'rule' => array('custom', '/.+(?<![\/])$/'),
				'message' => 'Slug must not end with a trailing slash',
			),
		),
	);

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct();
		$this->findMethods['load'] = true;
		$this->findMethods['spec'] = true;
	}

	public function _findLoad($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = array("{$this->alias}.active" => 1);
			$query['contain'] = false;
			$query['fields'] = array('spec', 'slug');
			$query['recursive'] = -1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results)) {
				return false;
			}

			$return = array();
			$count = count($results);
			for ($i = 0; $i < $count; $i++) {
				$parsed = $this->parse($results[$i][$this->alias]['spec']);
				if (!$parsed) {
					$this->log(sprintf('Unable to parse spec for slug::spec %s::%s',
						$results[$i][$this->alias]['slug'],
						$results[$i][$this->alias]['spec']
					), 'dynamic_route');
					continue;
				}

				$return[$results[$i][$this->alias]['slug']] = $parsed;
			}

			return $return;
		}
	}

	public function _findSpec($state, $query, $results = array()) {
		if ($state == 'before') {
			if (is_array($query['spec'])) {
				$query['spec'] = $this->normalize($query['spec']);
			}
			$query['conditions'] = array("{$this->alias}.spec" => $spec);
			$query['contain'] = false;
			$query['limit'] = 1;
			$query['recursive'] = -1;
			return $query;
		} elseif ($state == 'after') {
			if (empty($results[0])) {
				return false;
			}

			return $results[0];
		}
	}

/**
 * Does a bit of extra handling to ensure proper insertion of route slugs
 *
 * @param array $options array of options from Model::save() call
 * @return bool
 */
	public function beforeValidate($options = array()) {
		if (!empty($this->data[$this->alias]['slug'])) {
			$this->data[$this->alias]['slug'] = '/' . trim(trim($this->data[$this->alias]['slug']), '/');
		}
		return true;
	}

/**
 * Clears all dynamic routes from the cache
 *
 * @param bool $created whether the route was created or not
 * @return bool
 */
	public function afterSave($created) {
		if (Configure::read('DynamicRoute.cacheKey')) {
			Cache::delete(Configure::read('DynamicRoute.cacheKey'));
		}
		return true;
	}

/**
 * Helper method for creating new dynamic_route records
 *
 * @param mixed $spec a route spec
 * @param string $slug an associated route slug
 * @return bool
 */
	public function saveNew($spec, $slug) {
		$data = $spec;
		if (!is_array($spec)) {
			$data = array($this->alias => compact('spec', 'slug'));
		}

		if (isset($data['spec'])) {
			$data = array($this->alias => $data);
		}

		if (!empty($data[$this->alias]['slug'])) {
			$data[$this->alias]['slug'] = '/' . trim(trim($data[$this->alias]['slug']), '/');
		}
		if (!empty($data[$this->alias]['spec'])) {
			$data[$this->alias]['spec'] = $this->normalize($data[$this->alias]['spec']);
		}

		$this->create();
		return $this->save($data);
	}

/**
 * Normalizes a string/array specification into an internal CakePHP "url"
 *
 * @param mixed $params string|array referencing a specification
 * @return string specification as internal CakePHP "url"
 */
	public function normalize($params) {
		if (is_string($params)) {
			$params = $this->parse($spec);
		}

		$path = "{$params["controller"]}/{$params["action"]}";

		if (isset($params['controller'])) {
			unset($params['controller']);
		}
		if (isset($params['action'])) {
			unset($params['action']);
		}

		// We need to sort both the query params and sub-arrays
		// In order to insure that all slug => spec mappings created are unique
		ksort($params);
		foreach ($params as &$qp) {
			if (is_array($qp)) {
				sort($qp);
			}
		}

		$query = false;
		if (!empty($params)) {
			$query = http_build_query($params, '', '&');
		}

		return $path . ($query ? "" : ("?" . $query));
	}

/**
 * Turns a specification string into an array
 *
 * @param string $spec a route spec to be parsed
 * @return array
 */
	public function parse($spec) {
		if (is_array($spec)) {
			return $spec;
		}

		$path = null;
		$query = null;

		$parts = explode("?", $spec, 2);

		$path = $parts[0];
		if (count($parts) == 2) {
			$query = $parts[1];
		}

		// Don't support plugins here, use query params to do so
		if (count(explode("/", $path, 2)) !== 2) {
			return false;
		}

		list($controller, $action) = explode("/", $path, 2);

		$params = array();
		if ($query !== null) {
			foreach (explode("&", $query) as $pair) {
				$param = explode("=", $pair, 2);
				if (count($param) !== 2) {
					return false;
				}

				if (substr($param[0], -2) == "[]") {
					$paramname = substr($param[0], 0, strlen($param[0]) - 2);
					if (!array_key_exists($paramname, $params)) {
						$params[$paramname] = array();
					}
					$params[$paramname][]= $param[1];
				} else {
					$params[$param[0]] = $param[1];
				}
			}
		}

		return array_merge($params, compact('controller', 'action'));
	}

}
