<?php

/**
 * Activator for the Robin image optimizer
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 09.09.2017, Webcraftic
 * @see Factory412_Activator
 * @version 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WIO_Activation extends Wbcr_Factory412_Activator {
	
	/**
	 * Runs activation actions.
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		WRIO_Plugin::app()->updatePopulateOption( 'image_optimization_server', 'server_1' );
		WRIO_Plugin::app()->updatePopulateOption( 'backup_origin_images', 1 );
		WRIO_Plugin::app()->updatePopulateOption( 'save_exif_data', 1 );
	}
	
	/**
	 * Runs activation actions.
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		if ( class_exists( 'WRIO_Cron' ) ) {
			WRIO_Cron::stop();
		}
	}
}
