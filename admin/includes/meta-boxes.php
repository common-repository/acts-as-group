<?php

function aag_group_submit_meta_box( $post ) {
	$status = $post->status;

	if ( ! in_array( $status, array( 'open', 'closed' ) ) )
		$status = 'open';

?>
<div class="submitbox" id="submitlink">
<div id="misc-publishing-actions">
<div class="misc-pub-section status-radio">
<label class="open"><input type="radio" name="group_status" value="open"<?php echo 'open' == $status ? ' checked="checked"' : ''; ?> /><?php echo esc_html( __( 'Open', 'aag' ) ); ?></label><br />
<label class="closed"><input type="radio" name="group_status" value="closed"<?php echo 'closed' == $status ? ' checked="checked"' : ''; ?> /><?php echo esc_html( __( 'Closed', 'aag' ) ); ?></label>
</div>
</div><!-- #misc-publishing-actions -->

<div id="major-publishing-actions">

<div id="publishing-action">
<?php if ( ! empty( $post->id ) ) : ?>
	<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="<?php echo esc_attr( __( 'Update Group', 'aag' ) ); ?>" />
<?php else : ?>
	<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="<?php echo esc_attr( __( 'Add Group', 'aag' ) ); ?>" />
<?php endif; ?>
</div>

<div class="clear"></div>
</div><!-- #major-publishing-actions -->

<div class="clear"></div>
</div>
<?php
}

function aag_group_description_meta_box( $post ) {
?>
<textarea id="group_description" name="group_description" cols="40" rows="1" tabindex="2"><?php echo esc_textarea( $post->description ); ?></textarea>
<?php
}

?>