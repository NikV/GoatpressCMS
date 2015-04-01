<?php

/**
 * Disable error reporting
 *
 * Set this to error_reporting( -1 ) for debugging
 */
error_reporting(0);

/** Set ABSPATH for execution */
define( 'ABSPATH', dirname(dirname(__FILE__)) . '/' );
define( 'gpINC', 'gp-includes' );

/**
 * @ignore
 */
function __() {}

/**
 * @ignore
 */
function _x() {}

/**
 * @ignore
 */
function add_filter() {}

/**
 * @ignore
 */
function esc_attr() {}

/**
 * @ignore
 */
function apply_filters() {}

/**
 * @ignore
 */
function get_option() {}

/**
 * @ignore
 */
function is_lighttpd_before_150() {}

/**
 * @ignore
 */
function add_action() {}

/**
 * @ignore
 */
function do_action_ref_array() {}

/**
 * @ignore
 */
function get_bloginfo() {}

/**
 * @ignore
 */
function is_admin() {return true;}

/**
 * @ignore
 */
function site_url() {}

/**
 * @ignore
 */
function admin_url() {}

/**
 * @ignore
 */
function gp_guess_url() {}

function get_file($path) {

	if ( function_exists('realpath') )
		$path = realpath($path);

	if ( ! $path || ! @is_file($path) )
		return '';

	return @file_get_contents($path);
}

require( ABSPATH . gpINC . '/script-loader.php' );
require( ABSPATH . gpINC . '/version.php' );

$load = preg_replace( '/[^a-z0-9,_-]+/i', '', $_GET['load'] );
$load = array_unique( explode( ',', $load ) );

if ( empty($load) )
	exit;

$compress = ( isset($_GET['c']) && $_GET['c'] );
$force_gzip = ( $compress && 'gzip' == $_GET['c'] );
$rtl = ( isset($_GET['dir']) && 'rtl' == $_GET['dir'] );
$expires_offset = 31536000; // 1 year
$out = '';

$gp_styles = new gp_Styles();
gp_default_styles($gp_styles);

foreach( $load as $handle ) {
	if ( !array_key_exists($handle, $gp_styles->registered) )
		continue;

	$style = $gp_styles->registered[$handle];
	$path = ABSPATH . $style->src;

	if ( $rtl && ! empty( $style->extra['rtl'] ) ) {
		// All default styles have fully independent RTL files.
		$path = str_replace( '.min.css', '-rtl.min.css', $path );
	}

	$content = get_file( $path ) . "\n";

	if ( strpos( $style->src, '/' . gpINC . '/css/' ) === 0 ) {
		$content = str_replace( '../images/', '../' . gpINC . '/images/', $content );
		$content = str_replace( '../js/tinymce/', '../' . gpINC . '/js/tinymce/', $content );
		$content = str_replace( '../fonts/', '../' . gpINC . '/fonts/', $content );
		$out .= $content;
	} else {
		$out .= str_replace( '../images/', 'images/', $content );
	}
}

header('Content-Type: text/css; charset=UTF-8');
header('Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT');
header("Cache-Control: public, max-age=$expires_offset");

if ( $compress && ! ini_get('zlib.output_compression') && 'ob_gzhandler' != ini_get('output_handler') && isset($_SERVER['HTTP_ACCEPT_ENCODING']) ) {
	header('Vary: Accept-Encoding'); // Handle proxies
	if ( false !== stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') && function_exists('gzdeflate') && ! $force_gzip ) {
		header('Content-Encoding: deflate');
		$out = gzdeflate( $out, 3 );
	} elseif ( false !== stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && function_exists('gzencode') ) {
		header('Content-Encoding: gzip');
		$out = gzencode( $out, 3 );
	}
}

echo $out;
exit;
