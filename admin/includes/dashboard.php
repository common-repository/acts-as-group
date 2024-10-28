<?php

function aag_direct_messages_dashboard_widget() {
	$current_user = wp_get_current_user();

	$meta_query = array(
		'relation' => 'OR',
		0 => array( 'key' => '_from', 'value' => $current_user->user_login ),
		1 => array( 'key' => '_to', 'value' => $current_user->user_login ) );

	$messages = AAG_Message::find( array(
		'meta_query' => $meta_query,
		'orderby' => 'date',
		'order' => 'DESC',
		'posts_per_page' => 50 ) );

	$alt = 0;
?>
<div class="aag-messages">
<?php
	foreach ( $messages as $message ) {
		$alt = 1 - $alt;
		echo '<div class="message' . ( $alt ? ' alt' : '' ) . '">';
		echo '<div class="meta">'
			. sprintf( '%1$s &raquo; %2$s', $message->from, $message->to )
			. ' &emsp; ' . $message->date . '</div>';
		echo apply_filters( 'the_content', $message->content );
		echo '</div>';
	}
?>
</div>
<?php

	$users = get_users( array(
		'exclude' => get_current_user_id(),
		'fields' => array( 'ID', 'user_login', 'display_name' ) ) );

	$user_select = '';

	foreach ( $users as $user ) {
		$user_select .= '<option value="' . absint( $user->ID ) . '">' . esc_html( $user->user_login ) . '</option>';
	}

	$user_select = '<select name="aag_dm_recipient" id="aag_dm_recipient">' . $user_select . '</select>';

?>
<form method="post" action="">
<p><?php echo sprintf( __( 'Send a direct message to %s', 'aag' ), $user_select ); ?></p>

<div class="textarea-wrap">
<textarea name="aag_direct_message" id="aag_direct_message"></textarea>
</div>

<p class="submit textright">
<input type="hidden" name="action" value="aag_direct_message" />
<?php wp_nonce_field( 'aag-direct-message' ); ?>
<input type="submit" class="button-primary" value="<?php echo esc_attr( __( 'Send', 'aag' ) ); ?>" />
</p>
</form>
<?php
}

?>