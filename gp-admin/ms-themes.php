<?php
/**
 * Multisite themes administration panel.
 *
 * @package Goatpress
 * @subpackage Multisite
 * @since 3.0.0
 */

require_once( dirname( __FILE__ ) . '/admin.php' );

gp_redirect( network_admin_url('themes.php') );
exit;
