<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) )
	die( '-1' );

if ( $post->initial )
	$nonce_action = 'aag-add-group';
else
	$nonce_action = 'aag-update-group_' . $post->id;

add_meta_box( 'groupsubmitdiv', __( 'Save', 'aag' ),
	'aag_group_submit_meta_box', 'aag-group', 'side', 'core' );

add_meta_box( 'groupdescriptiondiv', __( 'Description', 'aag' ),
	'aag_group_description_meta_box', 'aag-group', 'normal', 'core' );

?>
<div class="wrap columns-2">
<?php screen_icon( 'users' ); ?>

<h2><?php
	echo esc_html( __( 'Edit Group', 'aag' ) );

	if ( current_user_can( 'aag_edit_groups' ) )
		echo ' <a href="' . esc_url( add_query_arg( array( 'post' => 'new' ), menu_page_url( 'aag_groups', false ) ) ) . '" class="add-new-h2">' . esc_html( __( 'Add New', 'aag' ) ) . '</a>';
?></h2>

<?php do_action( 'aag_admin_updated_message', $post ); ?>

<form name="editgroup" id="editgroup" method="post" action="">
<?php
wp_nonce_field( $nonce_action );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
?>

<div id="poststuff" class="metabox-holder has-right-sidebar">
<div id="side-info-column" class="inner-sidebar">
<?php
do_meta_boxes( 'aag-group', 'side', $post );
?>
</div><!-- #side-info-column -->

<div id="post-body">
<div id="post-body-content">

<div id="titlediv">
<div id="titlewrap">
<?php if ( $post->initial ) : ?>
<label class="hide-if-no-js" style="visibility:hidden" id="title-prompt-text" for="title"><?php echo esc_html( __( 'Enter group title here', 'aag' ) ); ?></label>
<input type="text" name="post_title" size="30" tabindex="1" value="<?php echo esc_attr( $post->title ); ?>" id="title" autocomplete="off" />
<?php else : ?>
<input type="text" name="post_title" size="30" tabindex="1" value="<?php echo esc_attr( $post->title ); ?>" id="title" />
<?php endif; ?>
</div>
</div>

<?php
do_meta_boxes( 'aag-group', 'normal', $post );
do_meta_boxes( 'aag-group', 'advanced', $post );
?>
</div><!-- #post-body-content -->
</div><!-- #post-body -->

<?php if ( $post->initial ) : ?>
<input type="hidden" name="action" value="add" />
<?php else: ?>
<input type="hidden" name="action" value="save" />
<input type="hidden" name="post" value="<?php echo (int) $post->id; ?>" />
<?php endif; ?>

</div><!-- #poststuff -->
</form>

</div><!-- .wrap -->