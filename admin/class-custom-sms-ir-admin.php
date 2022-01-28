<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Custom_SMS_IR
 * @subpackage Custom_SMS_IR/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Custom_SMS_IR
 * @subpackage Custom_SMS_IR/admin
 * @author     Your Name <email@example.com>
 */
class CUSTOM_SMS_IR_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $Custom_SMS_IR    The ID of this plugin.
	 */
	private $Custom_SMS_IR;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $Custom_SMS_IR       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $Custom_SMS_IR, $version ) {

		$this->Custom_SMS_IR = $Custom_SMS_IR;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in CUSTOM_SMS_IR_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The CUSTOM_SMS_IR_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->Custom_SMS_IR, plugin_dir_url( __FILE__ ) . 'css/custom-sms-ir-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in CUSTOM_SMS_IR_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The CUSTOM_SMS_IR_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->Custom_SMS_IR, plugin_dir_url( __FILE__ ) . 'js/custom-sms-ir-admin.js', array( 'jquery' ), $this->version, false );

	}

}
