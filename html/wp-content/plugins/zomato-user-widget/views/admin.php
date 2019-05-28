<p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'user_id' ); ?>"><?php _e( 'Registered email id:' ); ?></label>
  <input id="<?php echo $this->get_field_id( 'user_id' ); ?>" name="<?php echo $this->get_field_name( 'user_id' ); ?>" value="<?php echo $user_id; ?>" style="width:100%;"/>
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'lang' ); ?>"><?php _e( 'Language:' ) ?></label>
  <select id="<?php echo $this->get_field_id( 'lang' ); ?>" name="<?php echo $this->get_field_name( 'lang' ); ?>" class="widefat" style="width:100%;">
    <option value="en" <?php if ( $lang == 'en' ) echo 'selected="selected"'; ?>>English</option>
    <option value="id" <?php if ( $lang == 'id' ) echo 'selected="selected"'; ?>>Indonesian</option>
    <option value="tr" <?php if ( $lang == 'tr' ) echo 'selected="selected"'; ?>>Turkish</option>
    <option value="pt" <?php if ( $lang == 'pt' ) echo 'selected="selected"'; ?>>Portuguese</option>
    <option value="es" <?php if ( $lang == 'es' ) echo 'selected="selected"'; ?>>Spanish</option>
  </select>
</p>
