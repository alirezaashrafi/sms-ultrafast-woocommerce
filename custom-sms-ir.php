<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           Custom_SMS_IR
 *
 * @wordpress-plugin
 * Plugin Name:       Custom SMS IR
 * Description:       Manage Orders And Send Sms To Customers.
 * Version:           1.0.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-sms-ir
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CUSTOM_SMS_IR_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-custom-sms-ir-activator.php
 */
function activate_Custom_SMS_IR() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-sms-ir-activator.php';
	CUSTOM_SMS_IR_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-custom-sms-ir-deactivator.php
 */
function deactivate_Custom_SMS_IR() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-sms-ir-deactivator.php';
	CUSTOM_SMS_IR_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_Custom_SMS_IR' );
register_deactivation_hook( __FILE__, 'deactivate_Custom_SMS_IR' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-custom-sms-ir.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_Custom_SMS_IR() {

	$plugin = new Custom_SMS_IR();
	$plugin->run();

}
run_Custom_SMS_IR();
