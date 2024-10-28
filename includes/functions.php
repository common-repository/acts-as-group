<?php

function aag_plugin_path( $path = '' ) {
	return path_join( AAG_PLUGIN_DIR, trim( $path, '/' ) );
}

function aag_plugin_url( $path = '' ) {
	$url = untrailingslashit( AAG_PLUGIN_URL );

	if ( ! empty( $path ) && is_string( $path ) && false === strpos( $path, '..' ) )
		$url .= '/' . ltrim( $path, '/' );

	return $url;
}

?>