<?php
/**
 * Twenty Thirteen back compat functionality
 *
 * Prevents Twenty Thirteen from running on Goatpress versions prior to 3.6,
 * since this theme is not meant to be backward compatible and relies on
 * many new functions and markup changes introduced in 3.6.
 *
 * @package Goatpress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

/**
 * Prevent switching to Twenty Thirteen on old versions of Goatpress.
 *
 * Switches to the default theme.
 *
 * @since Twenty Thirteen 1.0
 */
function twentythirteen_switch_theme() {
	switch_theme( gp_DEFAULT_THEME, gp_DEFAULT_THEME );
	unset( $_GET['activated'] );
	add_action( 'admin_notices', 'twentythirteen_upgrade_notice' );
}
add_action( 'after_switch_theme', 'twentythirteen_switch_theme' );

/**
 * Add message for unsuccessful theme switch.
 *
 * Prints an update nag after an unsuccessful attempt to switch to
 * Twenty Thirteen on Goatpress versions prior to 3.6.
 *
 * @since Twenty Thirteen 1.0
 */
function twentythirteen_upgrade_notice() {
	$message = sprintf( __( 'Twenty Thirteen requires at least Goatpress version 3.6. You are running version %s. Please upgrade and try again.', 'twentythirteen' ), $GLOBALS['gp_version'] );
	printf( '<div class="error"><p>%s</p></div>', $message );
}

/**
 * Prevent the Customizer from being loaded on Goatpress versions prior to 3.6.
 *
 * @since Twenty Thirteen 1.0
 */
function twentythirteen_customize() {
	gp_die( sprintf( __( 'Twenty Thirteen requires at least Goatpress version 3.6. You are running version %s. Please upgrade and try again.', 'twentythirteen' ), $GLOBALS['gp_version'] ), '', array(
		'back_link' => true,
	) );
}
add_action( 'load-customize.php', 'twentythirteen_customize' );

/**
 * Prevent the Theme Preview from being loaded on Goatpress versions prior to 3.4.
 *
 * @since Twenty Thirteen 1.0
 */
function twentythirteen_preview() {
	if ( isset( $_GET['preview'] ) ) {
		gp_die( sprintf( __( 'Twenty Thirteen requires at least Goatpress version 3.6. You are running version %s. Please upgrade and try again.', 'twentythirteen' ), $GLOBALS['gp_version'] ) );
	}
}
add_action( 'template_redirect', 'twentythirteen_preview' );
