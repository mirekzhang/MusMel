<?php
/*
	Plugin Name: Zomato User Widget
	Plugin URI: http://www.zomato.com
	Description: A simple widget that enables you to flaunt your Zomato foodie stats!
	Version: 1.3.2
	Author: Zomato
	Author URI: http://www.zomato.com/team
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

include( plugin_dir_path( __FILE__ ) . 'classes/User.php' );
use Zomato\User as User;

class Zomato_User_Widget extends WP_Widget {


	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname' => 'zomato-user-widget-class',
			'description' => 'A simple widget for fluanting your Zomato foodie stats'
		);

		parent::__construct(
			'zomato-user-widget',   // Base ID
			'Zomato User Widget',   // Name
			$widget_ops             // Options
		);

		// Register widget styles
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );

	} // end constructor



	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @see WP_Widget::widget()
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args, EXTR_SKIP );

		// User-selected title.
		$title = apply_filters('widget_title', $instance['title'] );

		// Before widget (defined by themes).
		echo $before_widget;

		// Title of widget (before_title and after_title defined by themes).
		if ( $title ) { echo $before_title . $title . $after_title; }

		if ( empty($instance['user_id']) ) {
			$error_message = "Please enter a valid Email Address or User ID.";
			include( plugin_dir_path( __FILE__ ) . 'views/widget-error.php' );

		} else {

			try {
				$user = new User( $instance['user_id'], $instance['lang'] );
				include( plugin_dir_path( __FILE__ ) . 'views/widget.php' );
			} catch (Exception $e) {
				$error_message = $e->getMessage();
				include( plugin_dir_path( __FILE__ ) . 'views/widget-error.php' );
			}

		}

		// After widget (defined by themes).
		echo $after_widget;

	}



	/**
	 * Processes the widget's options to be saved.
	 *
	 * @see WP_Widget::update()
	 * @param array new_instance Values just sent to be saved.
	 * @param array old_instance Previously saved values from database.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		// Updating the widget's old values with the new, incoming values
		$instance['title']   = strip_tags( $new_instance['title'] );
		$instance['user_id'] = strip_tags( $new_instance['user_id'] );
		$instance['lang']    = strip_tags( $new_instance['lang'] );
		$instance['powered'] = strip_tags( $new_instance['powered'] );

		return $instance;

	}


	/**
	 * Generates the back-end form for the widget.
	 *
	 * @see WP_Widget::form()
	 * @param array instance The array of previously saved keys and values for the widget.
	 */
	public function form( $instance ) {

		// Define default values for your variables
		$defaults = array(
			'title'   => 'Zomato User Widget',
			'user_id' => '',
			'powered' => false,
			'lang'    => 'en',
		);

		// Merging default values with the newly set ones
		$instance = wp_parse_args(
			(array) $instance, $defaults
		);

		// Store the values of the widget in their own variable
		extract( $instance, EXTR_SKIP );

		// Display the admin form
		include( plugin_dir_path(__FILE__) . 'views/admin.php' );

	}

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	public function register_widget_styles() {
		wp_enqueue_style( 'zomato-user-widget-styles', plugins_url( 'css/widget.css', __FILE__ ) );
	}

} // end class


add_action( 'widgets_init', create_function( '', 'register_widget("Zomato_User_Widget");' ) );
