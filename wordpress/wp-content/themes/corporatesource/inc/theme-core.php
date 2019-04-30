<?php
/**
 * Corporate Source functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Corporate_Source
 */

if ( ! function_exists( 'corporatesource_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function corporatesource_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Corporate Source, use a find and replace
		 * to change 'corporatesource' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'corporatesource', get_template_directory() . '/languages' );

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
			'primary' => esc_html__( 'Primary', 'corporatesource' ),
		) );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support( 'custom-logo', array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		) );
		
		/*
		* Enable support for Post Formats.
		* See https://developer.wordpress.org/themes/functionality/post-formats/
		*/
		add_theme_support( 'post-formats', array(
			'image',
			'video',
			'gallery',
			'audio',
			'quote'
		) );
		
		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'corporatesource_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );
		// Add theme editor style.
		add_editor_style( 'assets/editor-style.css' );
		
	}
endif;
add_action( 'after_setup_theme', 'corporatesource_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function corporatesource_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'corporatesource_content_width', 640 );
}
add_action( 'after_setup_theme', 'corporatesource_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function corporatesource_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'corporatesource' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'corporatesource' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Footer', 'corporatesource' ),
		'id'            => 'footer',
		'description'   => esc_html__( 'Add widgets here.', 'corporatesource' ),
		'before_widget' => '<aside id="%1$s" class="col-md-4 col-sm-6 col-xs-6 ftr-widget widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
	
	register_sidebar( array(
		'name'          => esc_html__( 'Front Page Slider', 'corporatesource' ),
		'id'            => 'front_page_sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'corporatesource' ),
		'before_widget' => '<div id="%1$s" class="%2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title screen-reader-text">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => esc_html__( 'Blog Page Slider', 'corporatesource' ),
		'id'            => 'blog_page_sidebar',
		'description'   => esc_html__( 'Add widgets here.', 'corporatesource' ),
		'before_widget' => '<div id="%1$s" class="%2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title screen-reader-text">',
		'after_title'   => '</h3>',
	) );
	
	
}
add_action( 'widgets_init', 'corporatesource_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function corporatesource_scripts() {
	/* PLUGIN CSS */
	wp_enqueue_style( 'corporatesource-raleway','//fonts.googleapis.com/css?family=Raleway:400,600,700' );
	wp_enqueue_style( 'corporatesource-Roboto+Condensed','//fonts.googleapis.com/css?family=Roboto+Condensed:400,700' );
	wp_enqueue_style( 'corporatesource-Poppins','//fonts.googleapis.com/css?family=Poppins:400,600,700' );

	/* PLUGIN CSS */
	wp_enqueue_style( 'bootstrap', get_theme_file_uri( '/vendor/bootstrap/css/bootstrap.css' ), '3.3.7' );
	wp_enqueue_style( 'font-awesome', get_theme_file_uri( '/vendor/font-awesome/css/font-awesome.css' ), '4.7.0' );
	wp_enqueue_style( 'bootstrap-toolkit', get_theme_file_uri( '/assets/css/bootstrap_toolkit.css' ), '7.0.0' );
	wp_enqueue_style( 'rd-navbar-css', get_theme_file_uri( '/vendor/rd-navbar/css/rd-navbar.css' ), '2.1.6' );
	wp_enqueue_style( 'owl-carousel', get_theme_file_uri( '/vendor/owlCarousel/assets/owl.carousel.css' ), '2.2.1' );
	wp_enqueue_style( 'magnific-popup', get_theme_file_uri( '/vendor/magnific-popup/magnific-popup.css' ), '1.1.0' );
	
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'corporatesource-style', get_stylesheet_uri() );
	
	
	/* PLUGIN JS */
	wp_enqueue_script( 'tether', get_theme_file_uri( '/vendor/tether.js' ), 0, '', true );
	wp_enqueue_script( 'bootstrap-js', get_theme_file_uri( '/vendor/bootstrap/js/bootstrap.js' ), array('jquery','masonry','imagesloaded'), '3.3.7', true );
	
	wp_enqueue_script( 'rd-navbar-js', get_theme_file_uri( '/vendor/rd-navbar/js/jquery.rd-navbar.js' ), 0, '', true );
	wp_enqueue_script( 'jquery-toTop', get_theme_file_uri( '/vendor/jquery.toTop.js' ), 0, '', true );
	wp_enqueue_script( 'owl-carousel', get_theme_file_uri(  '/vendor/owlCarousel/owl.carousel.js' ), 0, '', true );
	wp_enqueue_script( 'magnific-popup', get_theme_file_uri(  '/vendor/magnific-popup/jquery.magnific-popup.js' ), 0, '', true );
	
	/*THEME JS */
	wp_enqueue_script( 'corporatesource-js', get_theme_file_uri( '/assets/js/corporatesource.js' ), 0, '20151215', true );

	

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'corporatesource_scripts' );



/**
 * Set up the WordPress core custom header feature.
 *
 * @uses corporatesource_header_style()
 */
function corporatesource_custom_header_setup() {
	
	add_theme_support( 'custom-header', apply_filters( 'corporatesource_custom_header_args', array(
			'default-image' => get_template_directory_uri() . '/images/custom-header.jpg',
			'width'         => 1920,
			'height'        => 500,
			'flex-height'   => false,
			'header-text'   => false,
	) ) );
	
	
	register_default_headers( array(
		'default-image' => array(
		'url' => '%s/images/custom-header.jpg',
		'thumbnail_url' => '%s/images/custom-header.jpg',
		'description' => esc_html__( 'Default Header Image', 'corporatesource' ),
		),
	));
}
add_action( 'after_setup_theme', 'corporatesource_custom_header_setup' );

if ( ! function_exists( 'corporatesource_header_style' ) ) :
	/**
	 * Styles the header image and text displayed on the blog.
	 *
	 * @see corporatesource_custom_header_setup().
	 */
	function corporatesource_header_style() {
		$header_text_color = get_header_textcolor();

		/*
		 * If no custom options for text are set, let's bail.
		 * get_header_textcolor() options: Any hex value, 'blank' to hide text. Default: add_theme_support( 'custom-header' ).
		 */
		if ( get_theme_support( 'custom-header', 'default-text-color' ) === $header_text_color ) {
			return;
		}

		// If we get this far, we have custom styles. Let's do this.
		?>
		<style type="text/css">
		<?php
		// Has the text been hidden?
		if ( ! display_header_text() ) :
		?>
			.site-title,
			.site-description {
				position: absolute;
				clip: rect(1px, 1px, 1px, 1px);
			}
		<?php
			// If the user has set a custom color for the text use that.
			else :
		?>
			.site-title a,
			.site-description {
				color: #<?php echo esc_attr( $header_text_color ); ?>;
			}
		<?php endif; ?>
		</style>
		<?php
	}
endif;


/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function corporatesource_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	return $classes;
}
add_filter( 'body_class', 'corporatesource_body_classes' );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 */
function corporatesource_pingback_header() {
	if ( is_singular() && pings_open() ) {
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	}
}
add_action( 'wp_head', 'corporatesource_pingback_header' );