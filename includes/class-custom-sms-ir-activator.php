<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Custom_SMS_IR
 * @subpackage Custom_SMS_IR/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Custom_SMS_IR
 * @subpackage Custom_SMS_IR/includes
 * @author     Your Name <email@example.com>
 */
class CUSTOM_SMS_IR_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        copy(__DIR__.'/../config.simple.php', __DIR__.'/../config.php');
	}

}
