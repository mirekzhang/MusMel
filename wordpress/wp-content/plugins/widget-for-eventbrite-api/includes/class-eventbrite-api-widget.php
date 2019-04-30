<?php

/**
 * Widget logic class, includes form excludes front end display
 */
namespace WidgetForEventbriteAPI\Includes;

use  WP_Widget ;
class EventBrite_API_Widget extends WP_Widget
{
    public function __construct()
    {
        $widget_ops = array(
            'classname'                   => 'widget_eventbrite_events',
            'description'                 => __( 'An advanced widget that calls the Eventbrite API plugin to allow you to display your forthcoming events' ),
            'customize_selective_refresh' => true,
        );
        $control_ops = array(
            'width'  => 400,
            'height' => 350,
        );
        parent::__construct(
            'eventbrite-events',
            __( 'Widget for Eventbrite' ),
            $widget_ops,
            $control_ops
        );
        $this->alt_option_name = 'widget_eventbrite_events';
    }
    
    /**
     * Outputs the content for the current EventBrite events widget instance.
     *
     */
    public function widget( $args, $instance )
    {
        /** @var \Freemius $wfea_fs Freemius global object. */
        global  $wfea_fs ;
        extract( $args );
        // Merge the input arguments and the defaults.
        $instance = wp_parse_args( (array) $instance, $this->default_args() );
        // Query arguments.
        $query = array(
            'nopaging' => true,
            'limit'    => $instance['limit'],
        );
        // Allow plugins/themes developer to filter the default query.
        $query = apply_filters( 'eawp_default_query_arguments', $query );
        // Perform the query.
        $events = new Eventbrite_Query( $query );
        $html = '';
        
        if ( is_wp_error( $events->api_results ) ) {
            
            if ( current_user_can( 'manage_options' ) ) {
                $error_string = $events->api_results->get_error_message();
                
                if ( is_array( $error_string ) ) {
                    $text = json_decode( $error_string['body'] );
                    $error_string = $text->error_description;
                }
                
                $html = '<div class="error">' . __( 'Display Eventbrite Plugin Error ( this shows to admins only ): ', 'widget-for-eventbrite-api' ) . $error_string . "</div>";
            }
        
        } else {
            $template_loader = new Template_Loader();
            $template_loader->set_template_data( array(
                'events' => $events,
                'args'   => $instance,
            ) );
            ob_start();
            $template_loader->get_template_part( 'widget' );
            $html = ob_get_clean();
        }
        
        $recent = wp_kses_post( $instance['before'] ) . apply_filters( 'eawp_markup', $html ) . wp_kses_post( $instance['after'] );
        // Restore original Post Data.
        wp_reset_postdata();
        // Allow devs to hook in stuff after the loop.
        do_action( 'eawp_after_loop' );
        // Return the  posts markup.
        
        if ( $recent ) {
            // Output the theme's $before_widget wrapper.
            echo  $before_widget ;
            // If both title and title url is not empty, display it.
            
            if ( !empty($instance['title_url']) && !empty($instance['title']) ) {
                echo  $before_title . '<a href="' . esc_url( $instance['title_url'] ) . '" title="' . esc_attr( $instance['title'] ) . '">' . apply_filters(
                    'widget_title',
                    $instance['title'],
                    $instance,
                    $this->id_base
                ) . '</a>' . $after_title ;
                // If the title not empty, display it.
            } elseif ( !empty($instance['title']) ) {
                echo  $before_title . apply_filters(
                    'widget_title',
                    $instance['title'],
                    $instance,
                    $this->id_base
                ) . $after_title ;
            }
            
            // Get the recent posts query.
            echo  $recent ;
            // Close the theme's widget wrapper.
            echo  $after_widget ;
        }
    
    }
    
    public static function default_args()
    {
        /** @var \Freemius $wfea_fs Freemius global object. */
        global  $wfea_fs ;
        $defaults = array(
            'title'            => esc_attr__( 'Upcoming Events', 'widget-for-eventbrite-api' ),
            'title_url'        => '',
            'limit'            => 5,
            'excerpt'          => false,
            'length'           => 10,
            'date'             => true,
            'readmore'         => false,
            'readmore_text'    => __( 'Read More &raquo;', 'widget-for-eventbrite-api' ),
            'booknow'          => false,
            'booknow_text'     => __( 'Book Now &raquo;', 'widget-for-eventbrite-api' ),
            'thumb'            => true,
            'thumb_width'      => 45,
            'thumb_default'    => 'http://placehold.it/45x45/f0f0f0/ccc',
            'thumb_align'      => 'eaw-alignleft',
            'cssID'            => '',
            'css_class'        => '',
            'before'           => '',
            'after'            => '',
            'layout'           => '1',
            'newtab'           => false,
            'tickets'          => false,
            'long_description' => false,
        );
        // Allow plugins/themes developer to filter the default arguments.
        return apply_filters( 'eawp_default_args', $defaults );
    }
    
    /**
     * Handles updating the settings for the current EventBrite widget instance.
     *
     */
    public function update( $new_instance, $old_instance )
    {
        /** @var \Freemius $wfea_fs Freemius global object. */
        global  $wfea_fs ;
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['title_url'] = esc_url_raw( $new_instance['title_url'] );
        $instance['number'] = (int) $new_instance['number'];
        $instance['excerpt'] = ( isset( $new_instance['excerpt'] ) ? (bool) $new_instance['excerpt'] : false );
        $instance['length'] = intval( $new_instance['length'] );
        $instance['date'] = ( isset( $new_instance['date'] ) ? (bool) $new_instance['date'] : false );
        $instance['readmore'] = ( isset( $new_instance['readmore'] ) ? (bool) $new_instance['readmore'] : false );
        $instance['readmore_text'] = sanitize_text_field( $new_instance['readmore_text'] );
        $instance['booknow'] = ( isset( $new_instance['booknow'] ) ? (bool) $new_instance['booknow'] : false );
        $instance['booknow_text'] = sanitize_text_field( $new_instance['booknow_text'] );
        $instance['limit'] = intval( $new_instance['limit'] );
        $instance['thumb'] = ( isset( $new_instance['thumb'] ) ? (bool) $new_instance['thumb'] : false );
        $instance['thumb_width'] = intval( $new_instance['thumb_width'] );
        $instance['thumb_default'] = esc_url_raw( $new_instance['thumb_default'] );
        $instance['thumb_align'] = esc_attr( $new_instance['thumb_align'] );
        $instance['cssID'] = sanitize_html_class( $new_instance['cssID'] );
        $instance['css_class'] = sanitize_html_class( $new_instance['css_class'] );
        $instance['newtab'] = ( isset( $new_instance['newtab'] ) ? (bool) $new_instance['newtab'] : false );
        $instance['tickets'] = ( isset( $new_instance['tickets'] ) ? (bool) $new_instance['tickets'] : false );
        
        if ( current_user_can( 'unfiltered_html' ) ) {
            $instance['before'] = $new_instance['before'];
        } else {
            $instance['before'] = wp_kses_post( $new_instance['before'] );
        }
        
        
        if ( current_user_can( 'unfiltered_html' ) ) {
            $instance['after'] = $new_instance['after'];
        } else {
            $instance['after'] = wp_kses_post( $new_instance['after'] );
        }
        
        return $instance;
    }
    
    /**
     * Outputs the settings form for the EventBrite widget.
     *
     */
    public function form( $instance )
    {
        /** @var \Freemius $wfea_fs Freemius global object. */
        global  $wfea_fs ;
        // Merge the user-selected arguments with the defaults.
        $instance = wp_parse_args( (array) $instance, self::default_args() );
        // Extract the array to allow easy use of variables.
        extract( $instance );
        
        if ( $wfea_fs->is_trial() ) {
            ?>
            <div class="notice inline notice-info notice-alt"><p>
					<?php 
            printf( __( 'You are in the Free trial - <a href="%1$s">Upgrade Now!</a> to keep benefits', 'widget-for-eventbrite-api' ), $wfea_fs->get_upgrade_url() );
            ?>
                </p>
            </div>
		<?php 
        } elseif ( $wfea_fs->is_free_plan() ) {
            ?>
            <div class="notice inline notice-info notice-alt"><p>
					<?php 
            printf( __( 'Try Pro. FREE trial 7 days <a href="%1$s">FREE trial 7 days.</a>', 'widget-for-eventbrite-api' ), $wfea_fs->get_trial_url() );
            ?>
                </p>
            </div>
		<?php 
        }
        
        ?>


        <div class="eaw-columns-2">
            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'title' ) ;
        ?>">
					<?php 
        _e( 'Title', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <input class="widefat" id="<?php 
        echo  $this->get_field_id( 'title' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'title' ) ;
        ?>" type="text"
                       value="<?php 
        echo  esc_attr( $instance['title'] ) ;
        ?>"/>
            </p>

            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'title_url' ) ;
        ?>">
					<?php 
        _e( 'Title URL', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <input class="widefat" id="<?php 
        echo  $this->get_field_id( 'title_url' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'title_url' ) ;
        ?>" type="text"
                       value="<?php 
        echo  esc_url( $instance['title_url'] ) ;
        ?>"/>
            </p>

            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'cssID' ) ;
        ?>">
					<?php 
        _e( 'CSS ID', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <input class="widefat" id="<?php 
        echo  $this->get_field_id( 'cssID' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'cssID' ) ;
        ?>" type="text"
                       value="<?php 
        echo  sanitize_html_class( $instance['cssID'] ) ;
        ?>"/>
            </p>

            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'css_class' ) ;
        ?>">
					<?php 
        _e( 'CSS Class', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <input class="widefat" id="<?php 
        echo  $this->get_field_id( 'css_class' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'css_class' ) ;
        ?>" type="text"
                       value="<?php 
        echo  sanitize_html_class( $instance['css_class'] ) ;
        ?>"/>
            </p>

            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'before' ) ;
        ?>">
					<?php 
        _e( 'HTML or text before the recent posts', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <textarea class="widefat" id="<?php 
        echo  $this->get_field_id( 'before' ) ;
        ?>"
                          name="<?php 
        echo  $this->get_field_name( 'before' ) ;
        ?>"
                          rows="5"><?php 
        echo  htmlspecialchars( stripslashes( $instance['before'] ) ) ;
        ?></textarea>
            </p>

            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'after' ) ;
        ?>">
					<?php 
        _e( 'HTML or text after the recent posts', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <textarea class="widefat" id="<?php 
        echo  $this->get_field_id( 'after' ) ;
        ?>"
                          name="<?php 
        echo  $this->get_field_name( 'after' ) ;
        ?>"
                          rows="5"><?php 
        echo  htmlspecialchars( stripslashes( $instance['after'] ) ) ;
        ?></textarea>
            </p>


            <p>
                <input id="<?php 
        echo  $this->get_field_id( 'booknow' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'booknow' ) ;
        ?>"
                       type="checkbox" <?php 
        checked( $instance['booknow'] );
        ?> />
                <label for="<?php 
        echo  $this->get_field_id( 'booknow' ) ;
        ?>">
					<?php 
        _e( 'Display Book Now Button', 'widget-for-eventbrite-api' );
        ?>
                </label>
            </p>
			<?php 
        ?>

            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'booknow_text' ) ;
        ?>">
					<?php 
        _e( 'Book Now Text', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <input class="widefat" id="<?php 
        echo  $this->get_field_id( 'booknow_text' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'booknow_text' ) ;
        ?>" type="text"
                       value="<?php 
        echo  strip_tags( $instance['booknow_text'] ) ;
        ?>"/>
            </p>
        </div>

        <div class="eaw-columns-2 eaw-column-last">

			<?php 
        ?>

            <p>
                <input class="checkbox" type="checkbox" <?php 
        checked( $instance['newtab'], 1 );
        ?>
                       id="<?php 
        echo  $this->get_field_id( 'newtab' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'newtab' ) ;
        ?>"/>
                <label for="<?php 
        echo  $this->get_field_id( 'newtab' ) ;
        ?>">
					<?php 
        _e( 'Open Eventbrite in a new tab', 'widget-for-eventbrite-api' );
        ?>
                </label>
            </p>


            <p>
                <input id="<?php 
        echo  $this->get_field_id( 'date' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'date' ) ;
        ?>"
                       type="checkbox" <?php 
        checked( $instance['date'] );
        ?> />
                <label for="<?php 
        echo  $this->get_field_id( 'date' ) ;
        ?>">
					<?php 
        _e( 'Display Date / Time', 'widget-for-eventbrite-api' );
        ?>
                </label>
            </p>


            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'limit' ) ;
        ?>">
					<?php 
        _e( 'Number of posts to show', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <input class="widefat" id="<?php 
        echo  $this->get_field_id( 'limit' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'limit' ) ;
        ?>" type="number" step="1" min="-1"
                       value="<?php 
        echo  (int) $instance['limit'] ;
        ?>"/>
            </p>

			<?php 
        
        if ( current_theme_supports( 'post-thumbnails' ) ) {
            ?>

                <p>
                    <input id="<?php 
            echo  $this->get_field_id( 'thumb' ) ;
            ?>"
                           name="<?php 
            echo  $this->get_field_name( 'thumb' ) ;
            ?>"
                           type="checkbox" <?php 
            checked( $instance['thumb'] );
            ?> />
                    <label for="<?php 
            echo  $this->get_field_id( 'thumb' ) ;
            ?>">
						<?php 
            _e( 'Display Thumbnail', 'widget-for-eventbrite-api' );
            ?>
                    </label>
                </p>

                <p>
                    <label class="eaw-block" for="<?php 
            echo  $this->get_field_id( 'thumb_width' ) ;
            ?>">
						<?php 
            _e( 'Thumbnail (width,align)', 'widget-for-eventbrite-api' );
            ?>
                    </label>
                    <input class="small-input" id="<?php 
            echo  $this->get_field_id( 'thumb_width' ) ;
            ?>"
                           name="<?php 
            echo  $this->get_field_name( 'thumb_width' ) ;
            ?>" type="number" step="1" min="0"
                           value="<?php 
            echo  (int) $instance['thumb_width'] ;
            ?>"/>
                    <select class="small-input" id="<?php 
            echo  $this->get_field_id( 'thumb_align' ) ;
            ?>"
                            name="<?php 
            echo  $this->get_field_name( 'thumb_align' ) ;
            ?>">
                        <option value="eaw-alignleft" <?php 
            selected( $instance['thumb_align'], 'eaw-alignleft' );
            ?>><?php 
            _e( 'Left', 'widget-for-eventbrite-api' );
            ?></option>
                        <option value="eaw-alignright" <?php 
            selected( $instance['thumb_align'], 'eaw-alignright' );
            ?>><?php 
            _e( 'Right', 'widget-for-eventbrite-api' );
            ?></option>
                        <option value="eaw-aligncenter" <?php 
            selected( $instance['thumb_align'], 'eaw-aligncenter' );
            ?>><?php 
            _e( 'Center', 'widget-for-eventbrite-api' );
            ?></option>
                    </select>
                </p>

                <p>
                    <label for="<?php 
            echo  $this->get_field_id( 'thumb_default' ) ;
            ?>">
						<?php 
            _e( 'Default Thumbnail', 'widget-for-eventbrite-api' );
            ?>
                    </label>
                    <input class="widefat" id="<?php 
            echo  $this->get_field_id( 'thumb_default' ) ;
            ?>"
                           name="<?php 
            echo  $this->get_field_name( 'thumb_default' ) ;
            ?>" type="text"
                           value="<?php 
            echo  $instance['thumb_default'] ;
            ?>"/>
                    <small><?php 
            _e( 'Leave it blank to disable.', 'widget-for-eventbrite-api' );
            ?></small>
                </p>

			<?php 
        }
        
        ?>

            <p>
                <input id="<?php 
        echo  $this->get_field_id( 'excerpt' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'excerpt' ) ;
        ?>"
                       type="checkbox" <?php 
        checked( $instance['excerpt'] );
        ?> />
                <label for="<?php 
        echo  $this->get_field_id( 'excerpt' ) ;
        ?>">
					<?php 
        _e( 'Display Event Description', 'widget-for-eventbrite-api' );
        ?>
                </label>
            </p>

            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'length' ) ;
        ?>">
					<?php 
        _e( 'Description Length', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <input class="widefat" id="<?php 
        echo  $this->get_field_id( 'length' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'length' ) ;
        ?>" type="number" step="1" min="0"
                       value="<?php 
        echo  (int) $instance['length'] ;
        ?>"/>
            </p>

            <p>
                <input id="<?php 
        echo  $this->get_field_id( 'readmore' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'readmore' ) ;
        ?>"
                       type="checkbox" <?php 
        checked( $instance['readmore'] );
        ?> />
                <label for="<?php 
        echo  $this->get_field_id( 'readmore' ) ;
        ?>">
					<?php 
        _e( 'Display Readmore', 'widget-for-eventbrite-api' );
        ?>
                </label>
            </p>

            <p>
                <label for="<?php 
        echo  $this->get_field_id( 'readmore_text' ) ;
        ?>">
					<?php 
        _e( 'Readmore Text', 'widget-for-eventbrite-api' );
        ?>
                </label>
                <input class="widefat" id="<?php 
        echo  $this->get_field_id( 'readmore_text' ) ;
        ?>"
                       name="<?php 
        echo  $this->get_field_name( 'readmore_text' ) ;
        ?>" type="text"
                       value="<?php 
        echo  strip_tags( $instance['readmore_text'] ) ;
        ?>"/>
            </p>


        </div>

        <div class="clear"></div>


		<?php 
    }

}