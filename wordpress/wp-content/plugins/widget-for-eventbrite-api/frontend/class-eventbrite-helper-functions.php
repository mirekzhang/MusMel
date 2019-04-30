<?php
namespace WidgetForEventbriteAPI\FrontEnd {

	class Eventbrite_Helper_Functions {

	}
}

namespace {  //global namespace
// legacy function used in templates
// don't add to this add to class

	if ( ! function_exists( 'eventbrite_event_eb_url' ) ) :
		/**
		 * Give the URL to an event's public viewing page on eventbrite.com.
		 *
		 * @return string URL on eventbrite.com
		 */
		function eventbrite_event_eb_url($ext = null) {
			return get_post()->url . $ext;
		}
	endif;

	if ( ! function_exists( 'eventbrite_event_start' ) ) :
		/**
		 * Give access to the current event's start time: timezone, local, utc
		 *
		 * @return object Start time properties
		 */
		function eventbrite_event_start() {
			return get_post()->start;
		}
	endif;

	if ( ! function_exists( 'eventbrite_event_end' ) ) :
		/**
		 * Give access to the current event's end time: timezone, local, utc
		 *
		 * @return object End time properties
		 */
		function eventbrite_event_end() {
			return get_post()->end;
		}
	endif;

	if ( ! function_exists( 'eventbrite_event_time' ) ) :
		/**
		 * Return an event's time.
		 *
		 * @return string Event time.
		 */
		function eventbrite_event_time() {
			// Collect our formats from the admin.
			$date_format     = get_option( 'date_format' );
			$time_format     = get_option( 'time_format' );
			$combined_format = apply_filters( 'eventbrite_date_time_format', $date_format . ', ' . $time_format, $date_format, $time_format );

			// Determine if the end time needs the date included (in the case of multi-day events).
			$end_time = ( eventbrite_is_multiday_event() )
				? mysql2date( $combined_format, eventbrite_event_end()->local )
				: mysql2date( $time_format, eventbrite_event_end()->local );

			// Assemble the full event time string.
			$event_time = sprintf(
				_x( '%1$s - %2$s', 'Event date and time. %1$s = start time, %2$s = end time', 'eventbrite_api' ),
				esc_html( mysql2date( $combined_format, eventbrite_event_start()->local ) ),
				esc_html( $end_time )
			);

			return $event_time;
		}
	endif;

	if ( ! function_exists( 'eventbrite_is_multiday_event' ) ) :
		/**
		 * Determine if an event spans multiple calendar days.
		 *
		 * @return bool True if start and end date are the same, false otherwise.
		 */
		function eventbrite_is_multiday_event() {
			// Set date variables for comparison.
			$start_date = mysql2date( 'Ymd', eventbrite_event_start()->local );
			$end_date = mysql2date( 'Ymd', eventbrite_event_end()->local );

			// Return true if they're different, false otherwise.
			return ( $start_date !== $end_date );
		}
	endif;

}
