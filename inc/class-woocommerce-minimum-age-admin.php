<?php

if (!defined('ABSPATH')) exit();

if (!class_exists('WooCommerce_Minimum_Age_Admin')):

class WooCommerce_Minimum_Age_Admin {

	/**
	 * Constructor.
	 *
	 * @date	4/14/20
	 */
	public function setup() {
		add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
		add_action('woocommerce_settings_tabs_minimum_age', array($this, 'settings_tab'));
		add_action('woocommerce_update_options_minimum_age', array($this, 'update_settings'));
		add_filter('plugin_action_links_'.WOO_MIN_AGE_NAME, array($this, 'filter_action_links'), 10, 1 );
		if (!empty(get_option(woocommerce_minimum_age()::$plugin['option_prefix'].'minimum_age'))) {
			add_filter('woocommerce_billing_fields', array($this, 'add_birth_date_billing_field'), 20, 1);
			add_action('woocommerce_checkout_process', array($this, 'check_birth_date'));
			add_action('woocommerce_checkout_update_order_meta', array($this, 'save_age'));
		}
		if (get_option(woocommerce_minimum_age()::$plugin['option_prefix'].'record_age_order_receipt') == 'yes') {
			add_action('woocommerce_email_order_meta_fields', array($this, 'show_age_in_emails'), 10, 3);
		}
		if (get_option(woocommerce_minimum_age()::$plugin['option_prefix'].'record_age_order_details') == 'yes') {
			add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'show_age_in_admin'));
		}
	}

	/**
	 * Add plugin action links
	 *
	 * @date	4/14/20
	 */
	public function filter_action_links($links) {
		$links = array_merge(array(
			'<a href="'.esc_url(admin_url('admin.php?page=wc-settings&tab=minimum_age')).'">'.esc_html__('Settings', 'woocommerce-minimum-age').'</a>'
		), $links);

		return $links;
	}

	/**
	 * Add a new tab to WooCommerce > Settings.
	 *
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @date	4/14/20
	 */
	public function add_settings_tab($settings_tabs) {
		$settings_tabs['minimum_age'] = __('Minimum Age', 'woocommerce-minimum-age');
		return $settings_tabs;
	}

	/**
	 * Output settings.
	 *
	 * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
	 *
	 * @date	4/14/20
	 */
	public function settings_tab() {
		woocommerce_admin_fields($this->get_settings());
	}


	/**
	 * Save settings.
	 *
	 * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
	 *
	 * @date	4/14/20
	 */
	public function update_settings() {
		woocommerce_update_options($this->get_settings());
	}

	/**
	 * Get settings
	 *
	 * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
	 *
	 * @date	4/14/20
	 */
	public function get_settings() {
		$settings = [
			[
				'name' => __('Age requirements', 'woocommerce-minimum-age'),
				'desc' => __('The following options are used to configure your stores required age at checkout.', 'woocommerce-minimum-age'),
				'type' => 'title',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'requirements',
			],
			[
				'name' => __('Minimum age', 'woocommerce-minimum-age'),
				'desc' => __('Leave blank to disable age capture on checkout.', 'woocommerce-minimum-age'),
				'desc_tip' => __('This is the age required at checkout to purchase a product.', 'woocommerce-minimum-age'),
				'placeholder' => 'Enter a number only',
				'type' => 'number',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'minimum_age',
			],
			[
				'type' => 'sectionend',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'section_end',
			],
			[
				'name' => __('Record age request', 'woocommerce-minimum-age'),
				'desc' => __('The following options are for recording your efforts to capture age (recommended).', 'woocommerce-minimum-age'),
				'type' => 'title',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'requirements',
			],
			[
				'name' => __('Enable in order details', 'woocommerce-minimum-age'),
				'desc' => __('Enable saving of age in order details', 'woocommerce-minimum-age'),
				'type' => 'checkbox',
				'default' => 'yes',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'record_age_order_details',
			],
			[
				'name' => __('Enable on receipt', 'woocommerce-minimum-age'),
				'desc' => __('Enable saving of age on the order receipt', 'woocommerce-minimum-age'),
				'type' => 'checkbox',
				'default' => 'yes',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'record_age_order_receipt',
			],
			[
				'type' => 'sectionend',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'section_end',
			],
			[
				'name' => __('Minimum age field', 'woocommerce-minimum-age'),
				'desc' => __('The following options affect the minumum age field values.', 'woocommerce-minimum-age'),
				'type' => 'title',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'settings',
			],
			[
				'name' => __('Field label', 'woocommerce-minimum-age'),
				'default' => __('Verify your age', 'woocommerce-minimum-age'),
				'desc_tip' => __('The text that appears above the age checkout field.', 'woocommerce-minimum-age'),
				'type' => 'text',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'minimum_age_label',
			],
			[
				'name' => __('Field description', 'woocommerce-minimum-age'),
				'default' => __('The date of birth is required to be recorded with the order as proof of age.', 'woocommerce-minimum-age'),
				'desc_tip' => __('The text that appears when a user clicks the label for more information.', 'woocommerce-minimum-age'),
				'type' => 'textarea',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'minimum_age_description',
			],
			[
				'type' => 'sectionend',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'section_end',
			],
			[
				'name' => __('Errors', 'woocommerce-minimum-age'),
				'desc' => __('The following options affect how age errors are displayed at checkout.', 'woocommerce-minimum-age'),
				'type' => 'title',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'requirements',
			],
			[
				'name' => __('Under age error', 'woocommerce-minimum-age'),
				'default' => __('You may not place an order on this shop if you are under {{age}}.', 'woocommerce-minimum-age'),
				'desc_tip' => __('The error text that appears when an under age user tries to check out. {{age}} will be replaced by the minimum age.', 'woocommerce-minimum-age'),
				'type' => 'textarea',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'under_age_error',
			],
			[
				'type' => 'sectionend',
				'id' => woocommerce_minimum_age()::$plugin['option_prefix'].'section_end',
			],
		];

		return $settings;
	}

	/**
	 * Add the age field to checkout.
	 *
	 * @date	4/14/20
	 */
	public function add_birth_date_billing_field($billing_fields) {
		$billing_fields['billing_order_dob'] = array(
			'type' => 'date',
			'label' => get_option(woocommerce_minimum_age()::$plugin['option_prefix'].'minimum_age_label'),
			'description' => get_option(woocommerce_minimum_age()::$plugin['option_prefix'].'minimum_age_description'),
			'class' => array('form-row-wide'),
			'priority' => 25,
			'required' => true,
			'clear' => true,
		);

		return $billing_fields;
	}

	/**
	 * Check customer age.
	 *
	 * @date	4/14/20
	 * @todo	Add option in woo settings to perform this if checked: WC()->cart->empty_cart();
	 */
	public function check_birth_date($order_id) {
		$dob = $_POST['billing_order_dob'];
		$age = date_diff(date_create($dob), date_create('now'))->y;
		$min_age = get_option(woocommerce_minimum_age()::$plugin['option_prefix'].'minimum_age');

		if (isset($dob) && !empty($dob)) {
			if ($age < $min_age) {
				$message = str_replace('{{age}}', $min_age, get_option(woocommerce_minimum_age()::$plugin['option_prefix'].'under_age_error'));
				wc_add_notice($message, 'error');
			}
		}
	}

	/**
	 * Save date of birth, and age, to the order.
	 *
	 * @date	4/14/20
	 */
	public function save_age($order_id) {
		$dob = $_POST['billing_order_dob'];
		$age = date_diff(date_create($dob), date_create('now'))->y;

		if (isset($dob)) {
			update_post_meta($order_id, 'billing_order_dob', $dob);
			update_post_meta($order_id, 'billing_order_age', $age);
		}
	}

	/**
	 * Add date of birth to emails.
	 *
	 * @date	4/14/20
	 */
	public function show_age_in_emails($keys, $sent_to_admin, $order) {
		if (is_numeric($order)) {
			$order = wc_get_order($order);
		}

		$dob = get_post_meta($order->get_id(), 'billing_order_dob', true);
		$age = get_post_meta($order->get_id(), 'billing_order_age', true);

		if ($dob) {
			$dob = strtotime($dob);
			$keys['billing_order_dob'] = [
				'label' => _x('Date of birth', 'order email field label', 'woocommerce-minimum-age'),
				'value' => date(_x('jS F, Y', 'order email date format', 'woocommerce-minimum-age'), $dob),
			];
			$keys['billing_order_age'] = [
				'label' => _x('Age', 'order email field label', 'woocommerce-minimum-age'),
				'value' => $age,
			];
		}

		return $keys;
	}

	/**
	 * add date of birth to admin order detail
	 *
	 * @date	4/14/20
	 */
	public function show_age_in_admin($order) {
		$dob = get_post_meta($order->get_id(), 'billing_order_dob', true);
		$age = get_post_meta($order->get_id(), 'billing_order_age', true);

		if ($dob) {
			$dob = date(_x('jS F, Y', 'admin date format', 'minimum-age-woocommerce'), strtotime($dob));
			printf('<p><strong>%s</strong>: %s</p>', esc_html_x('Date of birth', 'admin field label', 'minimum-age-woocommerce'), esc_html($dob));
			printf('<p><strong>%s</strong>: %s</p>', esc_html_x('Age', 'admin field label', 'minimum-age-woocommerce'), esc_html($age));
		}
	}
}

/**
 * Return WooCommerce_Minimum_Age_Admin class instance.
 *
 * @date	4/14/20
 */
function woocommerce_minimum_age_admin() {
	global $woocommerce_minimum_age_admin;

	// Instantiate only once.
	if (!isset($woocommerce_minimum_age_admin)) {
		$woocommerce_minimum_age_admin = new WooCommerce_Minimum_Age_Admin();
		$woocommerce_minimum_age_admin->setup();
	}
	return $woocommerce_minimum_age_admin;
}

// Instantiate.
woocommerce_minimum_age_admin();

endif; // class_exists check
