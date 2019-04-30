<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PluginName
 * @subpackage PluginName/includes
 */

/**
 * Fired during plugin activation.
 *
 */

namespace WidgetForEventbriteAPI\Includes;

use Keyring_SingleStore;

class Activator {

	/**
	 *
	 * @since    1.0.0
	 */
	public static function activate() {


		// Required only for backwards compatability with Keyring
		// Bail if Keyring isn't activated.
		if ( ! class_exists( 'Keyring_SingleStore' ) ) {
			return;
		}

		// Get any Eventbrite tokens we may already have.
		$tokens = Keyring_SingleStore::init()->get_tokens( array( 'service' => 'eventbrite' ) );

		// If we have one, just use the first.
		if ( ! empty( $tokens[0] ) ) {
			update_option( 'wfea_eventbrite_api_token', $tokens[0]->unique_id );
		}


	}

}
