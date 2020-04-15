<?php
/**
 * Plugin Name: WooCommerce Minimum Age
 * Plugin URI: http://wpsquadbox.com
 * Description: Add a required age field at checkout.
 * Version: 0.0.0
 * Author: WP Squadbox
 * Author URI: http://wpsquadbox.com
 * Text Domain: woocommerce-minimum-age
 */

if (!defined('ABSPATH')) exit();

define('WOO_MIN_AGE_NAME', basename(dirname(__FILE__)).'/'.basename(__FILE__));
define('WOO_MIN_AGE_PATH', plugin_dir_path(__FILE__));
define('WOO_MIN_AGE_URL', plugin_dir_url(__FILE__));

if (!class_exists('WooCommerce_Minimum_Age')):

class WooCommerce_Minimum_Age {

	public static $plugin = array(
		'name'          => 'WooCommerce Minimum Age',
		'version'       => '0.0.0',
		'file'          => __FILE__,
		'option_prefix' => 'woocommerce_minimum_age_',
	);

	/**
	 * Constructor.
	 *
	 * @date	4/14/20
	 */
	public function setup() {
		add_action('plugins_loaded', array($this, 'includes'));
	}

	/**
	 * Includes.
	 *
	 * If the WooCommerce plugin is not active, then don't allow includes to run.
	 *
	 * @date	4/14/20
	 */
	public function includes() {

		// Return null if WooCommerce is not active.
		if (!class_exists('WooCommerce')) {
			add_action('admin_notices', array($this, 'woocommerce_notice'));
			return null;
		}

		// Return if php version is below 5.5.0
		if (version_compare('5.5.0', PHP_VERSION, '>') && current_user_can('activate_plugins')) {
			add_action('admin_notices', array($this, 'php_version_notice'));
			return;
		}

		// Includes
		include_once(WOO_MIN_AGE_PATH.'inc/class-woocommerce-minimum-age-admin.php');
	}

	/**
	 * Display an error notice if WooCommerce is not active.
	 *
	 * @date	4/14/20
	 */
	public function woocommerce_notice() {
		printf('<div class="error"><p>'.__('<strong>WooCommerce</strong> is deactivated or does not exist. Please install and activate it to use <strong>'.self::$plugin['name'].'</strong>', 'woocommerce-minimum-age').'</p></div>', PHP_VERSION);
	}

	/**
	 * Display an error notice if the PHP version is lower than 5.5.
	 *
	 * @date	4/14/20
	 */
	public function php_version_notice() {
		printf('<div class="error"><p>'.__(self::$plugin['name'].' requires PHP version 5.5.0 or higher. Your server is running PHP version %s. Please contact your hosting company to upgrade your site to 5.5.0 or later.', 'woocommerce-minimum-age').'</p></div>', PHP_VERSION);
	}

}

/**
 * Return WooCommerce_Minimum_Age class instance.
 *
 * @date	4/8/19
 */
function woocommerce_minimum_age() {
	global $woocommerce_minimum_age;

	// Instantiate only once.
	if (!isset($woocommerce_minimum_age)) {
		$woocommerce_minimum_age = new WooCommerce_Minimum_Age();
		$woocommerce_minimum_age->setup();
	}
	return $woocommerce_minimum_age;
}

// Instantiate.
woocommerce_minimum_age();

endif; // class_exists check
