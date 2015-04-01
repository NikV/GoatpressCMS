<?php
/**
 * Front to the Goatpress application. This file doesn't do anything, but loads
 * gp-blog-header.php which does and tells Goatpress to load the theme.
 *
 * @package Goatpress
 */

/**
 * Tells Goatpress to load the Goatpress theme and output it.
 *
 * @var bool
 */
define('gp_USE_THEMES', true);

/** Loads the Goatpress Environment and Template */
require( dirname( __FILE__ ) . '/gp-blog-header.php' );
