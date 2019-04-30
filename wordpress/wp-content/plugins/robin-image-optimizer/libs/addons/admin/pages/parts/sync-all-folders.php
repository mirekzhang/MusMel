<p id="wio-sync-progress-msg"><?php _e( 'Directory synchronization in progress', 'robin-image-optimizer' ); ?></p>
<p id="wio-sync-success-msg" style="display:none;"><?php _e( 'Synchronization completed successfully.', 'robin-image-optimizer' ); ?></p>
<?php
$cf      = WRIO_Custom_Folders::get_instance();
$folders = $cf->getFolders();
?>
<div class="wio-sync-dirs">
	<?php foreach ( $folders as $folder ) : ?>
        <div class="wio-sync-dir">
            <p><?php echo esc_attr( $folder->get( 'path' ) ); ?></p>
            <div class="progress" style="">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
                </div>
            </div>
        </div>
	<?php endforeach; ?>
</div>
<input type="hidden" value="<?php echo wp_create_nonce( 'wio-iph' ) ?>" id="wio-iph-nonce">
