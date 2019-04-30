<?php

/**
 * Created
 * User: alan
 * Date: 04/04/18
 * Time: 13:45
 */
namespace WidgetForEventbriteAPI\Admin;

use  WidgetForEventbriteAPI\Includes\Eventbrite_Manager ;
class Admin_Settings extends Admin_Pages
{
    protected  $settings_page ;
    protected  $settings_page_id = 'settings_page_widget-for-eventbrite-api-settings' ;
    protected  $option_group = 'widget-for-eventbrite-api' ;
    protected  $settings_title ;
    /**
     * Settings constructor.
     *
     * @param string $plugin_name
     * @param string $version plugin version.
     * @param \Freemius $freemius Freemius SDK.
     */
    public function __construct( $plugin_name, $version, $freemius )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->freemius = $freemius;
        $this->settings_title = esc_html__( 'Display Eventbrite Events Settings', 'widget-for-eventbrite-api' );
        parent::__construct();
    }
    
    public function register_settings()
    {
        /* Register our setting. */
        register_setting(
            $this->option_group,
            /* Option Group */
            'widget-for-eventbrite-api-settings',
            /* Option Name */
            array( $this, 'sanitize_settings_1' )
        );
        register_setting(
            $this->option_group,
            /* Option Group */
            'widget-for-eventbrite-api-settings-api',
            /* Option Name */
            array( $this, 'sanitize_settings_4' )
        );
        /* Add settings menu page */
        $this->settings_page = add_submenu_page(
            'widget-for-eventbrite-api',
            'Settings',
            /* Page Title */
            'Settings',
            /* Menu Title */
            'manage_options',
            /* Capability */
            'widget-for-eventbrite-api',
            /* Page Slug */
            array( $this, 'settings_page' )
        );
        register_setting(
            $this->option_group,
            /* Option Group */
            "{$this->option_group}-reset",
            /* Option Name */
            array( $this, 'reset_sanitize' )
        );
    }
    
    public function delete_options()
    {
        update_option( 'widget-for-eventbrite-api-settings', self::option_defaults( 'widget-for-eventbrite-api-settings' ) );
        update_option( 'widget-for-eventbrite-api-settings-api', self::option_defaults( 'widget-for-eventbrite-api-settings-api' ) );
    }
    
    public static function option_defaults( $option )
    {
        switch ( $option ) {
            case 'widget-for-eventbrite-api-settings':
                return array(
                    'cache_clear'    => 0,
                    'cache_duration' => 86400,
                );
            case 'widget-for-eventbrite-api-settings-api':
                return array(
                    'key' => '',
                );
            default:
                return false;
        }
    }
    
    public function add_meta_boxes()
    {
        add_meta_box(
            'info',
            /* Meta Box ID */
            __( 'Information', 'widget-for-eventbrite-api' ),
            /* Title */
            array( $this, 'meta_box_1' ),
            /* Function Callback */
            $this->settings_page_id,
            /* Screen: Our Settings Page */
            'normal',
            /* Context */
            'default'
        );
        add_meta_box(
            'api',
            /* Meta Box ID */
            __( 'Eventbrite API Key', 'widget-for-eventbrite-api' ),
            /* Title */
            array( $this, 'meta_box_4' ),
            /* Function Callback */
            $this->settings_page_id,
            /* Screen: Our Settings Page */
            'normal',
            /* Context */
            'default'
        );
        add_meta_box(
            'cache',
            /* Meta Box ID */
            __( 'Manage Cache', 'widget-for-eventbrite-api' ),
            /* Title */
            array( $this, 'meta_box_2' ),
            /* Function Callback */
            $this->settings_page_id,
            /* Screen: Our Settings Page */
            'normal',
            /* Context */
            'default'
        );
        add_meta_box(
            'shortcode',
            /* Meta Box ID */
            __( 'Shortcode Syntax', 'widget-for-eventbrite-api' ),
            /* Title */
            array( $this, 'meta_box_3' ),
            /* Function Callback */
            $this->settings_page_id,
            /* Screen: Our Settings Page */
            'normal',
            /* Context */
            'default'
        );
    }
    
    public function sanitize_settings_1( $settings )
    {
        if ( empty($settings) ) {
            return $settings;
        }
        $options = get_option( 'widget-for-eventbrite-api-settings' );
        if ( empty($options) ) {
            return $settings;
        }
        
        if ( !isset( $settings['cache_clear'] ) ) {
            $settings['cache_clear'] = 0;
            // always set checkboxes of they dont exist
        }
        
        
        if ( 1 == $settings['cache_clear'] || $options['cache_duration'] != $settings['cache_duration'] ) {
            $eventbrite_manager = new Eventbrite_Manager();
            $eventbrite_manager->flush_transients( 'eventbrite' );
            $settings['cache_clear'] = 0;
            add_settings_error(
                'wfea-cache',
                esc_attr( 'cache_cleared' ),
                __( 'The Cache has been reset', 'widget-for-eventbrite-api' ),
                'updated'
            );
        }
        
        return $settings;
    }
    
    public function sanitize_settings_4( $settings )
    {
        $keyring = get_option( 'wfea_eventbrite_api_token' );
        $options = get_option( 'widget-for-eventbrite-api-settings-api' );
        if ( isset( $settings['key'] ) ) {
            
            if ( empty($settings['key']) ) {
                
                if ( empty($keyring) ) {
                    add_settings_error(
                        'wfea-api-key',
                        'wfea-api-key',
                        __( 'An API Key is required', 'fullworks-security' ),
                        'error'
                    );
                    $settings['key'] = $options['key'];
                    return $settings;
                } else {
                    
                    if ( Admin::has_active_connection() ) {
                        add_settings_error(
                            'wfea-api-key',
                            'wfea-api-key',
                            __( 'You are using the legacy Keyring connection, Keyring is deprecated for this plugin and will be removed in future releases - please use the Personal OAuth Token ', 'fullworks-security' ),
                            'error'
                        );
                        return $settings;
                    }
                
                }
            
            } else {
                // not empty
                // flush transients
                $eventbrite_manager = new Eventbrite_Manager();
                $eventbrite_manager->flush_transients( 'eventbrite' );
                // test the new key
                $organizations = $eventbrite_manager->request(
                    'organizations',
                    array(
                    'token' => $settings['key'],
                ),
                    false,
                    true
                );
                
                if ( is_wp_error( $organizations ) ) {
                    $msg = $organizations->get_error_message();
                    
                    if ( is_array( $msg ) ) {
                        $text = json_decode( $msg['body'] );
                        $msg = $text->error_description;
                        if ( 'INVALID_AUTH' == $text->error ) {
                            $msg .= __( ' : instructions on how to find your key are <a href="https://fullworks.net/technical-documentation/widget-for-eventbrite-technical-installation/eventbrite-key" target="_blank">here</a>', 'widget-for-eventbrite-api' );
                        }
                    }
                    
                    add_settings_error(
                        'wfea-api-key-fail',
                        'wfea-api-key-fail',
                        __( 'API Key Failed: ' . $msg, 'fullworks-security' ),
                        'error'
                    );
                    $settings['key'] = $options['key'];
                    return $settings;
                }
            
            }
        
        }
        $settings['key'] = sanitize_text_field( $settings['key'] );
        return $settings;
    }
    
    public function meta_box_1()
    {
        $infomsg = '<p>' . sprintf( __( '<p>Welcome. To use this plugin add the widget to your website</p><p>For more detailed setup instructions visit <a target= "_blank" href="https://fullworks.net/technical-documentation/widget-for-eventbrite-technical-installation/" >this page.</a></p>
            <p>Support for the <strong>free</strong> version is provided <a href="https://wordpress.org/support/plugin/widget-for-eventbrite-api" target="_blank">here on WordPress.org.</a></p>
			<p>Get a FREE trial of the Pro version - <a href="%1$s">click here</a> 
			<h2>Pro Version Benefits</h2>
			<ul style="list-style-type:disc;list-style-position: inside;">
			    <li>14 day free trial</li>
				<li>Let your users see your events on full pages and post</li>
				<li>Show your events off on layouts including styles for Divi, Genesis and WP default themes</li>
				<li>Need a calendar layout? We have one of those</li>
				<li>Like a grid layout? We have one of those too</li>
				<li>Full page? of course - why not browse the <a href="https://widget-for-eventbrite-api.demo.fullworks.net/" target="_blank">demo site</a> to see basic examples</li>
				<li>Want to show off invite only events? the pro version can</li>
				<li>Do you have lots of events and would like to filter them down? The shortcode has sophisticated filters</li>
				<li>Need to see your events during development or quickly? The pro version has cache management</li>
				<li>Worried about integrating to your theme? Don\'t fear, we are ready to support you.</li>
			</ul>
			<h2>** from only $16.99 **</h2>
			</p>
			', 'widget-for-eventbrite-api' ), $this->freemius->get_trial_url() );
        echo  $infomsg ;
    }
    
    public function meta_box_2()
    {
        $infomsg = __( '<p>Cache management is only available on the pro plan or trial</p>', 'widget-for-eventbrite-api' );
        $disabled = ' disabled="disabled" ';
        echo  $infomsg ;
        $options = get_option( 'widget-for-eventbrite-api-settings' );
        if ( !isset( $options['cache_clear'] ) ) {
            $options['cache_clear'] = 0;
        }
        if ( !isset( $options['cache_duration'] ) ) {
            $options['cache_duration'] = 86400;
        }
        $units = array(
            array( 604800, __( '1 Week', 'widget-for-eventbrite-api' ) ),
            array( 172800, __( '2 Days', 'widget-for-eventbrite-api' ) ),
            array( 86400, __( '1 Day', 'widget-for-eventbrite-api' ) ),
            array( 3600, __( '1 hour', 'widget-for-eventbrite-api' ) ),
            array( 60, __( '1 Minute', 'widget-for-eventbrite-api' ) )
        );
        ?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row"><?php 
        _e( 'Cache Duration', 'widget-for-eventbrite-api' );
        ?></th>
                <td>
                    <select <?php 
        echo  $disabled ;
        ?> name="widget-for-eventbrite-api-settings[cache_duration]"
                                                     id="widget-for-eventbrite-api-settings[cache_duration]"
                                                     class="small-text">
						<?php 
        foreach ( $units as $unit ) {
            ?>
                            <option value="<?php 
            echo  $unit[0] ;
            ?>"
								<?php 
            echo  ( $options['cache_duration'] == $unit[0] ? " selected" : "" ) ;
            ?>><?php 
            echo  $unit[1] ;
            ?></option>
						<?php 
        }
        ?>
                    </select>
                    <p>
                        <span class="description"><?php 
        _e( 'Set the cache period for the Eventbrite API. Gathering data from Eventbrite naturally takes time, so to provide the best user experience we recommend using a cache duration of at least 1 day in production', 'widget-for-eventbrite-api' );
        ?></span>
                    </p>
                </td>
            </tr>
            <tr valign="top" class="alternate">
                <th scope="row"><?php 
        _e( 'Clear Cache', 'widget-for-eventbrite-api' );
        ?></th>
                <td>
                    <label for="widget-for-eventbrite-api-settings[cache_clear]"><input <?php 
        echo  $disabled ;
        ?>
                                type="checkbox"
                                name="widget-for-eventbrite-api-settings[cache_clear]"
                                id="widget-for-eventbrite-api-settings[cache_clear]"
                                value="1"
							<?php 
        checked( '1', $options['cache_clear'] );
        ?>>
						<?php 
        _e( 'Tick and save to clear', 'widget-for-eventbrite-api' );
        ?></label>
                    <p>
                        <span class="description"><?php 
        _e( 'Clear the cache now, use for testing or if you change the cache period to reset right now', 'widget-for-eventbrite-api' );
        ?></span>
                    </p>
                </td>

            </tr>


            </tbody>
        </table>
		<?php 
    }
    
    public function meta_box_3()
    {
        $infomsg = '<p>' . sprintf( __( 'The shortcode is only available in the paid for version<br><br>
			Get a FREE trial of the Pro version - <a href="%1$s">click here</a> 
			<h2>Pro Version Benefits</h2>
			<ul style="list-style-type:disc;list-style-position: inside;">
				<li>14 day free trial</li>
				<li>Let your users see your events on full pages and post</li>
				<li>Show your events off on layouts including styles for Divi, Genesis and WP default themes</li>
				<li>Need a calendar layout? We have one of those</li>
				<li>Like a grid layout? We have one of those too</li>
				<li>Full page? of course - why not browse the <a href="https://widget-for-eventbrite-api.demo.fullworks.net/" target="_blank">demo site</a> to see basic examples</li>
				<li>Want to show off invite only events? the pro version can</li>
				<li>Do you have lots of events and would like to filter them down? The shortcode has sophisticated filters</li>
				<li>Need to see your events during development or quickly? The pro version has cache management</li>
				<li>Worried about setting it all up - or integrating to your theme? Don\'t fear, we are ready to support you.</li>			
			</ul>
			<h2>** from only $16.99 **</h2>
			Available when you go pro - Shortcode [wfea]  or [wfea layout="divi" ] or [wfea layout="grid" ] etc - see  <a href="https://fullworks.net/technical-documentation/widget-for-eventbrite-technical-installation/" target="_blank"> this page</a> for many optional arguments
			', 'widget-for-eventbrite-api' ), $this->freemius->get_trial_url() );
        echo  $infomsg ;
    }
    
    public function meta_box_4()
    {
        $options = get_option( 'widget-for-eventbrite-api-settings-api' );
        ?>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row"><?php 
        _e( 'Your personal OAuth token', 'widget-for-eventbrite-api' );
        ?></th>
                <td>
                    <input type="password" name="widget-for-eventbrite-api-settings-api[key]"
                           id="widget-for-eventbrite-api-settings-api[key]"
                           class="regular-text"
                           value="<?php 
        echo  $options['key'] ;
        ?>"
                    >
                    <p>
                        <span class="description"><?php 
        _e( 'The key is required to connect to Eventbrite - instructions on how to create a key are <a href="https://fullworks.net/technical-documentation/widget-for-eventbrite-technical-installation/eventbrite-key" target="_blank">here</a>', 'widget-for-eventbrite-api' );
        ?></span>
                    </p>
                </td>
            </tr>

            </tbody>
        </table>
		<?php 
    }

}