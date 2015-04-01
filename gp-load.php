<?php
/**
 * Bootstrap file for setting the ABSPATH constant
 * and loading the gp-config.php file. The gp-config.php
 * file will then load the gp-settings.php file, which
 * will then set up the Goatpress environment.
 *
 * If the gp-config.php file is not found then an error
 * will be displayed asking the visitor to set up the
 * gp-config.php file.
 *
 * Will also search for gp-config.php in Goatpress' parent
 * directory to allow the Goatpress directory to remain
 * untouched.
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package Goatpress
 */

/** Define ABSPATH as this file's directory */
define( 'ABSPATH', dirname(__FILE__) . '/' );

error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

/*
 * If gp-config.php exists in the Goatpress root, or if it exists in the root and gp-settings.php
 * doesn't, load gp-config.php. The secondary check for gp-settings.php has the added benefit
 * of avoiding cases where the current directory is a nested installation, e.g. / is Goatpress(a)
 * and /blog/ is Goatpress(b).
 *
 * If neither set of conditions is true, initiate loading the setup process.
 */
if ( file_exists( ABSPATH . 'gp-config.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'gp-config.php' );

} elseif ( file_exists( dirname(ABSPATH) . '/gp-config.php' ) && ! file_exists( dirname(ABSPATH) . '/gp-settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another install */
	require_once( dirname(ABSPATH) . '/gp-config.php' );

} else {

	// A config file doesn't exist

	define( 'gpINC', 'gp-includes' );
	require_once( ABSPATH . gpINC . '/load.php' );

	// Standardize $_SERVER variables across setups.
	gp_fix_server_vars();

	require_once( ABSPATH . gpINC . '/functions.php' );

	$path = gp_guess_url() . '/gp-admin/setup-config.php';

	/*
	 * We're going to redirect to setup-config.php. While this shouldn't result
	 * in an infinite loop, that's a silly thing to assume, don't you think? If
	 * we're traveling in circles, our last-ditch effort is "Need more help?"
	 */
	if ( false === strpos( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . $path );
		exit;
	}

	define( 'gp_CONTENT_DIR', ABSPATH . 'gp-content' );
	require_once( ABSPATH . gpINC . '/version.php' );

	gp_check_php_mysql_versions();
	gp_load_translations_early();

	// Die with an error message
	$die  = __( "There doesn't seem to be a <code>gp-config.php</code> file. I need this before we can get started." ) . '</p>';
	$die .= '<p>' . __( "Need more help? <a href='http://codex.Goatpress.org/Editing_gp-config.php'>We got it</a>." ) . '</p>';
	$die .= '<p>' . __( "You can create a <code>gp-config.php</code> file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file." ) . '</p>';
	$die .= '<p><a href="' . $path . '" class="button button-large">' . __( "Create a Configuration File" ) . '</a>';

	gp_die( $die, __( 'Goatpress &rsaquo; Error' ) );
}
