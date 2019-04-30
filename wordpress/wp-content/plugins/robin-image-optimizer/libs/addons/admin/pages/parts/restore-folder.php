<p id="wio-restore-backup-progress-msg"><?php _e( 'Restore from backup', 'robin-image-optimizer' ); ?></p>
<p id="wio-restore-backup-success-msg" style="display:none;"><?php _e( 'Restore completed successfully.', 'robin-image-optimizer' ); ?></p>
<div class="progress" id="wio-restore-backup-progress" style="">
    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
    </div>
</div>
<input type="hidden" value="<?php echo wp_create_nonce( 'wio-iph' ) ?>" id="wio-iph-nonce">
