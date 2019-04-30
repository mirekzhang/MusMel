<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 */
namespace WidgetForEventbriteAPI\Admin;

use  Keyring_SingleStore ;
class Admin
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
    }
    
    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Check if we have a valid user connection to Eventbrite.
     *
     * @return bool True if a valid user token exists, false otherwise.
     */
    public static function has_active_connection()
    {
        // Definitely no connection if Keyring isn't activated.
        if ( !class_exists( 'Keyring_SingleStore' ) ) {
            return false;
        }
        // Let's check for Eventbrite connections.
        $tokens = Keyring_SingleStore::init()->get_tokens( array(
            'service' => 'eventbrite',
            'user_id' => 0,
        ) );
        
        if ( !empty($tokens[0]) ) {
            return true;
        } else {
            return false;
        }
    
    }
    
    public function eventbrite_api_load_keyring_service()
    {
        require_once WIDGET_FOR_EVENTBRITE_API_PLUGIN_DIR . 'includes/class-eventbrite-api.php';
    }
    
    public function upgrader_process_complete( $upgrader_object, $options )
    {
        if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
            foreach ( $options['plugins'] as $each_plugin ) {
                
                if ( 'widget-for-eventbrite-api/widget-for-eventbrite-api.php' == $each_plugin ) {
                    // Bail if Keyring isn't activated.
                    if ( !class_exists( 'Keyring_SingleStore' ) ) {
                        return;
                    }
                    // Get any Eventbrite tokens we may already have.
                    $tokens = Keyring_SingleStore::init()->get_tokens( array(
                        'service' => 'eventbrite',
                    ) );
                    // If we have one, just use the first.
                    if ( !empty($tokens[0]) ) {
                        update_option( 'wfea_eventbrite_api_token', $tokens[0]->unique_id );
                    }
                }
            
            }
        }
    }
    
    /**
     * Register the widget
     */
    public function register_custom_widgets()
    {
        register_widget( 'WidgetForEventbriteAPI\\Includes\\EventBrite_API_Widget' );
    }
    
    public function display_admin_notice()
    {
        // Don't display notices to users that can't do anything about it.
        if ( !current_user_can( 'install_plugins' ) ) {
            return;
        }
        // Notices are only displayed on the dashboard, plugins, tools, and settings admin pages.
        $page = get_current_screen()->base;
        $display_on_pages = array(
            'dashboard',
            'plugins',
            'tools',
            'options-general',
            'settings_page_widget-for-eventbrite-api-settings'
        );
        if ( !in_array( $page, $display_on_pages ) ) {
            return;
        }
        $notice = '';
        $options = get_option( 'widget-for-eventbrite-api-settings-api' );
        if ( !isset( $options['key'] ) || empty($options['key']) ) {
            // no api key
            // Keyring active.
            if ( self::has_active_connection() ) {
                $notice = sprintf( __( 'Eventbrite Events Display Plugin: Keyring is deprecated and will be removed in future release - please use Personal OAuth Key in  <a href="%1$s">settings here</a> .', 'widget-for-eventbrite-api' ), esc_url( get_admin_url( null, 'options-general.php?page=widget-for-eventbrite-api-settings' ) ) );
            }
        }
        // Output notice HTML.
        if ( !empty($notice) ) {
            printf( '<div id="message" class="notice notice-error"><p>%s</p></div>', $notice );
        }
    }

}