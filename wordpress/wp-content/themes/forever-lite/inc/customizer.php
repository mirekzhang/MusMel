<?php
/**
 * Forever Lite Theme Customizer
 *
 * @package Forever Lite
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function forever_lite_customize_register( $wp_customize ) {
	
function forever_lite_sanitize_checkbox( $checked ) {
	// Boolean check.
	return ( ( isset( $checked ) && true == $checked ) ? true : false );
}
	
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		
	$wp_customize->add_setting('color_scheme', array(
		'default' => '#f08080',
		'sanitize_callback'	=> 'sanitize_hex_color',
	));
	
	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'color_scheme',array(
			'label' => __('Color Scheme','forever-lite'),
			'description'	=> __('Select color from here.','forever-lite'),
			'section' => 'colors',
			'settings' => 'color_scheme'
		))
	);
	
	$wp_customize->add_setting('headerbg-color', array(
		'default' => '#000000',
		'sanitize_callback'	=> 'sanitize_hex_color',
	));
	
	$wp_customize->add_control(
		new WP_Customize_Color_Control($wp_customize,'headerbg-color',array(
			'description'	=> __('Select background color for header.','forever-lite'),
			'section' => 'colors',
			'settings' => 'headerbg-color'
		))
	);
	
	// Slider Section Start		
	$wp_customize->add_section(
        'slider_section',
        array(
            'title' => __('Slider Settings', 'forever-lite'),
            'priority' => null,
			'description'	=> __('Recommended image size (1420x567). Slider will work only when you select the static front page.','forever-lite'),	
        )
    );
	
	$wp_customize->add_setting('page-setting7',array(
			'default' => '0',
			'capability' => 'edit_theme_options',
			'sanitize_callback'	=> 'absint'
	));
	
	$wp_customize->add_control('page-setting7',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide one:','forever-lite'),
			'section'	=> 'slider_section'
	));	
	
	$wp_customize->add_setting('page-setting8',array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback'	=> 'absint'
	));
	
	$wp_customize->add_control('page-setting8',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide two:','forever-lite'),
			'section'	=> 'slider_section'
	));	
	
	$wp_customize->add_setting('page-setting9',array(
			'default' => '0',
			'capability' => 'edit_theme_options',	
			'sanitize_callback'	=> 'absint'
	));
	
	$wp_customize->add_control('page-setting9',array(
			'type'	=> 'dropdown-pages',
			'label'	=> __('Select page for slide three:','forever-lite'),
			'section'	=> 'slider_section'
	));	
	$wp_customize->add_setting('hide_slider',array(
			'default' => true,
			'sanitize_callback' => 'forever_lite_sanitize_checkbox',
			'capability' => 'edit_theme_options',
	));	 

	$wp_customize->add_control( 'hide_slider', array(
		   'settings' => 'hide_slider',
    	   'section'   => 'slider_section',
    	   'label'     => __('Check this to hide slider.','forever-lite'),
    	   'type'      => 'checkbox'
     ));	
	
	// Slider Section End
	
}
add_action( 'customize_register', 'forever_lite_customize_register' );	

function forever_lite_css(){
		?>
        <style>
				a, 
				.tm_client strong,
				.postmeta a:hover,
				#sidebar ul li a:hover,
				.blog-post h3.entry-title,
				.sitenav ul li a:hover{
					color:<?php echo esc_attr(get_theme_mod('color_scheme','#f08080')); ?>;
				}
				a.blog-more:hover,
				.nav-links .current, 
				.nav-links a:hover,
				#commentform input#submit,
				input.search-submit,
				.nivo-controlNav a.active,
				.blog-date .date,
				a.read-more,
				.section-box .sec-left a,
				.sitenav ul li ul{
					background-color:<?php echo esc_attr(get_theme_mod('color_scheme','#f08080')); ?>;
				}
				.header,
				.sitenav ul li ul{
					background-color:<?php echo esc_attr(get_theme_mod('headerbg-color','#000000')); ?>;
				}
				
		</style>
	<?php }
add_action('wp_head','forever_lite_css');

function forever_lite_customize_preview_js() {
	wp_enqueue_script( 'forever-lite-customize-preview', get_template_directory_uri() . '/js/customize-preview.js', array( 'customize-preview' ), '20141216', true );
}
add_action( 'customize_preview_init', 'forever_lite_customize_preview_js' );