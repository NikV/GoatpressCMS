<?php
/**
 * Install theme network administration panel.
 *
 * @package Goatpress
 * @subpackage Multisite
 * @since 3.1.0
 */

if ( isset( $_GET['tab'] ) && ( 'theme-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

/** Load Goatpress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multisite() )
	gp_die( __( 'Multisite support is not enabled.' ) );

require( ABSPATH . 'gp-admin/theme-install.php' );
