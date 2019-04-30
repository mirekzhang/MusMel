<?php

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

// remove plugin options
global $wpdb;

if ( is_multisite() ) {
	$wpdb->query( "DELETE FROM {$wpdb->sitemeta}options WHERE option_name LIKE 'wbcr_io_%';" );

	$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	if ( ! empty( $blogs ) ) {
		foreach ( $blogs as $id ) {

			switch_to_blog( $id );

			$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'wio_%';" );
			$io_db_table = $wpdb->prefix . 'rio_process_queue';
			$wpdb->query( "DROP TABLE IF EXISTS {$io_db_table};" );
			restore_current_blog();
		}
	}
} else {
	$io_db_table = $wpdb->prefix . 'rio_process_queue';
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wbcr_io_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'wio_%';" );
	$wpdb->query( "DROP TABLE IF EXISTS {$io_db_table};" );
}

// remove backup dir
// --------------------------------------------------------------------------
require_once( dirname( __FILE__ ) . '/includes/functions.php' );
require_once( dirname( __FILE__ ) . '/includes/classes/class-rio-backup.php' );

WIO_Backup::get_instance()->removeBackupDir();

// --------------------------------------------------------------------------

// remove webp dir
// --------------------------------------------------------------------------
// Main plugin file, to have files included file deleting WebP folder
/*require_once( dirname( __FILE__ ) . '/robin-image-optimizer.php' );

if ( class_exists( 'WRIO_WebP_Api' ) ) {
	// Unlink WebP dir
	$path = WRIO_WebP_Api::get_base_dir_path();

	if ( file_exists( $path ) ) {
		@unlink( $path );
	}
}*/

// remove log file
// --------------------------------------------------------------------------

$wp_upload_dir = wp_upload_dir();

if ( isset( $wp_upload_dir['error'] ) && $wp_upload_dir['error'] !== false ) {
	return;
}

$log_dir = wp_normalize_path( trailingslashit( $wp_upload_dir['basedir'] ) . 'wrio/' );

if ( file_exists( $log_dir ) ) {
	wrio_rmdir( $log_dir );
}
// --------------------------------------------------------------------------
