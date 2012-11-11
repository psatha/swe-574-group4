<?php 
$checked = $inGallery === 1;
?>
<label for="in-gallery-yes-<?php echo $post->ID; ?>"><input <?php checked($checked,true); ?> type="radio" name="attachments[<?php echo $post->ID; ?>][in-gallery]" id="in-gallery-yes-<?php echo $post->ID; ?>" value="1" /><?php _e( 'Yes' ); ?></label> 
<label for="in-gallery-no-<?php echo $post->ID; ?>"><input <?php checked($checked,false); ?> type="radio" name="attachments[<?php echo $post->ID; ?>][in-gallery]" id="in-gallery-no-<?php echo $post->ID; ?>" value="0" /><?php _e( 'No' ); ?></label>