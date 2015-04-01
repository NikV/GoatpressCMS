<?php
/**
 * Comment Moderation Administration Screen.
 *
 * Redirects to edit-comments.php?comment_status=moderated.
 *
 * @package Goatpress
 * @subpackage Administration
 */
require_once( dirname( dirname( __FILE__ ) ) . '/gp-load.php' );
gp_redirect( admin_url('edit-comments.php?comment_status=moderated') );
exit;
