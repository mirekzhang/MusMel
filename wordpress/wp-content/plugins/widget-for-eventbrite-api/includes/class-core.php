<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
namespace WidgetForEventbriteAPI\Includes;

use  WidgetForEventbriteAPI\Admin\Admin ;
use  WidgetForEventbriteAPI\FrontEnd\FrontEnd ;
use  WidgetForEventbriteAPI\Admin\Admin_Settings ;
/**
 * Class Core
 *
 * @package WidgetForEventbriteAPI\Includes
 */
class Core
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     */
    protected  $loader ;
    /**
     * The unique identifier of this plugin.
     */
    protected  $plugin_name ;
    /**
     * The current version of the plugin.
     */
    protected  $version ;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @param \Freemius $freemius Object for freemius.
     */
    public function __construct( $freemius )
    {
        $this->plugin_name = 'widget-for-eventbrite-api';
        $this->version = WIDGET_FOR_EVENTBRITE_PLUGIN_VERSION;
        $this->freemius = $freemius;
        $this->loader = new Loader();
        $this->set_locale();
        $this->settings_pages();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WidgetForEventbriteAPIi18n class in order to set the domain and to register the hook
     * with WordPress.
     */
    private function set_locale()
    {
        $plugin_i18n = new i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function settings_pages()
    {
        // options set up
        if ( !get_option( 'widget-for-eventbrite-api-settings' ) ) {
            update_option( 'widget-for-eventbrite-api-settings', Admin_Settings::option_defaults( 'widget-for-eventbrite-api-settings' ) );
        }
        if ( !get_option( 'widget-for-eventbrite-api-settings-api' ) ) {
            update_option( 'widget-for-eventbrite-api-settings-api', Admin_Settings::option_defaults( 'widget-for-eventbrite-api-settings-api' ) );
        }
        $settings = new Admin_Settings( $this->get_plugin_name(), $this->get_version(), $this->freemius );
        $this->loader->add_action( 'admin_menu', $settings, 'settings_setup' );
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    
    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
    
    /**
     *  Defining all actions that occur in the admin area.
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'display_admin_notice' );
        $this->loader->add_action( 'widgets_init', $plugin_admin, 'register_custom_widgets' );
        $this->loader->add_action(
            'keyring_load_services',
            $plugin_admin,
            'eventbrite_api_load_keyring_service',
            11
        );
        $this->loader->add_action(
            'upgrader_process_complete',
            $plugin_admin,
            'upgrader_process_complete',
            10,
            2
        );
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    private function define_public_hooks()
    {
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        $plugin_public = new FrontEnd( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'init', $plugin_public, 'register_image_size' );
        $this->loader->add_filter(
            'jetpack_photon_skip_for_url',
            $plugin_public,
            'jetpack_photon_skip_for_url',
            9,
            2
        );
        $this->loader->add_filter( 'widget-for-eventbrite-api_template_paths', $plugin_public, 'template_paths' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

}