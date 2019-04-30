<?php

/**
 * Class WRIO_WebP_Api processing images from processing queue, sends them to API and saves locally.
 *
 * @author Alexander Teshabaev <sasha.tesh@gmail.com>
 */
class WRIO_WebP_Api {
	
	/**
	 * @var string API url.
	 */
	private $_api_url = 'http://142.93.91.206/';
	
	/**
	 * @var int|null Attachment ID.
	 */
	private $_models = null;
	
	/**
	 * @var null|int UNIX epoch when last request was processed.
	 */
	private $_last_request_tick = null;
	
	
	/**
	 * WRIO_WebP_Api constructor.
	 *
	 * @param RIO_Process_Queue[] $model Item to be converted to WebP.
	 */
	public function __construct( $model ) {
		$this->_models = $model;
	}
	
	/**
	 * Process image queue based on provided attachment ID.
	 *
	 * When attachment has multiple thumbnails, all of them would be converted one after another.
	 *
	 * Notice: when there are no items queried for provided data, false would be returned.
	 *
	 * @return bool true on success execution, false on failure or missing item in queue.
	 */
	public function process_image_queue() {
		
		foreach ( $this->_models as $model ) {
			/**
			 * @var RIOP_WebP_Extra_Data $extra_data
			 */
			$extra_data = $model->get_extra_data();
			
			if ( $extra_data === null ) {
				continue;
			}
			
			$response = $this->request( $model );
			
			if ( $this->can_save( $response ) && $this->save_file( $response, $model ) ) {
				$this->update( $model );
			}
		}
		
		return true;
	}
	
	/**
	 * Request API
	 *
	 * @param RIO_Process_Queue $model Queue model.
	 *
	 * @return array|bool|WP_Error
	 */
	public function request( $model ) {
		
		if ( $this->_last_request_tick === null ) {
			$this->_last_request_tick = time();
		} else {
			if ( is_int( $this->_last_request_tick ) && ( time() - $this->_last_request_tick ) < 1 ) {
				// Need to have some rest before calling REST :D to comply with API request limit
				sleep( 2 );
			}
			
			$this->_last_request_tick = time();
		}
		
		if ( ! wrio_is_license_activate() ) {
			WIO_Logger::error( "Unable to get license to make proper request to the API" );
			
			return false;
		}
		
		$transient_string = md5( WRIO_Plugin::app()->getPrefix() . '_processing_image' . $model->get_item_hash() );
		
		$transient_value = get_transient( $transient_string );
		
		if ( is_numeric( $transient_value ) && (int) $transient_value === 1 ) {
			WIO_Logger::info( sprintf( 'Skipping to wp_remote_get() as transient "%s" already exist. Usually it means that no request was returned yet', $transient_string ) );
			
			return false;
		}
		
		set_transient( $transient_string, 1 );
		
		$url = $this->_api_url . 'v1/image/convert?';
		
		$url .= http_build_query( [ 'format' => 'webp' ] );
		
		/**
		 * @var RIOP_WebP_Extra_Data $extra_data
		 */
		$extra_data = $model->get_extra_data();
		
		$multipartBoundary = '--------------------------' . microtime( true );
		
		$file_contents = file_get_contents( $extra_data->get_source_path() );
		
		$body = "--" . $multipartBoundary . "\r\n" . "Content-Disposition: form-data; name=\"file\"; filename=\"" . basename( $extra_data->get_source_path() ) . "\"\r\n" . "Content-Type: " . $model->get_original_mime_type() . "\r\n\r\n" . $file_contents . "\r\n";
		
		$body .= "--" . $multipartBoundary . "--\r\n";
		
		$headers = array(
			// should be base64 encoded, otherwise API would fail authentication
			'Authorization' => 'Bearer ' . base64_encode( wrio_get_license_key() ),
			'PluginId'      => wrio_get_freemius_plugin_id(),
			'Content-Type'  => 'multipart/form-data; boundary=' . $multipartBoundary,
		);
		
		$response = wp_remote_post( $url, [
			'timeout' => 60,
			'headers' => $headers,
			'body'    => $body,
		] );
		
		delete_transient( $transient_string );
		
		return $response;
	}
	
	/**
	 * Process response from API.
	 *
	 * @param array|WP_Error|false $response
	 *
	 * @return bool True means response image was successfully saved, false on failure.
	 */
	public function can_save( $response ) {
		if ( is_wp_error( $response ) ) {
			WIO_Logger::error( sprintf( 'Error response from API. Code: %s, error: %s', $response->get_error_code(), $response->get_error_message() ) );
			
			return false;
		}
		
		if ( false === $response ) {
			WIO_Logger::error( 'Unknown response returned from API or it was not requested, failing to process response' );
			
			return false;
		}
		
		$content_disposition = wp_remote_retrieve_header( $response, 'content-disposition' );
		
		if ( 0 === strpos( $content_disposition, 'attachment;' ) ) {
			
			$body = wp_remote_retrieve_body( $response );
			
			if ( empty( $body ) ) {
				WIO_Logger::error( 'Response returned content-disposition header as "attachment;", but empty body returned, failing to proceed' );
				
				return false;
			}
			
			return true;
		}
		
		$response_text = wp_remote_retrieve_body( $response );
		
		if ( ! empty( $response_text ) ) {
			$response_json = json_decode( $response_text );
			
			if ( ! empty( $response_json ) && ! empty( $response_json->error ) ) {
				WIO_Logger::error( sprintf( 'Unable to convert attachment as API returned error: "%s"', $response_json->error ) );
			}
		}
		
		return false;
	}
	
	/**
	 * Save file from response.
	 *
	 * It is assumed that it was checked by can_save() method.
	 *
	 * @param array|WP_Error|false $response
	 * @param RIO_Process_Queue $queue_model
	 *
	 * @return bool
	 * @see can_save() for further information.
	 *
	 */
	public function save_file( $response, $queue_model ) {
		try {
			$save_path = static::get_absolute_save_path( $queue_model );
		} catch( \Exception $exception ) {
			WIO_Logger::error( sprintf( 'Unable to process response failed to get save path: "%s"', $exception->getMessage() ) );
			
			return false;
		}
		
		$body = wp_remote_retrieve_body( $response );
		
		$file_saved = @file_put_contents( $save_path, $body );
		
		if ( ! $file_saved ) {
			/**
			 * @var $http_response WP_HTTP_Requests_Response
			 */
			$http_response = $response['http_response'];
			WIO_Logger::error( sprintf( 'Failed to save file "%s" under %s with file_put_contents()', $save_path, $http_response->get_response_object()->url ) );
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Update processing item data to finish its cycle.
	 *
	 * @param RIO_Process_Queue $queue_model Queue model to be update.
	 *
	 * @return bool
	 */
	public function update( $queue_model ) {
		
		try {
			$save_path = static::get_absolute_save_path( $queue_model );
		} catch( \Exception $exception ) {
			WIO_Logger::error( sprintf( 'Unable to update queue model #%s as of exception: %s', $queue_model->get_id(), $exception->getMessage() ) );
			
			return false;
		}
		
		$queue_model->result_status = RIO_Process_Queue::STATUS_SUCCESS;
		$queue_model->final_size    = filesize( $save_path );
		
		/**
		 * @var RIOP_WebP_Extra_Data $updated_extra_data
		 */
		$updated_extra_data = $queue_model->get_extra_data();
		$updated_extra_data->set_converted_src( $this->get_save_url( $queue_model ) );
		$updated_extra_data->set_converted_path( $save_path );
		
		$queue_model->extra_data = $updated_extra_data;
		
		/**
		 * Хук срабатывает после успешной конвертации в WebP
		 *
		 * @param RIO_Process_Queue $queue_model
		 *
		 * @since 1.2.0
		 */
		do_action( 'wbcr/rio/webp_success', $queue_model );
		
		return $queue_model->save();
	}
	
	/**
	 * Get temporary file name.
	 *
	 * @return string
	 */
	public function get_temp_path() {
		return wp_normalize_path( trailingslashit( static::get_base_dir_path() ) . uniqid() );
	}
	
	/**
	 * Get base dir path.
	 *
	 * @return bool|string String on success and false on failure e.g. when wp-content is not writable.
	 */
	public static function get_base_dir_path() {
		
		$upload_dirs = wp_upload_dir();
		
		if ( isset( $upload_dirs['error'] ) && $upload_dirs['error'] !== false ) {
			return false;
		}
		
		$content_path = $upload_dirs['basedir'];
		
		return wp_normalize_path( trailingslashit( $content_path ) . static::get_custom_folder_name() );
	}
	
	/**
	 * Get custom folder name where are WebP images will be stored.
	 *
	 * @return string
	 */
	public static function get_custom_folder_name() {
		return 'wrio-webp-uploads';
	}
	
	/**
	 * Get complete save url.
	 *
	 * @param RIO_Process_Queue $queue_model Instance of queue item.
	 *
	 * @return string
	 */
	public function get_save_url( $queue_model ) {
		$upload_dirs = wp_upload_dir();
		
		if ( isset( $upload_dirs['error'] ) && $upload_dirs['error'] !== false ) {
			return null;
		}
		
		$content_url = $upload_dirs['baseurl'];
		
		return sprintf( '%s/%s/%s/%s/%s', $content_url, static::get_custom_folder_name(), date( 'Y' ), date( 'm' ), static::get_file_name( $queue_model ) );
	}
	
	/**
	 * Get absolute save path.
	 *
	 * @return bool
	 * @throws Exception on failure to create missing directory
	 */
	public static function get_save_path() {
		
		$base_dir = static::get_base_dir_path();
		
		if ( false === $base_dir ) {
			return false;
		}
		
		$path = sprintf( '%s/%s/%s/', $base_dir, date( 'Y' ), date( 'm' ) );
		
		// Create DIR when does not exist
		if ( ! file_exists( $path ) ) {
			$dir_created = @mkdir( $path, 0755, true );
			
			if ( ! $dir_created ) {
				$message = sprintf( 'Failed to create directory %s with mode %s recursively', $path, 0755 );
				WIO_Logger::error( $message );
				throw new \Exception( $message );
			}
		}
		
		return $path;
	}
	
	/**
	 * Get absolute save path is a wrapper around get_save_path() with addition of file name.
	 *
	 * @param RIO_Process_Queue $queue_model Instance of queue item.
	 *
	 * @return string
	 * @throws Exception
	 * @see get_file_name() for generation of file name.
	 *
	 * @see get_save_path() for generation of save path.
	 */
	public static function get_absolute_save_path( $queue_model ) {
		return wp_normalize_path( trailingslashit( static::get_save_path() ) . static::get_file_name( $queue_model ) );
	}
	
	/**
	 * Get final file name.
	 *
	 * @param RIO_Process_Queue $queue_model Instance of queue item.
	 *
	 * @return string
	 */
	public static function get_file_name( $queue_model ) {
		
		/**
		 * @var $extra_data RIOP_WebP_Extra_Data
		 */
		$extra_data = $queue_model->get_extra_data();
		
		if ( empty( $extra_data ) ) {
			WIO_Logger::error( sprintf( 'Unable to get extra data for queue item #%s', $queue_model->get_id() ) );
			
			return null;
		}
		
		$path_data = pathinfo( $extra_data->get_source_path() );
		
		if ( ! isset( $path_data['filename'] ) ) {
			WIO_Logger::error( sprintf( 'Unable to get file name from path %s', $extra_data->get_source_path() ) );
			
			return null;
		}
		
		return trim( $path_data['filename'] ) . '.webp';
	}
}
