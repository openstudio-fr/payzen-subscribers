<?php


class Payzen_Subscribers_Settings {

    public function __construct() {

    }

    /**
     * @internal never define functions inside callbacks.
     * these functions could be run multiple times; this would result in a fatal error.
     */

    /**
     * custom option and settings
     */
    public function payzen_subscribers_settings_init() {
        // Register a new setting for "paysubs" page.
        register_setting( 'paysubs', 'paysubs_api_settings' );

        // Register a new section in the "paysubs" page.
        add_settings_section(
            'paysubs_section_api_settings',
            __( 'REST API key', 'payzen-subscribers' ), array($this, 'paysubs_section_api_settings_callback'),
            'paysubs'
        );

         // Register a new field in the "paysubs_section_api_settings" section, inside the "paysubs" page.
        add_settings_field(
            'paysubs_settings_test', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'API mode', 'payzen-subscribers' ),
            array($this, 'paysubs_mode_settings_cb'),
            'paysubs',
            'paysubs_section_api_settings',
            array(
                'label_mode'                => 'paysubs_field_mode',
            )
        );

        // Register a new field in the "paysubs_section_api_settings" section, inside the "paysubs" page.
        add_settings_field(
            'paysubs_settings', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'REST API key', 'payzen-subscribers' ),
            array($this, 'paysubs_api_rest_settings_cb'),
            'paysubs',
            'paysubs_section_api_settings',
            array(
                'label_user'                => 'paysubs_field_user',
                'label_test_password'       => 'paysubs_field_test_password',
                'label_product_password'    => 'paysubs_field_prod_password',
                'label_name_server'         => 'paysubs_field_name_server',
            )
        );

        // Register a new section in the "paysubs" page.
        add_settings_section(
            'paysubs_section_js_api_settings',
            __( 'Client javaScript key', 'payzen-subscribers' ), function(){},
            'paysubs'
        );

        add_settings_field(
            'paysubs_js_key_settings', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'API key', 'payzen-subscribers' ),
            array($this, 'paysubs_js_key_settings_cb'),
            'paysubs',
            'paysubs_section_js_api_settings',
            array(
                'label_js_key_prod'         => 'paysubs_field_js_key_prod',
                'label_js_key_test'         => 'paysubs_field_js_key_test',
            )
        );

        // Register a new section in the "paysubs" page.
        add_settings_section(
            'paysubs_section_hash_api_settings',
            __( 'SHA-256 keys', 'payzen-subscribers' ), function(){},
            'paysubs'
        );

        add_settings_field(
            'paysubs_hash_settings', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'API key', 'payzen-subscribers' ),
            array($this, 'paysubs_hash_key_settings_cb'),
            'paysubs',
            'paysubs_section_hash_api_settings',
            array(
                'label_hash_key_test'       => 'paysubs_field_hash_key_test',
                'label_hash_key_prod'       => 'paysubs_field_hash_key_prod',
            )
        );

        add_settings_field(
            'paysubs_key_settings', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'API key', 'payzen-subscribers' ),
            array($this, 'paysubs_key_settings_cb'),
            'paysubs',
            'paysubs_section_hash_api_settings',
            array(
                'label_test_api_key'       => 'paysubs_field_test_api_key',
                'label_api_key'            => 'paysubs_field_api_key',
            )
        );

        // Register a new section in the "paysubs" page.
        add_settings_section(
            'paysubs_section_divers_settings',
            __( 'Divers settings', 'payzen-subscribers' ), function(){},
            'paysubs'
        );

        add_settings_field(
            'paysubs_key_settings', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'Divers', 'payzen-subscribers' ),
            array($this, 'paysubs_divers_settings_cb'),
            'paysubs',
            'paysubs_section_divers_settings',
            array(
                'label_redirect_sdd'       => 'paysubs_field_redirect_sdd',
            )
        );
    }

    /**
     * Pill field callbakc function.
     *
     * WordPress has magic interaction with the following keys: label_for, class.
     * - the "label_for" key value is used for the "for" attribute of the <label>.
     * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
     * Note: you can add custom key value pairs to be used inside your callbacks.
     *
     * @param array $args
     */
    function paysubs_mode_settings_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'paysubs_api_settings' );
        ?>
        <input type="radio" id="test" name="paysubs_api_settings[<?php echo esc_attr( $args['label_mode'] ); ?>]"
            <?php echo isset( $options['paysubs_field_mode'] ) && $options['paysubs_field_mode'] === 'TEST' ?  'checked' : '' ;?> value="TEST">
        <label for="test">Test</label><br>
        <input type="radio" id="prod" name="paysubs_api_settings[<?php echo esc_attr( $args['label_mode'] ); ?>]"
            <?php echo isset( $options['paysubs_field_mode'] ) && $options['paysubs_field_mode'] === 'PRODUCTION' ?  'checked' : '' ;?> value="PRODUCTION">
        <label for="prod">Production</label><br>
        <?php
    }

    function paysubs_api_rest_settings_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'paysubs_api_settings' );
        ?>
        <label for="<?php echo esc_attr( $args['label_user'] ); ?>"><?php _e('Login', 'payzen-subscribers') ?></label>
        <input
            id="<?php echo esc_attr( $args['label_user'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_user'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_user'] ) ?  esc_attr($options['paysubs_field_user']) : '' ;?>"
            >
        <br>
        <label for="<?php echo esc_attr( $args['label_test_password'] ); ?>"><?php _e('Test password', 'payzen-subscribers'); ?>
        <input
            id="<?php echo esc_attr( $args['label_test_password'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_test_password'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_test_password'] ) ?  esc_attr($options['paysubs_field_test_password']) : '' ;?>"
            >
        <br>
        <label for="<?php echo esc_attr( $args['label_product_password'] ); ?>"><?php _e('Production password', 'payzen-subscribers'); ?>
        <input
            id="<?php echo esc_attr( $args['label_product_password'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_product_password'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_prod_password'] ) ?  esc_attr($options['paysubs_field_prod_password']) : '' ;?>"
            >
        <br>
        <label for="<?php echo esc_attr( $args['label_name_server'] ); ?>"><?php _e('Payzen server url', 'payzen-subscribers'); ?>
        <input
            id="<?php echo esc_attr( $args['label_name_server'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_name_server'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_name_server'] ) ?  esc_attr($options['paysubs_field_name_server']) : '' ;?>"
            >
        <?php
    }

    function paysubs_js_key_settings_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'paysubs_api_settings' );
        ?>
        <label for="<?php echo esc_attr( $args['label_js_key_test'] ); ?>"><?php _e('Javascript key test', 'payzen-subscribers'); ?></label>
        <input
            id="<?php echo esc_attr( $args['label_js_key_test'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_js_key_test'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_js_key_test'] ) ?  esc_attr($options['paysubs_field_js_key_test']) : '' ;?>"
            >
        <br>
        <label for="<?php echo esc_attr( $args['label_js_key_prod'] ); ?>"><?php _e('Javascript key production', 'payzen-subscribers'); ?></label>
        <input
            id="<?php echo esc_attr( $args['label_js_key_prod'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_js_key_prod'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_js_key_prod'] ) ?  esc_attr($options['paysubs_field_js_key_prod']) : '' ;?>"
            >
        <?php
    }

    function paysubs_hash_key_settings_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'paysubs_api_settings' );
        ?>
        <label for="<?php echo esc_attr( $args['label_hash_key_test'] ); ?>"><?php _e('Hash key test', 'payzen-subscribers'); ?></label>
        <input
            id="<?php echo esc_attr( $args['label_hash_key_test'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_hash_key_test'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_hash_key_test'] ) ?  esc_attr($options['paysubs_field_hash_key_test']) : '' ;?>"
            >
        <br>
        <label for="<?php echo esc_attr( $args['label_hash_key_prod'] ); ?>"><?php _e('Hash key production', 'payzen-subscribers'); ?></label>
        <input
            id="<?php echo esc_attr( $args['label_hash_key_prod'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_hash_key_prod'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_hash_key_prod'] ) ?  esc_attr($options['paysubs_field_hash_key_prod']) : '' ;?>"
            >
        <?php
    }

    function paysubs_key_settings_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'paysubs_api_settings' );
        ?>
        <label for="<?php echo esc_attr( $args['label_test_api_key'] ); ?>"><?php _e('Form test Api key', 'payzen-subscribers'); ?></label>
        <input
            id="<?php echo esc_attr( $args['label_test_api_key'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_test_api_key'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_test_api_key'] ) ?  esc_attr($options['paysubs_field_test_api_key']) : '' ;?>">
        <br>
        <label for="<?php echo esc_attr( $args['label_api_key'] ); ?>"><?php _e('Form production Api key', 'payzen-subscribers'); ?></label>
        <input
            id="<?php echo esc_attr( $args['label_api_key'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_api_key'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_api_key'] ) ?  esc_attr($options['paysubs_field_api_key']) : '' ;?>">
        <?php
    }

    function paysubs_divers_settings_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'paysubs_api_settings' );
        ?>
        <label for="<?php echo esc_attr( $args['label_redirect_sdd'] ); ?>"><?php _e('Redirect sepa', 'payzen-subscribers'); ?></label>
        <input
            id="<?php echo esc_attr( $args['label_redirect_sdd'] ); ?>"
            type="text" name="paysubs_api_settings[<?php echo esc_attr( $args['label_redirect_sdd'] ); ?>]"
            value="<?php echo isset( $options['paysubs_field_redirect_sdd'] ) ?  esc_attr($options['paysubs_field_redirect_sdd']) : '' ;?>">
        <?php
    }

    /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    function paysubs_section_api_settings_callback( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Enter the parameters of your PayZen REST API.', 'payzen-subscribers' ); ?></p>
        <?php
    }

    /**
     * Pill field callbakc function.
     *
     * WordPress has magic interaction with the following keys: label_for, class.
     * - the "label_for" key value is used for the "for" attribute of the <label>.
     * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
     * Note: you can add custom key value pairs to be used inside your callbacks.
     *
     * @param array $args
     */
    function paysubs_field_settings_cb( $args ) {
        // Get the value of the setting we've registered with register_setting()
        $options = get_option( 'paysubs_api_settings' );
        print_r($options);
        ?>
        <select
                id="<?php echo esc_attr( $args['label_for'] ); ?>"
                data-custom="<?php echo esc_attr( $args['paysubs_custom_data'] ); ?>"
                name="paysubs_api_settings[<?php echo esc_attr( $args['label_for'] ); ?>]">
            <option value="red" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'red', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'red pill', 'payzen-subscribers' ); ?>
            </option>
            <option value="blue" <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], 'blue', false ) ) : ( '' ); ?>>
                <?php esc_html_e( 'blue pill', 'payzen-subscribers' ); ?>
            </option>
        </select>
        <p class="description">
            <?php esc_html_e( 'You take the blue pill and the story ends. You wake in your bed and you believe whatever you want to believe.', 'payzen-subscribers' ); ?>
        </p>
        <p class="description">
            <?php esc_html_e( 'You take the red pill and you stay in Wonderland and I show you how deep the rabbit-hole goes.', 'payzen-subscribers' ); ?>
        </p>
        <?php
    }

    /**
     * Top level menu callback function
     */
    public function paysubs_options_page_html() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // add error/update messages

        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'paysubs_messages', 'paysubs_message', __( 'Settings Saved', 'payzen-subscribers' ), 'updated' );
        }

        // show error/update messages
        settings_errors( 'paysubs_messages' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "paysubs"
                settings_fields( 'paysubs' );
                // output setting sections and their fields
                // (sections are registered for "paysubs", each field is registered to a specific section)
                do_settings_sections( 'paysubs' );
                // output save settings button
                submit_button( __('Save','payzen-subscribers') );
                ?>
            </form>
        </div>
        <?php
    }

}
