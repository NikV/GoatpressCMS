<?php
/**
 * Update/Install Plugin/Theme network administration panel.
 *
 * @package Goatpress
 * @subpackage Multisite
 * @since 3.1.0
 */

if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'update-selected', 'activate-plugin', 'update-selected-themes' ) ) )
	define( 'IFRAME_REQUEST', true );

/** Load Goatpress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multisite() )
	gp_die( __( 'Multisite support is not enabled.' ) );

require( ABSPATH . 'gp-admin/update.php' );
