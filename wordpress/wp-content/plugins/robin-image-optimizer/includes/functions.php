<?php

/**
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.1
 *
 * @param      $string
 * @param bool $capitalize_first_character
 *
 * @return mixed|string
 */
function wrio_dashes_to_camel_case( $string, $capitalize_first_character = false ) {

	$str = str_replace( '-', '_', ucwords( $string, '-' ) );

	if ( ! $capitalize_first_character ) {
		$str = lcfirst( $str );
	}

	return $str;
}

/**
 * Alternative php functions basename. Our function works with сyrillic file names.
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.3.0
 *
 * @param string $str   file path
 *
 * @return string|string[]|null
 */
function wrio_basename( $str ) {
	return preg_replace( '/^.+[\\\\\\/]/', '', $str );
}

/**
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.3.0
 * @return bool
 */
function wrio_is_active_nextgen_gallery() {
	return is_plugin_active( 'nextgen-gallery/nggallery.php' );
}

/**
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.1
 *
 * @param string $dir
 *
 * @return bool
 */
function wrio_rmdir( $dir ) {
	if ( is_dir( $dir ) ) {
		$scn = scandir( $dir );

		foreach ( $scn as $files ) {
			if ( $files !== '.' ) {
				if ( $files !== '..' ) {
					if ( ! is_dir( $dir . '/' . $files ) ) {
						@unlink( $dir . '/' . $files );
					} else {
						wrio_rmdir( $dir . '/' . $files );
						if ( is_dir( $dir . '/' . $files ) ) {
							@rmdir( $dir . '/' . $files );
						}
					}
				}
			}
		}
		@rmdir( $dir );

		return true;
	}

	return false;
}

/**
 * Пересчёт размера файла в байтах на человекопонятный вид
 *
 * Пример: вводим 67894 байт, получаем 67.8 KB
 * Пример: вводим 6789477 байт, получаем 6.7 MB
 *
 * @param int $size   размер файла в байтах
 *
 * @return string
 */
function wrio_convert_bytes( $size ) {
	if ( ! $size ) {
		return 0;
	}
	$base   = log( $size ) / log( 1024 );
	$suffix = [ '', 'KB', 'MB', 'GB', 'TB' ];
	$f_base = intval( floor( $base ) );

	return round( pow( 1024, $base - floor( $base ) ), 2 ) . ' ' . $suffix[ $f_base ];
}

/**
 * Генерирует хеш строку
 *
 * @param int $length
 *
 * @return string
 */
function wrio_generate_random_string( $length = 10 ) {
	$characters       = '0123456789abcdefghiklmnopqrstuvwxyz';
	$charactersLength = strlen( $characters );
	$randomString     = '';
	for ( $i = 0; $i < $length; $i ++ ) {
		$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
	}

	return $randomString;
}

/**
 * Checks whether the license is activated for the plugin or not. If the Clearfy plugin is installed
 * in priorities checks its license.
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.3.0
 * @return bool
 */
function wrio_is_license_activate() {
	return wrio_is_clearfy_license_activate() || WRIO_Plugin::app()->premium->is_activate();
}

/**
 * Checks whether the license is activated for Clearfy plugin.
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.3.0
 * @return bool
 */
function wrio_is_clearfy_license_activate() {
	if ( class_exists( 'WCL_Plugin' ) ) {
		$current_license = WCL_Licensing::instance()->getStorage()->getLicense();

		if ( ! $current_license || ! isset( $current_license->id ) ) {
			return false;
		}

		return true;
	}

	return false;
}

/**
 * Checks active (not expired!) License for plugin or not. If the Clearfy plugin is installed
 * checks its license in priorities.
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.3.0
 * @return bool
 */
function wrio_is_license_active() {
	if ( class_exists( 'WCL_Plugin' ) ) {
		return WCL_Licensing::instance()->isLicenseValid();
	}

	return WRIO_Plugin::app()->premium->is_activate() && WRIO_Plugin::app()->premium->is_active();
}

/**
 * Allows you to get a license key. If the Clearfy plugin is installed, it will be prioritized
 * return it key.
 *
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.3.0
 * @return string|null
 */
function wrio_get_license_key() {
	if ( ! wrio_is_license_activate() ) {
		return null;
	}

	if ( class_exists( 'WCL_Plugin' ) ) {
		return WCL_Licensing::instance()->getStorage()->getLicense()->secret_key;
	}

	return WRIO_Plugin::app()->premium->get_license()->get_key();
}

/**
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.3.0
 * @return number|null
 */
function wrio_get_freemius_plugin_id() {
	if ( class_exists( 'WCL_Plugin' ) ) {
		return WCL_Plugin::app()->getPluginInfoAttr( 'freemius_plugin_id' );
	}

	return WRIO_Plugin::app()->premium->get_setting( 'plugin_id' );
}

/**
 * Get size information for all currently-registered image sizes.
 *
 * @return array $sizes Data for all currently-registered image sizes.
 * @uses   get_intermediate_image_sizes()
 * @global $_wp_additional_image_sizes
 */
function wrio_get_image_sizes() {
	global $_wp_additional_image_sizes;

	$sizes = [];

	foreach ( get_intermediate_image_sizes() as $_size ) {
		if ( in_array( $_size, [ 'thumbnail', 'medium', 'medium_large', 'large' ] ) ) {
			$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
			$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );
		} else if ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
			$sizes[ $_size ] = [
				'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
				'height' => $_wp_additional_image_sizes[ $_size ]['height'],
				'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
			];
		}
	}

	return $sizes;
}

/**
 * Возвращает URL сервера оптимизации
 *
 * @param string $server_name   имя сервера
 *
 * @return string
 */
function wrio_get_server_url( $server_name ) {

	$use_http = WRIO_Plugin::app()->getPopulateOption( 'use_http' );

	$servers = [
		'server_4' => 'https://clearfy.pro/oimg.php',
		'server_2' => $api_url = ( $use_http ? 'http://' : 'https://' ) . 'smushpro.wpmudev.org/1.0/',
		'server_1' => 'http://api.resmush.it/ws.php',
		'server_3' => 'https://webcraftic.com/smush_images.php'
	];

	$servers = apply_filters( 'wbcr/rio/allow_servers', $servers );

	if ( isset( $servers[ $server_name ] ) ) {
		return $servers[ $server_name ];
	}

	return null;
}

/**
 * Check whether there are some migrations left to be processed.
 *
 * @author Alexander Teshabaev <sasha.tesh@gmail.com>
 * @since  1.3.0
 * @return bool
 * @throws Exception
 */
function wbcr_rio_has_meta_to_migrate() {

	$db_version = WRIO_Plugin::app()->getOption( 'db_version', false );

	if ( $db_version !== false && (int) $db_version === 2 ) {
		return false;
	}

	// Low number to limit resources consumption
	$attachments = wbcr_rio_get_meta_to_migrate( 5 );

	return isset( $attachments->posts ) && count( $attachments->posts ) > 0;
}

/**
 * Get list of meta to migrate.
 *
 * @author Alexander Teshabaev <sasha.tesh@gmail.com>
 * @since  1.3.0
 *
 * @param int $limit   Attachment limit per page.
 *
 * @return WP_Query
 */
function wbcr_rio_get_meta_to_migrate( $limit = 0 ) {
	$args = [
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'post_mime_type' => [ 'image/jpeg', 'image/gif', 'image/png' ],
		'posts_per_page' => - 1,
		'meta_query'     => [
			[
				'key'     => 'wio_optimized',
				'compare' => 'EXISTS',
			],
		],
	];

	if ( $limit ) {
		$args['posts_per_page'] = $limit;
	}

	return new WP_Query( $args );
}

/**
 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
 * @since  1.3.0
 * @return string
 */
function wrio_get_meta_migration_notice_text() {
	$nonce = wp_create_nonce( 'wrio-meta-migrations' );

	return sprintf( __( 'There were big changes in database schema. Please <a href="#" id="wbcr-wio-meta-migration-action" class="button button-default" data-nonce="%s">click here</a> to upgrade it to the latest version', 'robin-image-optimizer' ), $nonce );
}