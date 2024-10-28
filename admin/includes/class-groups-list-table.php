<?php

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class AAG_Groups_List_Table extends WP_List_Table {

	public static function define_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Title', 'aag' ),
			'founder' => __( 'Founder', 'aag' ),
			'date' => __( 'Date', 'aag' ) );

		return $columns;
	}

	function __construct() {
		parent::__construct( array(
			'singular' => 'post',
			'plural' => 'posts',
			'ajax' => false ) );
	}

	function prepare_items() {
		$current_screen = get_current_screen();
		$per_page = $this->get_items_per_page( $current_screen->id . '_per_page' );

		$this->_column_headers = $this->get_column_info();

		$args = array(
			'posts_per_page' => $per_page,
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page,
			'orderby' => 'date',
			'order' => 'DESC' );

		if ( ! empty( $_REQUEST['s'] ) )
			$args['s'] = $_REQUEST['s'];

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			if ( 'title' == $_REQUEST['orderby'] )
				$args['orderby'] = 'title';
			elseif ( 'founder' == $_REQUEST['orderby'] )
				$args['orderby'] = 'author';
			elseif ( 'date' == $_REQUEST['orderby'] )
				$args['orderby'] = 'date';
		}

		if ( ! empty( $_REQUEST['order'] ) && 'asc' == strtolower( $_REQUEST['order'] ) )
			$args['order'] = 'ASC';

		if ( ! empty( $_REQUEST['post_status'] ) && 'trash' == $_REQUEST['post_status'] )
			$args['post_status'] = 'trash';

		$this->items = AAG_Group::find( $args );

		$total_items = AAG_Group::$found_items;
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page ) );

		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';
	}

	function get_views() {
		$status_links = array();
		$post_status = empty( $_REQUEST['post_status'] ) ? '' : $_REQUEST['post_status'];

		$group_url = menu_page_url( 'aag_groups', false );

		// Inbox
		AAG_Group::find( array( 'post_status' => 'any' ) );
		$posts_in_inbox = AAG_Group::$found_items;

		$inbox = sprintf(
			_nx( 'Inbox <span class="count">(%s)</span>', 'Inbox <span class="count">(%s)</span>',
				$posts_in_inbox, 'posts', 'aag' ),
			number_format_i18n( $posts_in_inbox ) );

		$status_links['inbox'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			$group_url,
			'trash' != $post_status ? ' class="current"' : '',
			$inbox );

		// Trash
		AAG_Group::find( array( 'post_status' => 'trash' ) );
		$posts_in_trash = AAG_Group::$found_items;

		if ( empty( $posts_in_trash ) )
			return $status_links;

		$trash = sprintf(
			_nx( 'Trash <span class="count">(%s)</span>', 'Trash <span class="count">(%s)</span>',
				$posts_in_trash, 'posts', 'aag' ),
			number_format_i18n( $posts_in_trash ) );

		$status_links['trash'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			add_query_arg( array( 'post_status' => 'trash' ), $group_url ),
			'trash' == $post_status ? ' class="current"' : '',
			$trash );

		return $status_links;
	}

	function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	function get_sortable_columns() {
		$columns = array(
			'title' => array( 'title', false ),
			'founder' => array( 'founder', false ),
			'date' => array( 'date', true ) );

		return $columns;
	}

	function get_bulk_actions() {
		$actions = array();

		$actions['join'] = __( 'Join', 'aag' );
		$actions['leave'] = __( 'Leave', 'aag' );

		if ( $this->is_trash )
			$actions['untrash'] = __( 'Restore', 'aag' );

		if ( $this->is_trash || ! EMPTY_TRASH_DAYS )
			$actions['delete'] = __( 'Delete Permanently', 'aag' );
		else
			$actions['trash'] = __( 'Move to Trash', 'aag' );

		return $actions;
	}

	function extra_tablenav( $which ) {
?>
<div class="alignleft actions">
<?php
		if ( $this->is_trash && current_user_can( 'aag_delete_groups' ) ) {
			submit_button( __( 'Empty Trash', 'aag' ),
				'button-secondary apply', 'delete_all', false );
		}
?>
</div>
<?php
	}

	function column_default( $item, $column_name ) {
		return '';
    }

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->id );
	}

	function column_title( $item ) {
		$url = add_query_arg(
				array( 'post' => absint( $item->id ) ),
				menu_page_url( 'aag_groups', false ) );

		$actions = array();

		$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

		if ( current_user_can( 'aag_edit_group', $item->id ) ) {
			$actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit', 'aag' ) . '</a>';

			$a = sprintf( '<a class="row-title" href="%1$s" title="%2$s">%3$s</a>',
				$edit_link,
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'aag' ), $item->title ) ),
				esc_html( $item->title ) );
		} else {
			$a = esc_html( $item->title );
		}

		return '<strong>' . $a . '</strong> ' . $this->row_actions( $actions );
	}

	function column_founder( $item ) {
		$post = get_post( $item->id );

		if ( ! $post )
			return;

		$author = get_userdata( $post->post_author );

		return esc_html( $author->display_name );
	}

	function column_date( $item ) {
		$post = get_post( $item->id );

		if ( ! $post )
			return;

		$t_time = mysql2date( __( 'Y/m/d g:i:s A', 'aag' ), $post->post_date, true );
		$m_time = $post->post_date;
		$time = mysql2date( 'G', $post->post_date ) - get_option( 'gmt_offset' ) * 3600;

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < 24*60*60 )
			$h_time = sprintf( __( '%s ago', 'aag' ), human_time_diff( $time ) );
		else
			$h_time = mysql2date( __( 'Y/m/d', 'aag' ), $m_time );

		return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}
}

?>