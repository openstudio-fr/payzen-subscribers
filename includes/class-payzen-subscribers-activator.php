<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.openstudio.fr/
 * @since      1.0.0
 *
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/includes
 * @author     Adrien BERARD <aberard@openstudio.fr>
 */
class Payzen_Subscribers_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
	    $db = new Payzen_DB();
        $db->install();
        $db->update();
	}

}
