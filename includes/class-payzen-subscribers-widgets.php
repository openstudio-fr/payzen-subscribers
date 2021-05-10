<?php

/**
 * Payzen widgets
 * submittedFormsActions : treatment submitted forms
 */

class Payzen_Subscribers_widgets {

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

    public function __construct() {
        if (defined('PAYZEN_SUBSCRIBERS_VERSION')) {
            $this->version = PAYZEN_SUBSCRIBERS_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'payzen-subscribers';

        $GLOBALS['registrationErrors'] = new WP_Error;
        $GLOBALS['loginErrors'] = new WP_Error;
        $GLOBALS['informationAccountErrors'] = new WP_Error;

        $this->load_dependencies();
        $this->load_basic_actions();
    }

    private function load_dependencies() {
        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-payzen-subscribers-widget-subscribe-form.php';

        if(file_exists(get_template_directory() . '/payzen-subscribers/public/class-payzen-subscribers-widget-subscribe-form.php')){
            include_once get_template_directory() . '/payzen-subscribers/public/class-payzen-subscribers-widget-subscribe-form.php';
        }else{
            include_once plugin_dir_path(dirname(__FILE__)) . 'public/class-payzen-subscribers-widget-subscribe-form.php';
        }

        include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-payzen-subscribers-widget-account.php';

        if(file_exists(get_template_directory() . '/payzen-subscribers/public/class-payzen-subscribers-widget-account.php')){
            include_once get_template_directory() . '/payzen-subscribers/public/class-payzen-subscribers-widget-account.php';
        }else{
            include_once plugin_dir_path(dirname(__FILE__)) . 'public/class-payzen-subscribers-widget-account.php';
        }


        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-payzen-subscribers-loader.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-payzen-subscribers-public.php';

        /**
         * Validator wordpress user
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/forms/class-payzen-subscribers-widget-payment-form.php';

        /**
         * Validator
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/forms/class-payzen-subscribers-widget-account-form.php';

        $this->loader = new Payzen_Subscribers_Loader();

    }

    private function load_basic_actions() {

        add_action('init', function (){
            /**
             * Submitted forms treatment is here for
             * Possible to add_action and
             * Login user
             */
            Payzen_Subscribers_Widget_Subscribe_Form_Pub::submittedFormsActions();
            Payzen_Subscribers_Widget_Account_Pub::submittedFormsActions();
        });

        add_action('widgets_init', function() {

            /**
             * Display subscribe form widget in widget zone
             */
            register_widget('Payzen_Subscribers_Widget_Subscribe_Form');

            /**
             * Display account widget in widget zone
             */
            register_widget('Payzen_Subscribers_Widget_Account');
        });

        /**
         * Display widget by shortcode [paysubs id=XX /]
         */
        add_shortcode( 'paysubs', array($this, 'paysubs_shortcode_cb') );

        /**
         * Display widget by shortcode
         */
        add_shortcode( 'paysubs_account', array($this, 'paysubs_account_shortcode_cb') );

    }

    /**
     * Add shortcode with this widget
     * @param $attrs
     * @param null $content
     * @return false|string
     */
    public function paysubs_shortcode_cb($attrs, $content = null ) {
        $shortcodes = shortcode_atts( array(
            'title' => '',
            'id'    => ''
        ), $attrs );

        ob_start();
        the_widget('Payzen_Subscribers_Widget_Subscribe_Form' ,
            array( //instance
                'id'=>$shortcodes['id'],
//                'title'=>$shortcodes['title'],
//                'content'=>$content
            ),array( //args
            'before_widget' => '',
            'after_widget' => ''
            )
        );
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Add shortcode with this widget
     * @param $attrs
     * @param null $content
     * @return false|string
     */
    public function paysubs_account_shortcode_cb($attrs, $content = null ) {
        ob_start();
        the_widget('Payzen_Subscribers_Widget_Account' ,
            array( //instance
            ),array( //args
                'before_widget' => '',
                'after_widget' => ''
            )
        );
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    function getLoader(): Payzen_Subscribers_Loader
    {
        return $this->loader;
    }

    function getPlugin_name() {
        return $this->plugin_name;
    }

    function getVersion() {
        return $this->version;
    }

}
