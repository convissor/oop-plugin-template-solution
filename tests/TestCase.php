<?php

/**
 * Parent TestCase class containing common methods and properties, plus an
 * override for the wp_mail() and wp_redirect() functions
 *
 * @package oop-plugin-template-solution
 * @author Daniel Convissor <danielc@analysisandsolutions.com>
 * @copyright The Analysis and Solutions Company, 2012
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * This plugin used the Object-Oriented Plugin Template Solution as a skeleton
 * REPLACE_PLUGIN_URI
 */

/*
 * Keep PHPUnit from messing up WordPress' crazy use of globals.
 *
 * This prevents the following errors:
 *  + Call to a member function add_rule() on a non-object in
 *    wp-includes/rewrite.php
 *  + Call to a member function add_rewrite_tag() on a non-object in
 *    wp-includes/taxonomy.php
 */
global $wp_rewrite;


/*
 * Hacks to keep WordPress multisite network mode happy under PHPUnit.
 */

// Undefined index: HTTP_HOST in wp-includes/ms-settings.php.
$_SERVER['HTTP_HOST'] = 'localhost';

// Undefined variable: wpdb in wp-includes/ms-settings.php.
global $wpdb;

// Trying to get property of non-object in wp-includes/functions.php.
global $current_site, $current_blog;


/**
 * Overrides the wp_mail() function so we can ensure the messages are
 * composed when and how they should be
 *
 * @uses TestCase::mail_to_file()  to store the data for later comparison
 */
function wp_mail($to, $subject, $message) {
	TestCase::mail_to_file($to, $subject, $message);
}

/**
 * Overrides the wp_redirect() function so we can ensure headers are
 * composed when and how they should be
 *
 * @uses TestCase::wp_redirect()  to store the data for later comparison
 */
function wp_redirect($location, $status = 302) {
	TestCase::wp_redirect($location, $status);
}

/**
 * Gather the WordPress infrastructure
 *
 * Use dirname(dirname()) because safe mode can disable "../" and use
 * dirname(__FILE__) instead of __DIR__ so tests run on PHP 5.2.
 */
$wp_load = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
if (!is_readable($wp_load)) {
	die("The plugin must be in the 'wp-content/plugins' directory of a working WordPress installation.\n");
}
require_once $wp_load;


if (is_multisite()) {
	// Workaround for the authentication check in my activate() method.
	define('WP_NETWORK_ADMIN', true);
}


/**
 * Get the plugin class to be tested and a parent class of it that gives us
 * access to protected methods and properties
 */
require_once dirname(__FILE__) . '/Accessor.php';

/**
 * Obtain the PHPUnit infrastructure
 */
require_once 'PHPUnit/Autoload.php';

/**
 * Parent TestCase class containing common methods and properties
 *
 * @package oop-plugin-template-solution
 * @author Daniel Convissor <danielc@analysisandsolutions.com>
 * @copyright The Analysis and Solutions Company, 2012
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * This plugin used the Object-Oriented Plugin Template Solution as a skeleton
 * REPLACE_PLUGIN_URI
 */
abstract class TestCase extends PHPUnit_Framework_TestCase {
	const ID = 'oop-plugin-template-solution';


	/**
	 * Keep PHPUnit from messing up WordPress' crazy use of globals.
	 *
	 * This prevents the following errors:
	 *  + Call to a member function add_rule() on a non-object in
	 *    wp-includes/rewrite.php
	 *  + Call to a member function add_rewrite_tag() on a non-object in
	 *    wp-includes/taxonomy.php
	 */
	protected $backupGlobals = false;

	/**
	 * Does the current test class touch the database?
	 * @var bool
	 */
	protected static $db_needed = true;

	/**
	 * Does the database support transactions?
	 * @var bool
	 */
	protected static $db_has_transactions = false;

	/**
	 * Error messges our error handler should expect
	 * @var array
	 * @see TestCase::expected_errors()
	 */
	protected $expected_error_list = array();

	/**
	 * Did the expected errors happen?
	 * @var bool
	 * @see TestCase::were_expected_errors_found()
	 */
	protected $expected_errors_found = false;

	/**
	 * The actual "Location" header sent by wp_redirect()
	 * @var string
	 */
	protected static $location_actual;

	/**
	 * The "Location" header we expect wp_redirect() to send
	 * @var string
	 */
	protected static $location_expected;

	/**
	 * @var Accessor
	 */
	protected static $o;

	/**
	 * Name of the mail file
	 * @var string
	 */
	protected static $mail_file_basename;

	/**
	 * Path and name of the mail file
	 * @var string
	 */
	protected static $mail_file;

	/**
	 * Path to the temporary directory
	 * @var string
	 */
	protected static $temp_dir;

	/**
	 * A mockup of the WP_User object
	 * @var WP_User
	 */
	protected $user;


	/**
	 * Prepares the environment before the first test is run
	 *
	 * NOTE: Not using standard setUpBeforeClass() because we need to have
	 * the child class setting one of this class' static properties
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		global $wpdb, $wp_object_cache;

		$wp_object_cache = new WP_Object_Cache;

		self::$o = new Accessor;

		if (self::$db_needed && self::are_transactions_available()) {
			self::$db_has_transactions = true;
			$wpdb->query('START TRANSACTION');
			$wpdb->query('DELETE FROM `' . self::$o->table_login . '`');
		} else {
			self::$db_has_transactions = false;
		}
	}

	/**
	 * Destroys the environment once the final test is done
	 */
	public static function tearDownAfterClass() {
		global $wpdb;

		if (self::$db_has_transactions) {
			$wpdb->query('ROLLBACK');
		}

		self::$o = null;
	}

	public function setUp() {
		if (self::$db_needed && !self::$db_has_transactions) {
			$this->markTestSkipped('Database transactions are needed to test these features, but your "options" and "usermeta" tables are not using the InnoDB engine.');
		}

		self::$location_actual = null;
		self::$location_expected = null;
		self::$mail_file = null;
		self::$mail_file_basename = null;

		$_SERVER['SERVER_PROTOCOL'] = 'http';

		$this->user = new WP_User;
		$this->user->data = new StdClass;
		$this->user->ID = 9999999;
		$this->user->user_login = 'aaaa';
		$this->user->user_email = 'bbbb';
		$this->user->user_url = 'cccc';
		$this->user->first_name = 'dddd';
		$this->user->last_name = 'eeee';
		$this->user->nickname = 'fff@1F*8ffff';
		$this->user->display_name = '简化字';
		$this->user->aim = 'hhhhhhhh';
		$this->user->yim = 'iiiiiiii';
		$this->user->jabber = 'jjjjjjjj';
		$this->user->user_pass = 'abcdefghij';
	}

	public function tearDown() {
		if (self::$mail_file) {
			@unlink(self::$mail_file);
		}
		$this->expected_error_list = array();
		restore_error_handler();
	}


	/**
	 * Determines if both the options and usermeta tables use InnoDB
	 * @return bool
	 */
	protected static function are_transactions_available() {
		global $wpdb;

		$opt = $wpdb->get_row("SHOW CREATE TABLE `$wpdb->options`", ARRAY_N);
		$usr = $wpdb->get_row("SHOW CREATE TABLE `$wpdb->usermeta`", ARRAY_N);

		return (
			strpos($opt[1], 'ENGINE=InnoDB')
			&& strpos($usr[1], 'ENGINE=InnoDB')
		);
	}

	/**
	 * Examines the last record inserted into the login table
	 */
	protected function check_login_record($user_name) {
		global $wpdb;

		$this->assertInternalType('integer', $wpdb->insert_id,
				'This should be an insert id.');

		$sql = 'SELECT *, SYSDATE() AS sysdate
				FROM `' . self::$o->table_login . '`
				WHERE login_id = %d';
		$actual = $wpdb->get_row($wpdb->prepare($sql, $wpdb->insert_id));
		if (!$actual) {
			$this->fail('Could not find the record in the "login" table.');
		}

		$this->assertEquals($user_name, $actual->user_login,
				"'user_name' field mismatch.");

		$date_login = new DateTime($actual->date_login);
		// Keep tests from going fatal under PHP 5.2.
		if (method_exists($date_login, 'diff')) {
			$sysdate = new DateTime($actual->sysdate);
			$interval = $date_login->diff($sysdate);
			$this->assertLessThanOrEqual('00000000000001',
					$interval->format('%Y%M%D%H%I%S'),
					"'date_login' field off by over 1 second: $actual->date_login.");
		}
	}

	/**
	 * @see TestCase::were_expected_errors_found()
	 */
	protected function expected_errors($error_messages) {
		$this->expected_error_list = (array) $error_messages;
		set_error_handler(array(&$this, 'expected_errors_handler'));
	}

	/**
	 * Determines if the fail tabe exists  and uses InnoDB
	 * @return bool
	 */
	protected static function is_table_login_configured() {
		global $wpdb;

		$fail = $wpdb->get_row("SHOW CREATE TABLE `"
				. self::$o->table_login . "`", ARRAY_N);

		return (
			!empty($fail)
			&& strpos($fail[1], 'ENGINE=InnoDB')
		);
	}

	/**
	 * @see TestCase::expected_errors()
	 */
	protected function were_expected_errors_found() {
		restore_error_handler();
		return $this->expected_errors_found;
	}

	/**
	 * Checks if expected errors were found
	 */
	public function expected_errors_handler($errno, $errstr) {
		foreach ($this->expected_error_list as $expect) {
			if (strpos($errstr, $expect) !== false) {
				$this->expected_errors_found = true;
				return true;
			}
		}
		return false;
	}

	/**
	 * Writes the "mail" contents to a file for later comparison
	 */
	public static function mail_to_file($to, $subject, $message) {
		if (!self::$mail_file_basename) {
			throw new Exception('wp_mail() called at unexpected time'
					. ' (mail_file_basename was not set).');
		}

		if (!self::$temp_dir) {
			self::$temp_dir = sys_get_temp_dir();
		}
		$basename = str_replace('::', '--', self::$mail_file_basename);
		self::$mail_file = self::$temp_dir . '/' . $basename;

		$contents = 'To: ' . implode(', ', (array) $to) . "\n"
				. "Subject: $subject\n\n$message";

		return file_put_contents(self::$mail_file, $contents, FILE_APPEND);
	}

	/**
	 * Examines the actual mail file against the expected mail file
	 */
	protected function check_mail_file() {
		if (!self::$mail_file) {
			$this->fail('wp_mail() has not been called.');
		}

		$basedir = dirname(__FILE__) . '/expected/';
		$locale = get_locale();
		if (!file_exists("$basedir/$locale")) {
			$locale = 'en_US';
		}

		$basename = str_replace('::', '--', self::$mail_file_basename);
		$this->assertStringMatchesFormatFile(
			"$basedir/$locale/$basename",
			file_get_contents(self::$mail_file)
		);
	}

	/**
	 * Writes the location header to a variable for later comparison
	 */
	public static function wp_redirect($location, $status) {
		if (!self::$location_expected) {
			throw new Exception('wp_redirect() called at unexpected time'
					. ' ($location_expected was not set).');
		}
		self::$location_actual = $location;
	}
}
