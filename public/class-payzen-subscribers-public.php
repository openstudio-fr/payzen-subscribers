<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.openstudio.fr/
 * @since      1.0.0
 *
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/public
 * @author     Adrien BERARD <aberard@openstudio.fr>
 */

class Payzen_Subscribers_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

        if(file_exists(get_template_directory() . '/payzen-subscribers/public/css/payzen-subscribers-public.css')){
            wp_enqueue_style( $this->plugin_name, get_template_directory_uri() . '/payzen-subscribers/public/css/payzen-subscribers-public.css', array(), $this->version, 'all' );
        }else{
            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/payzen-subscribers-public.css', array(), $this->version, 'all' );
        }

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

        wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/payzen-subscribers-public.js', array( 'jquery' ), '1.0.0', true);

        wp_localize_script($this->plugin_name, 'paysubsSettings', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'confirm_text' => __('Are you sure ?','payzen-subscribers')
        ));

        wp_localize_script($this->plugin_name, 'paysubsFunc', $this->getPaymentMethodForm());

        // Enqueued script with localized data.
        wp_enqueue_script($this->plugin_name);
	}

    /**
     * No AJAX function
     */
	private function getPaymentMethodForm(): array
    {
        $PaymentMethodForm = array();
        $db = new Payzen_DB();
        $dbPlans = $db->getPlans();
        foreach ($dbPlans as $dbPlan){
            $plan = new Payzen_Subscribers_Plan($dbPlan->id);
            $PaymentMethodForm[$plan->getId()] = paymentMethodFormAjax($plan->getPaymentMethodArray());
        }
        return array(
            "paymentMethodsForm" => $PaymentMethodForm
        );
    }

    //////////// AJAX ///////////////
    /// All ajax functions here
    ///
    ///
    /**
     * AJAX payzen-subscribers-public.js
     * @return string
     */
    public function ajaxResponse(): string
    {
        echo json_encode($_REQUEST);
        die();
    }
    ///
    ///
    ///
    /////////////////////////////////

    /**
     * Remove front admin interface for users registration
     */
    public function hide_admin_bar_from_front_end(): bool
    {
        if (!is_blog_admin() && is_user_logged_in()) {
            $user = wp_get_current_user(); // getting & setting the current user
            $roles = ( array ) $user->roles; // obtaining the role
            //TODO get roles by global settings
            if(in_array('site_subscriber', $roles)){
                return false;
            }
        }
        if (is_blog_admin()){
            return true;
        }
        return false;
    }

}
