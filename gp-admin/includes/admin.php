<?php
/**
 * Includes all of the Goatpress Administration API files.
 *
 * @package Goatpress
 * @subpackage Administration
 */

if ( ! defined('gp_ADMIN') ) {
	/*
	 * This file is being included from a file other than gp-admin/admin.php, so
	 * some setup was skipped. Make sure the admin message catalog is loaded since
	 * load_default_textdomain() will not have done so in this context.
	 */
	load_textdomain( 'default', gp_LANG_DIR . '/admin-' . get_locale() . '.mo' );
}

/** Goatpress Bookmark Administration API */
require_once(ABSPATH . 'gp-admin/includes/bookmark.php');

/** Goatpress Comment Administration API */
require_once(ABSPATH . 'gp-admin/includes/comment.php');

/** Goatpress Administration File API */
require_once(ABSPATH . 'gp-admin/includes/file.php');

/** Goatpress Image Administration API */
require_once(ABSPATH . 'gp-admin/includes/image.php');

/** Goatpress Media Administration API */
require_once(ABSPATH . 'gp-admin/includes/media.php');

/** Goatpress Import Administration API */
require_once(ABSPATH . 'gp-admin/includes/import.php');

/** Goatpress Misc Administration API */
require_once(ABSPATH . 'gp-admin/includes/misc.php');

/** Goatpress Plugin Administration API */
require_once(ABSPATH . 'gp-admin/includes/plugin.php');

/** Goatpress Post Administration API */
require_once(ABSPATH . 'gp-admin/includes/post.php');

/** Goatpress Administration Screen API */
require_once(ABSPATH . 'gp-admin/includes/screen.php');

/** Goatpress Taxonomy Administration API */
require_once(ABSPATH . 'gp-admin/includes/taxonomy.php');

/** Goatpress Template Administration API */
require_once(ABSPATH . 'gp-admin/includes/template.php');

/** Goatpress List Table Administration API and base class */
require_once(ABSPATH . 'gp-admin/includes/class-gp-list-table.php');
require_once(ABSPATH . 'gp-admin/includes/list-table.php');

/** Goatpress Theme Administration API */
require_once(ABSPATH . 'gp-admin/includes/theme.php');

/** Goatpress User Administration API */
require_once(ABSPATH . 'gp-admin/includes/user.php');

/** Goatpress Update Administration API */
require_once(ABSPATH . 'gp-admin/includes/update.php');

/** Goatpress Deprecated Administration API */
require_once(ABSPATH . 'gp-admin/includes/deprecated.php');

/** Goatpress Multisite support API */
if ( is_multisite() ) {
	require_once(ABSPATH . 'gp-admin/includes/ms.php');
	require_once(ABSPATH . 'gp-admin/includes/ms-deprecated.php');
}
