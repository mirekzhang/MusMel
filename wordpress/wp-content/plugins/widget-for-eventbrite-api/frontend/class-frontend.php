<?php

/**
 * The public-facing functionality of the plugin.
 *
 *
 */
namespace WidgetForEventbriteAPI\FrontEnd;

use  WidgetForEventbriteAPI\Includes\Template_Loader ;
use  WidgetForEventbriteAPI\Includes\Eventbrite_Query ;
class FrontEnd
{
    /**
     * The ID of this plugin.
     *
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     */
    private  $version ;
    /**
     * Initialize the class and set its properties.
     *
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        new Eventbrite_Helper_Functions();
    }
    
    public static function get_cal_locale()
    {
        $locale = str_replace( '_', '-', strtolower( get_locale() ) );
        $parts = explode( '-', $locale );
        if ( $parts[0] == $parts[1] ) {
            $locale = $parts[0];
        }
        return apply_filters( 'wfea_cal_locale', $locale );
    }
    
    public static function default_args()
    {
        $defaults = array(
            'limit'            => 5,
            'excerpt'          => 'true',
            'length'           => 50,
            'date'             => 'true',
            'readmore'         => 'true',
            'readmore_text'    => __( 'Read More &raquo;', 'widget-for-eventbrite-api' ),
            'booknow'          => 'true',
            'booknow_text'     => __( 'Register &raquo;', 'widget-for-eventbrite-api' ),
            'thumb'            => 'true',
            'thumb_default'    => 'http://placehold.it/600x400/f0f0f0/ccc',
            'cssID'            => '',
            'css_class'        => '',
            'display_private'  => 'true',
            'layout'           => '1',
            'filter_title'     => '',
            'filter_location'  => '',
            'newtab'           => 'false',
            'tickets'          => 'false',
            'organizer_id'     => '',
            'venue_id'         => '',
            'search'           => '',
            'long_description' => 'false',
            'debug'            => 'false',
        );
        // Allow plugins/themes developer to filter the default arguments.
        return apply_filters( 'eawp_shortcode_default_args', $defaults );
    }
    
    public function register_image_size()
    {
        add_image_size(
            'eaw-thumbnail',
            45,
            45,
            true
        );
    }
    
    public function template_paths( $file_paths )
    {
        $file_paths[20] = trailingslashit( dirname( WIDGET_FOR_EVENTBRITE_API_PLUGINS_TOP_DIR ) ) . trailingslashit( $this->plugin_name );
        return $file_paths;
    }
    
    public function jetpack_photon_skip_for_url( $skip, $image_url )
    {
        $banned_host_patterns = array( '/^img\\.evbuc\\.com$/' );
        $host = jetpack_photon_parse_url( $image_url, PHP_URL_HOST );
        foreach ( $banned_host_patterns as $banned_host_pattern ) {
            if ( 1 === preg_match( $banned_host_pattern, $host ) ) {
                return true;
            }
        }
        return $skip;
    }
    
    /**
     * Register the stylesheets for the frontend.
     */
    public function enqueue_styles()
    {
        /**  @var \Freemius $wfea_fs freemius SDK. */
        global  $wfea_fs ;
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/frontend.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     */
    public function enqueue_scripts()
    {
        /**  @var \Freemius $wfea_fs freemius SDK. */
        global  $wfea_fs ;
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/frontend.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }

}