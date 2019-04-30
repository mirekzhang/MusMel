<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Класс для работы со статистическими данными по оптимизации изображений
 *
 * @author        Eugene Jokerov <jokerov@gmail.com>
 * @copyright (c) 2018, Webcraftic
 * @version       1.0
 */
class WRIO_Image_Statistic {

	/**
	 * The single instance of the class.
	 *
	 * @since  1.3.0
	 * @access protected
	 * @var    object
	 */
	protected static $_instance;

	/**
	 * @var array
	 * @see WRIO_Image_Statistic::load()
	 */
	protected $statistic;

	/**
	 * Инициализация статистики
	 *
	 * @throws Exception
	 */
	public function __construct() {
		$this->statistic = $this->load();
	}

	/**
	 * @author Alexander Kovalev <alex.kovalevv@gmail.com>
	 * @since  1.3.0
	 *
	 * @return object|\static object Main instance.
	 */
	public static function get_instance() {
		if ( ! isset( static::$_instance ) ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Возвращает статистические данные
	 *
	 * @return array
	 */
	public function get() {
		return $this->statistic;
	}

	/**
	 * Добавляет новые данные к текущей статистике
	 * К текущим числам добавляются новые
	 *
	 * @param string $field   Поле, к которому добавляем значение
	 * @param int    $value   добавляемое значение
	 */
	public function addToField( $field, $value ) {
		if ( isset( $this->statistic[ $field ] ) ) {
			$this->statistic[ $field ] = $this->statistic[ $field ] + $value;
		}
	}

	/**
	 * Вычитает данные из текущей статистики
	 * Из текущего числа вычитается
	 *
	 * @param string $field   Поле, из которого вычитается значение
	 * @param int    $value   вычитаемое значение
	 */
	public function deductFromField( $field, $value ) {
		$value = (int) $value;
		if ( isset( $this->statistic[ $field ] ) ) {
			$this->statistic[ $field ] = $this->statistic[ $field ] - $value;
			if ( $this->statistic[ $field ] < 0 ) {
				$this->statistic[ $field ] = 0;
			}
		}
	}

	/**
	 * Сохранение статистики
	 */
	public function save() {
		WRIO_Plugin::app()->updateOption( 'original_size', $this->statistic['original_size'] );
		WRIO_Plugin::app()->updateOption( 'optimized_size', $this->statistic['optimized_size'] );
	}

	/**
	 * Загрузка статистики и расчёт некоторых параметров
	 *
	 * @return array {
	 *              Параметры
	 *              {type} int $original Всего картинок
	 *              {type} int $optimized Всего оптимизировано картинок
	 *              {type} int $optimized_percent Суммарная оптимизация относитально всех картинок в процентах
	 *              {type} int $percent_line Суммарный КПД оптимизации в процентах. Для вывода линии в интерфейсе.
	 *              {type} int $unoptimized Не оптимизировано
	 *              {type} int $optimized_size Всего оптимизировано. В байтах
	 *              {type} int $original_size Оригинальный размер всех картинок. В байтах
	 *              {type} int $save_size_percent Суммарный КПД оптимизации в процентах
	 *              {type} int $error Кол-во ошибок
	 * }
	 */
	public function load() {
		$original_size  = WRIO_Plugin::app()->getOption( 'original_size', 0 );
		$optimized_size = WRIO_Plugin::app()->getOption( 'optimized_size', 0 );

		global $wpdb;
		$sql             = "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_status = 'inherit' AND post_mime_type IN ( 'image/jpeg', 'image/gif', 'image/png' );";
		$total_images    = $wpdb->get_var( $sql );
		$error_count     = RIO_Process_Queue::count_by_type_status( 'attachment', 'error' );
		$optimized_count = RIO_Process_Queue::count_by_type_status( 'attachment', 'success' );

		if ( ! $total_images ) {
			$total_images = 0;
		}
		if ( ! $error_count ) {
			$error_count = 0;
		}
		if ( ! $optimized_count ) {
			$optimized_count = 0;
		}
		// unoptimized count: all - optimized - error
		$unoptimized_count = $this->getUnoptimizedCount();
		if ( $unoptimized_count < 0 ) {
			$unoptimized_count = 0;
		}
		if ( $optimized_size && $original_size ) {
			$percent_diff      = round( ( $original_size - $optimized_size ) * 100 / $original_size, 1 );
			$percent_diff_line = round( $optimized_size * 100 / $original_size, 0 );
		} else {
			$percent_diff      = 0;
			$percent_diff_line = 100;
		}
		if ( $total_images ) {
			$optimized_images_percent = round( $optimized_count * 100 / $total_images );
		} else {
			$optimized_images_percent = 0;
		}

		return [
			'original'          => $total_images,
			'optimized'         => $optimized_count,
			'optimized_percent' => $optimized_images_percent,
			'percent_line'      => $percent_diff_line,
			'unoptimized'       => $unoptimized_count,
			'optimized_size'    => $optimized_size,
			'original_size'     => $original_size,
			'save_size_percent' => $percent_diff,
			'error'             => $error_count,
		];
	}

	/**
	 * Кол-во неоптимизированных изображений
	 */
	public function getUnoptimizedCount() {
		global $wpdb;
		$db_table = RIO_Process_Queue::table_name();

		$sql_unoptimized = "SELECT COUNT(DISTINCT p.ID) 
			FROM {$wpdb->posts} p
			WHERE NOT EXISTS (SELECT * 
			     FROM {$db_table} 
			     WHERE p.ID = object_id AND p.post_type = item_type) 
			AND p.post_type = 'attachment' AND p.post_mime_type IN ( 'image/jpeg', 'image/gif', 'image/png' )";

		$total_images = $wpdb->get_var( $sql_unoptimized );

		return $total_images;
	}

	/**
	 * Возвращает результат последних оптимизаций изображений
	 *
	 * @param int $limit   лимит
	 *
	 * @return array {
	 *      Параметры
	 *      {type} string $id id
	 *      {type} string $file_name Имя файла
	 *      {type} string $url URL
	 *      {type} string $thumbnail_url URL превьюшки
	 *      {type} string $original_size Размер до оптимизации
	 *      {type} string $optimized_size Размер после оптимизации
	 *      {type} string $webp_enabled webP включен
	 *      {type} string $webp_size webP размер
	 *      {type} string $original_saving На сколько процентов изменился главный файл
	 *      {type} string $thumbnails_count Сколько превьюшек оптимизировано
	 *      {type} string $total_saving Процент оптимизации главного файла и превьюшек
	 * }
	 */
	public function getLastOptimizationLogs( $limit = 100 ) {
		global $wpdb;

		$logs     = [];
		$db_table = RIO_Process_Queue::table_name();
		$sql      = $wpdb->prepare( "SELECT t1.*,t2.meta_value as attachment_meta
					FROM {$db_table} as t1
					LEFT JOIN {$wpdb->prefix}postmeta as t2 ON t1.object_id = t2.post_id
					WHERE t2.meta_key = '_wp_attachment_metadata' AND t1.item_type = 'attachment' AND t1.result_status IN (%s, %s)
					ORDER BY id DESC
					LIMIT %d ;", RIO_Process_Queue::STATUS_SUCCESS, RIO_Process_Queue::STATUS_ERROR, $limit );

		$optimized_images = $wpdb->get_results( $sql );

		if ( empty( $optimized_images ) ) {
			return $logs;
		}

		$upload_dir = wp_upload_dir();

		if ( isset( $upload_dir['error'] ) && $upload_dir['error'] !== false ) {
			return $logs;
		}

		foreach ( $optimized_images as $row ) {
			$optimization_data = new RIO_Process_Queue( $row );
			$attachment_meta   = unserialize( $row->attachment_meta );

			$image_url     = trailingslashit( $upload_dir['baseurl'] ) . $attachment_meta['file'];
			$thumbnail_url = $image_url;

			if ( isset( $attachment_meta['sizes']['thumbnail'] ) ) {
				$image_basename = wrio_basename( $image_url );
				$thumbnail_url  = str_replace( $image_basename, $attachment_meta['sizes']['thumbnail']['file'], $image_url );
			}

			$item = [
				'id'               => $row->id,
				'url'              => $image_url,
				'thumbnail_url'    => $thumbnail_url,
				'file_name'        => wrio_basename( $attachment_meta['file'] ),
				'original_size'    => size_format( $row->original_size, 2 ),
				'optimized_size'   => size_format( $row->final_size, 2 ),
				'type'             => 'success',
				'webp_size'        => null,
				'original_saving'  => 0,
				'thumbnails_count' => 0,
				'total_saving'     => 0,
			];

			$main_file = trailingslashit( $upload_dir['basedir'] ) . $attachment_meta['file'];

			/**
			 * @var RIO_Attachment_Extra_Data $extra_data
			 */
			$extra_data = $optimization_data->get_extra_data();

			if ( ! empty( $extra_data ) ) {
				$original_main_size = $extra_data->get_original_main_size();

				if ( $original_main_size && file_exists( $main_file ) ) {
					$original_saving         = ( $original_main_size - filesize( $main_file ) ) * 100 / $original_main_size;
					$item['original_saving'] = round( $original_saving ) . '%';
				}

				$webp_size = $extra_data->get_webp_main_size();

				if ( $webp_size ) {
					$item['webp_size'] = size_format( $webp_size, 2 );
				}

				$item['thumbnails_count'] = $extra_data->get_thumbnails_count();

				if ( $row->result_status == RIO_Process_Queue::STATUS_ERROR || ! empty( $error ) ) {
					$item['type']      = 'error';
					$item['error_msg'] = $extra_data->get_error_msg();
				}
			}

			if ( $row->original_size ) {
				$total_saving         = ( $row->original_size - $row->final_size ) * 100 / $row->original_size;
				$item['total_saving'] = round( $total_saving, 2 ) . '%';
			}

			$logs[] = $item;
		}

		return $logs;
	}

	/**
	 * Возвращает общий процент оптимизированных изображений
	 *
	 * @return int общий процент оптимизации
	 */
	public function getOptimizedPercent() {
		if ( isset( $this->statistic['optimized_percent'] ) ) {
			return $this->statistic['optimized_percent'];
		}

		return 0;
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
	public function convertToReadableSize( $size ) {
		return wrio_convert_bytes( $size );
	}
}
