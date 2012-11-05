<?php

/**
 * Test login functionality
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
 * Set up the environment and get our PHPUnit class that is used as
 * the parent for our tests
 */
require_once dirname(__FILE__) .  '/TestCase.php';

/**
 * Test login functionality
 *
 * @package oop-plugin-template-solution
 * @author Daniel Convissor <danielc@analysisandsolutions.com>
 * @copyright The Analysis and Solutions Company, 2012
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * This plugin used the Object-Oriented Plugin Template Solution as a skeleton
 * REPLACE_PLUGIN_URI
 */
class LoginTest extends TestCase {
	protected $user_name;

	public static function setUpBeforeClass() {
		parent::$db_needed = true;
		parent::set_up_before_class();
	}

	public function setUp() {
		parent::setUp();

		if (!$this->is_table_login_configured()) {
			$this->markTestSkipped("The " . self::$o->table_login . " table doesn't exist or isn't using the InnoDB engine. Probably the plugin hasn't been activated.");
		}

		$this->user_name = 'testytester';

		$options = self::$o->options;
		$options['track_logins'] = 1;
		self::$o->options = $options;
	}


	/**
	 * How to test that emails get composed as expected
	 *
	 * Put the expected output in files named for the method into the
	 * tests/expected/en_US directory.  If you have translations, place
	 * those in a subdirectory of tests/expected named for the WPLANG
	 * to be tested.
	 */
	public function test_notify_login() {
		self::$mail_file_basename = __METHOD__;

		self::$o->notify_login($this->user_name);

		$this->check_mail_file();
	}

	/**
	 * How to account for expected errors and test wp_redirect() did what
	 * is expected
	 *
	 * Can't use PHPUnit's expectedException / expectedExceptionMessage
	 * functionality because test method execution ends when the error is
	 * generated.  We need this test method to run to its end so it can check
	 * the behavior after the error.
	 */
	public function test_redirect() {
		$expected_error = 'Cannot modify header information';
		$this->expected_errors($expected_error);
		self::$location_expected = wp_login_url() . '?action=retrievepassword';

		// For demonstration purposes only.  In the real world, the logout
		// and redirect would be triggred by some method in your plugin.
		wp_logout();
		wp_redirect(wp_login_url() . '?action=retrievepassword');

		$this->assertTrue($this->were_expected_errors_found(),
				"Expected error not found: '$expected_error'");
		$this->assertEquals(self::$location_expected, self::$location_actual,
				'wp_redirect() produced unexpected location header.');
	}

	/**
	 * Tests data inserted in a record that has an auto-increment ID
	 *
	 * Also an example of how to use a save point in the database.
	 */
	public function test_insert_login() {
		global $wpdb;
		$wpdb->query('SAVEPOINT no_metadata_login_time');

		self::$o->insert_login($this->user_name);
		$this->check_login_record($this->user_name);

		$wpdb->query('ROLLBACK TO no_metadata_login_time');
	}

	/**
	 * Makes sure the user's metadata is empty
	 */
	public function test_get_metadata_login_time__0() {
		$actual = self::$o->get_metadata_login_time($this->user->ID);
		$this->assertSame(0, $actual);
	}

	/**
	 * Adding user metadata returns the record id
	 * @depends test_get_metadata_login_time__0
	 */
	public function test_set_metadata_login_time__add() {
		$actual = self::$o->set_metadata_login_time($this->user->ID);
		$this->assertInternalType('integer', $actual, 'Bad return value.');
	}

	/**
	 * Updating user metadata returns true on success
	 * @depends test_set_metadata_login_time__add
	 */
	public function test_set_metadata_login_time__update() {
		sleep(1);
		$actual = self::$o->set_metadata_login_time($this->user->ID);
		$this->assertTrue($actual, 'Bad return value.');
	}

	/**
	 * @depends test_set_metadata_login_time__update
	 */
	public function test_get_metadata_login_time__existing() {
		$actual = self::$o->get_metadata_login_time($this->user->ID);
		$diff = (time() - $actual) < 1;
		$this->assertGreaterThanOrEqual(0,  $diff, 'Time was too long ago.');
		$this->assertLessThanOrEqual(1, $diff, 'Time was in the future.');
	}
}
