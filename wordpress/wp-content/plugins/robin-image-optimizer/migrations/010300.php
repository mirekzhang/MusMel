<?php #comp-page builds: premium

/**
 * Updates for altering the table used to store statistics data.
 * Adds new columns and renames existing ones in order to add support for the new social buttons.
 */
class WIOUpdate010105 extends Wbcr_Factory412_Update {
	
	public function install() {
		global $wpdb;
		
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'rio_process_queue';
		$sql             = "CREATE TABLE IF NOT EXISTS {$table_name} (
			  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `server_id` varchar(60) DEFAULT NULL,
			  `object_id` bigint(20) UNSIGNED NULL,
			  `object_name` varchar(255) NULL,
			  `item_type` varchar(60) NOT NULL,
			  `item_hash` CHAR(64) NULL COMMENT 'sha256 size',
			  `item_hash_alternative` CHAR(64) NULL COMMENT 'sha256 size',
			  `result_status` varchar(60) NOT NULL,
			  `processing_level` varchar(60) NOT NULL,
			  `is_backed_up` tinyint(1) NOT NULL DEFAULT '0',
			  `original_size` int(11) UNSIGNED NOT NULL,
			  `final_size` int(11) UNSIGNED NOT NULL,
			  `original_mime_type` varchar(60) NOT NULL,
			  `final_mime_type` varchar(60) NOT NULL,
			  `extra_data` TEXT NULL DEFAULT NULL,
			  `created_at` bigint(20) NOT NULL,
			  PRIMARY KEY (`id`)
			) $charset_collate;";
		
		$sql_index_type_status       = "ALTER TABLE {$table_name} ADD INDEX `index-type-status` (`item_type`, `result_status`);";
		$sql_index_type_status_level = "ALTER TABLE {$table_name} ADD INDEX `index-type-status-level` (`item_type`, `result_status`, `processing_level`);";
		$sql_index_hash              = "ALTER TABLE {$table_name} ADD UNIQUE `index-hash` (`item_hash`);";
		$sql_index_hash_alternative  = "ALTER TABLE {$table_name} ADD INDEX `index-hash-alternative` (`item_hash_alternative`);";
		
		$wpdb->query( $sql );
		$wpdb->query( $sql_index_type_status );
		$wpdb->query( $sql_index_type_status_level );
		$wpdb->query( $sql_index_hash );
		$wpdb->query( $sql_index_hash_alternative );
		
		WRIO_Plugin::app()->updateOption( 'db_version', 1 );
		WRIO_Plugin::app()->deleteOption( 'cron_running' );

		if ( class_exists( 'WRIO_Cron' ) ) {
			WRIO_Cron::stop();
		}
		
		$this->clear_log();
	}
	
	public function clear_log() {
		$wp_upload_dir = wp_upload_dir();
		
		if ( isset( $wp_upload_dir['error'] ) && $wp_upload_dir['error'] !== false ) {
			return;
		}
		
		$file_path = wp_normalize_path( trailingslashit( $wp_upload_dir['basedir'] ) . 'wio.log' );
		
		if ( file_exists( $file_path ) ) {
			@unlink( $file_path );
		}
	}
}
