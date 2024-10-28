<?php

add_filter( 'map_meta_cap', 'aag_map_meta_cap', 10, 4 );

function aag_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'aag_edit_group' => 'edit_users',
		'aag_edit_groups' => 'edit_users',
		'aag_delete_group' => 'edit_users',
		'aag_delete_groups' => 'edit_users',
		'aag_join_groups' => 'read',
		'aag_direct_message' => 'read' );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) )
		$caps[] = $meta_caps[$cap];

	return $caps;
}

?>