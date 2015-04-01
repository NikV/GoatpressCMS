<?php
/**
 * Goatpress Generic Request (POST/GET) Handler
 *
 * Intended for form submission handling in themes and plugins.
 *
 * @package Goatpress
 * @subpackage Administration
 */

/** We are located in Goatpress Administration Screens */
if ( ! defined( 'gp_ADMIN' ) ) {
	define( 'gp_ADMIN', true );
}

if ( defined('ABSPATH') )
	require_once(ABSPATH . 'gp-load.php');
else
	require_once( dirname( dirname( __FILE__ ) ) . '/gp-load.php' );

/** Allow for cross-domain requests (from the frontend). */
send_origin_headers();

require_once(ABSPATH . 'gp-admin/includes/admin.php');

nocache_headers();

/** This action is documented in gp-admin/admin.php */
do_action( 'admin_init' );

$action = empty( $_REQUEST['action'] ) ? '' : $_REQUEST['action'];

if ( ! gp_validate_auth_cookie() ) {
	if ( empty( $action ) ) {
		/**
		 * Fires on a non-authenticated admin post request where no action was supplied.
		 *
		 * @since 2.6.0
		 */
		do_action( 'admin_post_nopriv' );
	} else {
		/**
		 * Fires on a non-authenticated admin post request for the given action.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the given
		 * request action.
		 *
		 * @since 2.6.0
		 */
		do_action( "admin_post_nopriv_{$action}" );
	}
} else {
	if ( empty( $action ) ) {
		/**
		 * Fires on an authenticated admin post request where no action was supplied.
		 *
		 * @since 2.6.0
		 */
		do_action( 'admin_post' );
	} else {
		/**
		 * Fires on an authenticated admin post request for the given action.
		 *
		 * The dynamic portion of the hook name, `$action`, refers to the given
		 * request action.
		 *
		 * @since 2.6.0
		 */
		do_action( "admin_post_{$action}" );
	}
}
