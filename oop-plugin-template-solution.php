<?php

/**
 * Plugin Name: Object Oriented Plugin Template Solution
 *
 * Description: A well engineered template for creating plugins using
 * object-oriented programming practices.
 *
 * Plugin URI: http://wordpress.org/extend/plugins/oop-plugin-template-solution/
 * Version: 1.0.2
 *         (Remember to change the VERSION constant, below, as well!)
 * Author: Daniel Convissor
 * Author URI: http://www.analysisandsolutions.com/
 * License: GPLv2
 * @package oop-plugin-template-solution
 *
 * This plugin used the Object-Oriented Plugin Template Solution as a skeleton
 * REPLACE_PLUGIN_URI
 */

/**
 * The instantiated version of this plugin's class
 */
$GLOBALS['oop_plugin_template_solution'] = new oop_plugin_template_solution;

/**
 * Object Oriented Plugin Template Solution
 *
 * @package oop-plugin-template-solution
 * @link http://wordpress.org/extend/plugins/oop-plugin-template-solution/
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @author Daniel Convissor <danielc@analysisandsolutions.com>
 * @copyright The Analysis and Solutions Company, 2012
 *
 * This plugin used the Object-Oriented Plugin Template Solution as a skeleton
 * REPLACE_PLUGIN_URI
 */
class oop_plugin_template_solution {
	/**
	 * This plugin's identifier
	 */
	const ID = 'oop-plugin-template-solution';

	/**
	 * This plugin's name
	 */
	const NAME = 'Object Oriented Plugin Template Solution';

	/**
	 * This plugin's version
	 */
	const VERSION = '1.0.2';

	/**
	 * This plugin's table name prefix
	 * @var string
	 */
	protected $prefix = 'oop_plugin_template_solution_';


	/**
	 * Has the internationalization text domain been loaded?
	 * @var bool
	 */
	protected $loaded_textdomain = false;

	/**
	 * This plugin's options
	 *
	 * Options from the database are merged on top of the default options.
	 *
	 * @see oop_plugin_template_solution::set_options()  to obtain the saved
	 *      settings
	 * @var array
	 */
	protected $options = array();

	/**
	 * This plugin's default options
	 * @var array
	 */
	protected $options_default = array(
		'deactivate_deletes_data' => 1,
		'example_int' => 5,
		'example_string' => '',
		'track_logins' => 1,
	);

	/**
	 * Our option name for storing the plugin's settings
	 * @var string
	 */
	protected $option_name;

	/**
	 * Name, with $table_prefix, of the table tracking login failures
	 * @var string
	 */
	protected $table_login;

	/**
	 * Our usermeta key for tracking when a user logged in
	 * @var string
	 */
	protected $umk_login_time;


	/**
	 * Declares the WordPress action and filter callbacks
	 *
	 * @return void
	 * @uses oop_plugin_template_solution::initialize()  to set the object's
	 *       properties
	 */
	public function __construct() {
		$this->initialize();

		if ($this->options['track_logins']) {
			add_action('wp_login', array(&$this, 'wp_login'), 1, 2);
		}

		if (is_admin()) {
			$this->load_plugin_textdomain();

			require_once dirname(__FILE__) . '/admin.php';
			$admin = new oop_plugin_template_solution_admin;

			if (is_multisite()) {
				$admin_menu = 'network_admin_menu';
				$admin_notices = 'network_admin_notices';
				$plugin_action_links = 'network_admin_plugin_action_links_oop-plugin-template-solution/oop-plugin-template-solution.php';
			} else {
				$admin_menu = 'admin_menu';
				$admin_notices = 'admin_notices';
				$plugin_action_links = 'plugin_action_links_oop-plugin-template-solution/oop-plugin-template-solution.php';
			}

			add_action($admin_menu, array(&$admin, 'admin_menu'));
			add_action('admin_init', array(&$admin, 'admin_init'));
			add_filter($plugin_action_links, array(&$admin, 'plugin_action_links'));

			register_activation_hook(__FILE__, array(&$admin, 'activate'));
			if ($this->options['deactivate_deletes_data']) {
				register_deactivation_hook(__FILE__, array(&$admin, 'deactivate'));
			}
		}
	}

	/**
	 * Sets the object's properties and options
	 *
	 * This is separated out from the constructor to avoid undesirable
	 * recursion.  The constructor sometimes instantiates the admin class,
	 * which is a child of this class.  So this method permits both the
	 * parent and child classes access to the settings and properties.
	 *
	 * @return void
	 *
	 * @uses oop_plugin_template_solution::set_options()  to replace the default
	 *       options with those stored in the database
	 */
	protected function initialize() {
		global $wpdb;

		$this->table_login = $wpdb->get_blog_prefix(0) . $this->prefix . 'login';

		$this->option_name = self::ID . '-options';
		$this->umk_login_time = self::ID . '-login-time';

		$this->set_options();
	}

	/*
	 * ===== ACTION & FILTER CALLBACK METHODS =====
	 */

	/**
	 * Stores the time a user logs in
	 *
	 * NOTE: This method is automatically called by WordPress when users
	 * successfully log in.
	 *
	 * @param string $user_name  the user name from the current login form
	 * @param WP_User $user  the current user
	 * @return void
	 */
	public function wp_login($user_name, $user) {
		if (!$user_name) {
			return;
		}
		$this->insert_login($user_name);
		$this->set_metadata_login_time($user->ID);
		$this->notify_login($user_name);
	}

	/*
	 * ===== INTERNAL METHODS ====
	 */

	/**
	 * Obtains the email addresses the notifications should go to
	 * @return string
	 */
	protected function get_admin_email() {
		return get_site_option('admin_email');
	}

	/**
	 * Obtains the timestamp of the given user's last "login time"
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int  the Unix timestamp of the user's last login
	 */
	protected function get_metadata_login_time($user_ID) {
		return (int) get_user_meta($user_ID, $this->umk_login_time, true);
	}

	/**
	 * Sanitizes output via htmlspecialchars() using UTF-8 encoding
	 *
	 * Makes this program's native text and translated/localized strings
	 * safe for displaying in browsers.
	 *
	 * @param string $in   the string to sanitize
	 * @return string  the sanitized string
	 */
	protected function hsc_utf8($in) {
		return htmlspecialchars($in, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Saves the login info in the database
	 *
	 * @param string $user_login  the user name from the current login form
	 * @return void
	 */
	protected function insert_login($user_login) {
		global $wpdb;

		$wpdb->insert(
			$this->table_login,
			array(
				'user_login' => $user_login,
			),
			array('%s')
		);
	}

	/**
	 * A centralized way to load the plugin's textdomain for
	 * internationalization
	 * @return void
	 */
	protected function load_plugin_textdomain() {
		if (!$this->loaded_textdomain) {
			load_plugin_textdomain(self::ID, false, self::ID . '/languages');
			$this->loaded_textdomain = true;
		}
	}

	/**
	 * Sends an email to the blog's administrator telling them of a login
	 *
	 * @param string $user_name  the user name from the current login form
	 * @return bool
	 *
	 * @uses wp_mail()  to send the messages
	 */
	protected function notify_login($user_name) {
		$this->load_plugin_textdomain();

		$to = $this->sanitize_whitespace($this->get_admin_email());
		$blog = get_option('blogname');

		$subject = sprintf(__("LOGIN TO %s", self::ID), $blog);
		$subject = $this->sanitize_whitespace($subject);

		$message = sprintf(__("%s just logged in to %s.", self::ID),
				$user_name, $blog) . "\n";

		return wp_mail($to, $subject, $message);
	}

	/**
	 * Replaces all whitespace characters with one space
	 * @param string $in  the string to clean
	 * @return string  the cleaned string
	 */
	protected function sanitize_whitespace($in) {
		return preg_replace('/\s+/u', ' ', $in);
	}

	/**
	 * Stores the present time in the given user's "login time" metadata
	 *
	 * @param int $user_ID  the current user's ID number
	 * @return int|bool  the record number if added, TRUE if updated, FALSE
	 *                   if error
	 */
	protected function set_metadata_login_time($user_ID) {
		return update_user_meta($user_ID, $this->umk_login_time, time());
	}

	/**
	 * Replaces the default option values with those stored in the database
	 * @uses login_security_solution::$options  to hold the data
	 */
	protected function set_options() {
		if (is_multisite()) {
			switch_to_blog(1);
			$options = get_option($this->option_name);
			restore_current_blog();
		} else {
			$options = get_option($this->option_name);
		}
		if (!is_array($options)) {
			$options = array();
		}
		$this->options = array_merge($this->options_default, $options);
	}
}
