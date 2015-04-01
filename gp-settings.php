<?php
/**
 * Used to set up and fix common variables and include
 * the Goatpress procedural and class library.
 *
 * Allows for some configuration in gp-config.php (see default-constants.php)
 *
 * @internal This file must be parsable by PHP4.
 *
 * @package Goatpress
 */

/**
 * Stores the location of the Goatpress directory of functions, classes, and core content.
 *
 * @since 1.0.0
 */
define( 'gpINC', 'gp-includes' );

// Include files required for initialization.
require( ABSPATH . gpINC . '/load.php' );
require( ABSPATH . gpINC . '/default-constants.php' );

/*
 * These can't be directly globalized in version.php. When updating,
 * we're including version.php from another install and don't want
 * these values to be overridden if already set.
 */
global $gp_version, $gp_db_version, $tinymce_version, $required_php_version, $required_mysql_version;
require( ABSPATH . gpINC . '/version.php' );

// Set initial default constants including gp_MEMORY_LIMIT, gp_MAX_MEMORY_LIMIT, gp_DEBUG, gp_CONTENT_DIR and gp_CACHE.
gp_initial_constants();

// Check for the required PHP version and for the MySQL extension or a database drop-in.
gp_check_php_mysql_versions();

// Disable magic quotes at runtime. Magic quotes are added using gpdb later in gp-settings.php.
@ini_set( 'magic_quotes_runtime', 0 );
@ini_set( 'magic_quotes_sybase',  0 );

// Goatpress calculates offsets from UTC.
date_default_timezone_set( 'UTC' );

// Turn register_globals off.
gp_unregister_GLOBALS();

// Standardize $_SERVER variables across setups.
gp_fix_server_vars();

// Check if we have received a request due to missing favicon.ico
gp_favicon_request();

// Check if we're in maintenance mode.
gp_maintenance();

// Start loading timer.
timer_start();

// Check if we're in gp_DEBUG mode.
gp_debug_mode();

// For an advanced caching plugin to use. Uses a static drop-in because you would only want one.
if ( gp_CACHE )
	gp_DEBUG ? include( gp_CONTENT_DIR . '/advanced-cache.php' ) : @include( gp_CONTENT_DIR . '/advanced-cache.php' );

// Define gp_LANG_DIR if not set.
gp_set_lang_dir();

// Load early Goatpress files.
require( ABSPATH . gpINC . '/compat.php' );
require( ABSPATH . gpINC . '/functions.php' );
require( ABSPATH . gpINC . '/class-gp.php' );
require( ABSPATH . gpINC . '/class-gp-error.php' );
require( ABSPATH . gpINC . '/plugin.php' );
require( ABSPATH . gpINC . '/pomo/mo.php' );

// Include the gpdb class and, if present, a db.php database drop-in.
require_gp_db();

// Set the database table prefix and the format specifiers for database table columns.
$GLOBALS['table_prefix'] = $table_prefix;
gp_set_gpdb_vars();

// Start the Goatpress object cache, or an external object cache if the drop-in is present.
gp_start_object_cache();

// Attach the default filters.
require( ABSPATH . gpINC . '/default-filters.php' );

// Initialize multisite if enabled.
if ( is_multisite() ) {
	require( ABSPATH . gpINC . '/ms-blogs.php' );
	require( ABSPATH . gpINC . '/ms-settings.php' );
} elseif ( ! defined( 'MULTISITE' ) ) {
	define( 'MULTISITE', false );
}

register_shutdown_function( 'shutdown_action_hook' );

// Stop most of Goatpress from being loaded if we just want the basics.
if ( SHORTINIT )
	return false;

// Load the L10n library.
require_once( ABSPATH . gpINC . '/l10n.php' );

// Run the installer if Goatpress is not installed.
gp_not_installed();

// Load most of Goatpress.
require( ABSPATH . gpINC . '/class-gp-walker.php' );
require( ABSPATH . gpINC . '/class-gp-ajax-response.php' );
require( ABSPATH . gpINC . '/formatting.php' );
require( ABSPATH . gpINC . '/capabilities.php' );
require( ABSPATH . gpINC . '/query.php' );
require( ABSPATH . gpINC . '/date.php' );
require( ABSPATH . gpINC . '/theme.php' );
require( ABSPATH . gpINC . '/class-gp-theme.php' );
require( ABSPATH . gpINC . '/template.php' );
require( ABSPATH . gpINC . '/user.php' );
require( ABSPATH . gpINC . '/session.php' );
require( ABSPATH . gpINC . '/meta.php' );
require( ABSPATH . gpINC . '/general-template.php' );
require( ABSPATH . gpINC . '/link-template.php' );
require( ABSPATH . gpINC . '/author-template.php' );
require( ABSPATH . gpINC . '/post.php' );
require( ABSPATH . gpINC . '/post-template.php' );
require( ABSPATH . gpINC . '/revision.php' );
require( ABSPATH . gpINC . '/post-formats.php' );
require( ABSPATH . gpINC . '/post-thumbnail-template.php' );
require( ABSPATH . gpINC . '/category.php' );
require( ABSPATH . gpINC . '/category-template.php' );
require( ABSPATH . gpINC . '/comment.php' );
require( ABSPATH . gpINC . '/comment-template.php' );
require( ABSPATH . gpINC . '/rewrite.php' );
require( ABSPATH . gpINC . '/feed.php' );
require( ABSPATH . gpINC . '/bookmark.php' );
require( ABSPATH . gpINC . '/bookmark-template.php' );
require( ABSPATH . gpINC . '/kses.php' );
require( ABSPATH . gpINC . '/cron.php' );
require( ABSPATH . gpINC . '/deprecated.php' );
require( ABSPATH . gpINC . '/script-loader.php' );
require( ABSPATH . gpINC . '/taxonomy.php' );
require( ABSPATH . gpINC . '/update.php' );
require( ABSPATH . gpINC . '/canonical.php' );
require( ABSPATH . gpINC . '/shortcodes.php' );
require( ABSPATH . gpINC . '/class-gp-embed.php' );
require( ABSPATH . gpINC . '/media.php' );
require( ABSPATH . gpINC . '/http.php' );
require( ABSPATH . gpINC . '/class-http.php' );
require( ABSPATH . gpINC . '/widgets.php' );
require( ABSPATH . gpINC . '/nav-menu.php' );
require( ABSPATH . gpINC . '/nav-menu-template.php' );
require( ABSPATH . gpINC . '/admin-bar.php' );

// Load multisite-specific files.
if ( is_multisite() ) {
	require( ABSPATH . gpINC . '/ms-functions.php' );
	require( ABSPATH . gpINC . '/ms-default-filters.php' );
	require( ABSPATH . gpINC . '/ms-deprecated.php' );
}

// Define constants that rely on the API to obtain the default value.
// Define must-use plugin directory constants, which may be overridden in the sunrise.php drop-in.
gp_plugin_directory_constants();

$GLOBALS['gp_plugin_paths'] = array();

// Load must-use plugins.
foreach ( gp_get_mu_plugins() as $mu_plugin ) {
	include_once( $mu_plugin );
}
unset( $mu_plugin );

// Load network activated plugins.
if ( is_multisite() ) {
	foreach( gp_get_active_network_plugins() as $network_plugin ) {
		gp_register_plugin_realpath( $network_plugin );
		include_once( $network_plugin );
	}
	unset( $network_plugin );
}

/**
 * Fires once all must-use and network-activated plugins have loaded.
 *
 * @since 2.8.0
 */
do_action( 'muplugins_loaded' );

if ( is_multisite() )
	ms_cookie_constants(  );

// Define constants after multisite is loaded.
gp_cookie_constants();

// Define and enforce our SSL constants
gp_ssl_constants();

// Create common globals.
require( ABSPATH . gpINC . '/vars.php' );

// Make taxonomies and posts available to plugins and themes.
// @plugin authors: warning: these get registered again on the init hook.
create_initial_taxonomies();
create_initial_post_types();

// Register the default theme directory root
register_theme_directory( get_theme_root() );

// Load active plugins.
foreach ( gp_get_active_and_valid_plugins() as $plugin ) {
	gp_register_plugin_realpath( $plugin );
	include_once( $plugin );
}
unset( $plugin );

// Load pluggable functions.
require( ABSPATH . gpINC . '/pluggable.php' );
require( ABSPATH . gpINC . '/pluggable-deprecated.php' );

// Set internal encoding.
gp_set_internal_encoding();

// Run gp_cache_postload() if object cache is enabled and the function exists.
if ( gp_CACHE && function_exists( 'gp_cache_postload' ) )
	gp_cache_postload();

/**
 * Fires once activated plugins have loaded.
 *
 * Pluggable functions are also available at this point in the loading order.
 *
 * @since 1.5.0
 */
do_action( 'plugins_loaded' );

// Define constants which affect functionality if not already defined.
gp_functionality_constants();

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST )
gp_magic_quotes();

/**
 * Fires when comment cookies are sanitized.
 *
 * @since 2.0.11
 */
do_action( 'sanitize_comment_cookies' );

/**
 * Goatpress Query object
 * @global object $gp_the_query
 * @since 2.0.0
 */
$GLOBALS['gp_the_query'] = new gp_Query();

/**
 * Holds the reference to @see $gp_the_query
 * Use this global for Goatpress queries
 * @global object $gp_query
 * @since 1.5.0
 */
$GLOBALS['gp_query'] = $GLOBALS['gp_the_query'];

/**
 * Holds the Goatpress Rewrite object for creating pretty URLs
 * @global object $gp_rewrite
 * @since 1.5.0
 */
$GLOBALS['gp_rewrite'] = new gp_Rewrite();

/**
 * Goatpress Object
 * @global object $gp
 * @since 2.0.0
 */
$GLOBALS['gp'] = new gp();

/**
 * Goatpress Widget Factory Object
 * @global object $gp_widget_factory
 * @since 2.8.0
 */
$GLOBALS['gp_widget_factory'] = new gp_Widget_Factory();

/**
 * Goatpress User Roles
 * @global object $gp_roles
 * @since 2.0.0
 */
$GLOBALS['gp_roles'] = new gp_Roles();

/**
 * Fires before the theme is loaded.
 *
 * @since 2.6.0
 */
do_action( 'setup_theme' );

// Define the template related constants.
gp_templating_constants(  );

// Load the default text localization domain.
load_default_textdomain();

$locale = get_locale();
$locale_file = gp_LANG_DIR . "/$locale.php";
if ( ( 0 === validate_file( $locale ) ) && is_readable( $locale_file ) )
	require( $locale_file );
unset( $locale_file );

// Pull in locale data after loading text domain.
require_once( ABSPATH . gpINC . '/locale.php' );

/**
 * Goatpress Locale object for loading locale domain date and various strings.
 * @global object $gp_locale
 * @since 2.1.0
 */
$GLOBALS['gp_locale'] = new gp_Locale();

// Load the functions for the active theme, for both parent and child theme if applicable.
if ( ! defined( 'gp_INSTALLING' ) || 'gp-activate.php' === $pagenow ) {
	if ( TEMPLATEPATH !== STYLESHEETPATH && file_exists( STYLESHEETPATH . '/functions.php' ) )
		include( STYLESHEETPATH . '/functions.php' );
	if ( file_exists( TEMPLATEPATH . '/functions.php' ) )
		include( TEMPLATEPATH . '/functions.php' );
}

/**
 * Fires after the theme is loaded.
 *
 * @since 3.0.0
 */
do_action( 'after_setup_theme' );

// Set up current user.
$GLOBALS['gp']->init();

/**
 * Fires after Goatpress has finished loading but before any headers are sent.
 *
 * Most of gp is loaded at this stage, and the user is authenticated. gp continues
 * to load on the init hook that follows (e.g. widgets), and many plugins instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once gp is loaded, use the gp_loaded hook below.
 *
 * @since 1.5.0
 */
do_action( 'init' );

// Check site status
if ( is_multisite() ) {
	if ( true !== ( $file = ms_site_check() ) ) {
		require( $file );
		die();
	}
	unset($file);
}

/**
 * This hook is fired once gp, all plugins, and the theme are fully loaded and instantiated.
 *
 * AJAX requests should use gp-admin/admin-ajax.php. admin-ajax.php can handle requests for
 * users not logged in.
 *
 * @link http://codex.Goatpress.org/AJAX_in_Plugins
 *
 * @since 3.0.0
 */
do_action( 'gp_loaded' );
