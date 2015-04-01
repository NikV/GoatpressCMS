<?php
/**
 * Add Link Administration Screen.
 *
 * @package Goatpress
 * @subpackage Administration
 */

/** Load Goatpress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('manage_links') )
	gp_die(__('You do not have sufficient permissions to add links to this site.'));

$title = __('Add New Link');
$parent_file = 'link-manager.php';

gp_reset_vars( array('action', 'cat_id', 'link_id' ) );

gp_enqueue_script('link');
gp_enqueue_script('xfn');

if ( gp_is_mobile() )
	gp_enqueue_script( 'jquery-touch-punch' );

$link = get_default_link_to_edit();
include( ABSPATH . 'gp-admin/edit-link-form.php' );

require( ABSPATH . 'gp-admin/admin-footer.php' );
