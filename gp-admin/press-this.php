<?php
/**
 * Press This Display and Handler.
 *
 * @package Goatpress
 * @subpackage Press_This
 */

define('IFRAME_REQUEST' , true);

/** Goatpress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( get_post_type_object( 'post' )->cap->create_posts ) )
	gp_die( __( 'Cheatin&#8217; uh?' ), 403 );

if ( empty( $GLOBALS['gp_press_this'] ) ) {
	include( ABSPATH . 'gp-admin/includes/class-gp-press-this.php' );
}

$GLOBALS['gp_press_this']->html();
