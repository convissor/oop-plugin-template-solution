<?php

/**
 * Extend the class to be tested, providing access to protected elements
 *
 * @package oop-plugin-template-solution
 * @author Daniel Convissor <danielc@analysisandsolutions.com>
 * @copyright The Analysis and Solutions Company, 2012
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * This plugin used the Object-Oriented Plugin Template Solution as a skeleton
 * REPLACE_PLUGIN_URI
 */

/**
 * Obtain the parent class
 *
 * Use dirname(dirname()) because safe mode can disable "../" and use
 * dirname(__FILE__) instead of __DIR__ so tests run on PHP 5.2.
 */
require_once dirname(dirname(__FILE__)) . '/oop-plugin-template-solution.php';

/**
 * Get the admin class
 */
require_once dirname(dirname(__FILE__)) .  '/admin.php';

// Remove automatically created object.
unset($GLOBALS['oop_plugin_template_solution']);

/**
 * Extend the class to be tested, providing access to protected elements
 *
 * @package oop-plugin-template-solution
 * @author Daniel Convissor <danielc@analysisandsolutions.com>
 * @copyright The Analysis and Solutions Company, 2012
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * This plugin used the Object-Oriented Plugin Template Solution as a skeleton
 * REPLACE_PLUGIN_URI
 */
class Accessor extends oop_plugin_template_solution_admin {
	public function __call($method, $args) {
		return call_user_func_array(array($this, $method), $args);
	}
	public function __get($property) {
		return $this->$property;
	}
	public function __set($property, $value) {
		$this->$property = $value;
	}
	public function get_data_element($key) {
		return $this->data[$key];
	}
}
