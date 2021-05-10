<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.openstudio.fr/
 * @since      1.0.0
 *
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/includes
 * @author     Adrien BERARD <aberard@openstudio.fr>
 */
class Payzen_Subscribers {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Payzen_Subscribers_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PAYZEN_SUBSCRIBERS_VERSION' ) ) {
			$this->version = PAYZEN_SUBSCRIBERS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'payzen-subscribers';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Payzen_Subscribers_Loader. Orchestrates the hooks of the plugin.
	 * - Payzen_Subscribers_i18n. Defines internationalization functionality.
	 * - Payzen_Subscribers_Admin. Defines all hooks for the admin area.
	 * - Payzen_Subscribers_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-payzen-subscribers-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-payzen-subscribers-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-payzen-subscribers-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-payzen-subscribers-api-settings.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-payzen-subscribers-subscriptions-plans.php';

        /**
         * User adding settings
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-payzen-subscribers-user-settings.php';

		/**
		 * Autoloader composer.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

        /**
         * DB parameters
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payzen-subscribers-db.php';

        /**
         * utils
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payzen-subscribers-utils.php';

        /**
         * payzen api
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payzen-subscribers-api.php';

        /**
         * Create plans on payzen
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payzen-subscribers-plan.php';

        /**
         * Send emails
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payzen-subscribers-send-mails.php';

        /**
         * Cron jobs
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payzen-subscribers-cron.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-payzen-subscribers-public.php';

		$this->loader = new Payzen_Subscribers_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Payzen_Subscribers_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Payzen_Subscribers_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Payzen_Subscribers_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        /**
         * @link admin/class-payzen-subscribers-admin.php
         */
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');

        /**
         * Register our wporg_settings_init to the admin_init action hook.
         */
        $this->loader->add_action( 'admin_init', new Payzen_Subscribers_Settings, 'payzen_subscribers_settings_init' );

        $this->loader->add_action( 'show_user_profile', new User_Settings(), 'show_extra_profile_fields' );
        $this->loader->add_action( 'edit_user_profile', new User_Settings(), 'edit_extra_profile_fields' );

        $this->run();

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Payzen_Subscribers_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        /**
         * AJAX add functions
         */
        $this->loader->add_action( 'wp_ajax_ajaxResponse', $plugin_public, 'ajaxResponse' );

        $this->loader->add_filter('show_admin_bar', $plugin_public, 'hide_admin_bar_from_front_end');

        /**
         * Cron job
         */
        $cron = new Payzen_Cron();
        $this->loader->add_action('my_daily_event', $cron, 'do_cron_action');
        $this->loader->add_filter('cron_schedules', $cron, 'custom_cron_schedules');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Payzen_Subscribers_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
