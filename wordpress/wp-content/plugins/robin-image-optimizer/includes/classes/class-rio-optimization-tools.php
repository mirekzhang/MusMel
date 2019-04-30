<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Инструменты для оптмизации изображений
 *
 * @author        Webcraftic <wordpress.webraftic@gmail.com>
 * @copyright (c) 22.09.2018, Webcraftic
 * @version       1.0
 */
class WIO_OptimizationTools {

	/**
	 * @var WIO_Image_Processor_Abstract Объект оптимизатор
	 */
	private static $image_processor;

	/**
	 * Метод возвращает объект, отвечающий за оптимизацию изображений через API сторонних сервисов
	 *
	 * @return WIO_Image_Processor_Abstract
	 */
	public static function getImageProcessor() {
		if ( self::$image_processor ) {
			return self::$image_processor;
		}

		$server = WRIO_Plugin::app()->getPopulateOption( 'image_optimization_server', 'server_1' );

		switch ( $server ) {
			case 'server_1':
				require_once( WRIO_PLUGIN_DIR . '/includes/classes/processors/class-rio-server-resmush.php' ); // resmush api
				self::$image_processor = new WIO_Image_Processor_Resmush();
				break;
			case 'server_2':
				require_once( WRIO_PLUGIN_DIR . '/includes/classes/processors/class-rio-server-smushpro.php' ); // smushpro api
				self::$image_processor = new WIO_Image_Processor_Smushpro();
				break;
			case 'server_3':
				require_once( WRIO_PLUGIN_DIR . '/includes/classes/processors/class-rio-server-webcraftic.php' ); // webcraftic api
				self::$image_processor = new WIO_Image_Processor_Webcraftic();
				break;
			case 'server_4':
				require_once( WRIO_PLUGIN_DIR . '/includes/classes/processors/class-rio-server-clearfy1.php' ); // webcraftic api
				self::$image_processor = new WIO_Image_Processor_Clearfy1();
				break;
			default:
				require_once( WRIO_PLUGIN_DIR . '/includes/classes/processors/class-rio-server-resmush.php' ); // resmush api
				self::$image_processor = new WIO_Image_Processor_Resmush();
		}

		return self::$image_processor;
	}

	/**
	 * Проверяем таблицу в базе данных
	 *
	 * Если таблица не существует или её структура устарела, то обновляем.
	 * Проверка проводится при каждой инициализации плагина т.к. структура может измениться
	 * после очередного обновления плагина.
	 *
	 * @return bool
	 */
	public static function checkDatabase() {
		if ( ! RIO_Process_Queue::has_table_schema() ) {
			return false;
		}

		//do_action( 'wbcr/rio/multisite_current_blog' );

		$db_version = WRIO_Plugin::app()->getOption( 'db_version', 0 );

		if ( ! $db_version ) {
			global $wpdb;

			$sql = RIO_Process_Queue::get_table_schema();
			$wpdb->query( $sql );

			if ( RIO_Process_Queue::has_table_indexes() ) {
				$indexes = RIO_Process_Queue::get_table_indexes();

				foreach ( $indexes as $index ) {
					$wpdb->query( $index );
				}
			}

			WRIO_Plugin::app()->updateOption( 'db_version', 1 );
		}

		//do_action( 'wbcr/rio/multisite_restore_blog' );

		return true;
	}
}
