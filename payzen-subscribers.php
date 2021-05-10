<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://open.studio
 * @since             1.0.0
 * @package           Payzen_Subscribers
 *
 * @wordpress-plugin
 * Plugin Name:       Payzen subscribers
 * Plugin URI:        https://gitlab.openstudio-lab.com/openstudio/wp-payzen-subscribers
 * Description:       Payzen subscription for donate / subscribe to service
 * Version:           1.0.0
 * Author:            Adrien BERARD
 * Author URI:        https://www.openstudio.fr/
 * License:           GPL-3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain:       payzen-subscribers
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
define( 'PAYZEN_SUBSCRIBERS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-payzen-subscribers-activator.php
 */
function activate_payzen_subscribers() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-payzen-subscribers-activator.php';
	Payzen_Subscribers_Activator::activate();
    if (!wp_next_scheduled('my_daily_event')) {
        wp_schedule_event(strtotime('06:00:00'), '1day', 'my_daily_event');
    }
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-payzen-subscribers-deactivator.php
 */
function deactivate_payzen_subscribers() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-payzen-subscribers-deactivator.php';
	Payzen_Subscribers_Deactivator::deactivate();
    wp_clear_scheduled_hook('my_daily_event');
}

register_activation_hook( __FILE__, 'activate_payzen_subscribers' );
register_deactivation_hook( __FILE__, 'deactivate_payzen_subscribers' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-payzen-subscribers.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-payzen-subscribers-widgets.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_payzen_subscribers() {

	$plugin = new Payzen_Subscribers();
	$plugin->run();

}
run_payzen_subscribers();

function run_payzen_subscribers_widgets() {
    new Payzen_Subscribers_widgets();
}
run_payzen_subscribers_widgets();

