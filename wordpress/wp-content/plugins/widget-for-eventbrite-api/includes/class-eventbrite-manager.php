<?php /** @noinspection ALL */

namespace WidgetForEventbriteAPI\Includes;

use WP_Error;

class Eventbrite_Manager {
	const API_BASE = 'https://www.eventbriteapi.com/v3/';
	/**
	 * Class instance used by themes and plugins.
	 *
	 * @var object
	 */
	public static $instance;

	protected $token;

	/**
	 * The class constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		// Assign our instance.
		self::$instance = $this;

		// Add hooks.
		add_action( 'keyring_connection_deleted', array( $this, 'flush_transients' ), 10, 2 );

	}

	/**
	 * Make a call to the Eventbrite v3 REST API, or return an existing transient.
	 *
	 * @access public
	 *
	 * @param string $endpoint Valid Eventbrite v3 API endpoint.
	 * @param array $params Parameters passed to the API during a call.
	 * @param int|string|bool $id A specific event ID used for calls to the event_details endpoint.
	 * @param bool $force Force a fresh API call, ignoring any existing transient.
	 *
	 * @return object Request results
	 */
	public function request( $endpoint, $params = array(), $id = false, $force = false ) {
		// Make sure the endpoint and parameters are valid.
		if ( ! $this->validate_endpoint_params( $endpoint, $params ) ) {
			return false;
		}

		// Return a cached result if we have one.


		if ( ! $force ) {
			$cached = $this->get_cache( $endpoint, $params );
			if ( ! empty( $cached ) ) {
				return $cached;
			}
		}


		// Extend the HTTP timeout to account for Eventbrite API calls taking longer than ~5 seconds.
		add_filter( 'http_request_timeout', array( $this, 'increase_timeout' ) );

		// Make a fresh request.

		$options = get_option( 'widget-for-eventbrite-api-settings-api' );
		if ( (isset( $params['token'] ) && ! empty( $params['token'] )) || ( isset( $options['key'] ) && ! empty( $options['key'] ) ) ) {
			$this->token = $options['key'];
			$request     = $this->call( $endpoint, $params, $id );
		} else {  // use legacy Keyring calls
			if ( class_exists( '\WFEANoAutoload\Includes\Eventbrite_API' ) ) {
				$request = \WFEANoAutoload\Includes\Eventbrite_API::call( $endpoint, $params, $id );
			} else {
				$request = new WP_Error( 'noconnection', __( 'No connection available for Eventbrite', 'widget-for-eventbrite-api' ) );
			}
		}


		// Remove the timeout extension for any non-Eventbrite calls.
		remove_filter( 'http_request_timeout', array( $this, 'increase_timeout' ) );

		// If we get back a proper response, cache it.
		if ( ! is_wp_error( $request ) ) {
			$transient_name = $this->get_transient_name( $endpoint, $params );
			set_transient( $transient_name, $request, apply_filters( 'wfea_eventbrite_cache_expiry', DAY_IN_SECONDS ) );
			$this->register_transient( $transient_name );
		}

		return $request;
	}


	private function call( $endpoint, $query_params = array(), $object_id = null ) {

		$endpoint_map = array(
			'user_owned_events' => 'organizations/' . $object_id . '/events',
			'organizations'     => 'users/me/organizations',
			'description'       => 'events/' . $object_id . '/description',
		);

		$endpoint_base = trailingslashit( self::API_BASE . $endpoint_map[ $endpoint ] );
		$endpoint_url  = $endpoint_base;

		if ( 'user_owned_events' == $endpoint ) {
			// Query for 'live' events by default (rather than 'all', which includes events in the past).
			if ( ! isset( $query_params['status'] ) ) {
				$query_params['status'] = 'live';
			}
			$query_params['expand'] = apply_filters( 'eventbrite_api_expansions', 'logo,organizer,venue,ticket_classes,format,category,subcategory', $endpoint, $query_params, $object_id );
		}
		$endpoint_url = add_query_arg( $query_params, $endpoint_url );


		$response = $this->request_api( $endpoint_url, $query_params );

		if ( ! is_wp_error( $response ) ) {

			if ( isset( $response->pagination->has_more_items ) ) {

				while ( $response->pagination->has_more_items ) {
					$next_response        = $this->request_api( add_query_arg( array( 'continuation' => $response->pagination->continuation ), $endpoint_url ), $query_params );
					$response->events     = array_merge( $response->events, $next_response->events );
					$response->pagination = $next_response->pagination;
				}
			}


		}

		return apply_filters( 'eventbrite_api_call_response', $response, $endpoint, $query_params, $object_id );

	}


	private function request_api( $url, array $query_params = array() ) {


		$params = array( 'method' => 'GET' );

		if ( ! isset( $query_params['token'] ) || empty ( $query_params['token'] ) ) {
			$params['headers']['Authorization'] = 'Bearer' . ' ' . (string) $this->token;
		}




		$res = wp_remote_get( $url, $params );

		if ( in_array( wp_remote_retrieve_response_code( $res ), array( 200, 201, 202 ) ) ) {
			return json_decode( wp_remote_retrieve_body( $res ) );
		} else {
			return new WP_Error( 'eventbrite-api-request-error', $res );
		}
	}

	/**
	 * Validate the given parameters against its endpoint. Values are also validated where the API only accepts
	 * specific values.
	 *
	 * @access protected
	 *
	 * @param string $endpoint Endpoint to be called.
	 * @param array $params Parameters to be passed during the API call.
	 *
	 * @return bool True if all params were able to be validated, false otherwise.
	 */
	protected function validate_endpoint_params( $endpoint, $params ) {
		// Get valid request params.
		$valid = $this->get_endpoint_params();

		// Validate the endpoint.
		if ( ! array_key_exists( $endpoint, $valid ) ) {
			return false;
		}

		// Check that an array was passed for params.
		if ( ! is_array( $params ) ) {
			return false;
		}

		// Giving no parameters at all for queries is fine.
		if ( empty( $params ) ) {
			return true;
		}

		// The 'page' parameter is valid for any endpoint, as long as it's a positive integer.
		if ( isset( $params['page'] ) && ( 1 > (int) $params['page'] ) ) {
			return false;
		}
		unset( $params['page'] );

		// Compare each passed parameter and value against our valid ones, and fail if a match can't be found.
		foreach ( $params as $key => $value ) {
			// Check the parameter is valid for that endpoint.
			if ( ! array_key_exists( $key, $valid[ $endpoint ] ) ) {
				return false;
			}

			// If the parameter has a defined set of possible values, make sure the passed value is valid.
			if ( ! empty( $valid[ $endpoint ][ $key ] ) && ! in_array( $value, $valid[ $endpoint ][ $key ] ) ) {
				return false;
			}
		}

		// Looks good.
		return true;
	}


	/**
	 * Get user-owned private and public events.
	 *
	 * @access public
	 *
	 * @param array $params Parameters to be passed during the API call.
	 * @param bool $force Force a fresh API call, ignoring any existing transient.
	 *
	 * @return object Eventbrite_Manager
	 */
	public function get_organizations_events( $params = array(), $force = false ) {

		$organizations = $this->request( 'organizations', $params, false, $force );
		if ( is_wp_error($organizations )) {
			return $organizations;
		}
		if ( $organizations ) {
			$org_id = $organizations->organizations{0}->id;
		} else {
			$org_id = false;
		}

		// Get the raw results.
		$results = $this->request( 'user_owned_events', $params, $org_id, $force );

		// If we have events, map them to the format expected by Eventbrite_Event
		if ( ! empty( $results->events ) ) {
			$results->events = array_map( array( $this, 'map_event_keys' ), $results->events );
		}


		return $results;
	}

	public function get_descriptions( $id, $force = false ) {
		$params  = array( 'id' => $id );  // so cached  we get away with this as the description endpoint doesn't check get params
		$results = $this->request( 'description', $params, $id, $force );

		return $results;
	}


	/**
	 * Get the transient for a certain endpoint and combination of parameters.
	 * get_transient() returns false if not found.
	 *
	 * @access protected
	 *
	 * @param string $endpoint Endpoint being called.
	 * @param array $params Parameters to be passed during the API call.
	 *
	 * @return mixed Transient if found, false if not.
	 */
	protected function get_cache( $endpoint, $params ) {
		return get_transient( $this->get_transient_name( $endpoint, $params ) );
	}

	/**
	 * Determine a transient's name based on endpoint and parameters.
	 *
	 * @access protected
	 *
	 * @param string $endpoint Endpoint being called.
	 * @param array $params Parameters to be passed during the API call.
	 *
	 * @return string
	 */
	protected function get_transient_name( $endpoint, $params ) {
		// Results in 62 characters for the timeout option name (maximum is 64).
		$transient_name = 'wfea_' . md5( $endpoint . implode( $params ) );

		return apply_filters( 'wfea_transient_name', $transient_name, $endpoint, $params );
	}

	/**
	 * Return an array of valid request parameters by endpoint.
	 *
	 * @access protected
	 *
	 * @return array All valid request parameters for supported endpoints.
	 */
	protected function get_endpoint_params() {
		$params = array(

			'description'       => array(
				'id' => array()
			),
			'user_owned_events' => array(
				'status'   => array(
					'all',
					'cancelled',
					'draft',
					'ended',
					'live',
					'started',
				),
				'order_by' => array(
					'start_asc',
					'start_desc',
					'created_asc',
					'created_desc',
				),
			),
			'organizations'     => array(
				'token' => array(),
				'status'   => array(
					'all',
					'cancelled',
					'draft',
					'ended',
					'live',
					'started',
				),
				'order_by' => array(
					'start_asc',
					'start_desc',
					'created_asc',
					'created_desc',
				),
			),
		);

		return $params;
	}

	/**
	 * Convert the Eventbrite API properties into properties used by Eventbrite_Event.
	 *
	 * @access protected
	 *
	 * @param object $api_event A single event from the API results.
	 *
	 * @return object Event with Eventbrite_Event keys.
	 */
	protected function map_event_keys( $api_event ) {
		$event = array();

		$event['ID']            = ( isset( $api_event->id ) ) ? $api_event->id : '';
		$event['post_title']    = ( isset( $api_event->name->text ) ) ? $api_event->name->text : '';
		$event['post_content']  = ( isset( $api_event->description->html ) ) ? $api_event->description->html : '';
		$event['post_date']     = ( isset( $api_event->start->local ) ) ? $api_event->start->local : '';
		$event['post_date_gmt'] = ( isset( $api_event->start->utc ) ) ? $api_event->start->utc : '';
		$event['url']           = ( isset( $api_event->url ) ) ? $api_event->url : '';
		$event['logo_url']      = ( isset( $api_event->logo->url ) ) ? $api_event->logo->url : '';
		$event['logo']          = ( isset( $api_event->logo ) ) ? $api_event->logo : '';
		$event['start']         = ( isset( $api_event->start ) ) ? $api_event->start : '';
		$event['end']           = ( isset( $api_event->end ) ) ? $api_event->end : '';
		$event['organizer']     = ( isset( $api_event->organizer ) ) ? $api_event->organizer : '';
		$event['venue']         = ( isset( $api_event->venue ) ) ? $api_event->venue : '';
		$event['public']        = ( isset( $api_event->listed ) ) ? $api_event->listed : '';
		$event['tickets']       = ( isset( $api_event->ticket_classes ) ) ? $api_event->ticket_classes : '';
		$event['category']      = ( isset( $api_event->category ) ) ? $api_event->category : '';
		$event['subcategory']   = ( isset( $api_event->subcategory ) ) ? $api_event->subcategory : '';
		$event['format']        = ( isset( $api_event->format ) ) ? $api_event->format : '';

		return (object) $event;
	}

	/**
	 * Add a transient name to the list of registered transients, stored in the 'eventbrite_api_transients' option.
	 *
	 * @access protected
	 *
	 * @param string $transient_name The transient name/key used to store the transient.
	 */
	protected function register_transient( $transient_name ) {
		// Get any existing list of transients.
		$transients = get_option( 'wfea_transients', array() );

		// Add the new transient if it doesn't already exist.
		if ( ! in_array( $transient_name, $transients ) ) {
			$transients[] = $transient_name;
		}

		// Save the updated list of transients.
		update_option( 'wfea_transients', $transients );
	}

	/**
	 * Flush all transients.
	 *
	 * @access public
	 *
	 * @param string $service The Keyring service that has lost its connection.
	 * @param string $request The Keyring action that's been called ("delete", not used).
	 */
	public function flush_transients( $service, $request = null ) {
		// Bail if it wasn't an Eventbrite connection that got deleted.
		if ( 'eventbrite' != $service ) {
			return;
		}

		// Get the list of registered transients.
		$transients = get_option( 'wfea_transients', array() );

		// Bail if we have no transients.
		if ( ! $transients ) {
			return;
		}

		// Loop through all registered transients, deleting each one.
		foreach ( $transients as $transient ) {
			delete_transient( $transient );
		}

		// Reset the list of registered transients.
		delete_option( 'wefa_transients' );
	}

	/**
	 * Increase the timeout for Eventbrite API calls from the default 5 seconds to 15.
	 *
	 * @access public
	 */
	public function increase_timeout() {
		return 15;
	}
}

new Eventbrite_Manager;

