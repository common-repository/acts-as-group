<?php

require_once AAG_PLUGIN_DIR . '/admin/admin-functions.php';

/* Menu */

add_action( 'admin_menu', 'aag_admin_menu' );

function aag_admin_menu() {
	add_action( 'load-index.php', 'aag_load_dashboard_admin' );

	$group_admin = add_users_page( __( 'Groups', 'aag' ), __( 'Groups', 'aag' ),
		'aag_join_groups', 'aag_groups', 'aag_admin_page_groups' );

	add_action( 'load-' . $group_admin, 'aag_load_groups_admin' );
}

/* Style & Script */

add_action( 'admin_enqueue_scripts', 'aag_admin_enqueue_scripts' );

function aag_admin_enqueue_scripts( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'aag' ) && 'index.php' != $hook_suffix )
		return;

	wp_enqueue_script( 'aag-admin',
		aag_plugin_url( 'admin/script.js' ),
		array( 'postbox' ), AAG_VERSION, true );

	wp_enqueue_style( 'aag-admin',
		aag_plugin_url( 'admin/style.css' ),
		array(), AAG_VERSION, 'all' );
}

/* Updated Message */

add_action( 'aag_admin_updated_message', 'aag_admin_updated_message' );

function aag_admin_updated_message() {
	if ( ! empty( $_REQUEST['message'] ) ) {
		if ( 'groupadded' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Group created.', 'aag' ) );
		elseif ( 'groupupdated' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Group updated.', 'aag' ) );
		elseif ( 'grouptrashed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Groups trashed.', 'aag' ) );
		elseif ( 'groupuntrashed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Groups restored.', 'aag' ) );
		elseif ( 'groupdeleted' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Groups deleted.', 'aag' ) );
		else
			return;
	} else {
		return;
	}

	if ( empty( $updated_message ) )
		return;

?>
<div id="message" class="updated"><p><?php echo $updated_message; ?></p></div>
<?php
}

/* Dashboard */

add_action( 'wp_dashboard_setup', 'aag_dashboard_setup' );

function aag_dashboard_setup() {
	require_once AAG_PLUGIN_DIR . '/admin/includes/dashboard.php';

	wp_add_dashboard_widget( 'aag_direct_messages_dashboard_widget',
		__( 'Direct Messages', 'aag' ), 'aag_direct_messages_dashboard_widget' );
}

function aag_load_dashboard_admin() {
	$action = aag_current_action();

	$redirect_to = admin_url( 'index.php' );

	if ( 'aag_direct_message' == $action ) {

		if ( ! current_user_can( 'aag_direct_message' ) )
			wp_die( __( 'You are not allowed to send direct messages.', 'aag' ) );

		check_admin_referer( 'aag-direct-message' );

		$sender = wp_get_current_user();
		$recipient = get_userdata( absint( $_POST['aag_dm_recipient'] ) );

		$post = new AAG_Message();

		$post->title = sprintf( __( 'DM from %1$s to %2$s', 'aag' ),
			$sender->user_login, $recipient->user_login );

		$post->from = $sender->user_login;
		$post->to = $recipient->user_login;
		$post->content = trim( $_POST['aag_direct_message'] );

		$post->save();

		wp_safe_redirect( $redirect_to );
		exit();
	}
}

/* Groups */

function aag_load_groups_admin() {
	$action = aag_current_action();

	$redirect_to = menu_page_url( 'aag_groups', false );

	if ( 'add' == $action || 'save' == $action ) {
		$post = ( 'save' == $action )
			? new AAG_Group( $_REQUEST['post'] )
			: new AAG_Group();

		if ( ! empty( $post ) ) {
			if ( $post->initial ) {
				if ( ! current_user_can( 'aag_edit_groups' ) )
					wp_die( __( 'You are not allowed to edit this item.', 'aag' ) );

				check_admin_referer( 'aag-add-group' );
			} else {
				if ( ! current_user_can( 'aag_edit_group', $post->id ) )
					wp_die( __( 'You are not allowed to edit this item.', 'aag' ) );

				check_admin_referer( 'aag-update-group_' . $post->id );
			}

			$post->title = trim( $_POST['post_title'] );
			$post->description = trim( $_POST['group_description'] );
			$post->status = in_array( $_POST['group_status'], array( 'open', 'closed' ) )
				? $_POST['group_status'] : 'open';

			$post->save();

			$redirect_to = add_query_arg( array(
				'post' => $post->id,
				'message' => 'add' == $action ? 'groupadded' : 'groupupdated' ), $redirect_to );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'trash' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'aag-trash-group_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$trashed = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new AAG_Group( $post );

			if ( empty( $post ) || $post->initial )
				continue;

			if ( ! current_user_can( 'aag_delete_group', $post->id ) )
				wp_die( __( 'You are not allowed to move this item to the Trash.', 'aag' ) );

			if ( ! $post->trash() )
				wp_die( __( 'Error in moving to Trash.', 'aag' ) );

			$trashed += 1;
		}

		if ( ! empty( $trashed ) )
			$redirect_to = add_query_arg( array( 'message' => 'grouptrashed' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'untrash' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'aag-untrash-group_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$untrashed = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new AAG_Group( $post );

			if ( empty( $post ) || $post->initial )
				continue;

			if ( ! current_user_can( 'aag_delete_group', $post->id ) )
				wp_die( __( 'You are not allowed to restore this item from the Trash.', 'aag' ) );

			if ( ! $post->untrash() )
				wp_die( __( 'Error in restoring from Trash.', 'aag' ) );

			$untrashed += 1;
		}

		if ( ! empty( $untrashed ) )
			$redirect_to = add_query_arg( array( 'message' => 'groupuntrashed' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'delete_all' == $action ) {
		$_REQUEST['post'] = aag_get_all_ids_in_trash( AAG_Group::post_type );
		$action = 'delete';
	}

	if ( 'delete' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'aag-delete-group_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$deleted = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new AAG_Group( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'aag_delete_group', $post->id ) )
				wp_die( __( 'You are not allowed to delete this item.', 'aag' ) );

			if ( ! $post->delete() )
				wp_die( __( 'Error in deleting.', 'aag' ) );

			$deleted += 1;
		}

		if ( ! empty( $deleted ) )
			$redirect_to = add_query_arg( array( 'message' => 'groupdeleted' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( empty( $_GET['post'] ) ) {
		$current_screen = get_current_screen();

		if ( ! class_exists( 'AAG_Groups_List_Table' ) )
			require_once AAG_PLUGIN_DIR . '/admin/includes/class-groups-list-table.php';

		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'AAG_Groups_List_Table', 'define_columns' ) );

		add_screen_option( 'per_page', array(
			'label' => __( 'Groups', 'aag' ),
			'default' => 20 ) );
	}
}

function aag_admin_page_groups() {
	if ( ! empty( $_REQUEST['post'] ) ) {
		$post_id = $_REQUEST['post'];

		if ( 'new' == $post_id && current_user_can( 'aag_edit_groups' ) )
			$post = new AAG_Group();
		elseif ( AAG_Group::post_type == get_post_type( $post_id ) )
			$post = new AAG_Group( $_GET['post'] );

		if ( ! empty( $post ) && current_user_can( 'aag_edit_group', $post->id ) ) {
			aag_group_edit_page( $post->id );
			return;
		}
	}

	$list_table = new AAG_Groups_List_Table();
	$list_table->prepare_items();

?>
<div class="wrap">
<?php screen_icon( 'users' ); ?>

<h2><?php
	echo esc_html( __( 'Groups', 'aag' ) );

	if ( current_user_can( 'aag_edit_groups' ) )
		echo ' <a href="' . esc_url( add_query_arg( array( 'post' => 'new' ), menu_page_url( 'aag_groups', false ) ) ) . '" class="add-new-h2">' . esc_html( __( 'Add New', 'aag' ) ) . '</a>';

	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf( '<span class="subtitle">'
			. __( 'Search results for &#8220;%s&#8221;', 'aag' )
			. '</span>', esc_html( $_REQUEST['s'] ) );
	}
?></h2>

<?php do_action( 'aag_admin_updated_message' ); ?>

<?php $list_table->views(); ?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Groups', 'aag' ), 'aag-group' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
}

function aag_group_edit_page( $post_id ) {
	$post = new AAG_Group( $post_id );

	require_once AAG_PLUGIN_DIR . '/admin/includes/meta-boxes.php';
	include AAG_PLUGIN_DIR . '/admin/edit-group-form.php';
}

?>