<?php

namespace WBCR\Factory_412\Updates;

// Exit if accessed directly
use Wbcr_Factory412_Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @author Webcraftic <wordpress.webraftic@gmail.com>, Alex Kovalev <alex.kovalevv@gmail.com>
 * @link https://webcraftic.com
 * @copyright (c) 2018 Webraftic Ltd
 * @version 1.0
 */
abstract class Repository {
	
	/**
	 * @var bool
	 */
	protected $initialized = false;
	
	/**
	 * @var Wbcr_Factory412_Plugin
	 */
	protected $plugin;
	
	/**
	 * Repository constructor.
	 *
	 * @param Wbcr_Factory412_Plugin $plugin
	 * @param bool $is_premium
	 */
	abstract public function __construct( Wbcr_Factory412_Plugin $plugin );
	
	/**
	 * @return void
	 */
	abstract public function init();
	
	/**
	 * @return bool
	 */
	abstract public function need_check_updates();
	
	/**
	 * @return mixed
	 */
	abstract public function is_support_premium();
	
	/**
	 * @return string
	 */
	abstract public function get_download_url();
	
	/**
	 * @return string
	 */
	abstract public function get_last_version();
}