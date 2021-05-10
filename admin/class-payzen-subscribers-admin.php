<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.openstudio.fr/
 * @since      1.0.0
 *
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/admin
 * @author     Adrien BERARD <aberard@openstudio.fr>
 */
class Payzen_Subscribers_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
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
		 * defined in Payzen_Subscribers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Payzen_Subscribers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/payzen-subscribers-admin.css', array(), $this->version, 'all' );

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
		 * defined in Payzen_Subscribers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Payzen_Subscribers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/payzen-subscribers-admin.js', array( 'jquery' ), $this->version, false );

	}

    public function add_admin_menu() {

	    $settings = new Payzen_Subscribers_Settings();
	    $subSettings = new Payzen_Subscribers_Subscriptions_Plans();

        add_menu_page(
                __('Payzen subscribers',
                    'payzen-subscribers'),
                __('Payzen subscribers',
                    'payzen-subscribers'),
                'manage_options',
                'payzen-subscribers',
                array($settings, 'paysubs_options_page_html'),
                'dashicons-money'
        );
        add_submenu_page(
            'payzen-subscribers',
            __('Payzen API Settings','payzen-subscribers'),
            __('API settings','payzen-subscribers'),
            'activate_plugins',
            'payzen-subscribers',
            array($settings, 'paysubs_options_page_html')
        );
        add_submenu_page(
                'payzen-subscribers',
                __('Subscriptions plans',
                    'payzen-subscribers'),
                __('Subscriptions plans',
                    'payzen-subscribers'),
                'activate_plugins',
                'payzen-souscriptions-settings',
                array($subSettings, 'displaySettings')
        );
        add_submenu_page(
            'payzen-subscribers',
            __('Add plan',
                'payzen-subscribers'),
            __('Add plan',
                'payzen-subscribers'),
            'activate_plugins',
            'payzen-subscriptions-setting',
            array($subSettings, 'displaySetting')
        );
    }

}
