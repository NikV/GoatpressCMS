<?php
/**
 * Action handler for Multisite administration panels.
 *
 * @package Goatpress
 * @subpackage Multisite
 * @since 3.0.0
 */

require_once( dirname( __FILE__ ) . '/admin.php' );

gp_redirect( network_admin_url() );
exit;
