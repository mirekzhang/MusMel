<?php
/**
 * Factory clearfy
 *
 * @author Alex Kovalev <alex.kovalevv@gmail.com>
 * @copyright (c) 2018, Webcraftic Ltd
 *
 * @package clearfy
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'FACTORY_CLEARFY_208_LOADED' ) ) {
	return;
}

define( 'FACTORY_CLEARFY_208_LOADED', true );

define( 'FACTORY_CLEARFY_208', '2.0.7' );

define( 'FACTORY_CLEARFY_208_DIR', dirname( __FILE__ ) );
define( 'FACTORY_CLEARFY_208_URL', plugins_url( null, __FILE__ ) );

load_plugin_textdomain( 'wbcr_factory_clearfy_208', false, dirname( plugin_basename( __FILE__ ) ) . '/langs' );

require( FACTORY_CLEARFY_208_DIR . '/includes/ajax-handlers.php' );
require( FACTORY_CLEARFY_208_DIR . '/includes/class-clearfy-helpers.php' );
require( FACTORY_CLEARFY_208_DIR . '/includes/class-clearfy-configurate.php' );

// module provides function only for the admin area
if ( is_admin() ) {
	/**
	 * Подключаем скрипты для установки компонентов Clearfy
	 * на все страницы админпанели
	 */
	add_action( 'admin_enqueue_scripts', function () {
		wp_enqueue_script( 'wbcr-factory-clearfy-208-global', FACTORY_CLEARFY_208_URL . '/assets/js/globals.js', array( 'jquery' ), FACTORY_CLEARFY_208 );
	} );
	
	// TODO: Предполагается, что загрузка модуля pages будет раньше этого модуля.
	require( FACTORY_CLEARFY_208_DIR . '/pages/class-clearfy-more-features.php' );
	require( FACTORY_CLEARFY_208_DIR . '/pages/class-clearfy-pages.php' );
	require( FACTORY_CLEARFY_208_DIR . '/pages/class-clearfy-license.php' );
}