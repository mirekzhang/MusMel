<?php
/**
 * Travel Booking custom functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Travel_Booking
 */

if ( ! function_exists( 'travel_booking_setup' ) ) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function travel_booking_setup() {
    	/*
    	 * Make theme available for translation.
    	 * Translations can be filed in the /languages/ directory.
    	 * If you're building a theme based on Travel Booking, use a find and replace
    	 * to change 'travel-booking' to the name of your theme in all the template files.
    	 */
    	load_theme_textdomain( 'travel-booking', get_template_directory() . '/languages' );

    	// Add default posts and comments RSS feed links to head.
    	add_theme_support( 'automatic-feed-links' );

    	/*
    	 * Let WordPress manage the document title.
    	 * By adding theme support, we declare that this theme does not use a
    	 * hard-coded <title> tag in the document head, and expect WordPress to
    	 * provide it for us.
    	 */
    	add_theme_support( 'title-tag' );

    	/*
    	 * Enable support for Post Thumbnails on posts and pages.
    	 *
    	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
    	 */
    	add_theme_support( 'post-thumbnails' );

    	// This theme uses wp_nav_menu() in one location.
    	register_nav_menus( array(
    		'primary' => esc_html__( 'Primary', 'travel-booking' ),
    	) );

    	/*
    	 * Switch default core markup for search form, comment form, and comments
    	 * to output valid HTML5.
    	 */
    	add_theme_support( 'html5', array(
    		'gallery',
    		'caption',
    	) );
        
        //Custom Header
        add_theme_support( 'custom-header', apply_filters( 'travel_booking_custom_header_args', array(
    		'default-image' => get_template_directory_uri() . '/images/banner-img.jpg',
    		'width'         => 1920,
    		'height'        => 800,
    		'video'	   		=> true,
    		'header-text'   => false,
    	) ) );

    	register_default_headers( array(
            'default-image' => array(
                'url'           => '%s/images/banner-img.jpg',
                'thumbnail_url' => '%s/images/banner-img.jpg',
                'description'   => __( 'Default Header Image', 'travel-booking' ),
            ),
        ) );
        
    	// Set up the WordPress core custom background feature.
    	add_theme_support( 'custom-background', apply_filters( 'travel_booking_custom_background_args', array(
    		'default-color' => 'ffffff',
    		'default-image' => '',
    	) ) );

    	// Add theme support for selective refresh for widgets.
    	add_theme_support( 'customize-selective-refresh-widgets' );
        
        /** Custom Logo */
        add_theme_support( 'custom-logo', array( 
            'height'      => 50,
            'width'       => 47,
            'flex-height' => true,
            'flex-width'  => true,
        	'header-text' => array( 'site-title', 'site-description' ),
        ) );
        
        /** Image Sizes */
        add_image_size( 'travel-booking-blog-full', 1290, 737, true );
        add_image_size( 'travel-booking-blog-single', 770, 440, true );
        add_image_size( 'travel-booking-popular-package', 370, 263, true );
        add_image_size( 'travel-booking-deals-discount', 270, 385, true );
        add_image_size( 'travel-booking-destination', 270, 330, true );
        add_image_size( 'travel-booking-blog', 410, 265, true );
        add_image_size( 'travel-booking-related', 370, 235, true );
        add_image_size( 'travel-booking-schema', 600, 60 );
            
        /** Starter Content */
        $starter_content = array(
            // Specify the core-defined pages to create and add custom thumbnails to some of them.
    		'posts' => array( 'home', 'blog' ),
    		
            // Default to a static front page and assign the front and posts pages.
    		'options' => array(
    			'show_on_front' => 'page',
    			'page_on_front' => '{{home}}',
    			'page_for_posts' => '{{blog}}',
    		),
            
            // Set up nav menus for each of the two areas registered in the theme.
    		'nav_menus' => array(
    			// Assign a menu to the "top" location.
    			'primary' => array(
    				'name' => __( 'Primary', 'travel-booking' ),
    				'items' => array(
    					'page_home',
    					'page_blog'
    				)
    			)
    		),
        );
        
        $starter_content = apply_filters( 'travel_booking_starter_content', $starter_content );

    	add_theme_support( 'starter-content', $starter_content );
        
        // Add theme support for Responsive Videos.
    	add_theme_support( 'jetpack-responsive-videos' );    

    	// Add theme support for WooCommerce.
    	add_theme_support( 'woocommerce' );  
    }
endif;
add_action( 'after_setup_theme', 'travel_booking_setup' );

if( ! function_exists( 'travel_booking_content_width' ) ) :
    /**
     * Set the content width in pixels, based on the theme's design and stylesheet.
     *
     * Priority 0 to make it available to lower priority callbacks.
     *
     * @global int $content_width
     */
    function travel_booking_content_width() {
    	$GLOBALS['content_width'] = apply_filters( 'travel_booking_content_width', 910 );
    }
endif;
add_action( 'after_setup_theme', 'travel_booking_content_width', 0 );

if( ! function_exists( 'travel_booking_template_redirect_content_width' ) ) :
    /**
    * Adjust content_width value according to template.
    *
    * @return void
    */
    function travel_booking_template_redirect_content_width(){

    	// Full Width in the absence of sidebar.
    	$sidebar_layout = travel_booking_sidebar_layout();
        if( $sidebar_layout == 'no-sidebar' ) $GLOBALS['content_width'] = 1290;        
    }
endif;
add_action( 'template_redirect', 'travel_booking_template_redirect_content_width' );

if( ! function_exists( 'travel_booking_scripts' ) ) :
    /**
     * Enqueue scripts and styles.
     */
    function travel_booking_scripts() {
    	// Use minified libraries if SCRIPT_DEBUG is turned off
        $build          = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '/build' : '';
        $suffix         = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $wpte_activated = travel_booking_is_wpte_activated();
        
        if( $wpte_activated ){
            wp_enqueue_style( 'perfect-scrollbar', get_template_directory_uri(). '/css' . $build . '/perfect-scrollbar' . $suffix . '.css', array(), '1.3.0' );
            wp_enqueue_script( 'perfect-scrollbar', get_template_directory_uri() . '/js' . $build . '/perfect-scrollbar' . $suffix . '.js', array( 'jquery' ), '1.3.0', true ); 
        }

        wp_enqueue_style( 'font-awesome', get_template_directory_uri(). '/css' . $build . '/font-awesome' . $suffix . '.css', array(), '4.7' );

        $args = array(
    	  'family' => 'Lato:100,100i,300,300i,400,400i,700,700i,900,900i',
    	);
    	
        wp_enqueue_style( 'travel-booking-google-fonts', add_query_arg( $args, "//fonts.googleapis.com/css" ) );

        if( travel_booking_is_woocommerce_activated() ){
            wp_enqueue_style( 'travel-booking-woocommerce', get_template_directory_uri(). '/css' . $build . '/woocommerce-style' . $suffix . '.css' );
        }

        wp_enqueue_style( 'travel-booking-style', get_stylesheet_uri(), array(), TRAVEL_BOOKING_THEME_VERSION );

        wp_enqueue_script( 'all', get_template_directory_uri() . '/js' . $build . '/all' . $suffix . '.js', array( 'jquery' ), '5.6.3', true );

        wp_enqueue_script( 'v4-shims', get_template_directory_uri() . '/js' . $build . '/v4-shims' . $suffix . '.js', array( 'jquery' ), '5.6.3', true );

        wp_enqueue_script( 'travel-booking-custom', get_template_directory_uri() . '/js' . $build . '/custom' . $suffix . '.js', array( 'jquery' ), TRAVEL_BOOKING_THEME_VERSION, true );
        
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
    		wp_enqueue_script( 'comment-reply' );
    	}
    }
endif;
add_action( 'wp_enqueue_scripts', 'travel_booking_scripts' );

if( ! function_exists( 'travel_booking_body_classes' ) ) :
    /**
     * Adds custom classes to the array of body classes.
     *
     * @param array $classes Classes for the body element.
     * @return array
     */
    function travel_booking_body_classes( $classes ) {

    	$enable_banner = get_theme_mod( 'ed_banner_section', true );
    	$banner_html   = get_custom_header_markup();

        // Adds class if banner is active in frontpage
        if( is_front_page() && ! is_home() && $enable_banner && ! empty( $banner_html ) ){
            $classes[] = 'homepage hasbanner';
        }

        // Adds a class of hfeed to non-singular pages.
        if ( ! is_singular() ) {
            $classes[] = 'hfeed';
        }
        
        // Adds a class of custom-background-image to sites with a custom background image.
        if ( get_background_image() ) {
            $classes[] = 'custom-background-image custom-background';
        }
        
        // Adds a class of custom-background-color to sites with a custom background color.
        if ( get_background_color() != 'ffffff' ) {
            $classes[] = 'custom-background-color custom-background';
        }
        
        // Add class in 404 page
        if( is_404() ){
            $classes[] = 'error404';
        }

        $sidebar_layout = travel_booking_sidebar_layout();
        $classes[] = $sidebar_layout;
        
    	return $classes;
    }
endif;
add_filter( 'body_class', 'travel_booking_body_classes' );

if( ! function_exists( 'travel_booking_post_classes' ) ) :
    /**
     * Adds custom class in post class1
     */
    function travel_booking_post_classes( $classes ){
        if( is_search() ){
            $classes[] = 'post';
        }
        
        return $classes;    
    }
endif;
add_filter( 'post_class', 'travel_booking_post_classes' );

if( ! function_exists( 'travel_booking_pingback_header' ) ) :
    /**
     * Add a pingback url auto-discovery header for singularly identifiable articles.
     */
    function travel_booking_pingback_header() {
    	if ( is_singular() && pings_open() ) {
    		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
    	}
    }
endif;
add_action( 'wp_head', 'travel_booking_pingback_header' );

if ( ! function_exists( 'travel_booking_excerpt_more' ) ) :
    /**
     * Replaces "[...]" (appended to automatically generated excerpts) with ... * 
     */
    function travel_booking_excerpt_more( $more ) {
    	return is_admin() ? $more : ' &hellip; ';
    }
endif;
add_filter( 'excerpt_more', 'travel_booking_excerpt_more' );

if ( ! function_exists( 'travel_booking_excerpt_length' ) ) :
    /**
     * Changes the default 55 character in excerpt 
     */
    function travel_booking_excerpt_length( $length ) {
    	$excerpt_length = get_theme_mod( 'excerpt_length', 30 );

    	return is_admin() ? $length : $excerpt_length;    
    }
endif;
add_filter( 'excerpt_length', 'travel_booking_excerpt_length', 999 );

if( ! function_exists( 'travel_booking_modify_search_form,' ) ) :
    /**
     *  Filters the HTML format of the search form.
     */
    function travel_booking_modify_search_form( $search_form ){
        $search_form = '<form role="search" method="get" class="search-form" action="'. esc_url( home_url( '/' ) ) .'">
            <span class="screen-reader-text">'. _x( 'Search for:', 'label', 'travel-booking' ) .'</span>
            <label>
                <input type="search" placeholder="'. esc_attr_x( 'Search Here&hellip;', 'placeholder', 'travel-booking' ) .'" value="'. get_search_query() .'" name="s" title="'. esc_attr_x( 'Search for:', 'label', 'travel-booking' ) .'" />
            </label>
            <input type="submit" value="'. esc_attr_x( 'Search', 'label', 'travel-booking' ) .'">
        </form>';

        return $search_form;
    }
    endif;
add_filter( 'get_search_form', 'travel_booking_modify_search_form' );

if( ! function_exists( 'travel_booking_change_comment_form_default_fields' ) ) :
    /**
     * Change Comment form default fields i.e. author, email & url.
     * https://blog.josemcastaneda.com/2016/08/08/copy-paste-hurting-theme/
     */
    function travel_booking_change_comment_form_default_fields( $fields ){
        
        // get the current commenter if available
        $commenter = wp_get_current_commenter();
     
        // core functionality
        $req = get_option( 'require_name_email' );
        $aria_req = ( $req ? " aria-required='true'" : '' );    
     
        // Change just the author field
        $fields['author'] = '<p class="comment-form-author"><label for="author">' . esc_html__( 'Name', 'travel-booking' ) . '<span class="required">*</span></label><input id="author" name="author" placeholder="' . esc_attr__( 'Name*', 'travel-booking' ) . '" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>';
        
        $fields['email'] = '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'travel-booking' ) . '<span class="required">*</span></label><input id="email" name="email" placeholder="' . esc_attr__( 'Email*', 'travel-booking' ) . '" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>';
        
        $fields['url'] = '<p class="comment-form-url"><label for="url">' . esc_html__( 'Website', 'travel-booking' ) . '</label><input id="url" name="url" placeholder="' . esc_attr__( 'Website', 'travel-booking' ) . '" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></p>'; 
        
        return $fields;
        
    }
endif;
add_filter( 'comment_form_default_fields', 'travel_booking_change_comment_form_default_fields' );

if( ! function_exists( 'travel_booking_change_comment_form_defaults' ) ) :
    /**
     * Change Comment Form defaults
     * https://blog.josemcastaneda.com/2016/08/08/copy-paste-hurting-theme/
     */
    function travel_booking_change_comment_form_defaults( $defaults ){
        
        $defaults['comment_field'] = '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Comment', 'travel-booking' ) . '</label><textarea id="comment" name="comment" placeholder="' . esc_attr__( 'Comment', 'travel-booking' ) . '" cols="45" rows="8" aria-required="true"></textarea></p>';
        
        return $defaults;
        
    }
endif;
add_filter( 'comment_form_defaults', 'travel_booking_change_comment_form_defaults' );

if ( ! function_exists( 'travel_booking_video_controls' ) ) :
    /**
     * Customize video play/pause button in the custom header.
     *
     * @param array $settings Video settings.
     */
    function travel_booking_video_controls( $settings ) {
        $settings['l10n']['play'] = '<span class="screen-reader-text">' . esc_html__( 'Play background video', 'travel-booking' ) . '</span>' . travel_booking_get_svg( array( 'icon' => 'play' ) );
        $settings['l10n']['pause'] = '<span class="screen-reader-text">' . esc_html__( 'Pause background video', 'travel-booking' ) . '</span>' . travel_booking_get_svg( array( 'icon' => 'pause' ) );
        return $settings;
    }
endif;
add_filter( 'header_video_settings', 'travel_booking_video_controls' );

if( ! function_exists( 'travel_booking_include_svg_icons' ) ) :
    /**
     * Add SVG definitions to the footer.
     */
    function travel_booking_include_svg_icons() {
        // Define SVG sprite file.
        $svg_icons = get_parent_theme_file_path( '/images/svg-icons.svg' );

        // If it exists, include it.
        if ( file_exists( $svg_icons ) ) {
            require_once( $svg_icons );
        }
    }
endif;
add_action( 'wp_footer', 'travel_booking_include_svg_icons', 9999 );

if( ! function_exists( 'travel_booking_single_post_schema' ) ) :
    /**
     * Single Post Schema
     *
     * @return string
     */
    function travel_booking_single_post_schema() {
        if ( is_singular( 'post' ) ) {
            global $post;
            $custom_logo_id = get_theme_mod( 'custom_logo' );

            $site_logo   = wp_get_attachment_image_src( $custom_logo_id , 'travel-booking-schema' );
            $images      = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
            $excerpt     = travel_booking_escape_text_tags( $post->post_excerpt );
            $content     = $excerpt === "" ? mb_substr( travel_booking_escape_text_tags( $post->post_content ), 0, 110 ) : $excerpt;
            $schema_type = ! empty( $custom_logo_id ) && has_post_thumbnail( $post->ID ) ? "BlogPosting" : "Blog";

            $args = array(
                "@context"  => "http://schema.org",
                "@type"     => $schema_type,
                "mainEntityOfPage" => array(
                    "@type" => "WebPage",
                    "@id"   => esc_url( get_permalink( $post->ID ) )
                ),
                "headline"  => esc_html( get_the_title( $post->ID ) ),
                "image"     => array(
                    "@type"  => "ImageObject",
                    "url"    => $images[0],
                    "width"  => $images[1],
                    "height" => $images[2]
                ),
                "datePublished" => esc_html( get_the_time( DATE_ISO8601, $post->ID ) ),
                "dateModified"  => esc_html( get_post_modified_time(  DATE_ISO8601, __return_false(), $post->ID ) ),
                "author"        => array(
                    "@type"     => "Person",
                    "name"      => travel_booking_escape_text_tags( get_the_author_meta( 'display_name', $post->post_author ) )
                ),
                "publisher" => array(
                    "@type"       => "Organization",
                    "name"        => esc_html( get_bloginfo( 'name' ) ),
                    "description" => esc_html( get_bloginfo( 'description' ) ),
                    "logo"        => array(
                        "@type"   => "ImageObject",
                        "url"     => $site_logo[0],
                        "width"   => $site_logo[1],
                        "height"  => $site_logo[2]
                    )
                ),
                "description" => ( class_exists('WPSEO_Meta') ? WPSEO_Meta::get_value( 'metadesc' ) : $content )
            );

            if ( has_post_thumbnail( $post->ID ) ) :
                $args['image'] = array(
                    "@type"  => "ImageObject",
                    "url"    => $images[0],
                    "width"  => $images[1],
                    "height" => $images[2]
                );
            endif;

            if ( ! empty( $custom_logo_id ) ) :
                $args['publisher'] = array(
                    "@type"       => "Organization",
                    "name"        => esc_html( get_bloginfo( 'name' ) ),
                    "description" => esc_html( get_bloginfo( 'description' ) ),
                    "logo"        => array(
                        "@type"   => "ImageObject",
                        "url"     => $site_logo[0],
                        "width"   => $site_logo[1],
                        "height"  => $site_logo[2]
                    )
                );
            endif;

            echo '<script type="application/ld+json">';

            if ( version_compare( PHP_VERSION, '5.4.0' , '>=' ) ) {
                echo wp_json_encode( $args, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
            } else {
                echo wp_json_encode( $args );
            }

            echo '</script>';
        }
    }
endif;
add_action( 'wp_head', 'travel_booking_single_post_schema' );