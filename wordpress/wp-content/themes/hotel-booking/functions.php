<?php 
add_action('wp_enqueue_scripts', 'hotel_booking_style');
function hotel_booking_style() {
	wp_enqueue_style( 'hotel-main-style', get_template_directory_uri() . '/style.css' );
}

?>