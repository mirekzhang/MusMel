<?php
/**
 * Front Page Template
 * 
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Travel_Booking
 */

get_header(); 

$home_sections = travel_booking_get_homepage_section();

if ( 'posts' == get_option( 'show_on_front' ) ) { //Show Static Blog Page
    include( get_home_template() );
}elseif( $home_sections ){ 
    
    //If any one section are enabled then show custom home page.
    foreach( $home_sections as $section ){
        travel_booking_get_template_part( esc_attr( $section ) );  
    }
    
}else {
    //If all section are disabled then show respective page template. 
    include( get_page_template() );
}

get_footer();