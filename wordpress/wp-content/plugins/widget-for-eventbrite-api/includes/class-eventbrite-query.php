<?php

namespace WidgetForEventbriteAPI\Includes;

use WP_Query;


class Eventbrite_Query extends WP_Query {
	/**
	 * Results from the API call. Includes up to 50 objects plus pagination info.
	 *
	 * @var object
	 */
	public $api_results;


	/**
	 * Constructor.
	 *
	 * Sets up the Eventbrite query.
	 *
	 * @access public
	 *
	 * @param string $query URL query string.
	 */
	public function __construct( $query = '' ) {
		// Process any query args from the URL.
		$query = $this->process_query_args( $query );
		// Assign hooks.
		remove_filter( 'the_content', 'wpautop' );


		// Put our query in motion.
		$this->query( $query );


	}

	/**
	 * Handle any query args that come from the requested URL.
	 *
	 * @access protected
	 *
	 * @param  mixed $query Query string.
	 *
	 * @return array Query arguments
	 */
	protected function process_query_args( $query ) {
		// Handle requests for paged events.
		$paged = get_query_var( 'paged' );
		if ( 2 <= $paged ) {
			$query['paged'] = $paged;
		}

		// Filter by organizer ID if an "author archive" (organizer events) was requested.
		$organizer_id = get_query_var( 'organizer_id' );
		if ( empty( $query['organizer_id'] ) && ! empty( $organizer_id ) ) {
			$query['organizer_id'] = (int) $organizer_id;
		}

		// Filter by venue ID if a venue archive (all events at a certain venue) was requested.
		$venue_id = get_query_var( 'venue_id' );
		if ( empty( $query['venue_id'] ) && ! empty( $venue_id ) ) {
			$query['venue_id'] = (int) $venue_id;
		}

		// Filter by category ID if a category archive (all events in a certain category) was requested.
		$category_id = get_query_var( 'category_id' );
		if ( empty( $query['category_id'] ) && ! empty( $category_id ) ) {
			$query['category_id'] = (int) $category_id;
		}

		// Filter by subcategory ID if a subcategory archive (all events in a certain subcategory) was requested.
		$subcategory_id = get_query_var( 'subcategory_id' );
		if ( empty( $query['subcategory_id'] ) && ! empty( $subcategory_id ) ) {
			$query['subcategory_id'] = (int) $subcategory_id;
		}

		// Filter by format ID if a format archive (all events in a certain format) was requested.
		$format_id = get_query_var( 'format_id' );
		if ( empty( $query['format_id'] ) && ! empty( $format_id ) ) {
			$query['format_id'] = (int) $format_id;
		}

		return $query;
	}

	/**
	 * Retrieve the posts based on query variables.
	 *
	 * @access public
	 *
	 * @return array List of posts.
	 */
	public function get_posts() {
		// Set up query variables.
		$this->parse_query();

		// Restore `paged` if changed to `page` (in the case of index pagination).
		if ( ! empty( $this->query_vars['page'] ) ) {
			$this->query_vars['paged'] = $this->query_vars['page'];
			unset( $this->query_vars['page'] );
		}

		// Set any required parameters for the API request based on the query vars.
		$params = $this->set_api_params();


		$this->api_results = Eventbrite_Manager::$instance->get_organizations_events( $params );


		// Do any post-API query processing.
		$this->post_api_filters();

		// Set properties based on the results.
		$this->set_properties();

		// Return what we have for posts.
		return $this->posts;
	}

	/**
	 * Determine parameters for an API call.
	 *
	 * @access protected
	 *
	 * @return array API call parameters
	 */
	protected function set_api_params() {
		$params = array();

		// Add 'page' parameter if we need events above the first 50.
		if ( 5 < $this->query_vars['paged'] ) {
			/**
			 * The API returns pages of 50, and we currently only support a fixed number of 10 events per WordPress page.
			 */
			$params['page'] = ceil( $this->query_vars['paged'] / 5 );
		}


		// Adjust status for private events if necessary.
		if ( ! empty( $this->query_vars['status'] ) ) {
			$params['status'] = $this->query_vars['status'];
		}

		return $params;
	}

	/**
	 * Set properties based on the fully processed results.
	 *
	 * @access protected
	 */
	protected function set_properties() {
		if ( empty( $this->api_results->events ) ) {
			$this->posts = array();
		} else {
			// Set found_posts based on all posts returned after Eventbrite_Query filtering.
			$this->found_posts = ( isset( $this->query_vars['limit'] ) && ( $this->query_vars['limit'] < $this->api_results->pagination->object_count ) ) ? count( $this->api_results->events ) : $this->api_results->pagination->object_count;

			// Return all posts if `nopaging` is true (maximum of 50).
			if ( isset( $this->query_vars['nopaging'] ) && true === $this->query_vars['nopaging'] ) {
				$this->posts    = array_slice( $this->api_results->events, 0, $this->found_posts );
				$posts_per_page = count( $this->posts );
			} // Determine posts according to any pagination querying. Math hurts.
			else {
				$modulus        = ( 2 <= $this->query_vars['paged'] && 0 == $this->query_vars['paged'] % 5 ) ? 5 : $this->query_vars['paged'] % 5;
				$offset         = ( 2 <= $modulus && 5 >= $modulus ) ? ( $modulus - 1 ) * 10 : 0;
				$this->posts    = array_slice( $this->api_results->events, $offset, 10 );
				$posts_per_page = 10;
			}

			// Turn the posts into Eventbrite_Event objects.
			$this->posts = array_map( array( $this, 'create_eventbrite_event' ), $this->posts );

			// Determine the maximum number of pages.
			$this->post_count    = count( $this->posts );
			$this->max_num_pages = ceil( $this->found_posts / $posts_per_page );

			// Set the first post.
			$this->post = reset( $this->posts );
		}

		// Adjust some WP_Query parsing.
		if ( ! empty( $this->query_vars['p'] ) ) {
			$this->is_single = true;
		} else {
			$this->is_category = true;
			$this->is_archive  = true;
			$this->is_page     = false;
		}
		$this->is_home = false;
	}

	/**
	 * Turn a given event into a proper Eventbrite_Event object.
	 *
	 * @access protected
	 *
	 * @param  null|object $event An event object from the API results.
	 *
	 * @return object Eventbrite_Event object.
	 */
	protected function create_eventbrite_event( $event = null ) {
		// Bail if nothing is passed in.
		if ( empty( $event ) ) {
			return null;
		}

		if ( is_a( $event, 'Eventbrite_Event' ) ) {
			// We already have an Eventbrite_Event object. Nothing to do here.
			$_event = $event;
		} elseif ( is_object( $event ) ) {
			// Looks like we have an object already, so make it an Eventbrite_Event object.
			$_event = new Eventbrite_Event( $event );
		} else {
			return null;  // used to be ID
		}

		// That was a bust. We've got nothing.
		if ( ! $_event ) {
			return null;
		}

		// Return our Eventbrite_Event object.
		return $_event;
	}

	/**
	 * Process any remaining internal query parameters. These are parameters that are specific to Eventbrite_Query, not the API calls.
	 *
	 * @access protected
	 */
	protected function post_api_filters() {
		// Do nothing if API results were empty, false, or an error.
		if ( empty( $this->api_results ) || is_wp_error( $this->api_results ) ) {
			return false;
		}

		// Filter out private events: 'display_private'
		if ( ! isset( $this->query_vars['display_private'] ) ) {
			$this->query_vars['display_private'] = false;
		}
		$this->api_results->events = array_filter( $this->api_results->events, array(
			$this,
			'filter_by_display_private'
		) );


		// Filter title by reqex
		if ( isset( $this->query_vars['filter_title'] ) && ! empty( $this->query_vars['filter_title'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array( $this, 'filter_by_title' ) );
		}

		// Filter location by reqex
		if ( isset( $this->query_vars['filter_location'] ) && ! empty( $this->query_vars['filter_location'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array(
				$this,
				'filter_by_location'
			) );
		}


		// Filter out specified IDs: 'post__not_in'
		if ( isset( $this->query_vars['post__not_in'] ) && is_array( $this->query_vars['post__not_in'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array(
				$this,
				'filter_by_post_not_in'
			) );
		}

		// Filter by organizer: 'organizer_id'
		if ( isset( $this->query_vars['organizer_id'] ) && is_integer( $this->query_vars['organizer_id'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array(
				$this,
				'filter_by_organizer'
			) );
		}

		// Filter by venue: 'venue_id'
		if ( isset( $this->query_vars['venue_id'] ) && is_integer( $this->query_vars['venue_id'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array( $this, 'filter_by_venue' ) );
		}

		// Filter by category: 'category_id'
		if ( isset( $this->query_vars['category_id'] ) && is_integer( $this->query_vars['category_id'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array(
				$this,
				'filter_by_category'
			) );
		}

		// Filter by subcategory: 'subcategory_id'
		if ( isset( $this->query_vars['subcategory_id'] ) && is_integer( $this->query_vars['subcategory_id'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array(
				$this,
				'filter_by_subcategory'
			) );
		}

		// Filter by format: 'format_id'
		if ( isset( $this->query_vars['format_id'] ) && is_integer( $this->query_vars['format_id'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array( $this, 'filter_by_format' ) );
		}

		// use long description rather than summary

		if ( isset( $this->query_vars['long_description'] ) && true === $this->query_vars['long_description'] ) {
			$this->get_descriptions();
		}


		// Perform search  of title, location, description: 'search'
		if ( isset( $this->query_vars['search'] ) ) {
			$this->api_results->events = array_filter( $this->api_results->events, array( $this, 'search' ) );
		}

		// Limit the number of results: 'limit'
		if ( isset( $this->query_vars['limit'] ) && is_integer( $this->query_vars['limit'] ) ) {
			$this->api_results->events = array_slice( $this->api_results->events, 0, absint( $this->query_vars['limit'] ) );
		}
	}

	/**
	 * Determine by ID if an event is to be filtered out.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True with no ID match, false if the ID is in the array of events to be removed.
	 */
	protected function filter_by_post_not_in( $event ) {
		// Allow events not found in the array.
		return ! in_array( $event->ID, $this->query_vars['post__not_in'] );
	}

	/**
	 * Filter by title.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function filter_by_title( $event ) {

		return ( isset( $event->post_title ) ) ? preg_match( $this->query_vars['filter_title'], $event->post_title ) : false;
	}

	/**
	 * Filter by location.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function filter_by_location( $event ) {
		return ( isset( $event->venue->name ) ) ? preg_match( $this->query_vars['filter_location'], $event->venue->name ) : false;
	}


	/**
	 * Determine if an event is private.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function filter_by_display_private( $event ) {
		return ( isset( $event->public ) ) ? ( $event->public != ( $this->query_vars['display_private'] ) || ( $this->query_vars['display_private'] ) ) : false;
	}

	/**
	 * Determine if an event is managed by a certain organizer.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function filter_by_organizer( $event ) {
		return ( isset( $event->organizer->id ) ) ? $event->organizer->id == $this->query_vars['organizer_id'] : false;
	}

	/**
	 * Determine if an event is occurring at a given venue.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function filter_by_venue( $event ) {
		return ( isset( $event->venue->id ) ) ? $event->venue->id == $this->query_vars['venue_id'] : false;
	}

	/**
	 * Determine if an event is part of a given category.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function filter_by_category( $event ) {
		return ( isset( $event->category->id ) ) ? $event->category->id == $this->query_vars['category_id'] : false;
	}

	/**
	 * Determine if an event is part of a given subcategory.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function filter_by_subcategory( $event ) {
		return ( isset( $event->subcategory->id ) ) ? $event->subcategory->id == $this->query_vars['subcategory_id'] : false;
	}

	/**
	 * Determine if an event is part of a given format.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function filter_by_format( $event ) {
		return ( isset( $event->format->id ) ) ? $event->format->id == $this->query_vars['format_id'] : false;
	}


	/**
	 * @return bool|false|int
	 */
	protected function get_descriptions() {

		//$this->api_results->events;
		foreach ( $this->api_results->events as $key => $event ) {
			$results = Eventbrite_Manager::$instance->get_descriptions( $event->ID );
			$this->api_results->events[ $key ]->post_content = preg_replace('/^.+\n/', '', $results->description);
		}


	}

	/**
	 * Determine if an event matches search string.
	 *
	 * @access protected
	 *
	 * @param  object $event A single event from the API call results.
	 *
	 * @return bool True if properties match, false otherwise.
	 */
	protected function search( $event ) {
		// build regex
		$regex = '/.*' . str_replace( ' ', '.*', $this->query_vars['search'] ) . '.*/i';

		if ( empty( $event->venue ) ) {
			$venue = '';
		} else {
			$venue = $event->venue->name;
		}

		$haystack = $event->post_title . ' ' . $venue . ' ' . $event->post_content;

		return ( ! empty ( $haystack ) ) ? preg_match( $regex, $haystack ) : false;
	}


}
