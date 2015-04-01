<?php
/**
 * Edit user network administration panel.
 *
 * @package Goatpress
 * @subpackage Multisite
 * @since 3.1.0
 */

/** Load Goatpress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multisite() )
	gp_die( __( 'Multisite support is not enabled.' ) );

require( ABSPATH . 'gp-admin/user-edit.php' );
