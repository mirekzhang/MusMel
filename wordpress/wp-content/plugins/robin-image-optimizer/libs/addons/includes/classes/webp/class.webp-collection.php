<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WRIO_Webp_Collection converts and replace JPEG & PNG images within HTML doc.
 *
 * Images converted via third-party service, saved locally and then replaced based on parsed DOM <img>, or other elements.
 *
 * @see DOMDocument for what is used to parse DOM elements.
 *
 * Some good reference materials:
 * @link https://caniuse.com/#search=webp
 * @link https://css-tricks.com/using-webp-images/
 * @link https://dev.opera.com/articles/responsive-images/#different-image-types-use-case
 *
 * @author Webcraftic <wordpress.webraftic@gmail.com>
 * @author Alexander Teshabaev <sasha.tesh@gmail.com>
 * @copyright (c) 22.09.2018, Webcraftic
 * @version 1.0
 */
class WRIO_Webp_Collection {
	
	/**
	 * WRIO_Webp constructor.
	 */
	public function __construct() {
		$this->init();
	}
	
	/**
	 * Initiate the class.
	 */
	public function init() {
		/*if ( is_admin() ) {
			add_filter( 'wbcr/rio/settings_page/options', [ $this, 'init_admin_option' ] );
		}*/
		
		if ( static::is_webp_enabled() ) {
			// todo: It is executed with any request. Temporarily disabled
			//WIO_Logger::info( sprintf( "WebP option enabled and browser \"%s\" is supported, ready to process buffer",
			//isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '*undefined*' ) );
			
			if ( ! is_admin() && $this->is_supported_browser() ) {
				add_action( 'template_redirect', array( $this, 'process_buffer' ), 1 );
			}
		}
	}
	
	/**
	 * Check whether WebP options enabled or not.
	 *
	 * @return bool
	 */
	public static function is_webp_enabled() {
		return (bool) WRIO_Plugin::app()->getPopulateOption( 'convert_webp_format' );
	}
	
	/**
	 * Add new admin option.
	 *
	 * @param array $options List of options to be displayed on the admin.
	 *
	 * @return array Filtered list of options with addition of WebP format option.
	 */
	/*public function init_admin_option ( $options ) {
		$options[] = array(
			'type' => 'html',
			'html' => '<div class="wbcr-factory-page-group-header"><strong>' . __( 'Convertions', 'robin-image-optimizer' ) . '</strong><p>' . __( 'Here you can specify additional image convertion options.', 'robin-image-optimizer' ) . '</p></div>',
		);

		$options[] = array(
			'type'    => 'checkbox',
			'way'     => 'buttons',
			'name'    => 'convert_webp_format',
			'title'   => __( 'Convert Images to WebP', 'robin-image-optimizer' ),
			'layout'  => array( 'hint-type' => 'icon', 'hint-icon-color' => 'grey' ),
			'hint'    => __( 'Convert JPEG & PNG images into WebP format and replace them for browsers which support it. Unsupported browsers would be skipped.', 'robin-image-optimizer' ),
			'default' => true,
		);

		return $options;
	}*/
	
	/**
	 * Process HTML template buffer.
	 */
	public function process_buffer() {
		ob_start( array( $this, 'process_tags' ) );
	}
	
	/**
	 * Process tags to replace those elements which match converted to WebP within buffer.
	 *
	 * @param string $content HTML buffer.
	 *
	 * @return string
	 */
	public function process_tags( $content ) {
		if ( empty( $content ) ) {
			WIO_Logger::info( "Buffer content is empty, skipping processing" );
			
			return $content;
		}
		
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
		
		$document = new DOMDocument();
		libxml_use_internal_errors( true );
		$document->loadHTML( utf8_decode( $content ) );
		
		// Get images from current DOM elements.
		$images = $document->getElementsByTagName( 'img' );
		
		// If images found, set attachment ids.
		if ( ! empty( $images ) ) {
			WIO_Logger::info( sprintf( "Found %s image, ready to process them", count( $images ) ) );
			$this->process_elements( $images );
		}
		
		return $document->saveHTML();
	}
	
	/**
	 * Set attachment IDs of image as data.
	 *
	 * Get attachment IDs from URLs and set new data property to img.
	 * We can use WP_Query to find attachment ids of all images on current page content.
	 *
	 * @param DOMNodeList $images Expected to be list of images.
	 */
	private function process_elements( $images ) {
		
		if ( ! empty( $images ) ) {
			/**
			 * @var $image DOMElement
			 */
			foreach ( $images as $key => $image ) {
				$this->process_element_attributes( $image );
			}
		}
	}
	
	/**
	 * Process single srcset.
	 *
	 * @param string $src Single srcset to be prepared.
	 *
	 * @return array|null null on failure, array on success. Array's first position is src (http://domain.com/image.png)
	 * and second is size (e.g. 1024w).
	 */
	public static function retrieve_single_srcset( $src ) {
		$src      = trim( $src );
		$exploded = explode( ' ', $src, 2 );
		
		$url   = trim( $exploded[0] );
		$width = isset( $exploded[1] ) ? trim( $exploded[1] ) : null;
		
		if ( empty( $url ) || empty( $width ) ) {
			return null;
		}
		
		return array( $url, $width );
	}
	
	
	/**
	 * @param DOMElement $image Instance of image DOM element.
	 *
	 * @return bool
	 */
	public function process_element_attributes( $image ) {
		
		$items = array();
		
		$items[] = [
			'element'   => $image,
			'attribute' => 'src',
			'value'     => $image->getAttribute( 'src' ),
		];
		
		$srcset = $image->getAttribute( 'srcset' );
		
		if ( ! empty( $srcset ) ) {
			$this->split_srcset( $srcset, function ( $src, $width ) use ( $image, &$items ) {
				$items[] = [
					'element'   => $image,
					'attribute' => 'srcset',
					'value'     => $src,
				];
			} );
		}
		
		unset( $srcset );
		
		if ( ! empty( $image->parentNode ) && $image->parentNode->getAttribute( 'class' ) === 'ngg-simplelightbox' ) {
			$items[] = [
				'element'   => $image->parentNode,
				'attribute' => 'href',
				'value'     => $image->parentNode->getAttribute( 'href' ),
			];
			
			$items[] = [
				'element'   => $image->parentNode,
				'attribute' => 'data-src',
				'value'     => $image->parentNode->getAttribute( 'data-src' ),
			];
			
			$items[] = [
				'element'   => $image->parentNode,
				'attribute' => 'data-thumbnail',
				'value'     => $image->parentNode->getAttribute( 'data-thumbnail' ),
			];
		}
		
		if ( empty( $items ) ) {
			return false;
		}
		
		WIO_Logger::info( sprintf( 'Started processing %s of items', count( $items ) ) );
		
		$unique_hashes = array();
		
		foreach ( $items as $key => $item ) {
			$url = WRIO_Url::normalize( $item['value'] );
			
			$model_hash    = hash( 'sha256', $url );
			$item['value'] = $url;
			$item['hash']  = $model_hash;
			
			$unique_hashes[ $model_hash ][] = $item;
		}
		
		unset( $items );
		
		$models = RIO_Process_Queue::find_by_hashes( array_keys( $unique_hashes ), RIO_Process_Queue::STATUS_SUCCESS );
		
		if ( ! empty( $models ) ) {
			
			WIO_Logger::info( sprintf( "Found %s items by hashes, ready to process them", count( $models ) ) );
			
			/**
			 * @var RIO_Process_Queue $model
			 */
			foreach ( $models as $key => $model ) {
				
				WIO_Logger::info( sprintf( "Now working on ID: %s", $model->get_id() ) );
				
				/**
				 * @var RIOP_WebP_Extra_Data $extra_data
				 */
				$extra_data    = $model->get_extra_data();
				$source_src    = $extra_data->get_source_src();
				$converted_src = $extra_data->get_converted_src();
				
				if ( empty( $converted_src ) ) {
					WIO_Logger::info( sprintf( 'Item with ID: %s does not have converted src yet, skipping', $model->get_id() ) );
					continue;
				}
				
				$model_hash = $model->get_item_hash();
				
				if ( isset( $unique_hashes[ $model_hash ] ) ) {
					foreach ( $unique_hashes[ $model_hash ] as $item ) {
						
						/**
						 * @var $element DOMElement
						 */
						$element        = $item['element'];
						$attribute      = $item['attribute'];
						$original_value = $item['value'];
						
						if ( $attribute === 'srcset' ) {
							
							$original_srcset = $element->getAttribute( $attribute );
							$original_srcset = str_replace( $original_value, $converted_src, $original_srcset );
							$element->setAttribute( $attribute, $original_srcset );
						} else {
							$element->setAttribute( $attribute, str_replace( $source_src, $converted_src, $original_value ) );
						}
					}
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Splits passed src and executed callback.
	 *
	 * @param string $srcset srcset attribute.
	 * @param callable $callback Callback signature: function($src, $width), where src is single srcset src and width is its width :)
	 *
	 * @return bool
	 */
	public function split_srcset( $srcset, $callback ) {
		if ( empty( $srcset ) ) {
			return false;
		}
		
		$pieces = explode( ',', $srcset );
		
		foreach ( $pieces as $piece ) {
			$single_src = static::retrieve_single_srcset( $piece );
			
			if ( empty( $single_src ) ) {
				continue;
			}
			
			if ( is_callable( $callback ) ) {
				call_user_func( $callback, $single_src[0], $single_src[1] );
			}
		}
		
		return true;
	}
	
	/**
	 * Find elements by specified class name.
	 *
	 * @param string $className Class name to search for.
	 * @param DOMDocument $dom
	 *
	 * @return DOMNodeList
	 */
	public function find_by_className( $classname, $dom ) {
		$finder = new DomXPath( $dom );
		
		return $finder->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]" );
	}
	
	/**
	 * Check whether browser supports WebP or not.
	 *
	 * @return bool
	 */
	public function is_supported_browser() {
		if ( isset( $_SERVER['HTTP_ACCEPT'] ) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false || isset( $_SERVER['HTTP_USER_AGENT'] ) && strpos( $_SERVER['HTTP_USER_AGENT'], ' Chrome/' ) !== false ) {
			return true;
		}
		
		return false;
	}
}
