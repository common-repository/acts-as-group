<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function aag_delete_plugin() {
	global $wpdb;

	if ( isset( $wpdb->base_prefix ) ) { // WPMU
		$prefix = $wpdb->base_prefix;
	} else {
		$prefix = $wpdb->prefix;
	}

	$tables = array( 'groups', 'group_members', 'group_messages' );

	foreach ( $tables as $table ) {
		$table = $prefix . $table;
		$wpdb->query( "DROP TABLE IF EXISTS $table" );
	}

	delete_option( 'aag' );

	$posts = get_posts( array(
		'numberposts' => -1,
		'post_type' => array( 'aag_group', 'aag_message' ),
		'post_status' => 'any' ) );

	foreach ( $posts as $post )
		wp_delete_post( $post->ID, true );
}

aag_delete_plugin();

?>