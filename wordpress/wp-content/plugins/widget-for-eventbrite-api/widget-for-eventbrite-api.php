<?php

/**
 *
 * @wordpress-plugin
 * Plugin Name:       Eventbrite Events Display in WordPress
 * Plugin URI:        https://fullworks.net/products/widget-for-eventbrite/
 * Description:       An Events Widget that displays Eventbrite events like recent posts for upcoming events.
 * Version:           2.7.10
 * Author:            Fullworks
 * Author URI:        https://fullworks.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       widget-for-eventbrite-api
 * Domain Path:       /languages
 *
 *
 *
 * Acknowledgements:
 * Lots of code and coding ideas for the widget have been from the GPL licenced Recent Posts Widget Extended by Satrya https://www.theme-junkie.com/
 *
 * This plugin used to depend on  https://wordpress.org/plugins/eventbrite-api/ by Automattic
 * However Automattic stopped supporting and maintaining it in July 2018, so I have taken onboard many GPL licenced classes and functions
 * directly within this code line, whilst some changes have  been made the code originates from Automattic
 *
 */

namespace WidgetForEventbriteAPI;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}



if (!function_exists('WidgetForEventbriteAPI\run_wfea')) {

// define some useful constants
	define( 'WIDGET_FOR_EVENTBRITE_API_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	define( 'WIDGET_FOR_EVENTBRITE_API_PLUGINS_TOP_DIR', plugin_dir_path( __DIR__ ) );
	define( 'WIDGET_FOR_EVENTBRITE_PLUGIN_VERSION', '2.7.10' );

// Include the autoloader so we can dynamically include the classes.
	require_once WIDGET_FOR_EVENTBRITE_API_PLUGIN_DIR . 'includes/autoloader.php';



	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-activator.php
	 */
	register_activation_hook(__FILE__, array('\WidgetForEventbriteAPI\Includes\Activator', 'activate'));


	add_action('setup_theme','WidgetForEventbriteAPI\run_wfea');

	function run_wfea() {
		global  $wfea_fs ;
		// run the plugin now
		$plugin = new \WidgetForEventbriteAPI\Includes\Core( $wfea_fs );
		$plugin->run();
	}



	function run_freemius() {


		/**
		 * The core plugin class that is used to define internationalization,
		 * admin-specific hooks, and public-facing site hooks.
		 */
		/**
		 *  Load freemius SDK
		 */
		$freemius    = new \WidgetForEventbriteAPI\Includes\Freemius_Config();
		$freemius = $freemius->init();
		// Signal that SDK was initiated.
		do_action( 'wfea_fs_loaded' );


		/**
		 * The code that runs during plugin uninstall.
		 * This action is documented in includes/class-uninstall.php
		 * * use freemius hook
		 *
		 * @var \Freemius $freemius freemius SDK.
		 */
		$freemius->add_action( 'after_uninstall', array( '\WidgetForEventbriteAPI\Includes\Uninstall', 'uninstall' ) );



	}
	run_freemius();
} else {
	/**  @var \Freemius $wfea_fs freemius SDK. */
	global $wfea_fs;
	$wfea_fs->set_basename( true, __FILE__ );
	return;
}
