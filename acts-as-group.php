<?php
/*
Plugin Name: Acts as Group
Plugin URI: http://ideasilo.wordpress.com/2009/07/28/acts-as-group/
Description: Acts as Group provides a means for communication on multi-user blogs.
Author: Takayuki Miyoshi
Author URI: http://ideasilo.wordpress.com/
Version: 2.0
*/

/*  Copyright 2009-2012 Takayuki Miyoshi (email: takayukister at gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'AAG_VERSION', '2.0' );

if ( ! defined( 'AAG_PLUGIN_BASENAME' ) )
	define( 'AAG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'AAG_PLUGIN_NAME' ) )
	define( 'AAG_PLUGIN_NAME', trim( dirname( AAG_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'AAG_PLUGIN_DIR' ) )
	define( 'AAG_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'AAG_PLUGIN_URL' ) )
	define( 'AAG_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

require_once AAG_PLUGIN_DIR . '/includes/functions.php';
require_once AAG_PLUGIN_DIR . '/includes/capabilities.php';
require_once AAG_PLUGIN_DIR . '/includes/class-group.php';
require_once AAG_PLUGIN_DIR . '/includes/class-message.php';

if ( is_admin() )
	require_once AAG_PLUGIN_DIR . '/admin/admin.php';

/* Init */

add_action( 'init', 'aag_init' );

function aag_init() {

	/* L10N */
	load_plugin_textdomain( 'aag', false, 'acts-as-group/languages' );

	/* Custom Post Types */
	AAG_Group::register_post_type();
	AAG_Message::register_post_type();

	do_action( 'aag_init' );
}

/* Upgrading */

add_action( 'aag_init', 'aag_upgrade' );

function aag_upgrade() {
	$opt = get_option( 'aag' );

	if ( ! is_array( $opt ) )
		$opt = array();

	$old_ver = isset( $opt['version'] ) ? (string) $opt['version'] : '0';
	$new_ver = AAG_VERSION;

	if ( $old_ver == $new_ver )
		return;

	do_action( 'aag_upgrade', $new_ver, $old_ver );

	$opt['version'] = $new_ver;

	update_option( 'aag', $opt );
}

?>