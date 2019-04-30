<?php

/**
* helper Class to load freemius SDK
*/
namespace WidgetForEventbriteAPI\Includes;

class Freemius_Config
{
    public function init()
    {
        global  $wfea_fs ;
        
        if ( !isset( $wfea_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
            $wfea_fs = fs_dynamic_init( array(
                'id'             => '1330',
                'slug'           => 'widget-for-eventbrite-api',
                'type'           => 'plugin',
                'public_key'     => 'pk_97d4242a859ccad67940512ad19ab',
                'is_premium'     => false,
                'premium_suffix' => '( Pro )',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                'days'               => 7,
                'is_require_payment' => false,
            ),
                'menu'           => array(
                'slug'    => 'widget-for-eventbrite-api-settings',
                'support' => false,
                'parent'  => array(
                'slug' => 'options-general.php',
            ),
            ),
                'is_live'        => true,
            ) );
        }
        
        $wfea_fs->add_filter(
            'is_submenu_visible',
            array( $this, '_fs_show_support_menu' ),
            10,
            2
        );
        return $wfea_fs;
    }
    
    public function _fs_show_support_menu( $is_visible, $menu_id )
    {
        /** @var \Freemius $wfea_fs Freemius global object. */
        global  $wfea_fs ;
        if ( 'support' === $menu_id ) {
            return $wfea_fs->is_free_plan();
        }
        return $is_visible;
    }

}