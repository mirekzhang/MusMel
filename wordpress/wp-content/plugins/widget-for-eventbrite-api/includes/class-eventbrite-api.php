<?php

namespace WFEANoAutoload\Includes; // namespace set so autoloader doesn't pick it up as it has to be an action of keywring

use WP_Error;
use Keyring_Service_Eventbrite;
use Keyring_Error;
use Keyring;
use Keyring_SingleStore;

class Eventbrite_API extends Keyring_Service_Eventbrite {

	static $instance;

	/**
	 * Constructor.
	 */
	function __construct() {


		parent::__construct();

// Remove duplicate UI elements caused by constructors.
		remove_action( 'keyring_eventbrite_manage_ui', array( $this, 'basic_ui' ) );
		remove_filter( 'keyring_eventbrite_basic_ui_intro', array( $this, 'basic_ui_intro' ) );

		self::$instance = $this;

		$token = get_option( 'wfea_eventbrite_api_token' );
		if ( ! empty( $token ) ) {
			$this->set_token( Keyring::init()->get_token_store()->get_token( array(
				'type' => 'access',
				'id'   => $token
			) ) );
		}
		$this->define_endpoints();
		add_action( 'keyring_connection_verified', array( $this, 'keyring_connection_verified' ), 10, 3 );
		add_action( 'keyring_connection_deleted', array( $this, 'keyring_connection_deleted' ) );
	}

	/**
	 * Get the user's API token.
	 *
	 * @access public
	 *
	 * @return string The user's token
	 */
	public function get_token() {
		$token = get_option( 'wfea_eventbrite_api_token' );
		if ( empty( $token ) ) {
			return false;
		}

		$this->set_token( Keyring::init()->get_token_store()->get_token( array(
			'type' => 'access',
			'id'   => $token
		) ) );

		$this->eventbrite_external_id = ( defined( 'IS_WPCOM' ) && IS_WPCOM ) ? $this->token->get_meta( 'external_id' ) : $this->token->get_meta( 'user_id' );

		return $this->token;
	}

	/**
	 * Define API endpoints.
	 *
	 * @access private
	 */
	private function define_endpoints() {
		$token = self::$instance->get_token();
		if ( empty( $token ) ) {
			return;
		}

		$this->set_endpoint( 'user_owned_events', self::API_BASE . 'users/' . $this->eventbrite_external_id . '/owned_events', 'GET' );
		$this->set_endpoint( 'event_details', self::API_BASE . 'events/', 'GET' );
		$this->set_endpoint( 'event_search', self::API_BASE . 'events/search/', 'GET' );
	}

	/**
	 * Make an API call.
	 *
	 * @access public
	 *
	 * @param  string $endpoint API endpoint supported by the plugin.
	 * @param  array $query_params Parameters to be passed with the API call.
	 * @param  integer $object_id Eventbrite event ID used when requesting a single event from the API.
	 *
	 * @return object API response if successful, error (Keyring_Error or WP_Error) otherwise
	 */
	public static function call( $endpoint, $query_params = array(), $object_id = null ) {
		if ( 'organizations' == $endpoint ) {   // keyring is legacy - but need to return on org endpoint to stop errors
			return false;
		}
		$token = self::$instance->get_token();
		if ( empty( $token ) ) // Bail if Keyring isn't activated.
		{
			if ( class_exists( 'Keyring_SingleStore' ) ) {

				// Get any Eventbrite tokens we may already have.
				$tokens = Keyring_SingleStore::init()->get_tokens( array( 'service' => 'eventbrite' ) );

				// If we have one, just use the first.
				if ( ! empty( $tokens[0] ) ) {
					update_option( 'wfea_eventbrite_api_token', $tokens[0]->unique_id );
				} else {
					return new Keyring_Error( '400', 'No token present for the Eventbrite API.' );
				}
			}
		}

		$endpoint_url = trailingslashit( self::$instance->{$endpoint . '_url'} );
		// Query for 'live' events by default (rather than 'all', which includes events in the past).
		if ( ! isset( $query_params['status'] ) ) {
			$query_params['status'] = 'live';
		}
		$query_params['expand'] = apply_filters( 'eventbrite_api_expansions', 'logo,organizer,venue,ticket_classes,format,category,subcategory', $endpoint, $query_params, $object_id );
		$method = self::$instance->{$endpoint . '_method'};
		$params = array( 'method' => $method );

		if ( ! empty( $object_id ) && is_numeric( $object_id ) ) {
			$endpoint_url = trailingslashit( $endpoint_url . absint( $object_id ) );
		}

		if ( 'GET' == $method ) {
			$endpoint_url = add_query_arg( $query_params, $endpoint_url );
		} else if ( 'POST' == $method ) {
			$params['body'] = $query_params;
		} else {
			return new WP_Error( '500', 'Method ' . $method . ' is not implemented in the Eventbrite API.' );
		}

		$response = self::$instance->request( $endpoint_url, $params );

		return apply_filters( 'eventbrite_api_call_response', $response, $endpoint, $query_params, $object_id );
	}

	/**
	 * Save the token for our Keyring connection.
	 *
	 * @param string $service The Keyring service being checked.
	 * @param int $id The current user's token.
	 * @param object $request_token Keyring_Request_Token object containing info required for the service's API call.
	 */
	function keyring_connection_verified( $service, $id, $request_token ) {
		if ( 'eventbrite' != $service || 'eventbrite' != $request_token->name ) {
			return;
		}

		update_option( 'wfea_eventbrite_api_token', $id );
	}

	/**
	 * Remove the stored token when the Keyring connection is lost.
	 *
	 * @param string $service The Keyring service connection being deleted.
	 */
	function keyring_connection_deleted( $service ) {
		if ( 'eventbrite' != $service ) {
			return;
		}

		delete_option( 'wfea_eventbrite_api_token' );
	}

}

$options = get_option( 'widget-for-eventbrite-api-settings-api' );
if ( ! isset($options['key'])  ||  empty($options['key']) ) {
	new Eventbrite_API;   //instantiate legacy
}
