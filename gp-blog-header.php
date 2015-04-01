<?php
/**
 * Loads the Goatpress environment and template.
 *
 * @package Goatpress
 */

if ( !isset($gp_did_header) ) {

	$gp_did_header = true;

	require_once( dirname(__FILE__) . '/gp-load.php' );

	gp();

	require_once( ABSPATH . gpINC . '/template-loader.php' );

}
