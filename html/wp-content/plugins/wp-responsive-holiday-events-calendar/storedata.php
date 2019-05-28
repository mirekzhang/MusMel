<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

if(isset($_POST['checknonce']) && !empty($_POST['checknonce'])){
	////// checking for nonce
	if( ! wp_verify_nonce($_POST['checknonce'], 'check_holoday_nonce')){
		wp_die(__("You don't have permission to view this page","wp-responsive-holiday-events-calendar"));
		exit;
	}

	// Edit case
	if(isset($_POST['edit'])){

		// For id
		if(isset($_POST['id']) && is_numeric($_POST['id'])){
			$id = $_POST['id'];
		}
		else{
			echo 'error';
			exit;
		}

		// For title
		if(isset($_POST['title']) && !empty($_POST['title'])){
			$title = sanitize_text_field($_POST['title']);
		}
		else{
			echo 't_error';
			exit;
		}
		// For date
		if(isset($_POST['date']) && !empty($_POST['date'])){

			$date = ( strtotime( $_POST['date'] ) )? sanitize_text_field( $_POST['date'] ) : 'ERROR';
			if($date == 'ERROR'){
				echo 'd_error';
				exit;
			}
		}
		else{
			echo 'd_error';
			exit;
		}

		// get other parameters
		$url = esc_url($_POST['url']);
		$target = sanitize_text_field($_POST['target']);

		// get already stored holiday
		$holiday = get_option('whec_holiday_list');

		// if found related entry then update it
		if(isset($holiday[$id])){
			$holiday[$id]['title'] = $title;
			$holiday[$id]['date'] = $date;
			$holiday[$id]['url'] = $url;
			$holiday[$id]['target'] = $target;
			update_option('whec_holiday_list',$holiday);
		}
		else{
			echo 'error';
			exit;
		}
	}
	// Delete case
	else if(isset($_POST['id']) && is_numeric($_POST['id'])){

		// get already stored holiday
		$holiday = get_option('whec_holiday_list');
		$id = $_POST['id'];
		// check holiday key exist or not
		if(isset($holiday[$id])){
			unset($holiday[$id]);
			update_option('whec_holiday_list',$holiday);
		}
		else{
			echo 'del_error';
			exit;
		}
	}
	// Insert new holiday
	else{

		// For title
		if(isset($_POST['title']) && !empty($_POST['title'])){
			$title = sanitize_text_field($_POST['title']);
		}
		else{
			echo 't_error';
			exit;
		}
		// For date
		if(isset($_POST['date']) && !empty($_POST['date'])){
			$date = sanitize_text_field($_POST['date']);
			$date = ( strtotime( $_POST['date'] ) )? sanitize_text_field( $_POST['date'] ) : 'ERROR';
			if($date == 'ERROR'){
				echo 'd_error';
				exit;
			}
		}
		else{
			echo 'd_error';
			exit;
		}
		// get other parameters
		$url = esc_url($_POST['url']);
		$target = sanitize_text_field($_POST['target']);

		// get already stored holiday
		$holiday = get_option('whec_holiday_list');

		// make holiday entry
		$holiday[]=array("title"=> $title,"date" => $date,"url" => $url,"target" => $target);

		// add new holiday
		update_option('whec_holiday_list',$holiday);

		// Make holiday structure
		$keys = array_keys($holiday);
		$key=end($keys);
		if(!empty($holiday)){
			?><tr class="border-cell"><?php
				echo '<td align="center"><input type="checkbox" name="selected[]" class="selectrow" value="'. esc_attr($key) .'"></td>';
				echo '<td align="center" valign="top"><input type="text" name="title'. esc_attr($key) .'" class="title" value="'.stripslashes($holiday[$key]["title"]).'" /></td>';
				echo '<td align="center" valign="top"><input readonly type="text" name="date'. esc_attr($key) .'" class="date" value="'.$holiday[$key]["date"].'" /></td>';
				echo '<td align="center" valign="top"><input type="text" name="url'. esc_attr($key) .'" class="url" value="'.$holiday[$key]["url"].'" /></td>';
				if($holiday[$key]["target"] == 'new') { $select= 'selected="selected"'; } else { $select= ''; }
				echo '<td align="center" valign="top">
						<select name="target'. esc_attr($key) .'" class="target ">
							<option value="existing">'.__('Existing Tab','wp-responsive-holiday-events-calendar').'</option>
							<option value="new"' .$select.'>'.__('New Tab','wp-responsive-holiday-events-calendar').'</option>
						</select>
					</td>';
				echo '<td>
						<div class="row-actions" style="left:0;">
							<input type="hidden" class="holidayid" value="'. esc_attr($key) .'">
							<span class=""><a href="javascript:;" id="1" class="edit">'.__('Update','wp-responsive-holiday-events-calendar').'</a></span>
							<span class="trash"> | <a href="javascript:;" class="remove">'.__('Delete','wp-responsive-holiday-events-calendar').'</a></span>
						</div>
					</td>';

				$i++;
			?></tr><?php
		}
	}
}
wp_die();