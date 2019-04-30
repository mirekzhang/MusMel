<p id="wio-optimize-progress-msg"><?php _e( 'The images from the directory are being optimized.', 'robin-image-optimizer' ); ?></p>
<p id="wio-optimize-success-msg" style="display:none;"><?php _e( 'Optimization completed successfully.', 'robin-image-optimizer' ); ?></p>
<div class="progress" id="wio-optimize-progress" style="">
    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0;">
    </div>
</div>
<input type="hidden" value="<?php echo wp_create_nonce( 'wio-iph' ) ?>" id="wio-iph-nonce">
