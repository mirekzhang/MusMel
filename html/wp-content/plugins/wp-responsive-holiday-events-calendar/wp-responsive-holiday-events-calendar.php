<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link
 * @since             1.0.0
 * @package           wp-responsive-holiday-events-calendar
 *
 * @wordpress-plugin
 * Plugin Name:       WP Responsive Holiday/Events Calendar
 * Plugin URI:        https://wordpress.org/plugins/wp-responsive-holiday-events-calendar/
 * Description:       Beautiful way to show your upcoming  Holidays and Events in WordPress.
 * Version:           1.1.0
 * Author:            Vsourz Digital
 * Author URI:        https://www.vsourz.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-responsive-holiday-events-calendar
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
 load_plugin_textdomain(
			'wp-responsive-holiday-events-calendar',
			false,
			 'wp-responsive-holiday-events-calendar/languages/'
		);
///////////////////// Adding custom menu page /////////////////////
add_action( 'admin_menu' ,'wp_responsive_holiday_events_calendar_page');
function wp_responsive_holiday_events_calendar_page(){
	add_menu_page(
		__( 'WP Responsive Holiday Events Calendar', 'textdomain' ),
		'Holiday/Event',
		'manage_options',
		'wp-responsive-holiday-events-calendar',
		'wp_responsive_holiday_events_calendar_menu_page',
		'dashicons-calendar-alt',
		7
	);
}

///////////////////// Callback function for custom menu	/////////////////////
function wp_responsive_holiday_events_calendar_menu_page(){

	// Defining initial parameters
	$bulk = '';
	$arrError = array();
	// bulk delete process
	if(isset($_POST['whec_nonce']) && !empty($_POST['whec_nonce']) && wp_verify_nonce($_POST['whec_nonce'], 'check_holoday_nonce')){

		if(isset($_POST['bulkaction']) && $_POST['bulkaction'] == 'deleteall'){

			if(isset($_POST['selected']) && !empty($_POST['selected'])){

				// Fetch the stored holiday list from DB
				$holiday = get_option('whec_holiday_list');

				// remove selected holiday list
				foreach($_POST['selected'] as $key){
					// check for key is numeric value and exist into holiday array
					if(is_array($holiday) && is_numeric($key) && isset($holiday[$key])){
						unset($holiday[$key]);
					}
				}

				// Save holiday list after remove selected holiday from all holiday list
				$bulk = 'deleted';
				update_option('whec_holiday_list',$holiday);
			}
		}

		// bulk update process
		if(isset($_POST['bulkaction']) && $_POST['bulkaction'] == 'updateall'){

			if(isset($_POST['selected']) && !empty($_POST['selected'])){

				// Fetch the stored holiday list from DB
				$holiday = get_option('whec_holiday_list');

				// Update selected holiday list
				foreach($_POST['selected'] as $key){

					if(isset($_POST['title'.$key]) && isset($_POST['date'.$key]) && !empty($_POST['title'.$key]) && !empty($_POST['date'.$key])){

						// check date first if valid then update or give error accordingly
						$date = ( strtotime( $_POST['date'.$key] ) )? $_POST['date'.$key] : 'ERROR';
						if($date != 'ERROR'){
							$holiday[$key]['date']=$date;
							$holiday[$key]['title'] = sanitize_text_field($_POST['title'.$key]);
							$holiday[$key]['url']= esc_url($_POST['url'.$key]);
							$holiday[$key]['target'] = sanitize_text_field($_POST['target'.$key]);
						}
						else{
							//printf(__('Name: %s', 'wpml-string-translation'), $str['name'])
							$arrError[] = printf(__('Please enter valid date for <b> %s </b> holiday entry.','wp-responsive-holiday-events-calendar'),sanitize_text_field($_POST['title'.$key]));
						}
					}
				}

				// Save holiday list after update it in overall holiday list
				if(empty($arrError)){
					$bulk = 'updated';
					update_option('whec_holiday_list',$holiday);
				}
			}
		}
	}

	// incliding datepicker js
	wp_enqueue_script( 'jquery-ui-datepicker' );

    // You need styling for the datepicker. For simplicity I've linked to Google's hosted jQuery UI CSS.
	wp_register_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'assets/css/jquery-ui.css' );
	wp_register_style( 'holiday-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/holiday_admin.css' );
	wp_enqueue_style( 'jquery-ui' );
	wp_register_style( 'fullcalendar.min', plugin_dir_url( __FILE__ ) . 'assets/css/fullcalendar.min.css' );
	wp_enqueue_style( 'fullcalendar.min' );
	wp_register_style( 'fullcalendar.print.min', plugin_dir_url( __FILE__ ) . 'assets/css/fullcalendar.print.min.css' );
	wp_enqueue_style( 'fullcalendar.print.min' );
	wp_enqueue_style( 'holiday-admin-css' );
	wp_register_script('js-colorpicker',plugin_dir_url(__FILE__)."assets/js/jscolor.js",true);
	wp_enqueue_script('js-colorpicker');
	wp_enqueue_style( 'bootstrap-holiday', plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap.min.css', 'all' );
	wp_register_script('moment.min',plugin_dir_url(__FILE__)."assets/js/moment.min.js",true);
	wp_enqueue_script('moment.min');
	wp_register_script('fullcalendar.min',plugin_dir_url(__FILE__)."assets/js/fullcalendar.min.js",true);
	wp_enqueue_script('fullcalendar.min');
	wp_enqueue_script('locale-all',plugin_dir_url(__FILE__)."assets/js/locale-all.js",true);





	/// Displaying initial note
	?><style>
		body{ background:#f1f1f1!important;}
		.fc-day-top .fc-today span{
			background: #f1f1f1 none repeat scroll 0 0;
			border-radius: 50%;
			color: #000;
			height: 25px;
			line-height: 2;
			text-align: center;
			width: 25px;
		}
	</style>
	<div class="wrap">
		<h2 style="margin-bottom:30px;"><?php echo __('WP Responsive Holiday Events Calendar','wp-responsive-holiday-events-calendar');?></h2>
		<div class="holiday-tab">
			<a href="#holiday" class="holiday-tab-active"><?php echo __('Manage Holiday/Event','wp-responsive-holiday-events-calendar');?></a>
			<a href="#holiday-option"><?php echo __('Display Option','wp-responsive-holiday-events-calendar');?></a>
		</div>

		<div class="holiday holiday-tab-list" id="holiday" >
			<div class="holiday-outer" >
				<div class="form-group"><?php

					// Name
					?><label><?php echo __('Name*','wp-responsive-holiday-events-calendar');?></label>
					<input type="text" name="title" class="title " ><?php

					// Date
					?><label><?php echo __('Date*','wp-responsive-holiday-events-calendar');?></label>
					<input type="text" name="title" class="date " readonly><?php

					// URL
					?><label><?php echo __('Url','wp-responsive-holiday-events-calendar');?></label>
					<input type="text" name="url" class="url "><?php

					// URL Destination
					?><label><?php echo __('Open Url','wp-responsive-holiday-events-calendar');?></label>
					<select name="target" class="target ">
						<option value="existing"><?php echo __('Existing Tab','wp-responsive-holiday-events-calendar');?></option>
						<option value="new"><?php echo __('New Tab','wp-responsive-holiday-events-calendar');?></option>
					</select><?php

					// Add button
					?><label></label>
					<button id="add" class="button"><?php echo __('Add Holiday/Event','wp-responsive-holiday-events-calendar');?></button>
				</div>
			</div>

			<div class="holiday-list" >
				<div class="bulkaction-msg"><?php

					//Display bulkaction massage
					if($bulk == 'updated'){
						echo '<div class="updated notice notice-success is-dismissible"><p>'.__('Holidays updated successfully!','wp-responsive-holiday-events-calendar').'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
					}

					if($bulk == 'deleted'){
						echo '<div class="deleted notice notice-success is-dismissible"><p>'.__('Holidays deleted successfully!','wp-responsive-holiday-events-calendar').'</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
					}

					// if getting any error related update or delete
					if(!empty($arrError)){
						echo '<div class="updated error notice-error is-dismissible">';
						foreach($arrError as $key => $error){
							echo '<p>'.$error.'</p>';
						}
						echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
					}
				?></div>
				<form method="post" id="holiday">

					<!--  Display holiday listing  -->
					<div class="tablenav top">
						<div class="alignleft actions bulkactions">
							<label for="bulk-action-selector-top" class="screen-reader-text"><?php echo ('Select bulk action');?></label>
							<select name="bulkaction" id="bulkaction">
								<option value=""><?php echo __('Bulk Actions','wp-responsive-holiday-events-calendar');?></option>
								<option value="updateall"><?php echo __('Update','wp-responsive-holiday-events-calendar');?></option>
								<option value="deleteall"><?php echo __('Delete','wp-responsive-holiday-events-calendar');?></option>
							</select>
							<button type="submit" name="submitbulk" id="submitbulk" class="button "><?php echo __('Apply','wp-responsive-holiday-events-calendar');?></button>
						</div>
					</div>
					<table class="wp-list-table widefat fixed striped pages" style="border:1px solid #ccc;">
						<thead>
							<tr>
								<td class="manage-column column-cb check-column"><input class="selectall" type="checkbox"></td>
								<th class="manage-column column-title column-primary"><?php
									echo __('Name','wp-responsive-holiday-events-calendar');
								?></th>
								<th class="manage-column column-manager column-primary" width="190">Date</th>
								<th class="manage-column column-manager column-primary"><?php
									echo __('Url','wp-responsive-holiday-events-calendar');
								?></th>
								<th class="manage-column column-manager column-primary" width="120"><?php
									echo __('Open Url','wp-responsive-holiday-events-calendar');
								?></th>
								<th class="manage-column column-phone column-primary" width="120"><?php
									echo __('Actions','wp-responsive-holiday-events-calendar');
								?></th>
							</tr>
						</thead><?php
						//Get holiday
						$holiday=get_option('whec_holiday_list');
						if(!empty($holiday)){
							$i = 0;

							// sort by date
							usort($holiday, 'date_compare');

							update_option('whec_holiday_list', $holiday);
							foreach($holiday as $key => $data){
								?><tr class="border-cell"><?php
									echo '<td align="center"><input type="checkbox" name="selected[]" class="selectrow" value="'.esc_attr($key).'"></td>';
									echo '<td align="center" valign="top"><input type="text" name="title'. esc_attr($key) .'" class="title" value="'. stripslashes($data['title']) .'" /></td>';
									echo '<td align="center" valign="top"><input readonly type="text" name="date'. esc_attr($key).'" class="date" value="'. $data["date"] .'" /></td>';
									echo '<td align="center" valign="top"><input type="text" name="url'. esc_attr($key) .'" class="url" value="'. esc_url($data['url']) .'" /></td>';
									if($data['target'] == 'new'){
										$select= 'selected="selected"';
									}
									else{
										$select= '';
									}
									echo '<td align="center" valign="top">
												<select name="target'. esc_attr($key) .'" class="target ">
												<option value="existing">'.__('Existing Tab','wp-responsive-holiday-events-calendar').'</option>
												<option value="new"' .$select.'>'.__('New Tab','wp-responsive-holiday-events-calendar').'</option>
											</select>
										</td>';
									echo '<td>
											<div class="row-actions" style="left:0;">
												<input type="hidden" class="holidayid" value="'. esc_attr($key) .'">
												<span class="">
													<a href="javascript:;" id="1" class="edit">'.__('Update','wp-responsive-holiday-events-calendar').'</a>
												</span>
												<span class="trash">
													 | <a href="javascript:;" class="remove">'.__('Delete','wp-responsive-holiday-events-calendar').'</a>
												</span>
											</div>
										</td>';
								$i++;
								?></tr><?php
							}
						}
						?><tfoot>
							<tr>
								<td class="manage-column column-cb check-column"><input class="selectall" type="checkbox"></td>
								<th class="manage-column column-title column-primary"><?php
									echo __('Name','wp-responsive-holiday-events-calendar');
								?></th>
								<th class="manage-column column-manager column-primary" width="190"><?php
									echo __('Date','wp-responsive-holiday-events-calendar');
								?></th>
								<th class="manage-column column-manager column-primary"><?php
									echo __('Url','wp-responsive-holiday-events-calendar');
								?></th>
								<th class="manage-column column-manager column-primary" width="120"><?php
									echo __('Open Url','wp-responsive-holiday-events-calendar');
								?></th>
								<th class="manage-column column-phone column-primary" width="120"><?php
									echo __('Actions','wp-responsive-holiday-events-calendar');
								?></th>
							</tr>
						</tfoot>
					</table>
					<input type="hidden" name="whec_nonce" value="<?php echo wp_create_nonce('check_holoday_nonce'); ?>"/>
				</form>
			</div>
		</div>
		<div class="holiday-option holiday-tab-list" id="holiday-option" >
			<div class="row" style="margin:0;">
				<div class="width30 col-sm-4">
					<div class="option-form-group form">
						<label><?php echo ('View');?></label>
						<select class="view" style="width:45%;" >
							<option value="view1"><?php echo __('List View','wp-responsive-holiday-events-calendar'); ?></option>
							<option value="view2"><?php echo __('Grid View','wp-responsive-holiday-events-calendar'); ?></option>
							<option value="view3"><?php echo __('List Hover View','wp-responsive-holiday-events-calendar'); ?></option>
							<option value="view4"><?php echo __('Calendar View','wp-responsive-holiday-events-calendar'); ?></option>
						</select>
					</div>
					<div class="option-form-group">
						<label><?php echo __('Date Font color','wp-responsive-holiday-events-calendar'); ?></label>
						<input type="text" class="date_font_color jscolor" style="height:35px;" >
					</div>
					<div class="option-form-group">
						<label><?php echo __('Text Font Color','wp-responsive-holiday-events-calendar'); ?></label>
						<input type="text" class="text_font_color jscolor" style="height:35px;" value="#000">
					</div>
					<div class="option-form-group">
						<label><?php echo __('Date Background Color','wp-responsive-holiday-events-calendar'); ?></label>
						<input type="text" class="date_bg jscolor" style="height:35px;" value="#2e4d7b">
					</div>
					<div class="option-form-group">
						<label><?php echo __('Text Background Color','wp-responsive-holiday-events-calendar'); ?></label>
						<input type="text" class="text_bg jscolor" style="height:35px;" value="#d3deef ">
					</div>
					<div class="option-form-group text-right" style="padding:5px 20px 0 0;">
						<button id="preview" class="button button-primary" style="margin-right:10px;height: 37px;padding:5px 15px;"><?php echo ('Preview'); ?></button>
						<button id="genrate" class="button button-primary" style="height: 37px;padding:5px 15px;"><?php echo ('Generate Short Code'); ?></button>
					</div>
				</div>
				<div class="col-sm-8 previewdiv">
					<div class="preview-msg"><?php echo __('Display Preview','wp-responsive-holiday-events-calendar'); ?></div>
					<!-- All View  -->
					<div class="width70 view1" id="view1" style="display:none">
						<div class="row">
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row media ">
									<div class="date media-left media-middle">
										<div class="day">14</div>
										<div class="year"><?php _e('Jan','wp-responsive-holiday-events-calendar'); ?>,  2017</div>
									</div>
									<div class="title media-body media-middle">Kite Flying Day </div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row media ">
									<div class="date media-left media-middle">
										<div class="day">26</div>
										<div class="year"><?php _e('Jan','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="title media-body media-middle">Republic Day</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row media ">
									<div class="date media-left media-middle">
										<div class="day">14</div>
										<div class="year"><?php _e('Feb','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="title media-body media-middle">Valentine's Day</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row media ">
									<div class="date media-left media-middle">
										<div class="day">14</div>
										<div class="year"><?php _e('Apr','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="title media-body media-middle">Good Friday</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row media ">
									<div class="date media-left media-middle">
										<div class="day">15</div>
										<div class="year"><?php _e('Aug','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="title media-body media-middle">Independence Day</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row media ">
									<div class="date media-left media-middle">
										<div class="day">25</div>
										<div class="year"><?php _e('Dec','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="title media-body media-middle">Christmas</div>
								</div>
							</div>
						</div>
					</div>
					<div class="width70" id="view2" style="display:none">
						<div class="row">
							<div class="col-md-3 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">14</div>
										<div class="year"><?php _e('Jan','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="display-title">
										<div class="title">Kite Flying Day </div>
									</div>
								</div>
							</div>
							<div class="col-md-3 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">26</div>
										<div class="year"><?php _e('Jan','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="display-title">
										<div class="title">Republic Day</div>
									</div>
								</div>
							</div>
							<div class="col-md-3 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">14</div>
										<div class="year"><?php _e('Feb','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="display-title">
										<div class="title">Valentine's Day</div>
									</div>
								</div>
							</div>
							<div class="col-md-3 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">14</div>
										<div class="year"><?php _e('Apr','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="display-title">
										<div class="title">Good Friday</div>
									</div>
								</div>
							</div>
							<div class="col-md-3 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">25</div>
										<div class="year"><?php _e('Dec','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="display-title">
										<div class="title">Christmas</div>
									</div>
								</div>
							</div>
							<div class="col-md-3 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">15</div>
										<div class="year"><?php _e('Aug','wp-responsive-holiday-events-calendar'); ?>,  2017</div>
									</div>
									<div class="display-title">
										<div class="title">Independence Day</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="width70" id="view3" style="display:none">
						<div class="row">
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">14</div>
										<div class="year"><?php _e('Jan','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="holiday-hover">
										<div class="holiday-hover-title"><div class="text-center">Kite Flying Day </div></div>
									</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">26</div>
										<div class="year"><?php _e('Jan','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="holiday-hover">
										<div class="holiday-hover-title"><div class="text-center">Republic Day</div></div>
									</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">14</div>
										<div class="year"><?php _e('Feb','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="holiday-hover">
										<div class="holiday-hover-title"><div class="text-center">Valentine's Day</div></div>
									</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">14</div>
										<div class="year"><?php _e('Apr','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="holiday-hover">
										<div class="holiday-hover-title"><div class="text-center">Good Friday</div></div>
									</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">15</div>
										<div class="year"><?php _e('Aug','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="holiday-hover">
										<div class="holiday-hover-title"><div class="text-center">Independence Day</div></div>
									</div>
								</div>
							</div>
							<div class="col-md-4 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day">25</div>
										<div class="year"><?php _e('Dec','wp-responsive-holiday-events-calendar'); ?>, 2017</div>
									</div>
									<div class="holiday-hover">
										<div class="holiday-hover-title"><div class="text-center">Christmas</div></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="width70" id="view4" >
						<div id='vsz-calendar'></div>
					</div>
				</div>
			</div>
			<div class="shortcode-display" style="display:none;">
				<input type="text" value='' class="inputselect">
			</div>
		</div>
		<!-- display calendar -->
		<div class="calendejs"></div>
	</div>
	<?php
	$locale  = get_locale();
	$pos = strpos($locale, '_');
	if($pos === false){
		$sitelocale  = get_locale();
	}else{
		$localearray  = explode('_',$locale);
		if(strtolower($localearray[0]) == strtolower($localearray[1])){
			$sitelocale = $localearray[0];
		}
		else{
			$sitelocale = $locale;
		}
	}
	?>
	<script>
	jQuery(document).ready(function(){

		var alreadydisplaymsg = 0;

		//Genrate Shortcode
		jQuery('#genrate').click(function(){
			jQuery('.shortcode-display').css('display','block');
			jQuery('.inputselect').val("[vsz_responsive_holiday_events_calendar view="+jQuery('.view').val()+"  date_color="+jQuery('.date_font_color').val()+" date_bg="+jQuery('.date_bg').val()+" text_color="+jQuery('.text_font_color').val()+" text_bg="+jQuery('.text_bg').val()+"]");
			jQuery('.inputselect').select();

		});

		jQuery('.inputselect').focus(function(){
			jQuery('.inputselect').select();
		});

		//Display Preview and Set text and bg color
		jQuery("#preview").click(function(){
			jQuery('.preview-msg').css('display','none');
			jQuery(".width70").css("display","none");
			var view = jQuery('.view').val();
			jQuery('#'+view).css('display','inline-block');
			jQuery(".width70 .date").attr('style','color:#'+jQuery('.date_font_color').val()+';background:'+'#'+jQuery('.date_bg').val());
			jQuery(".width70 .title").attr('style','color:#'+jQuery('.text_font_color').val()+';background:#'+jQuery('.text_bg').val());
			jQuery("#view3 .holiday-row").css("border-color","#"+jQuery('.text_bg').val());
			jQuery("#view3 .holiday-row .holiday-hover").attr('style','color:#'+jQuery('.text_font_color').val()+';background:#'+jQuery('.text_bg').val());
			jQuery("#view3 .holiday-row").css("border-color","#"+jQuery('.text_bg').val());
			jQuery("#view4 .fc hr,#view4 .fc tbody,#view4 .fc td,#view4 .fc th,#view4 .fc thead,#view4 .fc-row").attr('style','color:#'+jQuery('.date_font_color').val()+';background:'+'#'+jQuery('.date_bg').val()+'!important');
			jQuery("#view4 .fc-event-container .fc-event").attr('style','color:#'+jQuery('.text_font_color').val()+';background:#'+jQuery('.text_bg').val());
		});

		jQuery(document).live("click",".fc-button-prev",function(){
			jQuery("#view4 .fc hr,#view4 .fc tbody,#view4 .fc td,#view4 .fc th,#view4 .fc thead,#view4 .fc-row").attr('style','color:#'+jQuery('.date_font_color').val()+'!important;background:'+'#'+jQuery('.date_bg').val()+'!important');
			jQuery("#view4 .fc-event-container .fc-event").attr('style','color:#'+jQuery('.text_font_color').val()+'!important;background:#'+jQuery('.text_bg').val()+'!important');
		});

		//To tab
		jQuery(".holiday-tab a").click(function(){
			jQuery('.holiday-tab-list').css('display','none');
			jQuery(jQuery(this).attr('href')).css('display','block');
			jQuery(".holiday-tab a").removeClass('holiday-tab-active');
			jQuery(this).addClass('holiday-tab-active');
			return false;
		});

		// To initiate the jscolor fields
		jQuery( document ).ajaxComplete( function(){
			jscolor.installByClassName("jscolor");
		});

		// set initial parameters
		var checkdel = 0;
		var checkclick = 0;
		var checksubmit = 0;

		// When form submitted
		jQuery('#holiday').submit(function(){
			// To validate the title
			jQuery('.holiday-list').find('.selectrow').each(function(){
				if(jQuery(this).prop("checked") == true){
					var checkempty = jQuery(this).parent().parent().find('.title').val().trim();
					if(checkempty == ''){
						jQuery(this).parent().parent().find('.title').css('border','1px solid red');
						checkdel=0;
					}
					else{
						jQuery(this).parent().parent().find('.title').css('border','3px solid #d4deef');
					}
					checkclick=1;
				}
			});
			if(checkdel == 0 || checkclick == 0){
				if(alreadydisplaymsg == 0 && checkdel == 1){
					alert("Select Any One To Update Or Delete");
					alreadydisplaymsg = 1;
				}
				return false;
			}
		});

		// To check for any action selected
		jQuery('#submitbulk').click(function(){
			if(jQuery('#bulkaction').val() == ''){
				return false;
			}
			if(!confirm("Are you sure you want to perform this action?") == true){
				return false;
			}
			checkdel=1;
			alreadydisplaymsg = 0;
			jQuery('#holiday').submit();
		});

		// To initialize the date picker
		var dateToday = new Date();
		jQuery('input.date').datepicker({
			dateFormat : "yy-mm-dd",
			changeMonth: true, changeYear: true,minDate: 0
		});

		// For add case
		jQuery('#add').click(function(){
			var title = jQuery(".title").val().trim();
			var date = 	jQuery(".date").val().trim();

			if(title == '' || date == ''){
				if(date == '' ){
					jQuery(this).parent().find('.date').css('border','1px solid red');
				}
				else{
					jQuery(this).parent().find('.date').css('border','1px solid #ddd');
				}
				if(title == '' ){
					jQuery(this).parent().find('.title').css('border','1px solid red');
					jQuery(this).parent().find('.title').focus();
				}else{
					jQuery(this).parent().find('.title').css('border',' 1px solid #ddd');
				}
				return false;
			}

			var url = jQuery(".url").val().trim();

			jQuery(this).parent().find('.title').css('border','1px solid #ddd');
			jQuery(this).parent().find('.date').css('border','1px solid #ddd');
			jQuery(this).parent().find('.date').val('');
			jQuery(this).parent().find('.title').val('');
			jQuery(this).parent().find('.url').val('');

			var target = jQuery(".target option:selected").val();

			// Creating nonce
			var checkNonce = '<?php echo wp_create_nonce('check_holoday_nonce'); ?>';

			// Calling ajax
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {'title':title,'date':date,'target':target,'url':url,'action':'vsz_holiday_storedata','checknonce':checkNonce},
				success: function(data){
					if(data == 't_error'){
						alert("Please Enter title");
						return false;
					}
					if(data == 'd_error'){
						alert("Please Enter Valid Date");
						return false;
					}
					if(data == 'error'){
						alert("Your entry not added successfully, Please try again.");
						return false;
					}


					alert("Holiday added successfully");
					jQuery('.holiday-list form table').append(data);
					jQuery('input.date').datepicker({
						dateFormat : "yy-mm-dd",
						changeMonth: true, changeYear: true,
					});
					jQuery('.selectall').click(function(){
						if(jQuery(this).prop("checked") == true){
							jQuery('.selectrow').attr('checked','true');
						}
						else{
							jQuery('.selectrow').removeAttr('checked');
						}
					});
					jQuery('#submitbulk').click(function(){
						checkdel=1;
						jQuery('#holiday').submit();
					});
				}
			});
		});

		// For delete case
		jQuery('.remove').live('click',function(){
			if (confirm("Are you sure you want to delete this holiday?") != true) {
				return false;
			}

			var dataraw=jQuery(this).parent().parent().parent().parent();
			var id = jQuery(this).parent().parent().find('.holidayid').val();
			var checkNonce = '<?php echo wp_create_nonce('check_holoday_nonce'); ?>';

			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {'id':id, 'action':'vsz_holiday_storedata','checknonce':checkNonce},
				success: function(data){
					if(data == 'del_error'){
						alert("Your entry not deleted successfully, Please try again.");
						return false;
					}
					dataraw.remove();
					alert("Holiday deleted successfully");
				}
			});
		});

		// For edit case
		jQuery('.edit').live('click',function(){
			var dataraw=jQuery(this).parent().parent().parent().parent();
			var id = jQuery(this).parent().parent().find('.holidayid').val();
			var title = jQuery(this).parent().parent().parent().parent().find('.title').val().trim();
			var date = jQuery(this).parent().parent().parent().parent().find('.date').val().trim();
			var url = jQuery(this).parent().parent().parent().parent().find('.url').val().trim();
			var target = jQuery(this).parent().parent().parent().parent().find('.target option:selected').val().trim();

			if(title == '' || date == ''){
				jQuery(this).parent().parent().find('.title').css('border','1px solid red');
				jQuery(this).parent().parent().find('.title').focus();
				return false;
			}

			jQuery(this).parent().parent().find('.title').css('border','3px solid #d4deef');

			var edit = 1;
			var checkNonce = '<?php echo wp_create_nonce('check_holoday_nonce'); ?>';

			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {'id':id,'title':title,'date':date,'edit':edit,'url':url,'target':target,'action':'vsz_holiday_storedata','checknonce':checkNonce},
				success: function(data){
					if(data == 't_error'){
						alert("Please Enter title");
						return false;
					}
					if(data == 'd_error'){
						alert("Please Enter Valid Date");
						return false;
					}
					if(data == 'error'){
						alert("Your entry not updated successfully, Please try again.");
						return false;
					}
					alert("Holiday updated successfully");
				}
			});
		});

		// To select / unselect all
		jQuery('.selectall').click(function(){
			if(jQuery(this).prop("checked") == true){
				jQuery('.selectrow').attr('checked','true');
			}
			else{
				jQuery('.selectrow').removeAttr('checked');
			}
		});

		//View 5 Calender
		jQuery('#vsz-calendar').fullCalendar({
			 locale: '<?php echo $sitelocale;?>',
			header: {
				left: 'prev,next,today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			defaultDate: '2017-01-14',
			editable: true,
			eventLimit: true, // allow "more" link when too many events
			events: [
				{
					title: 'Kite Flying Day',
					start: '2017-01-14'
				},
				{
					title: 'Republic Day',
					start: '2017-01-26'
				},
				{
					title: "Valentine's Day",
					start: '2017-02-14'
				},
				{
					title: 'Good Friday',
					start: '2017-04-14'
				},
				{
					title: 'Independence Day',
					start: '2017-08-15'
				},
				{
					title: 'Christmas',
					start: '2017-12-25'
				},
			]
		});

		jQuery('#holiday-option').css('display','none');
		jQuery('#view4').css('display','none');
	});

	</script><?php
}

// for date compare
function date_compare($a, $b){
	$t1 = strtotime($a['date']);
	$t2 = strtotime($b['date']);
	return $t1 - $t2;
}

/////////////////// To call ajax to update holiday ///////////////////
add_action('wp_ajax_vsz_holiday_storedata','wp_responsive_holiday_events_calendar_storedata');

// Callback function for holiday ajax call
function wp_responsive_holiday_events_calendar_storedata(){
	require_once plugin_dir_path(__FILE__)."storedata.php";
}

/////////////////// To register shortcode ///////////////////
add_action( 'after_setup_theme','wp_responsive_holiday_events_calendar_shortcodes' );

/////////////////// Callback for shortcode ///////////////////
function wp_responsive_holiday_events_calendar_shortcodes(){

	add_shortcode( 'vsz_responsive_holiday_events_calendar',  'wp_responsive_holiday_events_calendar_display_front' );
}

/////////////////// callback function to display html for shortcode ///////////////////
global $shortcode_counter;
$shortcode_counter = 1;

function wp_responsive_holiday_events_calendar_display_front($atts, $content, $name){
	ob_start();

	//Increment dynamic number for shortcode calles
	global $shortcode_counter;

	$shortcode_counter++;
	$holiday = get_option('whec_holiday_list');

	//sort by parent_id in descending order
	$my_array = whec_multi_sort($holiday, "date", true);

	// adding css file
	wp_register_style( 'holiday-display-front-css', plugin_dir_url( __FILE__ ) . 'assets/css/holiday_display_front.css' );
	wp_enqueue_style( 'holiday-display-front-css' );
	wp_enqueue_style( 'jquery-ui' );
	wp_enqueue_style( 'bootstrap-holiday', plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap.min.css', 'all' );
	wp_enqueue_script( 'bootstrap-holiday', plugin_dir_url( __FILE__ ) . 'assets/js/bootstrap.min.js', array( 'jquery' ),'', false );

	//If calender view then load calender css and js
	if($atts['view'] == 'view4' ){
		wp_register_style( 'fullcalendar.min', plugin_dir_url( __FILE__ ) . 'assets/css/fullcalendar.min.css' );
		wp_enqueue_style( 'fullcalendar.min' );
		wp_register_style( 'fullcalendar.print.min', plugin_dir_url( __FILE__ ) . 'assets/css/fullcalendar.print.min.css' );
		wp_enqueue_style( 'fullcalendar.print.min' );
		wp_register_script('moment.min',plugin_dir_url(__FILE__)."assets/js/moment.min.js",true);
		wp_enqueue_script('moment.min');
		wp_register_script('fullcalendar.min',plugin_dir_url(__FILE__)."assets/js/fullcalendar.min.js",true);
		wp_enqueue_script('fullcalendar.min');
		//wp_enqueue_script('rumin',plugin_dir_url(__FILE__)."assets/js/ru.js",true);
		wp_enqueue_script('locale-all',plugin_dir_url(__FILE__)."assets/js/locale-all.js",true);
	}

	?><style>
		#holiday-calender<?php echo $shortcode_counter;?> .fc button, .fc-button-group, .fc-time-grid .fc-event .fc-time span {
			display:block!important;
		}
		#holiday-calender<?php echo $shortcode_counter;?> .holiday-row .date{
			color:#<?php echo $atts['date_color'];?>!important;
			background:#<?php echo $atts['date_bg'];?>!important;
		}
		#holiday-calender<?php echo $shortcode_counter;?> .holiday-row .title,#holiday-calender<?php echo $shortcode_counter;?> .holiday-row .title a{
			color:#<?php echo $atts['text_color'];?>!important;
			background:#<?php echo $atts['text_bg'];?>!important;
		}
		#holiday-calender<?php echo $shortcode_counter;?> #view3 .holiday-row{
			border-color:#<?php echo $atts['text_bg'];?>!important;
		}
		#holiday-calender<?php echo $shortcode_counter;?> #view3 .holiday-row .holiday-hover,#holiday-calender<?php echo $shortcode_counter;?> #view3 .holiday-row .holiday-hover a,#holiday-calender<?php echo $shortcode_counter;?> #view4 .fc-event-container .fc-event{
			color:#<?php echo $atts['text_color'];?>!important;
			background:#<?php echo $atts['text_bg'];?>!important;
		}
		#holiday-calender<?php echo $shortcode_counter;?> #view4 .fc hr,#holiday-calender<?php echo $shortcode_counter;?> #view4 .fc tbody,#holiday-calender<?php echo $shortcode_counter;?> #view4 .fc td,#holiday-calender<?php echo $shortcode_counter;?> #view4 .fc th,#holiday-calender<?php echo $shortcode_counter;?> #view4 .fc thead,#holiday-calender<?php echo $shortcode_counter;?> #view4 .fc-row{
			color:#<?php echo $atts['date_color'];?>!important;
			background:#<?php echo $atts['date_bg'];?>!important;
		}
		#view2 .display-title{
			display:table;
			width:100%;
		}
		#view2 .title{
			min-height:90px;
			display: table-cell;
			height: 120px;
			min-height: 90px;
			vertical-align: middle;
			width: 100%;
			padding:40px 20px;
		}
		#view4 .fc-event .fc-content {
			padding: 10px;
			position: relative;
			z-index: 2;
		}
		 .fc td, .fc th {
			border-style: solid;
			border-width: 1px;
			padding: 0!important;
			vertical-align: top;
		}
		#view4 table {
			margin:0px!important;
		}
		#view4 table .fc-day-top.fc-today span{
			background: #f1f1f1 none repeat scroll 0 0;
			border-radius: 50%;
			color: #000;
			height: 25px;
			line-height: 2;
			text-align: center;
			width: 25px;
		}
		#holiday-calender<?php echo $shortcode_counter;?>  #view4 table .fc-widget-header.fc-today{
			background:#f1f1f1!important;
			color:#000!important;
		}
		#view4 .fc-content-skeleton table{
			min-height:130px;
		}
		.holiday-row .title a{
			text-decoration:none;
		}
		.holiday-row .title a:hover{
			text-decoration:underline;
		}
	</style>
	<div id="holiday-calender<?php echo $shortcode_counter;?>">
		<div class="row <?php echo $atts['view'];?>" id="<?php echo $atts['view'];?>"><?php
			$checkdublicatedate = 0;

			if($atts['view'] != 'view4'){
				foreach($my_array as $data)
				{
					 if($checkdublicatedate != 0){
						if($data['date'] == $retrivedata[$checkdublicatedate - 1]['date']){
							if(!empty($retrivedata[$checkdublicatedate - 1]['url']))
							{
								if($retrivedata[$checkdublicatedate - 1]['target'] == 'new'){
									$retrivedata[$checkdublicatedate - 1]['title']= '<a href="'.$retrivedata[$checkdublicatedate - 1]['url'].'" target=" "_blank">'.$retrivedata[$checkdublicatedate - 1]['title'].'</a>';
								}
								else{
									$retrivedata[$checkdublicatedate - 1]['title']= '<a href="'.$retrivedata[$checkdublicatedate - 1]['url'].'" target=" "_self">'.$retrivedata[$checkdublicatedate - 1]['title'].'</a>';
								}
							}
							if(!empty($data['url']))
							{
								if($data['target'] == 'new'){
									$data['title']= '<a href="'.$data['url'].'" target=" "_blank">'.$data['title'].'</a>';
								}
								else{
									$data['title'] = '<a href="'.$data['url'].'" target=" "_self">'.$data['title'].'</a>';
								}
							}
							$data['title'] =$retrivedata[$checkdublicatedate - 1]['title'].',<br/>'.$data['title'];
							unset($retrivedata[$checkdublicatedate - 1]);
							$retrivedata[] = array('date' => $data['date'],'title' => $data['title']);
						}
						else{
							$retrivedata[] = array('date' => $data['date'],'title' => $data['title'],'url'=>$data['url'],'target'=>$data['target']);
						}
					}else{
						$retrivedata[] = array('date' => $data['date'],'title' => $data['title'],'url'=>$data['url'],'target'=>$data['target']);
					}
					$checkdublicatedate = $checkdublicatedate + 1;
				}
			}

			foreach($retrivedata as $data){

				$date = new DateTime($data['date']);
				if($atts['view'] == 'view1'){
				?><div class="col-md-4 col-sm-6 ">
					<div class="holiday-row media ">
						<div class="date media-left media-middle">
							<div class="day"><?php echo $date->format('d');?></div>
							<div class="year"><?php echo __($date->format('M'),'wp-responsive-holiday-events-calendar');?>,<?php echo $date->format('Y');?></div>
						</div>

						<div class="title media-body media-middle vsz-equlheight-view1">
						<?php
							$title = stripslashes($data['title']);
							if(!empty($data['url'])){
								?><a href="<?php echo $data['url'];?>" target="<?php if($data['target'] == 'new'){ echo "_blank";}else{ echo "_self"; }?>"><?php echo $title;?></a>
								<?php }
								else{
									echo $title;
								}
						?>
						</div>
					</div>
				</div><?php }
				else if($atts['view'] == 'view2'){
				?>	<div class="col-md-2 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day"><?php echo $date->format('d');?></div>
										<div class="year"><?php echo __($date->format('M'),'wp-responsive-holiday-events-calendar');?>,<?php echo $date->format('Y');?></div>
									</div>
									<div class="display-title">
										<div class="title vsz-equlheight"><?php
							$title = stripslashes($data['title']);
							if(!empty($data['url'])){
								?><a href="<?php echo $data['url'];?>" target="<?php if($data['target'] == 'new'){ echo "_blank";}else{ echo "_self"; }?>"><?php echo $title;?></a>
								<?php }
								else{
									echo $title;
								}
						?></div>
									</div>
								</div>
					</div><?php }
				else if($atts['view'] == 'view3'){
				?><div class="col-md-3 col-sm-6 ">
								<div class="holiday-row ">
									<div class="date">
										<div class="day"><?php echo $date->format('d');?></div>
										<div class="year"><?php echo __($date->format('M'),'wp-responsive-holiday-events-calendar');?>,<?php echo $date->format('Y');?></div>
									</div>
									<div class="holiday-hover">
										<div class="holiday-hover-title"><div class="text-center"><?php
							$title = stripslashes($data['title']);
							if(!empty($data['url'])){
								?><a href="<?php echo $data['url'];?>" target="<?php if($data['target'] == 'new'){ echo "_blank";}else{ echo "_self"; }?>"><?php echo $title;?></a>
								<?php }
								else{
									echo $title;
								}
						?></div></div>
									</div>
								</div>
				</div><?php }
			}

			if($atts['view'] == 'view4' ){

				$locale  = get_locale();
				$pos = strpos($locale, '_');
				if($pos === false){
					$sitelocale  = get_locale();
				}else{
					$localearray  = explode('_',$locale);
					if(strtolower($localearray[0]) == strtolower($localearray[1])){
						$sitelocale = $localearray[0];
					}
					else{
						$sitelocale = strtolower($locale);
					}
				}

				?><div class='vsz-calendar'></div>
				<script  type="text/javascript">
					jQuery(document).ready(function(){
						jQuery('.vsz-calendar').fullCalendar({
							 locale: '<?php echo $sitelocale;?>',
							header: {
								left: 'prev,next,today',
								center: 'title',
								right: 'month,agendaWeek,agendaDay'
							},
							defaultDate: '<?php echo date('Y-m-d');?>',
							editable: true,
							eventLimit: true, // allow "more" link when too many events
							events: [
								<?php
								// Pass holiday data
								foreach($my_array as $data){ ?>
										{
											title:'<?php echo $data['title'];?>',
											start: '<?php echo $data['date'];?>',
											url:'<?php echo $data['url'];?>',
											target:'<?php if($data['target'] == 'new'){ echo "_blank";}else{ echo "_self"; }?>'
										},
								<?php } ?>

							],
							eventClick: function(event) {
								if (event.url) {
									window.open(event.url, event.target);
									return false;
								}
							}
						});
					});
				</script><?php
			}
		?></div>
	</div>

	<script type="text/javascript">
		equalheight = function(container){
			var currentTallest = 0,
				currentRowStart = 0,
				rowDivs = new Array(),
				$el,
				topPosition = 0;

			jQuery(container).each(function() {

				$el = jQuery(this);
				jQuery($el).height('auto');

				topPostion = $el.position().top;

				if (currentRowStart != topPostion) {
					for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
						rowDivs[currentDiv].height(currentTallest);
					}
					rowDivs.length = 0; // empty the array
					currentRowStart = topPostion;
					currentTallest = $el.height();
					rowDivs.push($el);
				}else {
					rowDivs.push($el);
					currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
				}

				for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
					rowDivs[currentDiv].height(currentTallest);
				}
			});
		}

		jQuery(document).ready(function(){
			equalheight('.vsz-equlheight');
			equalheight('.vsz-equlheight-view1');

			var resizeTimer;
			jQuery(window).on('resize', function(e) {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function() {
									equalheight('.vsz-equlheight');
									equalheight('.vsz-equlheight-view1');
								}, 400);
			});
		});
	</script><?php

	$result = ob_get_contents(); // get everything in to $result variable
    ob_end_clean();

	return $result;
}

// function to get multi sortable values
function whec_multi_sort(&$array, $key, $asc=true){
	$sorter = new whec_array_sorter($array, $key, $asc);	// Create new object for "array_sorter" class
	return $sorter->whec_sortit();
}

////// Class for multi sortable values
/***************************************************		"array_sorter" CLASS STARTS		*****************************************************************/
class whec_array_sorter{

	var $skey = false;
	var $sarray = false;
	var $sasc = true;

	/*** Constructor */
	function whec_array_sorter(&$array, $key, $asc=true)
	{
		$this->sarray = $array;
		$this->skey = $key;
		$this->sasc = $asc;
	}

	/*** Sort method */
	function whec_sortit($remap=true)
	{
		$array = &$this->sarray;
		uksort($array, array($this, "whec_as_cmp"));
		if ($remap)
		{
			$tmp = array();
			while (list($id, $data) = each($array))
				$tmp[] = $data;
			return $tmp;
		}
		return $array;
	}

	/*** Custom sort function */
	function whec_as_cmp($a, $b)
	{
		//since uksort will pass here only indexes get real values from our array
		if (!is_array($a) && !is_array($b))
		{
			$a = $this->sarray[$a][$this->skey];
			$b = $this->sarray[$b][$this->skey];
		}

		//if string - use string comparision
		if (!ctype_digit($a) && !ctype_digit($b))
		{
			if ($this->sasc)
				return strcasecmp($a, $b);
			else
				return strcasecmp($b, $a);
		}
		else
		{
			if (intval($a) == intval($b))
				return 0;

			if ($this->sasc)
				return (intval($a) > intval($b)) ? -1 : 1;
			else
				return (intval($a) > intval($b)) ? 1 : -1;
		}
	}
}

/***************************************************		"array_sorter" CLASS ENDS		*****************************************************************/

/***************************************************		"Holiday" CLASS STARTS		*****************************************************************/
//////////////// This is class for widget of "Holiday Calender"
class Whec_Holiday extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct( false, 'Holiday Calender' );
	}

	function widget( $args, $instance ) {

		// Overall parameters
		$title = '';
		$header_background_color = '';
		$month_year_dropdown_color = '';
		$month_year_text_color = '';
		$calender_box_background_color = '';
		$calender_box_text_color = '';
		$holiday_date_background_color = '';
		$holiday_date_text_color = '';
		$holiday_tooltip_background_color = '';
		$holiday_tooltip_text_color = '';
		$title = apply_filters( 'widget_title', $instance['title'] );
		$header_background_color = apply_filters( 'widget_title', $instance['header_background_color'] );
		$month_year_dropdown_color = apply_filters( 'widget_title', $instance['month_year_dropdown_color'] );
		$month_year_text_color = apply_filters( 'widget_title', $instance['month_year_text_color'] );
		$calender_box_background_color = apply_filters( 'calender_box_background_color', $instance['calender_box_background_color'] );
		$calender_box_text_color = apply_filters( 'widget_title', $instance['calender_box_text_color'] );
		$holiday_date_background_color = apply_filters( 'widget_title', $instance['holiday_date_background_color'] );
		$holiday_date_text_color = apply_filters( 'widget_title', $instance['holiday_date_text_color'] );
		$holiday_tooltip_background_color = apply_filters( 'widget_title', $instance['holiday_tooltip_background_color'] );
		$holiday_tooltip_text_color = apply_filters( 'widget_title', $instance['holiday_tooltip_text_color'] );

		// enque styles and scripts
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_register_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'assets/css/jquery-ui.css',array(),'all' );
		wp_register_style( 'holiday-widget-css', plugin_dir_url( __FILE__ ) . 'assets/css/holiday_widget.css','all' );
		wp_enqueue_style( 'holiday-widget-css' );
		wp_enqueue_style( 'jquery-ui' );
		wp_enqueue_style( 'bootstrap-holiday', plugin_dir_url( __FILE__ ) . 'assets/css/bootstrap.min.css', 'all' );
		wp_enqueue_script( 'bootstrap-holiday', plugin_dir_url( __FILE__ ) . 'assets/js/bootstrap.min.js', array( 'jquery' ),'', false );

		// Widget output
		$holiday=get_option('whec_holiday_list');
		?><style>
			/* For Header */
			select::-ms-expand {
				display: none;
			}
			.ll-skin-lugo .ui-datepicker-header {
				background: #<?php echo $header_background_color; ?> ! important;
				color: #<?php echo $font_color; ?> ! important;
			}
			.ll-skin-cangas .ui-datepicker-header {
				background-color: #<?php echo $header_background_color; ?> ! important;
				color: #<?php echo $font_color; ?> ! important;
			}

			.ll-skin-cangas td .ui-state-active,
			.ll-skin-cangas td .ui-state-hover {
				background-color: #<?php echo $header_background_color; ?> ! important;
				color: #<?php echo $font_color; ?> ! important;
			}

			/* For month & year select boxes */
			.ui-datepicker-month, .ui-datepicker-year {
				-webkit-appearance:none;-moz-appearance:none;
				background-color: #<?php echo $month_year_dropdown_color; ?> ! important;
				color: #<?php echo $month_year_text_color; ?> ! important;
			}

			/* For previous next arrows */
			.ll-skin-cangas .ui-datepicker-header .ui-state-hover {
				background-color: #<?php echo $month_year_dropdown_color; ?> ! important;
				color: #<?php echo $month_year_text_color; ?> ! important;
			}

			.alignCenter .ui-datepicker-calendar,
			.alignCenter .ui-datepicker-calendar td,
			.alignCenter .ui-datepicker-calendar td a{
				background-color: #<?php  echo $calender_box_background_color; ?>;
				color: #<?php  echo $calender_box_text_color; ?> ! important;
			}
			.alignCenter .ui-datepicker-calendar th{
				background-color: #<?php  echo $calender_box_background_color; ?> ! important;
				color: #<?php  echo $calender_box_text_color; ?> ! important;
			}
			.alignCenter .ui-datepicker-calendar td a:hover,
			.alignCenter .ui-datepicker-calendar td a:focus,
			.alignCenter .ui-datepicker-calendar td a:active{
				background-color: #<?php  echo $calender_box_text_color; ?> ! important;
				color: #<?php  echo $calender_box_background_color; ?> ! important;
			}

			/* For holiday */
			.alignCenter .ui-datepicker-calendar td.Highlighted a{
				background-color : #<?php echo $holiday_date_background_color; ?>;
				color: #<?php echo $holiday_date_text_color; ?> ! important;
			}

			/* For tooltip */
			.tooltip-inner{
				background-color : #<?php echo $holiday_tooltip_background_color; ?> ! important;
				color: #<?php echo $holiday_tooltip_text_color; ?> ! important;
			}
			.tooltip.top .tooltip-arrow{
				border-top-color: #<?php echo $holiday_tooltip_background_color; ?> ! important;
			}
			.tooltip.right .tooltip-arrow{
				border-right-color: #<?php echo $holiday_tooltip_background_color; ?> ! important;
			}
			.tooltip.bottom .tooltip-arrow{
				border-bottom-color: #<?php echo $holiday_tooltip_background_color; ?> ! important;
			}
			.tooltip.left .tooltip-arrow{
				border-left-color: #<?php echo $holiday_tooltip_background_color; ?> ! important;
			}

		</style>
		<div class="row">
			<div class="col-sm-4">
				<div class="displayTitle"><?php
					echo $title ;
				?></div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-4">
				<div class="txtDate ll-skin-cangas alignCenter"></div>
			</div>
		</div>
		<script>
		jQuery(document).ready(function() {
			var SelectedDates = {};
			var SeletedText = {};

			<?php

			$olddata = array();
			$alldata = array();
			$title = array();

			// create array of holiday and set this holiday in datepicker
			foreach($holiday as $data){
				$title = array();
				if(in_array($data['date'],$olddata)){

					foreach($alldata as $result){
						if($result['date'] == $data['date'] && $result['title'] != $data['title']){
							$title[] = $result['title'];
						}
					}
					$displaytitle['title'] = implode(' \u000d  ', array_unique($data['title'],SORT_STRING ));
				}
				else{
					$displaytitle['title'] = $data['title'];
				}

				$olddata[] = $data['date'];
				$alldata[] = array('date'=>$data['date'], 'title'=>$data['title']);

				$date = new DateTime($data['date']);

				?>

				SelectedDates['<?php echo date($date->format('m').'/'.$date->format('d').'/'.$date->format('Y')); ?>'] = '<?php echo date($date->format('m').'/'.$date->format('d').'/'.$date->format('Y')); ?>';
				SeletedText['<?php echo date($date->format('m').'/'.$date->format('d').'/'.$date->format('Y')); ?>'] = '<?php echo $displaytitle['title']; ?>';

				<?php

			}

			?>

			jQuery('.txtDate').datepicker({
				changeMonth: true,
				changeYear: true,
				beforeShowDay: function(date) {
					var month = ('0' + (date.getMonth() + 1)).slice(-2);
					var day = ('0' + date.getDate()).slice(-2);
					var year = date.getFullYear();
					date = month + "/" + day + "/" + year;

					var Highlight = SelectedDates[date];
					var HighlighText = SeletedText[date];

					if (Highlight) {
						return [true, "Highlighted", HighlighText];
					}
					else {
						return [true, '', ''];
					}
				},
				onselect : function( dateText, inst ) {

					setTimeout(function() {
						jQuery(".Highlighted").tooltip('destroy');
						jQuery('.Highlighted').tooltip({container: 'body',trigger: "hover"});
					}, 500);

				},
				onChangeMonthYear : function( year,  month,  inst ) {

					setTimeout(function() {
						jQuery(".Highlighted").tooltip('destroy');
						jQuery('.Highlighted').tooltip({container: 'body'});
						jQuery('.txtDate a').click(false);
					}, 500);

				},
			});
			jQuery('.Highlighted').tooltip({container: 'body'});
			jQuery('.ui-state-default').click(function(){
				return false;
			});
		});
		</script><?php
	}

	function update( $new_instance, $old_instance ) {

		// Save widget options
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['header_background_color'] = ( ! empty( $new_instance['header_background_color'] ) ) ? strip_tags( $new_instance['header_background_color'] ) : 'a3d143';
		$instance['month_year_dropdown_color'] = ( ! empty( $new_instance['month_year_dropdown_color'] ) ) ? strip_tags( $new_instance['month_year_dropdown_color'] ) : '8dc532';
		$instance['month_year_text_color'] = ( ! empty( $new_instance['month_year_text_color'] ) ) ? strip_tags( $new_instance['month_year_text_color'] ) : 'ffffff';
		$instance['calender_box_background_color'] = ( ! empty( $new_instance['calender_box_background_color'] ) ) ? strip_tags( $new_instance['calender_box_background_color'] ) : 'c1c1c1';
		$instance['calender_box_text_color'] = ( ! empty( $new_instance['calender_box_text_color'] ) ) ? strip_tags( $new_instance['calender_box_text_color'] ) : '222222';
		$instance['holiday_date_background_color'] = ( ! empty( $new_instance['holiday_date_background_color'] ) ) ? strip_tags( $new_instance['holiday_date_background_color'] ) : '008000';
		$instance['holiday_date_text_color'] = ( ! empty( $new_instance['holiday_date_text_color'] ) ) ? strip_tags( $new_instance['holiday_date_text_color'] ) : 'ffffff';
		$instance['holiday_tooltip_background_color'] = ( ! empty( $new_instance['holiday_tooltip_background_color'] ) ) ? strip_tags( $new_instance['holiday_tooltip_background_color'] ) : '000000';
		$instance['holiday_tooltip_text_color'] = ( ! empty( $new_instance['holiday_tooltip_text_color'] ) ) ? strip_tags( $new_instance['holiday_tooltip_text_color'] ) : 'ffffff';
		return $instance;
	}

	function form( $instance ) {
		// Output admin widget options form
		wp_register_script('js-colorpicker',plugin_dir_path(__FILE__)."assets/js/jscolor.js",true);
		wp_enqueue_script('js-colorpicker');

		$title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
		$header_background_color = isset( $instance[ 'header_background_color' ] ) ? $instance[ 'header_background_color' ] : 'a3d143';
		$month_year_dropdown_color = isset( $instance[ 'month_year_dropdown_color' ] ) ? $instance[ 'month_year_dropdown_color' ] : '8dc532';
		$month_year_text_color = isset( $instance[ 'month_year_text_color' ] ) ? $instance[ 'month_year_text_color' ] : 'ffffff';
		$calender_box_background_color = isset( $instance[ 'calender_box_background_color' ] ) ? $instance[ 'calender_box_background_color' ] : 'c1c1c1';
		$calender_box_text_color = isset( $instance[ 'calender_box_text_color' ] ) ? $instance[ 'calender_box_text_color' ] : '222222';
		$holiday_date_background_color = isset( $instance[ 'holiday_date_background_color' ] ) ? $instance[ 'holiday_date_background_color' ] : '008000';
		$holiday_date_text_color = isset( $instance[ 'holiday_date_text_color' ] ) ? $instance[ 'holiday_date_text_color' ] : 'ffffff';
		$holiday_tooltip_background_color = isset( $instance[ 'holiday_tooltip_background_color' ] ) ? $instance[ 'holiday_tooltip_background_color' ] : '000000';
		$holiday_tooltip_text_color = isset( $instance[ 'holiday_tooltip_text_color' ] ) ? $instance[ 'holiday_tooltip_text_color' ] : 'ffffff';
		?><p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo 'Title:'; ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'header_background_color' ); ?>"><?php echo 'Header Background Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'header_background_color' ); ?>" name="<?php echo $this->get_field_name( 'header_background_color' ); ?>" type="text" value="<?php echo esc_attr( $header_background_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'month_year_dropdown_color' ); ?>"><?php echo 'Month & Year Dropdown Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'month_year_dropdown_color' ); ?>" name="<?php echo $this->get_field_name( 'month_year_dropdown_color' ); ?>" type="text" value="<?php echo esc_attr( $month_year_dropdown_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'month_year_text_color' ); ?>"><?php echo 'Month & Year Text Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'month_year_text_color' ); ?>" name="<?php echo $this->get_field_name( 'month_year_text_color' ); ?>" type="text" value="<?php echo esc_attr( $month_year_text_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'calender_box_background_color' ); ?>"><?php echo 'Calender Box Background Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'calender_box_background_color' ); ?>" name="<?php echo $this->get_field_name( 'calender_box_background_color' ); ?>" type="text" value="<?php echo esc_attr( $calender_box_background_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'calender_box_text_color' ); ?>"><?php echo 'Calender Box Text Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'calender_box_text_color' ); ?>" name="<?php echo $this->get_field_name( 'calender_box_text_color' ); ?>" type="text" value="<?php echo esc_attr( $calender_box_text_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'holiday_date_background_color' ); ?>"><?php echo 'Holiday Date Background Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'holiday_date_background_color' ); ?>" name="<?php echo $this->get_field_name( 'holiday_date_background_color' ); ?>" type="text" value="<?php echo esc_attr( $holiday_date_background_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'holiday_date_text_color' ); ?>"><?php echo 'Holiday Date Text Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'holiday_date_text_color' ); ?>" name="<?php echo $this->get_field_name( 'holiday_date_text_color' ); ?>" type="text" value="<?php echo esc_attr( $holiday_date_text_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'holiday_tooltip_background_color' ); ?>"><?php echo 'Holiday Tooltip Background Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'holiday_tooltip_background_color' ); ?>" name="<?php echo $this->get_field_name( 'holiday_tooltip_background_color' ); ?>" type="text" value="<?php echo esc_attr( $holiday_tooltip_background_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'holiday_tooltip_text_color' ); ?>"><?php echo 'Holiday Tooltip Text Color:'; ?></label>
			<input class="widefat jscolor" id="<?php echo $this->get_field_id( 'holiday_tooltip_text_color' ); ?>" name="<?php echo $this->get_field_name( 'holiday_tooltip_text_color' ); ?>" type="text" value="<?php echo esc_attr( $holiday_tooltip_text_color ); ?>" onclick="this.jscolor.show();" />
		</p>
		<script>
			// To re-initiate the jscolor fields
			jQuery(document).ready(function(){
				jQuery( document ).ajaxComplete( function(){
					jscolor.installByClassName("jscolor");
				});
			});
		</script><?php
	}
}

/***************************************************		"Holiday" CLASS ENDS		*****************************************************************/

// Register widget "Holiday Calender" callback
function wp_responsive_holiday_events_calendar_register_widgets() {
	register_widget( 'Whec_Holiday' );
}
// hook to register widget
add_action( 'widgets_init', 'wp_responsive_holiday_events_calendar_register_widgets' );
