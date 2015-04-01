<?php
/**
 * Action handler for Multisite administration panels.
 *
 * @package Goatpress
 * @subpackage Multisite
 * @since 3.0.0
 */

/** Load Goatpress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! is_multisite() )
	gp_die( __( 'Multisite support is not enabled.' ) );

if ( empty( $_GET['action'] ) ) {
	gp_redirect( network_admin_url() );
	exit;
}

/**
 * Fires just before the action handler in several Network Admin screens.
 *
 * This hook fires on multiple screens in the Multisite Network Admin,
 * including Users, Network Settings, and Site Settings.
 *
 * @since 3.0.0
 */
do_action( 'gpmuadminedit' );

/**
 * Fires the requested handler action.
 *
 * The dynamic portion of the hook name, `$_GET['action']`, refers to the name
 * of the requested action.
 *
 * @since 3.1.0
 */
do_action( 'network_admin_edit_' . $_GET['action'] );

gp_redirect( network_admin_url() );
exit();
