<?php
/**
 * Custom template widgets for this theme
 *
 * @package sin
 */
 
function sin_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'sin' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'sin' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title"><span>',
		'after_title'   => '</span></h2>',
	) );
}
add_action( 'widgets_init', 'sin_widgets_init' );
