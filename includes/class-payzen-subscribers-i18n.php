<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.openstudio.fr/
 * @since      1.0.0
 *
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/includes
 * @author     Adrien BERARD <aberard@openstudio.fr>
 */
class Payzen_Subscribers_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'payzen-subscribers',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
