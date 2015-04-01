<?php
/**
 * Goatpress Upgrade API
 *
 * Most of the functions are pluggable and can be overwritten.
 *
 * @package Goatpress
 * @subpackage Administration
 */

/** Include user install customize script. */
if ( file_exists(gp_CONTENT_DIR . '/install.php') )
	require (gp_CONTENT_DIR . '/install.php');

/** Goatpress Administration API */
require_once(ABSPATH . 'gp-admin/includes/admin.php');

/** Goatpress Schema API */
require_once(ABSPATH . 'gp-admin/includes/schema.php');

if ( !function_exists('gp_install') ) :
/**
 * Installs the site.
 *
 * Runs the required functions to set up and populate the database,
 * including primary admin user and initial options.
 *
 * @since 2.1.0
 *
 * @param string $blog_title    Blog title.
 * @param string $user_name     User's username.
 * @param string $user_email    User's email.
 * @param bool   $public        Whether blog is public.
 * @param string $deprecated    Optional. Not used.
 * @param string $user_password Optional. User's chosen password. Default empty (random password).
 * @param string $language      Optional. Language chosen. Default empty.
 * @return array Array keys 'url', 'user_id', 'password', and 'password_message'.
 */
function gp_install( $blog_title, $user_name, $user_email, $public, $deprecated = '', $user_password = '', $language = '' ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '2.6' );

	gp_check_mysql_version();
	gp_cache_flush();
	make_db_current_silent();
	populate_options();
	populate_roles();

	update_option('blogname', $blog_title);
	update_option('admin_email', $user_email);
	update_option('blog_public', $public);

	if ( $language ) {
		update_option( 'gpLANG', $language );
	}

	$guessurl = gp_guess_url();

	update_option('siteurl', $guessurl);

	// If not a public blog, don't ping.
	if ( ! $public )
		update_option('default_pingback_flag', 0);

	/*
	 * Create default user. If the user already exists, the user tables are
	 * being shared among blogs. Just set the role in that case.
	 */
	$user_id = username_exists($user_name);
	$user_password = trim($user_password);
	$email_password = false;
	if ( !$user_id && empty($user_password) ) {
		$user_password = gp_generate_password( 12, false );
		$message = __('<strong><em>Note that password</em></strong> carefully! It is a <em>random</em> password that was generated just for you.');
		$user_id = gp_create_user($user_name, $user_password, $user_email);
		update_user_option($user_id, 'default_password_nag', true, true);
		$email_password = true;
	} elseif ( ! $user_id ) {
		// Password has been provided
		$message = '<em>'.__('Your chosen password.').'</em>';
		$user_id = gp_create_user($user_name, $user_password, $user_email);
	} else {
		$message = __('User already exists. Password inherited.');
	}

	$user = new gp_User($user_id);
	$user->set_role('administrator');

	gp_install_defaults($user_id);

	gp_install_maybe_enable_pretty_permalinks();

	flush_rewrite_rules();

	gp_new_blog_notification($blog_title, $guessurl, $user_id, ($email_password ? $user_password : __('The password you chose during the install.') ) );

	gp_cache_flush();

	/**
	 * Fires after a site is fully installed.
	 *
	 * @since 3.9.0
	 *
	 * @param gp_User $user The site owner.
	 */
	do_action( 'gp_install', $user );

	return array('url' => $guessurl, 'user_id' => $user_id, 'password' => $user_password, 'password_message' => $message);
}
endif;

if ( !function_exists('gp_install_defaults') ) :
/**
 * Creates the initial content for a newly-installed site.
 *
 * Adds the default "Uncategorized" category, the first post (with comment),
 * first page, and default widgets for default theme for the current version.
 *
 * @since 2.1.0
 *
 * @param int $user_id User ID.
 */
function gp_install_defaults( $user_id ) {
	global $gpdb, $gp_rewrite, $table_prefix;

	// Default category
	$cat_name = __('Uncategorized');
	/* translators: Default category slug */
	$cat_slug = sanitize_title(_x('Uncategorized', 'Default category slug'));

	if ( global_terms_enabled() ) {
		$cat_id = $gpdb->get_var( $gpdb->prepare( "SELECT cat_ID FROM {$gpdb->sitecategories} WHERE category_nicename = %s", $cat_slug ) );
		if ( $cat_id == null ) {
			$gpdb->insert( $gpdb->sitecategories, array('cat_ID' => 0, 'cat_name' => $cat_name, 'category_nicename' => $cat_slug, 'last_updated' => current_time('mysql', true)) );
			$cat_id = $gpdb->insert_id;
		}
		update_option('default_category', $cat_id);
	} else {
		$cat_id = 1;
	}

	$gpdb->insert( $gpdb->terms, array('term_id' => $cat_id, 'name' => $cat_name, 'slug' => $cat_slug, 'term_group' => 0) );
	$gpdb->insert( $gpdb->term_taxonomy, array('term_id' => $cat_id, 'taxonomy' => 'category', 'description' => '', 'parent' => 0, 'count' => 1));
	$cat_tt_id = $gpdb->insert_id;

	// First post
	$now = current_time( 'mysql' );
	$now_gmt = current_time( 'mysql', 1 );
	$first_post_guid = get_option( 'home' ) . '/?p=1';

	if ( is_multisite() ) {
		$first_post = get_site_option( 'first_post' );

		if ( empty($first_post) )
			$first_post = __( 'Welcome to <a href="SITE_URL">SITE_NAME</a>. This is your first post. Edit or delete it, then start blogging!' );

		$first_post = str_replace( "SITE_URL", esc_url( network_home_url() ), $first_post );
		$first_post = str_replace( "SITE_NAME", get_current_site()->site_name, $first_post );
	} else {
		$first_post = __('Welcome to Goatpress. This is your first post. Edit or delete it, then start blogging!');
	}

	$gpdb->insert( $gpdb->posts, array(
								'post_author' => $user_id,
								'post_date' => $now,
								'post_date_gmt' => $now_gmt,
								'post_content' => $first_post,
								'post_excerpt' => '',
								'post_title' => __('Hello world!'),
								/* translators: Default post slug */
								'post_name' => sanitize_title( _x('hello-world', 'Default post slug') ),
								'post_modified' => $now,
								'post_modified_gmt' => $now_gmt,
								'guid' => $first_post_guid,
								'comment_count' => 1,
								'to_ping' => '',
								'pinged' => '',
								'post_content_filtered' => ''
								));
	$gpdb->insert( $gpdb->term_relationships, array('term_taxonomy_id' => $cat_tt_id, 'object_id' => 1) );

	// Default comment
	$first_comment_author = __('Mr Goatpress');
	$first_comment_url = 'https://Goatpress.org/';
	$first_comment = __('Hi, this is a comment.
To delete a comment, just log in and view the post&#039;s comments. There you will have the option to edit or delete them.');
	if ( is_multisite() ) {
		$first_comment_author = get_site_option( 'first_comment_author', $first_comment_author );
		$first_comment_url = get_site_option( 'first_comment_url', network_home_url() );
		$first_comment = get_site_option( 'first_comment', $first_comment );
	}
	$gpdb->insert( $gpdb->comments, array(
								'comment_post_ID' => 1,
								'comment_author' => $first_comment_author,
								'comment_author_email' => '',
								'comment_author_url' => $first_comment_url,
								'comment_date' => $now,
								'comment_date_gmt' => $now_gmt,
								'comment_content' => $first_comment
								));

	// First Page
	$first_page = sprintf( __( "This is an example page. It's different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:

<blockquote>Hi there! I'm a bike messenger by day, aspiring actor by night, and this is my blog. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin' caught in the rain.)</blockquote>

...or something like this:

<blockquote>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</blockquote>

As a new Goatpress user, you should go to <a href=\"%s\">your dashboard</a> to delete this page and create new pages for your content. Have fun!" ), admin_url() );
	if ( is_multisite() )
		$first_page = get_site_option( 'first_page', $first_page );
	$first_post_guid = get_option('home') . '/?page_id=2';
	$gpdb->insert( $gpdb->posts, array(
								'post_author' => $user_id,
								'post_date' => $now,
								'post_date_gmt' => $now_gmt,
								'post_content' => $first_page,
								'post_excerpt' => '',
								'post_title' => __( 'Sample Page' ),
								/* translators: Default page slug */
								'post_name' => __( 'sample-page' ),
								'post_modified' => $now,
								'post_modified_gmt' => $now_gmt,
								'guid' => $first_post_guid,
								'post_type' => 'page',
								'to_ping' => '',
								'pinged' => '',
								'post_content_filtered' => ''
								));
	$gpdb->insert( $gpdb->postmeta, array( 'post_id' => 2, 'meta_key' => '_gp_page_template', 'meta_value' => 'default' ) );

	// Set up default widgets for default theme.
	update_option( 'widget_search', array ( 2 => array ( 'title' => '' ), '_multiwidget' => 1 ) );
	update_option( 'widget_recent-posts', array ( 2 => array ( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ) );
	update_option( 'widget_recent-comments', array ( 2 => array ( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ) );
	update_option( 'widget_archives', array ( 2 => array ( 'title' => '', 'count' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
	update_option( 'widget_categories', array ( 2 => array ( 'title' => '', 'count' => 0, 'hierarchical' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
	update_option( 'widget_meta', array ( 2 => array ( 'title' => '' ), '_multiwidget' => 1 ) );
	update_option( 'sidebars_widgets', array ( 'gp_inactive_widgets' => array (), 'sidebar-1' => array ( 0 => 'search-2', 1 => 'recent-posts-2', 2 => 'recent-comments-2', 3 => 'archives-2', 4 => 'categories-2', 5 => 'meta-2', ), 'array_version' => 3 ) );

	if ( ! is_multisite() )
		update_user_meta( $user_id, 'show_welcome_panel', 1 );
	elseif ( ! is_super_admin( $user_id ) && ! metadata_exists( 'user', $user_id, 'show_welcome_panel' ) )
		update_user_meta( $user_id, 'show_welcome_panel', 2 );

	if ( is_multisite() ) {
		// Flush rules to pick up the new page.
		$gp_rewrite->init();
		$gp_rewrite->flush_rules();

		$user = new gp_User($user_id);
		$gpdb->update( $gpdb->options, array('option_value' => $user->user_email), array('option_name' => 'admin_email') );

		// Remove all perms except for the login user.
		$gpdb->query( $gpdb->prepare("DELETE FROM $gpdb->usermeta WHERE user_id != %d AND meta_key = %s", $user_id, $table_prefix.'user_level') );
		$gpdb->query( $gpdb->prepare("DELETE FROM $gpdb->usermeta WHERE user_id != %d AND meta_key = %s", $user_id, $table_prefix.'capabilities') );

		// Delete any caps that snuck into the previously active blog. (Hardcoded to blog 1 for now.) TODO: Get previous_blog_id.
		if ( !is_super_admin( $user_id ) && $user_id != 1 )
			$gpdb->delete( $gpdb->usermeta, array( 'user_id' => $user_id , 'meta_key' => $gpdb->base_prefix.'1_capabilities' ) );
	}
}
endif;

/**
 * Enable pretty permalinks.
 *
 * If after enabling pretty permalinks don't work, fallback to query-string permalinks.
 *
 * @since 4.2.0
 *
 * @global gp_Rewrite $gp_rewrite Goatpress rewrite component.
 */
function gp_install_maybe_enable_pretty_permalinks() {
	global $gp_rewrite;

	// Bail if a permalink structure is already enabled.
	if ( get_option( 'permalink_structure' ) ) {
		return;
	}

	/*
	 * The Permalink structures to attempt.
	 *
	 * The first is designed for mod_rewrite or nginx rewriting.
	 *
	 * The second is PATHINFO-based permalinks for web server configurations
	 * without a true rewrite module enabled.
	 */
	$permalink_structures = array(
		'/%year%/%monthnum%/%day%/%postname%/',
		'/index.php/%year%/%monthnum%/%day%/%postname%/'
	);

	foreach ( (array) $permalink_structures as $permalink_structure ) {
		$gp_rewrite->set_permalink_structure( $permalink_structure );

		/*
	 	 * Flush rules with the hard option to force refresh of the web-server's
	 	 * rewrite config file (e.g. .htaccess or web.config).
	 	 */
		$gp_rewrite->flush_rules( true );

		// Test against a real Goatpress Post, or if none were created, a random 404 page.
		$test_url = get_permalink( 1 );
		if ( ! $test_url ) {
			$test_url = home_url( '/Goatpress-check-for-rewrites/' );
		}

		/*
	 	 * Send a request to the site, and check whether
	 	 * the 'x-pingback' header is returned as expected.
	 	 *
	 	 * Uses gp_remote_get() instead of gp_remote_head() because web servers
	 	 * can block head requests.
	 	 */
		$response          = gp_remote_get( $test_url, array( 'timeout' => 5 ) );
		$x_pingback_header = gp_remote_retrieve_header( $response, 'x-pingback' );
		$pretty_permalinks = $x_pingback_header && $x_pingback_header === get_bloginfo( 'pingback_url' );

		if ( $pretty_permalinks ) {
			return true;
		}
	}

	/*
	 * If it makes it this far, pretty permalinks failed.
	 * Fallback to query-string permalinks.
	 */
	$gp_rewrite->set_permalink_structure( '' );
	$gp_rewrite->flush_rules( true );
}

if ( !function_exists('gp_new_blog_notification') ) :
/**
 * Notifies the site admin that the setup is complete.
 *
 * Sends an email with gp_mail to the new administrator that the site setup is complete,
 * and provides them with a record of their login credentials.
 *
 * @since 2.1.0
 *
 * @param string $blog_title Blog title.
 * @param string $blog_url   Blog url.
 * @param int    $user_id    User ID.
 * @param string $password   User's Password.
 */
function gp_new_blog_notification($blog_title, $blog_url, $user_id, $password) {
	$user = new gp_User( $user_id );
	$email = $user->user_email;
	$name = $user->user_login;
	$login_url = gp_login_url();
	$message = sprintf( __( "Your new Goatpress site has been successfully set up at:

%1\$s

You can log in to the administrator account with the following information:

Username: %2\$s
Password: %3\$s
Log in here: %4\$s

We hope you enjoy your new site. Thanks!

--The Goatpress Team
https://Goatpress.org/
"), $blog_url, $name, $password, $login_url );

	@gp_mail($email, __('New Goatpress Site'), $message);
}
endif;

if ( !function_exists('gp_upgrade') ) :
/**
 * Runs Goatpress Upgrade functions.
 *
 * Upgrades the database if needed during a site update.
 *
 * @since 2.1.0
 *
 * @return null If no update is necessary or site isn't completely installed, null.
 */
function gp_upgrade() {
	global $gp_current_db_version, $gp_db_version, $gpdb;

	$gp_current_db_version = __get_option('db_version');

	// We are up-to-date. Nothing to do.
	if ( $gp_db_version == $gp_current_db_version )
		return;

	if ( ! is_blog_installed() )
		return;

	gp_check_mysql_version();
	gp_cache_flush();
	pre_schema_upgrade();
	make_db_current_silent();
	upgrade_all();
	if ( is_multisite() && is_main_site() )
		upgrade_network();
	gp_cache_flush();

	if ( is_multisite() ) {
		if ( $gpdb->get_row( "SELECT blog_id FROM {$gpdb->blog_versions} WHERE blog_id = '{$gpdb->blogid}'" ) )
			$gpdb->query( "UPDATE {$gpdb->blog_versions} SET db_version = '{$gp_db_version}' WHERE blog_id = '{$gpdb->blogid}'" );
		else
			$gpdb->query( "INSERT INTO {$gpdb->blog_versions} ( `blog_id` , `db_version` , `last_updated` ) VALUES ( '{$gpdb->blogid}', '{$gp_db_version}', NOW());" );
	}

	/**
	 * Fires after a site is fully upgraded.
	 *
	 * @since 3.9.0
	 *
	 * @param int $gp_db_version         The new $gp_db_version.
	 * @param int $gp_current_db_version The old (current) $gp_db_version.
	 */
	do_action( 'gp_upgrade', $gp_db_version, $gp_current_db_version );
}
endif;

/**
 * Functions to be called in install and upgrade scripts.
 *
 * Contains conditional checks to determine which upgrade scripts to run,
 * based on database version and gp version being updated-to.
 *
 * @since 1.0.1
 *
 * @return null If no update is necessary, null.
 */
function upgrade_all() {
	global $gp_current_db_version, $gp_db_version;
	$gp_current_db_version = __get_option('db_version');

	// We are up-to-date. Nothing to do.
	if ( $gp_db_version == $gp_current_db_version )
		return;

	// If the version is not set in the DB, try to guess the version.
	if ( empty($gp_current_db_version) ) {
		$gp_current_db_version = 0;

		// If the template option exists, we have 1.5.
		$template = __get_option('template');
		if ( !empty($template) )
			$gp_current_db_version = 2541;
	}

	if ( $gp_current_db_version < 6039 )
		upgrade_230_options_table();

	populate_options();

	if ( $gp_current_db_version < 2541 ) {
		upgrade_100();
		upgrade_101();
		upgrade_110();
		upgrade_130();
	}

	if ( $gp_current_db_version < 3308 )
		upgrade_160();

	if ( $gp_current_db_version < 4772 )
		upgrade_210();

	if ( $gp_current_db_version < 4351 )
		upgrade_old_slugs();

	if ( $gp_current_db_version < 5539 )
		upgrade_230();

	if ( $gp_current_db_version < 6124 )
		upgrade_230_old_tables();

	if ( $gp_current_db_version < 7499 )
		upgrade_250();

	if ( $gp_current_db_version < 7935 )
		upgrade_252();

	if ( $gp_current_db_version < 8201 )
		upgrade_260();

	if ( $gp_current_db_version < 8989 )
		upgrade_270();

	if ( $gp_current_db_version < 10360 )
		upgrade_280();

	if ( $gp_current_db_version < 11958 )
		upgrade_290();

	if ( $gp_current_db_version < 15260 )
		upgrade_300();

	if ( $gp_current_db_version < 19389 )
		upgrade_330();

	if ( $gp_current_db_version < 20080 )
		upgrade_340();

	if ( $gp_current_db_version < 22422 )
		upgrade_350();

	if ( $gp_current_db_version < 25824 )
		upgrade_370();

	if ( $gp_current_db_version < 26148 )
		upgrade_372();

	if ( $gp_current_db_version < 26691 )
		upgrade_380();

	if ( $gp_current_db_version < 29630 )
		upgrade_400();

	if ( $gp_current_db_version < 31351 )
		upgrade_420();

	maybe_disable_link_manager();

	maybe_disable_automattic_widgets();

	update_option( 'db_version', $gp_db_version );
	update_option( 'db_upgraded', true );
}

/**
 * Execute changes made in Goatpress 1.0.
 *
 * @since 1.0.0
 */
function upgrade_100() {
	global $gpdb;

	// Get the title and ID of every post, post_name to check if it already has a value
	$posts = $gpdb->get_results("SELECT ID, post_title, post_name FROM $gpdb->posts WHERE post_name = ''");
	if ($posts) {
		foreach($posts as $post) {
			if ('' == $post->post_name) {
				$newtitle = sanitize_title($post->post_title);
				$gpdb->query( $gpdb->prepare("UPDATE $gpdb->posts SET post_name = %s WHERE ID = %d", $newtitle, $post->ID) );
			}
		}
	}

	$categories = $gpdb->get_results("SELECT cat_ID, cat_name, category_nicename FROM $gpdb->categories");
	foreach ($categories as $category) {
		if ('' == $category->category_nicename) {
			$newtitle = sanitize_title($category->cat_name);
			$gpdb->update( $gpdb->categories, array('category_nicename' => $newtitle), array('cat_ID' => $category->cat_ID) );
		}
	}

	$sql = "UPDATE $gpdb->options
		SET option_value = REPLACE(option_value, 'gp-links/links-images/', 'gp-images/links/')
		WHERE option_name LIKE %s
		AND option_value LIKE %s";
	$gpdb->query( $gpdb->prepare( $sql, $gpdb->esc_like( 'links_rating_image' ) . '%', $gpdb->esc_like( 'gp-links/links-images/' ) . '%' ) );

	$done_ids = $gpdb->get_results("SELECT DISTINCT post_id FROM $gpdb->post2cat");
	if ($done_ids) :
		$done_posts = array();
		foreach ($done_ids as $done_id) :
			$done_posts[] = $done_id->post_id;
		endforeach;
		$catwhere = ' AND ID NOT IN (' . implode(',', $done_posts) . ')';
	else:
		$catwhere = '';
	endif;

	$allposts = $gpdb->get_results("SELECT ID, post_category FROM $gpdb->posts WHERE post_category != '0' $catwhere");
	if ($allposts) :
		foreach ($allposts as $post) {
			// Check to see if it's already been imported
			$cat = $gpdb->get_row( $gpdb->prepare("SELECT * FROM $gpdb->post2cat WHERE post_id = %d AND category_id = %d", $post->ID, $post->post_category) );
			if (!$cat && 0 != $post->post_category) { // If there's no result
				$gpdb->insert( $gpdb->post2cat, array('post_id' => $post->ID, 'category_id' => $post->post_category) );
			}
		}
	endif;
}

/**
 * Execute changes made in Goatpress 1.0.1.
 *
 * @since 1.0.1
 */
function upgrade_101() {
	global $gpdb;

	// Clean up indices, add a few
	add_clean_index($gpdb->posts, 'post_name');
	add_clean_index($gpdb->posts, 'post_status');
	add_clean_index($gpdb->categories, 'category_nicename');
	add_clean_index($gpdb->comments, 'comment_approved');
	add_clean_index($gpdb->comments, 'comment_post_ID');
	add_clean_index($gpdb->links , 'link_category');
	add_clean_index($gpdb->links , 'link_visible');
}

/**
 * Execute changes made in Goatpress 1.2.
 *
 * @since 1.2.0
 */
function upgrade_110() {
	global $gpdb;

	// Set user_nicename.
	$users = $gpdb->get_results("SELECT ID, user_nickname, user_nicename FROM $gpdb->users");
	foreach ($users as $user) {
		if ('' == $user->user_nicename) {
			$newname = sanitize_title($user->user_nickname);
			$gpdb->update( $gpdb->users, array('user_nicename' => $newname), array('ID' => $user->ID) );
		}
	}

	$users = $gpdb->get_results("SELECT ID, user_pass from $gpdb->users");
	foreach ($users as $row) {
		if (!preg_match('/^[A-Fa-f0-9]{32}$/', $row->user_pass)) {
			$gpdb->update( $gpdb->users, array('user_pass' => md5($row->user_pass)), array('ID' => $row->ID) );
		}
	}

	// Get the GMT offset, we'll use that later on
	$all_options = get_alloptions_110();

	$time_difference = $all_options->time_difference;

		$server_time = time()+date('Z');
	$weblogger_time = $server_time + $time_difference * HOUR_IN_SECONDS;
	$gmt_time = time();

	$diff_gmt_server = ($gmt_time - $server_time) / HOUR_IN_SECONDS;
	$diff_weblogger_server = ($weblogger_time - $server_time) / HOUR_IN_SECONDS;
	$diff_gmt_weblogger = $diff_gmt_server - $diff_weblogger_server;
	$gmt_offset = -$diff_gmt_weblogger;

	// Add a gmt_offset option, with value $gmt_offset
	add_option('gmt_offset', $gmt_offset);

	// Check if we already set the GMT fields (if we did, then
	// MAX(post_date_gmt) can't be '0000-00-00 00:00:00'
	// <michel_v> I just slapped myself silly for not thinking about it earlier
	$got_gmt_fields = ! ($gpdb->get_var("SELECT MAX(post_date_gmt) FROM $gpdb->posts") == '0000-00-00 00:00:00');

	if (!$got_gmt_fields) {

		// Add or subtract time to all dates, to get GMT dates
		$add_hours = intval($diff_gmt_weblogger);
		$add_minutes = intval(60 * ($diff_gmt_weblogger - $add_hours));
		$gpdb->query("UPDATE $gpdb->posts SET post_date_gmt = DATE_ADD(post_date, INTERVAL '$add_hours:$add_minutes' HOUR_MINUTE)");
		$gpdb->query("UPDATE $gpdb->posts SET post_modified = post_date");
		$gpdb->query("UPDATE $gpdb->posts SET post_modified_gmt = DATE_ADD(post_modified, INTERVAL '$add_hours:$add_minutes' HOUR_MINUTE) WHERE post_modified != '0000-00-00 00:00:00'");
		$gpdb->query("UPDATE $gpdb->comments SET comment_date_gmt = DATE_ADD(comment_date, INTERVAL '$add_hours:$add_minutes' HOUR_MINUTE)");
		$gpdb->query("UPDATE $gpdb->users SET user_registered = DATE_ADD(user_registered, INTERVAL '$add_hours:$add_minutes' HOUR_MINUTE)");
	}

}

/**
 * Execute changes made in Goatpress 1.5.
 *
 * @since 1.5.0
 */
function upgrade_130() {
	global $gpdb;

	// Remove extraneous backslashes.
	$posts = $gpdb->get_results("SELECT ID, post_title, post_content, post_excerpt, guid, post_date, post_name, post_status, post_author FROM $gpdb->posts");
	if ($posts) {
		foreach($posts as $post) {
			$post_content = addslashes(deslash($post->post_content));
			$post_title = addslashes(deslash($post->post_title));
			$post_excerpt = addslashes(deslash($post->post_excerpt));
			if ( empty($post->guid) )
				$guid = get_permalink($post->ID);
			else
				$guid = $post->guid;

			$gpdb->update( $gpdb->posts, compact('post_title', 'post_content', 'post_excerpt', 'guid'), array('ID' => $post->ID) );

		}
	}

	// Remove extraneous backslashes.
	$comments = $gpdb->get_results("SELECT comment_ID, comment_author, comment_content FROM $gpdb->comments");
	if ($comments) {
		foreach($comments as $comment) {
			$comment_content = deslash($comment->comment_content);
			$comment_author = deslash($comment->comment_author);

			$gpdb->update($gpdb->comments, compact('comment_content', 'comment_author'), array('comment_ID' => $comment->comment_ID) );
		}
	}

	// Remove extraneous backslashes.
	$links = $gpdb->get_results("SELECT link_id, link_name, link_description FROM $gpdb->links");
	if ($links) {
		foreach($links as $link) {
			$link_name = deslash($link->link_name);
			$link_description = deslash($link->link_description);

			$gpdb->update( $gpdb->links, compact('link_name', 'link_description'), array('link_id' => $link->link_id) );
		}
	}

	$active_plugins = __get_option('active_plugins');

	/*
	 * If plugins are not stored in an array, they're stored in the old
	 * newline separated format. Convert to new format.
	 */
	if ( !is_array( $active_plugins ) ) {
		$active_plugins = explode("\n", trim($active_plugins));
		update_option('active_plugins', $active_plugins);
	}

	// Obsolete tables
	$gpdb->query('DROP TABLE IF EXISTS ' . $gpdb->prefix . 'optionvalues');
	$gpdb->query('DROP TABLE IF EXISTS ' . $gpdb->prefix . 'optiontypes');
	$gpdb->query('DROP TABLE IF EXISTS ' . $gpdb->prefix . 'optiongroups');
	$gpdb->query('DROP TABLE IF EXISTS ' . $gpdb->prefix . 'optiongroup_options');

	// Update comments table to use comment_type
	$gpdb->query("UPDATE $gpdb->comments SET comment_type='trackback', comment_content = REPLACE(comment_content, '<trackback />', '') WHERE comment_content LIKE '<trackback />%'");
	$gpdb->query("UPDATE $gpdb->comments SET comment_type='pingback', comment_content = REPLACE(comment_content, '<pingback />', '') WHERE comment_content LIKE '<pingback />%'");

	// Some versions have multiple duplicate option_name rows with the same values
	$options = $gpdb->get_results("SELECT option_name, COUNT(option_name) AS dupes FROM `$gpdb->options` GROUP BY option_name");
	foreach ( $options as $option ) {
		if ( 1 != $option->dupes ) { // Could this be done in the query?
			$limit = $option->dupes - 1;
			$dupe_ids = $gpdb->get_col( $gpdb->prepare("SELECT option_id FROM $gpdb->options WHERE option_name = %s LIMIT %d", $option->option_name, $limit) );
			if ( $dupe_ids ) {
				$dupe_ids = join($dupe_ids, ',');
				$gpdb->query("DELETE FROM $gpdb->options WHERE option_id IN ($dupe_ids)");
			}
		}
	}

	make_site_theme();
}

/**
 * Execute changes made in Goatpress 2.0.
 *
 * @since 2.0.0
 */
function upgrade_160() {
	global $gpdb, $gp_current_db_version;

	populate_roles_160();

	$users = $gpdb->get_results("SELECT * FROM $gpdb->users");
	foreach ( $users as $user ) :
		if ( !empty( $user->user_firstname ) )
			update_user_meta( $user->ID, 'first_name', gp_slash($user->user_firstname) );
		if ( !empty( $user->user_lastname ) )
			update_user_meta( $user->ID, 'last_name', gp_slash($user->user_lastname) );
		if ( !empty( $user->user_nickname ) )
			update_user_meta( $user->ID, 'nickname', gp_slash($user->user_nickname) );
		if ( !empty( $user->user_level ) )
			update_user_meta( $user->ID, $gpdb->prefix . 'user_level', $user->user_level );
		if ( !empty( $user->user_icq ) )
			update_user_meta( $user->ID, 'icq', gp_slash($user->user_icq) );
		if ( !empty( $user->user_aim ) )
			update_user_meta( $user->ID, 'aim', gp_slash($user->user_aim) );
		if ( !empty( $user->user_msn ) )
			update_user_meta( $user->ID, 'msn', gp_slash($user->user_msn) );
		if ( !empty( $user->user_yim ) )
			update_user_meta( $user->ID, 'yim', gp_slash($user->user_icq) );
		if ( !empty( $user->user_description ) )
			update_user_meta( $user->ID, 'description', gp_slash($user->user_description) );

		if ( isset( $user->user_idmode ) ):
			$idmode = $user->user_idmode;
			if ($idmode == 'nickname') $id = $user->user_nickname;
			if ($idmode == 'login') $id = $user->user_login;
			if ($idmode == 'firstname') $id = $user->user_firstname;
			if ($idmode == 'lastname') $id = $user->user_lastname;
			if ($idmode == 'namefl') $id = $user->user_firstname.' '.$user->user_lastname;
			if ($idmode == 'namelf') $id = $user->user_lastname.' '.$user->user_firstname;
			if (!$idmode) $id = $user->user_nickname;
			$gpdb->update( $gpdb->users, array('display_name' => $id), array('ID' => $user->ID) );
		endif;

		// FIXME: RESET_CAPS is temporary code to reset roles and caps if flag is set.
		$caps = get_user_meta( $user->ID, $gpdb->prefix . 'capabilities');
		if ( empty($caps) || defined('RESET_CAPS') ) {
			$level = get_user_meta($user->ID, $gpdb->prefix . 'user_level', true);
			$role = translate_level_to_role($level);
			update_user_meta( $user->ID, $gpdb->prefix . 'capabilities', array($role => true) );
		}

	endforeach;
	$old_user_fields = array( 'user_firstname', 'user_lastname', 'user_icq', 'user_aim', 'user_msn', 'user_yim', 'user_idmode', 'user_ip', 'user_domain', 'user_browser', 'user_description', 'user_nickname', 'user_level' );
	$gpdb->hide_errors();
	foreach ( $old_user_fields as $old )
		$gpdb->query("ALTER TABLE $gpdb->users DROP $old");
	$gpdb->show_errors();

	// Populate comment_count field of posts table.
	$comments = $gpdb->get_results( "SELECT comment_post_ID, COUNT(*) as c FROM $gpdb->comments WHERE comment_approved = '1' GROUP BY comment_post_ID" );
	if ( is_array( $comments ) )
		foreach ($comments as $comment)
			$gpdb->update( $gpdb->posts, array('comment_count' => $comment->c), array('ID' => $comment->comment_post_ID) );

	/*
	 * Some alpha versions used a post status of object instead of attachment
	 * and put the mime type in post_type instead of post_mime_type.
	 */
	if ( $gp_current_db_version > 2541 && $gp_current_db_version <= 3091 ) {
		$objects = $gpdb->get_results("SELECT ID, post_type FROM $gpdb->posts WHERE post_status = 'object'");
		foreach ($objects as $object) {
			$gpdb->update( $gpdb->posts, array(	'post_status' => 'attachment',
												'post_mime_type' => $object->post_type,
												'post_type' => ''),
										 array( 'ID' => $object->ID ) );

			$meta = get_post_meta($object->ID, 'imagedata', true);
			if ( ! empty($meta['file']) )
				update_attached_file( $object->ID, $meta['file'] );
		}
	}
}

/**
 * Execute changes made in Goatpress 2.1.
 *
 * @since 2.1.0
 */
function upgrade_210() {
	global $gpdb, $gp_current_db_version;

	if ( $gp_current_db_version < 3506 ) {
		// Update status and type.
		$posts = $gpdb->get_results("SELECT ID, post_status FROM $gpdb->posts");

		if ( ! empty($posts) ) foreach ($posts as $post) {
			$status = $post->post_status;
			$type = 'post';

			if ( 'static' == $status ) {
				$status = 'publish';
				$type = 'page';
			} elseif ( 'attachment' == $status ) {
				$status = 'inherit';
				$type = 'attachment';
			}

			$gpdb->query( $gpdb->prepare("UPDATE $gpdb->posts SET post_status = %s, post_type = %s WHERE ID = %d", $status, $type, $post->ID) );
		}
	}

	if ( $gp_current_db_version < 3845 ) {
		populate_roles_210();
	}

	if ( $gp_current_db_version < 3531 ) {
		// Give future posts a post_status of future.
		$now = gmdate('Y-m-d H:i:59');
		$gpdb->query ("UPDATE $gpdb->posts SET post_status = 'future' WHERE post_status = 'publish' AND post_date_gmt > '$now'");

		$posts = $gpdb->get_results("SELECT ID, post_date FROM $gpdb->posts WHERE post_status ='future'");
		if ( !empty($posts) )
			foreach ( $posts as $post )
				gp_schedule_single_event(mysql2date('U', $post->post_date, false), 'publish_future_post', array($post->ID));
	}
}

/**
 * Execute changes made in Goatpress 2.3.
 *
 * @since 2.3.0
 */
function upgrade_230() {
	global $gp_current_db_version, $gpdb;

	if ( $gp_current_db_version < 5200 ) {
		populate_roles_230();
	}

	// Convert categories to terms.
	$tt_ids = array();
	$have_tags = false;
	$categories = $gpdb->get_results("SELECT * FROM $gpdb->categories ORDER BY cat_ID");
	foreach ($categories as $category) {
		$term_id = (int) $category->cat_ID;
		$name = $category->cat_name;
		$description = $category->category_description;
		$slug = $category->category_nicename;
		$parent = $category->category_parent;
		$term_group = 0;

		// Associate terms with the same slug in a term group and make slugs unique.
		if ( $exists = $gpdb->get_results( $gpdb->prepare("SELECT term_id, term_group FROM $gpdb->terms WHERE slug = %s", $slug) ) ) {
			$term_group = $exists[0]->term_group;
			$id = $exists[0]->term_id;
			$num = 2;
			do {
				$alt_slug = $slug . "-$num";
				$num++;
				$slug_check = $gpdb->get_var( $gpdb->prepare("SELECT slug FROM $gpdb->terms WHERE slug = %s", $alt_slug) );
			} while ( $slug_check );

			$slug = $alt_slug;

			if ( empty( $term_group ) ) {
				$term_group = $gpdb->get_var("SELECT MAX(term_group) FROM $gpdb->terms GROUP BY term_group") + 1;
				$gpdb->query( $gpdb->prepare("UPDATE $gpdb->terms SET term_group = %d WHERE term_id = %d", $term_group, $id) );
			}
		}

		$gpdb->query( $gpdb->prepare("INSERT INTO $gpdb->terms (term_id, name, slug, term_group) VALUES
		(%d, %s, %s, %d)", $term_id, $name, $slug, $term_group) );

		$count = 0;
		if ( !empty($category->category_count) ) {
			$count = (int) $category->category_count;
			$taxonomy = 'category';
			$gpdb->query( $gpdb->prepare("INSERT INTO $gpdb->term_taxonomy (term_id, taxonomy, description, parent, count) VALUES ( %d, %s, %s, %d, %d)", $term_id, $taxonomy, $description, $parent, $count) );
			$tt_ids[$term_id][$taxonomy] = (int) $gpdb->insert_id;
		}

		if ( !empty($category->link_count) ) {
			$count = (int) $category->link_count;
			$taxonomy = 'link_category';
			$gpdb->query( $gpdb->prepare("INSERT INTO $gpdb->term_taxonomy (term_id, taxonomy, description, parent, count) VALUES ( %d, %s, %s, %d, %d)", $term_id, $taxonomy, $description, $parent, $count) );
			$tt_ids[$term_id][$taxonomy] = (int) $gpdb->insert_id;
		}

		if ( !empty($category->tag_count) ) {
			$have_tags = true;
			$count = (int) $category->tag_count;
			$taxonomy = 'post_tag';
			$gpdb->insert( $gpdb->term_taxonomy, compact('term_id', 'taxonomy', 'description', 'parent', 'count') );
			$tt_ids[$term_id][$taxonomy] = (int) $gpdb->insert_id;
		}

		if ( empty($count) ) {
			$count = 0;
			$taxonomy = 'category';
			$gpdb->insert( $gpdb->term_taxonomy, compact('term_id', 'taxonomy', 'description', 'parent', 'count') );
			$tt_ids[$term_id][$taxonomy] = (int) $gpdb->insert_id;
		}
	}

	$select = 'post_id, category_id';
	if ( $have_tags )
		$select .= ', rel_type';

	$posts = $gpdb->get_results("SELECT $select FROM $gpdb->post2cat GROUP BY post_id, category_id");
	foreach ( $posts as $post ) {
		$post_id = (int) $post->post_id;
		$term_id = (int) $post->category_id;
		$taxonomy = 'category';
		if ( !empty($post->rel_type) && 'tag' == $post->rel_type)
			$taxonomy = 'tag';
		$tt_id = $tt_ids[$term_id][$taxonomy];
		if ( empty($tt_id) )
			continue;

		$gpdb->insert( $gpdb->term_relationships, array('object_id' => $post_id, 'term_taxonomy_id' => $tt_id) );
	}

	// < 3570 we used linkcategories. >= 3570 we used categories and link2cat.
	if ( $gp_current_db_version < 3570 ) {
		/*
		 * Create link_category terms for link categories. Create a map of link
		 * cat IDs to link_category terms.
		 */
		$link_cat_id_map = array();
		$default_link_cat = 0;
		$tt_ids = array();
		$link_cats = $gpdb->get_results("SELECT cat_id, cat_name FROM " . $gpdb->prefix . 'linkcategories');
		foreach ( $link_cats as $category) {
			$cat_id = (int) $category->cat_id;
			$term_id = 0;
			$name = gp_slash($category->cat_name);
			$slug = sanitize_title($name);
			$term_group = 0;

			// Associate terms with the same slug in a term group and make slugs unique.
			if ( $exists = $gpdb->get_results( $gpdb->prepare("SELECT term_id, term_group FROM $gpdb->terms WHERE slug = %s", $slug) ) ) {
				$term_group = $exists[0]->term_group;
				$term_id = $exists[0]->term_id;
			}

			if ( empty($term_id) ) {
				$gpdb->insert( $gpdb->terms, compact('name', 'slug', 'term_group') );
				$term_id = (int) $gpdb->insert_id;
			}

			$link_cat_id_map[$cat_id] = $term_id;
			$default_link_cat = $term_id;

			$gpdb->insert( $gpdb->term_taxonomy, array('term_id' => $term_id, 'taxonomy' => 'link_category', 'description' => '', 'parent' => 0, 'count' => 0) );
			$tt_ids[$term_id] = (int) $gpdb->insert_id;
		}

		// Associate links to cats.
		$links = $gpdb->get_results("SELECT link_id, link_category FROM $gpdb->links");
		if ( !empty($links) ) foreach ( $links as $link ) {
			if ( 0 == $link->link_category )
				continue;
			if ( ! isset($link_cat_id_map[$link->link_category]) )
				continue;
			$term_id = $link_cat_id_map[$link->link_category];
			$tt_id = $tt_ids[$term_id];
			if ( empty($tt_id) )
				continue;

			$gpdb->insert( $gpdb->term_relationships, array('object_id' => $link->link_id, 'term_taxonomy_id' => $tt_id) );
		}

		// Set default to the last category we grabbed during the upgrade loop.
		update_option('default_link_category', $default_link_cat);
	} else {
		$links = $gpdb->get_results("SELECT link_id, category_id FROM $gpdb->link2cat GROUP BY link_id, category_id");
		foreach ( $links as $link ) {
			$link_id = (int) $link->link_id;
			$term_id = (int) $link->category_id;
			$taxonomy = 'link_category';
			$tt_id = $tt_ids[$term_id][$taxonomy];
			if ( empty($tt_id) )
				continue;
			$gpdb->insert( $gpdb->term_relationships, array('object_id' => $link_id, 'term_taxonomy_id' => $tt_id) );
		}
	}

	if ( $gp_current_db_version < 4772 ) {
		// Obsolete linkcategories table
		$gpdb->query('DROP TABLE IF EXISTS ' . $gpdb->prefix . 'linkcategories');
	}

	// Recalculate all counts
	$terms = $gpdb->get_results("SELECT term_taxonomy_id, taxonomy FROM $gpdb->term_taxonomy");
	foreach ( (array) $terms as $term ) {
		if ( ('post_tag' == $term->taxonomy) || ('category' == $term->taxonomy) )
			$count = $gpdb->get_var( $gpdb->prepare("SELECT COUNT(*) FROM $gpdb->term_relationships, $gpdb->posts WHERE $gpdb->posts.ID = $gpdb->term_relationships.object_id AND post_status = 'publish' AND post_type = 'post' AND term_taxonomy_id = %d", $term->term_taxonomy_id) );
		else
			$count = $gpdb->get_var( $gpdb->prepare("SELECT COUNT(*) FROM $gpdb->term_relationships WHERE term_taxonomy_id = %d", $term->term_taxonomy_id) );
		$gpdb->update( $gpdb->term_taxonomy, array('count' => $count), array('term_taxonomy_id' => $term->term_taxonomy_id) );
	}
}

/**
 * Remove old options from the database.
 *
 * @since 2.3.0
 */
function upgrade_230_options_table() {
	global $gpdb;
	$old_options_fields = array( 'option_can_override', 'option_type', 'option_width', 'option_height', 'option_description', 'option_admin_level' );
	$gpdb->hide_errors();
	foreach ( $old_options_fields as $old )
		$gpdb->query("ALTER TABLE $gpdb->options DROP $old");
	$gpdb->show_errors();
}

/**
 * Remove old categories, link2cat, and post2cat database tables.
 *
 * @since 2.3.0
 */
function upgrade_230_old_tables() {
	global $gpdb;
	$gpdb->query('DROP TABLE IF EXISTS ' . $gpdb->prefix . 'categories');
	$gpdb->query('DROP TABLE IF EXISTS ' . $gpdb->prefix . 'link2cat');
	$gpdb->query('DROP TABLE IF EXISTS ' . $gpdb->prefix . 'post2cat');
}

/**
 * Upgrade old slugs made in version 2.2.
 *
 * @since 2.2.0
 */
function upgrade_old_slugs() {
	// Upgrade people who were using the Redirect Old Slugs plugin.
	global $gpdb;
	$gpdb->query("UPDATE $gpdb->postmeta SET meta_key = '_gp_old_slug' WHERE meta_key = 'old_slug'");
}

/**
 * Execute changes made in Goatpress 2.5.0.
 *
 * @since 2.5.0
 */
function upgrade_250() {
	global $gp_current_db_version;

	if ( $gp_current_db_version < 6689 ) {
		populate_roles_250();
	}

}

/**
 * Execute changes made in Goatpress 2.5.2.
 *
 * @since 2.5.2
 */
function upgrade_252() {
	global $gpdb;

	$gpdb->query("UPDATE $gpdb->users SET user_activation_key = ''");
}

/**
 * Execute changes made in Goatpress 2.6.
 *
 * @since 2.6.0
 */
function upgrade_260() {
	global $gp_current_db_version;

	if ( $gp_current_db_version < 8000 )
		populate_roles_260();
}

/**
 * Execute changes made in Goatpress 2.7.
 *
 * @since 2.7.0
 */
function upgrade_270() {
	global $gpdb, $gp_current_db_version;

	if ( $gp_current_db_version < 8980 )
		populate_roles_270();

	// Update post_date for unpublished posts with empty timestamp
	if ( $gp_current_db_version < 8921 )
		$gpdb->query( "UPDATE $gpdb->posts SET post_date = post_modified WHERE post_date = '0000-00-00 00:00:00'" );
}

/**
 * Execute changes made in Goatpress 2.8.
 *
 * @since 2.8.0
 */
function upgrade_280() {
	global $gp_current_db_version, $gpdb;

	if ( $gp_current_db_version < 10360 )
		populate_roles_280();
	if ( is_multisite() ) {
		$start = 0;
		while( $rows = $gpdb->get_results( "SELECT option_name, option_value FROM $gpdb->options ORDER BY option_id LIMIT $start, 20" ) ) {
			foreach( $rows as $row ) {
				$value = $row->option_value;
				if ( !@unserialize( $value ) )
					$value = stripslashes( $value );
				if ( $value !== $row->option_value ) {
					update_option( $row->option_name, $value );
				}
			}
			$start += 20;
		}
		refresh_blog_details( $gpdb->blogid );
	}
}

/**
 * Execute changes made in Goatpress 2.9.
 *
 * @since 2.9.0
 */
function upgrade_290() {
	global $gp_current_db_version;

	if ( $gp_current_db_version < 11958 ) {
		// Previously, setting depth to 1 would redundantly disable threading, but now 2 is the minimum depth to avoid confusion
		if ( get_option( 'thread_comments_depth' ) == '1' ) {
			update_option( 'thread_comments_depth', 2 );
			update_option( 'thread_comments', 0 );
		}
	}
}

/**
 * Execute changes made in Goatpress 3.0.
 *
 * @since 3.0.0
 */
function upgrade_300() {
	global $gp_current_db_version, $gpdb;

	if ( $gp_current_db_version < 15093 )
		populate_roles_300();

	if ( $gp_current_db_version < 14139 && is_multisite() && is_main_site() && ! defined( 'MULTISITE' ) && get_site_option( 'siteurl' ) === false )
		add_site_option( 'siteurl', '' );

	// 3.0 screen options key name changes.
	if ( is_main_site() && !defined('DO_NOT_UPGRADE_GLOBAL_TABLES') ) {
		$sql = "DELETE FROM $gpdb->usermeta
			WHERE meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key = 'manageedittagscolumnshidden'
			OR meta_key = 'managecategoriescolumnshidden'
			OR meta_key = 'manageedit-tagscolumnshidden'
			OR meta_key = 'manageeditcolumnshidden'
			OR meta_key = 'categories_per_page'
			OR meta_key = 'edit_tags_per_page'";
		$prefix = $gpdb->esc_like( $gpdb->base_prefix );
		$gpdb->query( $gpdb->prepare( $sql,
			$prefix . '%' . $gpdb->esc_like( 'meta-box-hidden' ) . '%',
			$prefix . '%' . $gpdb->esc_like( 'closedpostboxes' ) . '%',
			$prefix . '%' . $gpdb->esc_like( 'manage-'	   ) . '%' . $gpdb->esc_like( '-columns-hidden' ) . '%',
			$prefix . '%' . $gpdb->esc_like( 'meta-box-order'  ) . '%',
			$prefix . '%' . $gpdb->esc_like( 'metaboxorder'    ) . '%',
			$prefix . '%' . $gpdb->esc_like( 'screen_layout'   ) . '%'
		) );
	}

}

/**
 * Execute changes made in Goatpress 3.3.
 *
 * @since 3.3.0
 */
function upgrade_330() {
	global $gp_current_db_version, $gpdb, $gp_registered_widgets, $sidebars_widgets;

	if ( $gp_current_db_version < 19061 && is_main_site() && ! defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) {
		$gpdb->query( "DELETE FROM $gpdb->usermeta WHERE meta_key IN ('show_admin_bar_admin', 'plugins_last_view')" );
	}

	if ( $gp_current_db_version >= 11548 )
		return;

	$sidebars_widgets = get_option( 'sidebars_widgets', array() );
	$_sidebars_widgets = array();

	if ( isset($sidebars_widgets['gp_inactive_widgets']) || empty($sidebars_widgets) )
		$sidebars_widgets['array_version'] = 3;
	elseif ( !isset($sidebars_widgets['array_version']) )
		$sidebars_widgets['array_version'] = 1;

	switch ( $sidebars_widgets['array_version'] ) {
		case 1 :
			foreach ( (array) $sidebars_widgets as $index => $sidebar )
			if ( is_array($sidebar) )
			foreach ( (array) $sidebar as $i => $name ) {
				$id = strtolower($name);
				if ( isset($gp_registered_widgets[$id]) ) {
					$_sidebars_widgets[$index][$i] = $id;
					continue;
				}
				$id = sanitize_title($name);
				if ( isset($gp_registered_widgets[$id]) ) {
					$_sidebars_widgets[$index][$i] = $id;
					continue;
				}

				$found = false;

				foreach ( $gp_registered_widgets as $widget_id => $widget ) {
					if ( strtolower($widget['name']) == strtolower($name) ) {
						$_sidebars_widgets[$index][$i] = $widget['id'];
						$found = true;
						break;
					} elseif ( sanitize_title($widget['name']) == sanitize_title($name) ) {
						$_sidebars_widgets[$index][$i] = $widget['id'];
						$found = true;
						break;
					}
				}

				if ( $found )
					continue;

				unset($_sidebars_widgets[$index][$i]);
			}
			$_sidebars_widgets['array_version'] = 2;
			$sidebars_widgets = $_sidebars_widgets;
			unset($_sidebars_widgets);

		case 2 :
			$sidebars_widgets = retrieve_widgets();
			$sidebars_widgets['array_version'] = 3;
			update_option( 'sidebars_widgets', $sidebars_widgets );
	}
}

/**
 * Execute changes made in Goatpress 3.4.
 *
 * @since 3.4.0
 */
function upgrade_340() {
	global $gp_current_db_version, $gpdb;

	if ( $gp_current_db_version < 19798 ) {
		$gpdb->hide_errors();
		$gpdb->query( "ALTER TABLE $gpdb->options DROP COLUMN blog_id" );
		$gpdb->show_errors();
	}

	if ( $gp_current_db_version < 19799 ) {
		$gpdb->hide_errors();
		$gpdb->query("ALTER TABLE $gpdb->comments DROP INDEX comment_approved");
		$gpdb->show_errors();
	}

	if ( $gp_current_db_version < 20022 && is_main_site() && ! defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) {
		$gpdb->query( "DELETE FROM $gpdb->usermeta WHERE meta_key = 'themes_last_view'" );
	}

	if ( $gp_current_db_version < 20080 ) {
		if ( 'yes' == $gpdb->get_var( "SELECT autoload FROM $gpdb->options WHERE option_name = 'uninstall_plugins'" ) ) {
			$uninstall_plugins = get_option( 'uninstall_plugins' );
			delete_option( 'uninstall_plugins' );
			add_option( 'uninstall_plugins', $uninstall_plugins, null, 'no' );
		}
	}
}

/**
 * Execute changes made in Goatpress 3.5.
 *
 * @since 3.5.0
 */
function upgrade_350() {
	global $gp_current_db_version, $gpdb;

	if ( $gp_current_db_version < 22006 && $gpdb->get_var( "SELECT link_id FROM $gpdb->links LIMIT 1" ) )
		update_option( 'link_manager_enabled', 1 ); // Previously set to 0 by populate_options()

	if ( $gp_current_db_version < 21811 && is_main_site() && ! defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) {
		$meta_keys = array();
		foreach ( array_merge( get_post_types(), get_taxonomies() ) as $name ) {
			if ( false !== strpos( $name, '-' ) )
			$meta_keys[] = 'edit_' . str_replace( '-', '_', $name ) . '_per_page';
		}
		if ( $meta_keys ) {
			$meta_keys = implode( "', '", $meta_keys );
			$gpdb->query( "DELETE FROM $gpdb->usermeta WHERE meta_key IN ('$meta_keys')" );
		}
	}

	if ( $gp_current_db_version < 22422 && $term = get_term_by( 'slug', 'post-format-standard', 'post_format' ) )
		gp_delete_term( $term->term_id, 'post_format' );
}

/**
 * Execute changes made in Goatpress 3.7.
 *
 * @since 3.7.0
 */
function upgrade_370() {
	global $gp_current_db_version;
	if ( $gp_current_db_version < 25824 )
		gp_clear_scheduled_hook( 'gp_auto_updates_maybe_update' );
}

/**
 * Execute changes made in Goatpress 3.7.2.
 *
 * @since 3.7.2
 * @since 3.8.0
 */
function upgrade_372() {
	global $gp_current_db_version;
	if ( $gp_current_db_version < 26148 )
		gp_clear_scheduled_hook( 'gp_maybe_auto_update' );
}

/**
 * Execute changes made in Goatpress 3.8.0.
 *
 * @since 3.8.0
 */
function upgrade_380() {
	global $gp_current_db_version;
	if ( $gp_current_db_version < 26691 ) {
		deactivate_plugins( array( 'mp6/mp6.php' ), true );
	}
}

/**
 * Execute changes made in Goatpress 4.0.0.
 *
 * @since 4.0.0
 */
function upgrade_400() {
	global $gp_current_db_version;
	if ( $gp_current_db_version < 29630 ) {
		if ( ! is_multisite() && false === get_option( 'gpLANG' ) ) {
			if ( defined( 'gpLANG' ) && ( '' !== gpLANG ) && in_array( gpLANG, get_available_languages() ) ) {
				update_option( 'gpLANG', gpLANG );
			} else {
				update_option( 'gpLANG', '' );
			}
		}
	}
}

/**
 * Execute changes made in Goatpress 4.2.0.
 *
 * @since 4.2.0
 */
function upgrade_420() {
	global $gp_current_db_version, $gpdb;

	if ( $gp_current_db_version < 31351 && $gpdb->charset === 'utf8mb4' ) {
		if ( is_multisite() ) {
			$tables = $gpdb->tables( 'blog' );
		} else {
			$tables = $gpdb->tables( 'all' );
		}

		foreach ( $tables as $table ) {
			maybe_convert_table_to_utf8mb4( $table );
		}
	}
}

/**
 * Executes network-level upgrade routines.
 *
 * @since 3.0.0
 */
function upgrade_network() {
	global $gp_current_db_version, $gpdb;

	// Always.
	if ( is_main_network() ) {
		/*
		 * Deletes all expired transients. The multi-table delete syntax is used
		 * to delete the transient record from table a, and the corresponding
		 * transient_timeout record from table b.
		 */
		$time = time();
		$sql = "DELETE a, b FROM $gpdb->sitemeta a, $gpdb->sitemeta b
			WHERE a.meta_key LIKE %s
			AND a.meta_key NOT LIKE %s
			AND b.meta_key = CONCAT( '_site_transient_timeout_', SUBSTRING( a.meta_key, 17 ) )
			AND b.meta_value < %d";
		$gpdb->query( $gpdb->prepare( $sql, $gpdb->esc_like( '_site_transient_' ) . '%', $gpdb->esc_like ( '_site_transient_timeout_' ) . '%', $time ) );
	}

	// 2.8.
	if ( $gp_current_db_version < 11549 ) {
		$gpmu_sitewide_plugins = get_site_option( 'gpmu_sitewide_plugins' );
		$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins' );
		if ( $gpmu_sitewide_plugins ) {
			if ( !$active_sitewide_plugins )
				$sitewide_plugins = (array) $gpmu_sitewide_plugins;
			else
				$sitewide_plugins = array_merge( (array) $active_sitewide_plugins, (array) $gpmu_sitewide_plugins );

			update_site_option( 'active_sitewide_plugins', $sitewide_plugins );
		}
		delete_site_option( 'gpmu_sitewide_plugins' );
		delete_site_option( 'deactivated_sitewide_plugins' );

		$start = 0;
		while( $rows = $gpdb->get_results( "SELECT meta_key, meta_value FROM {$gpdb->sitemeta} ORDER BY meta_id LIMIT $start, 20" ) ) {
			foreach( $rows as $row ) {
				$value = $row->meta_value;
				if ( !@unserialize( $value ) )
					$value = stripslashes( $value );
				if ( $value !== $row->meta_value ) {
					update_site_option( $row->meta_key, $value );
				}
			}
			$start += 20;
		}
	}

	// 3.0
	if ( $gp_current_db_version < 13576 )
		update_site_option( 'global_terms_enabled', '1' );

	// 3.3
	if ( $gp_current_db_version < 19390 )
		update_site_option( 'initial_db_version', $gp_current_db_version );

	if ( $gp_current_db_version < 19470 ) {
		if ( false === get_site_option( 'active_sitewide_plugins' ) )
			update_site_option( 'active_sitewide_plugins', array() );
	}

	// 3.4
	if ( $gp_current_db_version < 20148 ) {
		// 'allowedthemes' keys things by stylesheet. 'allowed_themes' keyed things by name.
		$allowedthemes  = get_site_option( 'allowedthemes'  );
		$allowed_themes = get_site_option( 'allowed_themes' );
		if ( false === $allowedthemes && is_array( $allowed_themes ) && $allowed_themes ) {
			$converted = array();
			$themes = gp_get_themes();
			foreach ( $themes as $stylesheet => $theme_data ) {
				if ( isset( $allowed_themes[ $theme_data->get('Name') ] ) )
					$converted[ $stylesheet ] = true;
			}
			update_site_option( 'allowedthemes', $converted );
			delete_site_option( 'allowed_themes' );
		}
	}

	// 3.5
	if ( $gp_current_db_version < 21823 )
		update_site_option( 'ms_files_rewriting', '1' );

	// 3.5.2
	if ( $gp_current_db_version < 24448 ) {
		$illegal_names = get_site_option( 'illegal_names' );
		if ( is_array( $illegal_names ) && count( $illegal_names ) === 1 ) {
			$illegal_name = reset( $illegal_names );
			$illegal_names = explode( ' ', $illegal_name );
			update_site_option( 'illegal_names', $illegal_names );
		}
	}

	// 4.2
	if ( $gp_current_db_version < 31351 && $gpdb->charset === 'utf8mb4' ) {
		if ( ! ( defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) && DO_NOT_UPGRADE_GLOBAL_TABLES ) ) {
			$gpdb->query( "ALTER TABLE $gpdb->usermeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
			$gpdb->query( "ALTER TABLE $gpdb->site DROP INDEX domain, ADD INDEX domain(domain(140),path(51))" );
			$gpdb->query( "ALTER TABLE $gpdb->sitemeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
			$gpdb->query( "ALTER TABLE $gpdb->signups DROP INDEX domain, ADD INDEX domain(domain(140),path(51))" );

			$tables = $gpdb->tables( 'global' );

			foreach ( $tables as $table ) {
				maybe_convert_table_to_utf8mb4( $table );
			}
		}
	}
}

//
// General functions we use to actually do stuff
//

/**
 * Creates a table in the database if it doesn't already exist.
 *
 * This method checks for an existing database and creates a new one if it's not
 * already present. It doesn't rely on MySQL's "IF NOT EXISTS" statement, but chooses
 * to query all tables first and then run the SQL statement creating the table.
 *
 * @since 1.0.0
 *
 * @param string $table_name Database table name to create.
 * @param string $create_ddl SQL statement to create table.
 * @return bool If table already exists or was created by function.
 */
function maybe_create_table($table_name, $create_ddl) {
	global $gpdb;

	$query = $gpdb->prepare( "SHOW TABLES LIKE %s", $gpdb->esc_like( $table_name ) );

	if ( $gpdb->get_var( $query ) == $table_name ) {
		return true;
	}

	// Didn't find it try to create it..
	$gpdb->query($create_ddl);

	// We cannot directly tell that whether this succeeded!
	if ( $gpdb->get_var( $query ) == $table_name ) {
		return true;
	}
	return false;
}

/**
 * Drops a specified index from a table.
 *
 * @since 1.0.1
 *
 * @param string $table Database table name.
 * @param string $index Index name to drop.
 * @return bool True, when finished.
 */
function drop_index($table, $index) {
	global $gpdb;
	$gpdb->hide_errors();
	$gpdb->query("ALTER TABLE `$table` DROP INDEX `$index`");
	// Now we need to take out all the extra ones we may have created
	for ($i = 0; $i < 25; $i++) {
		$gpdb->query("ALTER TABLE `$table` DROP INDEX `{$index}_$i`");
	}
	$gpdb->show_errors();
	return true;
}

/**
 * Adds an index to a specified table.
 *
 * @since 1.0.1
 *
 * @param string $table Database table name.
 * @param string $index Database table index column.
 * @return bool True, when done with execution.
 */
function add_clean_index($table, $index) {
	global $gpdb;
	drop_index($table, $index);
	$gpdb->query("ALTER TABLE `$table` ADD INDEX ( `$index` )");
	return true;
}

/**
 * Adds column to a database table if it doesn't already exist.
 *
 * @since 1.3.0
 *
 * @param string $table_name  The table name to modify.
 * @param string $column_name The column name to add to the table.
 * @param string $create_ddl  The SQL statement used to add the column.
 * @return True if already exists or on successful completion, false on error.
 */
function maybe_add_column($table_name, $column_name, $create_ddl) {
	global $gpdb;
	foreach ($gpdb->get_col("DESC $table_name", 0) as $column ) {
		if ($column == $column_name) {
			return true;
		}
	}

	// Didn't find it try to create it.
	$gpdb->query($create_ddl);

	// We cannot directly tell that whether this succeeded!
	foreach ($gpdb->get_col("DESC $table_name", 0) as $column ) {
		if ($column == $column_name) {
			return true;
		}
	}
	return false;
}

/**
 * If a table only contains utf8 or utf8mb4 columns, convert it to utf8mb4.
 *
 * @since 4.2.0
 *
 * @param string $table The table to convert.
 * @return bool true if the table was converted, false if it wasn't.
 */
function maybe_convert_table_to_utf8mb4( $table ) {
	global $gpdb;

	$results = $gpdb->get_results( "SHOW FULL COLUMNS FROM `$table`" );
	if ( ! $results ) {
		return false;
	}

	foreach ( $results as $column ) {
		if ( $column->Collation ) {
			list( $charset ) = explode( '_', $column->Collation );
			$charset = strtolower( $charset );
			if ( 'utf8' !== $charset && 'utf8mb4' !== $charset ) {
				// Don't upgrade tables that have non-utf8 columns.
				return false;
			}
		}
	}

	return $gpdb->query( "ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" );
}

/**
 * Retrieve all options as it was for 1.2.
 *
 * @since 1.2.0
 *
 * @return stdClass List of options.
 */
function get_alloptions_110() {
	global $gpdb;
	$all_options = new stdClass;
	if ( $options = $gpdb->get_results( "SELECT option_name, option_value FROM $gpdb->options" ) ) {
		foreach ( $options as $option ) {
			if ( 'siteurl' == $option->option_name || 'home' == $option->option_name || 'category_base' == $option->option_name )
				$option->option_value = untrailingslashit( $option->option_value );
			$all_options->{$option->option_name} = stripslashes( $option->option_value );
		}
	}
	return $all_options;
}

/**
 * Utility version of get_option that is private to install/upgrade.
 *
 * @ignore
 * @since 1.5.1
 * @access private
 *
 * @param string $setting Option name.
 * @return mixed
 */
function __get_option($setting) {
	global $gpdb;

	if ( $setting == 'home' && defined( 'gp_HOME' ) )
		return untrailingslashit( gp_HOME );

	if ( $setting == 'siteurl' && defined( 'gp_SITEURL' ) )
		return untrailingslashit( gp_SITEURL );

	$option = $gpdb->get_var( $gpdb->prepare("SELECT option_value FROM $gpdb->options WHERE option_name = %s", $setting ) );

	if ( 'home' == $setting && '' == $option )
		return __get_option( 'siteurl' );

	if ( 'siteurl' == $setting || 'home' == $setting || 'category_base' == $setting || 'tag_base' == $setting )
		$option = untrailingslashit( $option );

	return maybe_unserialize( $option );
}

/**
 * Filters for content to remove unnecessary slashes.
 *
 * @since 1.5.0
 *
 * @param string $content The content to modify.
 * @return string The de-slashed content.
 */
function deslash($content) {
	// Note: \\\ inside a regex denotes a single backslash.

	/*
	 * Replace one or more backslashes followed by a single quote with
	 * a single quote.
	 */
	$content = preg_replace("/\\\+'/", "'", $content);

	/*
	 * Replace one or more backslashes followed by a double quote with
	 * a double quote.
	 */
	$content = preg_replace('/\\\+"/', '"', $content);

	// Replace one or more backslashes with one backslash.
	$content = preg_replace("/\\\+/", "\\", $content);

	return $content;
}

/**
 * Modifies the database based on specified SQL statements.
 *
 * Useful for creating new tables and updating existing tables to a new structure.
 *
 * @since 1.5.0
 *
 * @param string|array $queries Optional. The query to run. Can be multiple queries
 *                              in an array, or a string of queries separated by
 *                              semicolons. Default empty.
 * @param bool         $execute Optional. Whether or not to execute the query right away.
 *                              Default true.
 * @return array Strings containing the results of the various update queries.
 */
function dbDelta( $queries = '', $execute = true ) {
	global $gpdb;

	if ( in_array( $queries, array( '', 'all', 'blog', 'global', 'ms_global' ), true ) )
	    $queries = gp_get_db_schema( $queries );

	// Separate individual queries into an array
	if ( !is_array($queries) ) {
		$queries = explode( ';', $queries );
		$queries = array_filter( $queries );
	}

	/**
	 * Filter the dbDelta SQL queries.
	 *
	 * @since 3.3.0
	 *
	 * @param array $queries An array of dbDelta SQL queries.
	 */
	$queries = apply_filters( 'dbdelta_queries', $queries );

	$cqueries = array(); // Creation Queries
	$iqueries = array(); // Insertion Queries
	$for_update = array();

	// Create a tablename index for an array ($cqueries) of queries
	foreach($queries as $qry) {
		if ( preg_match( "|CREATE TABLE ([^ ]*)|", $qry, $matches ) ) {
			$cqueries[ trim( $matches[1], '`' ) ] = $qry;
			$for_update[$matches[1]] = 'Created table '.$matches[1];
		} elseif ( preg_match( "|CREATE DATABASE ([^ ]*)|", $qry, $matches ) ) {
			array_unshift( $cqueries, $qry );
		} elseif ( preg_match( "|INSERT INTO ([^ ]*)|", $qry, $matches ) ) {
			$iqueries[] = $qry;
		} elseif ( preg_match( "|UPDATE ([^ ]*)|", $qry, $matches ) ) {
			$iqueries[] = $qry;
		} else {
			// Unrecognized query type
		}
	}

	/**
	 * Filter the dbDelta SQL queries for creating tables and/or databases.
	 *
	 * Queries filterable via this hook contain "CREATE TABLE" or "CREATE DATABASE".
	 *
	 * @since 3.3.0
	 *
	 * @param array $cqueries An array of dbDelta create SQL queries.
	 */
	$cqueries = apply_filters( 'dbdelta_create_queries', $cqueries );

	/**
	 * Filter the dbDelta SQL queries for inserting or updating.
	 *
	 * Queries filterable via this hook contain "INSERT INTO" or "UPDATE".
	 *
	 * @since 3.3.0
	 *
	 * @param array $iqueries An array of dbDelta insert or update SQL queries.
	 */
	$iqueries = apply_filters( 'dbdelta_insert_queries', $iqueries );

	$global_tables = $gpdb->tables( 'global' );
	foreach ( $cqueries as $table => $qry ) {
		// Upgrade global tables only for the main site. Don't upgrade at all if DO_NOT_UPGRADE_GLOBAL_TABLES is defined.
		if ( in_array( $table, $global_tables ) && ( !is_main_site() || defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) ) {
			unset( $cqueries[ $table ], $for_update[ $table ] );
			continue;
		}

		// Fetch the table column structure from the database
		$suppress = $gpdb->suppress_errors();
		$tablefields = $gpdb->get_results("DESCRIBE {$table};");
		$gpdb->suppress_errors( $suppress );

		if ( ! $tablefields )
			continue;

		// Clear the field and index arrays.
		$cfields = $indices = array();

		// Get all of the field names in the query from between the parentheses.
		preg_match("|\((.*)\)|ms", $qry, $match2);
		$qryline = trim($match2[1]);

		// Separate field lines into an array.
		$flds = explode("\n", $qryline);

		// todo: Remove this?
		//echo "<hr/><pre>\n".print_r(strtolower($table), true).":\n".print_r($cqueries, true)."</pre><hr/>";

		// For every field line specified in the query.
		foreach ($flds as $fld) {

			// Extract the field name.
			preg_match("|^([^ ]*)|", trim($fld), $fvals);
			$fieldname = trim( $fvals[1], '`' );

			// Verify the found field name.
			$validfield = true;
			switch (strtolower($fieldname)) {
			case '':
			case 'primary':
			case 'index':
			case 'fulltext':
			case 'unique':
			case 'key':
				$validfield = false;
				$indices[] = trim(trim($fld), ", \n");
				break;
			}
			$fld = trim($fld);

			// If it's a valid field, add it to the field array.
			if ($validfield) {
				$cfields[strtolower($fieldname)] = trim($fld, ", \n");
			}
		}

		// For every field in the table.
		foreach ($tablefields as $tablefield) {

			// If the table field exists in the field array ...
			if (array_key_exists(strtolower($tablefield->Field), $cfields)) {

				// Get the field type from the query.
				preg_match("|".$tablefield->Field." ([^ ]*( unsigned)?)|i", $cfields[strtolower($tablefield->Field)], $matches);
				$fieldtype = $matches[1];

				// Is actual field type different from the field type in query?
				if ($tablefield->Type != $fieldtype) {
					// Add a query to change the column type
					$cqueries[] = "ALTER TABLE {$table} CHANGE COLUMN {$tablefield->Field} " . $cfields[strtolower($tablefield->Field)];
					$for_update[$table.'.'.$tablefield->Field] = "Changed type of {$table}.{$tablefield->Field} from {$tablefield->Type} to {$fieldtype}";
				}

				// Get the default value from the array
					// todo: Remove this?
					//echo "{$cfields[strtolower($tablefield->Field)]}<br>";
				if (preg_match("| DEFAULT '(.*?)'|i", $cfields[strtolower($tablefield->Field)], $matches)) {
					$default_value = $matches[1];
					if ($tablefield->Default != $default_value) {
						// Add a query to change the column's default value
						$cqueries[] = "ALTER TABLE {$table} ALTER COLUMN {$tablefield->Field} SET DEFAULT '{$default_value}'";
						$for_update[$table.'.'.$tablefield->Field] = "Changed default value of {$table}.{$tablefield->Field} from {$tablefield->Default} to {$default_value}";
					}
				}

				// Remove the field from the array (so it's not added).
				unset($cfields[strtolower($tablefield->Field)]);
			} else {
				// This field exists in the table, but not in the creation queries?
			}
		}

		// For every remaining field specified for the table.
		foreach ($cfields as $fieldname => $fielddef) {
			// Push a query line into $cqueries that adds the field to that table.
			$cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
			$for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
		}

		// Index stuff goes here. Fetch the table index structure from the database.
		$tableindices = $gpdb->get_results("SHOW INDEX FROM {$table};");

		if ($tableindices) {
			// Clear the index array.
			$index_ary = array();

			// For every index in the table.
			foreach ($tableindices as $tableindex) {

				// Add the index to the index data array.
				$keyname = $tableindex->Key_name;
				$index_ary[$keyname]['columns'][] = array('fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part);
				$index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0)?true:false;
			}

			// For each actual index in the index array.
			foreach ($index_ary as $index_name => $index_data) {

				// Build a create string to compare to the query.
				$index_string = '';
				if ($index_name == 'PRIMARY') {
					$index_string .= 'PRIMARY ';
				} elseif ( $index_data['unique'] ) {
					$index_string .= 'UNIQUE ';
				}
				$index_string .= 'KEY ';
				if ($index_name != 'PRIMARY') {
					$index_string .= $index_name;
				}
				$index_columns = '';

				// For each column in the index.
				foreach ($index_data['columns'] as $column_data) {
					if ($index_columns != '') $index_columns .= ',';

					// Add the field to the column list string.
					$index_columns .= $column_data['fieldname'];
					if ($column_data['subpart'] != '') {
						$index_columns .= '('.$column_data['subpart'].')';
					}
				}
				// Add the column list to the index create string.
				$index_string .= ' ('.$index_columns.')';
				if (!(($aindex = array_search($index_string, $indices)) === false)) {
					unset($indices[$aindex]);
					// todo: Remove this?
					//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br />Found index:".$index_string."</pre>\n";
				}
				// todo: Remove this?
				//else echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">{$table}:<br /><b>Did not find index:</b>".$index_string."<br />".print_r($indices, true)."</pre>\n";
			}
		}

		// For every remaining index specified for the table.
		foreach ( (array) $indices as $index ) {
			// Push a query line into $cqueries that adds the index to that table.
			$cqueries[] = "ALTER TABLE {$table} ADD $index";
			$for_update[] = 'Added index ' . $table . ' ' . $index;
		}

		// Remove the original table creation query from processing.
		unset( $cqueries[ $table ], $for_update[ $table ] );
	}

	$allqueries = array_merge($cqueries, $iqueries);
	if ($execute) {
		foreach ($allqueries as $query) {
			// todo: Remove this?
			//echo "<pre style=\"border:1px solid #ccc;margin-top:5px;\">".print_r($query, true)."</pre>\n";
			$gpdb->query($query);
		}
	}

	return $for_update;
}

/**
 * Updates the database tables to a new schema.
 *
 * By default, updates all the tables to use the latest defined schema, but can also
 * be used to update a specific set of tables in gp_get_db_schema().
 *
 * @since 1.5.0
 *
 * @uses dbDelta
 *
 * @param string $tables Optional. Which set of tables to update. Default is 'all'.
 */
function make_db_current( $tables = 'all' ) {
	$alterations = dbDelta( $tables );
	echo "<ol>\n";
	foreach($alterations as $alteration) echo "<li>$alteration</li>\n";
	echo "</ol>\n";
}

/**
 * Updates the database tables to a new schema, but without displaying results.
 *
 * By default, updates all the tables to use the latest defined schema, but can
 * also be used to update a specific set of tables in gp_get_db_schema().
 *
 * @since 1.5.0
 *
 * @see make_db_current()
 *
 * @param string $tables Optional. Which set of tables to update. Default is 'all'.
 */
function make_db_current_silent( $tables = 'all' ) {
	dbDelta( $tables );
}

/**
 * Creates a site theme from an existing theme.
 *
 * {@internal Missing Long Description}}
 *
 * @since 1.5.0
 *
 * @param string $theme_name The name of the theme.
 * @param string $template   The directory name of the theme.
 * @return bool
 */
function make_site_theme_from_oldschool($theme_name, $template) {
	$home_path = get_home_path();
	$site_dir = gp_CONTENT_DIR . "/themes/$template";

	if (! file_exists("$home_path/index.php"))
		return false;

	/*
	 * Copy files from the old locations to the site theme.
	 * TODO: This does not copy arbitrary include dependencies. Only the standard gp files are copied.
	 */
	$files = array('index.php' => 'index.php', 'gp-layout.css' => 'style.css', 'gp-comments.php' => 'comments.php', 'gp-comments-popup.php' => 'comments-popup.php');

	foreach ($files as $oldfile => $newfile) {
		if ($oldfile == 'index.php')
			$oldpath = $home_path;
		else
			$oldpath = ABSPATH;

		// Check to make sure it's not a new index.
		if ($oldfile == 'index.php') {
			$index = implode('', file("$oldpath/$oldfile"));
			if (strpos($index, 'gp_USE_THEMES') !== false) {
				if (! @copy(gp_CONTENT_DIR . '/themes/' . gp_DEFAULT_THEME . '/index.php', "$site_dir/$newfile"))
					return false;

				// Don't copy anything.
				continue;
				}
		}

		if (! @copy("$oldpath/$oldfile", "$site_dir/$newfile"))
			return false;

		chmod("$site_dir/$newfile", 0777);

		// Update the blog header include in each file.
		$lines = explode("\n", implode('', file("$site_dir/$newfile")));
		if ($lines) {
			$f = fopen("$site_dir/$newfile", 'w');

			foreach ($lines as $line) {
				if (preg_match('/require.*gp-blog-header/', $line))
					$line = '//' . $line;

				// Update stylesheet references.
				$line = str_replace("<?php echo __get_option('siteurl'); ?>/gp-layout.css", "<?php bloginfo('stylesheet_url'); ?>", $line);

				// Update comments template inclusion.
				$line = str_replace("<?php include(ABSPATH . 'gp-comments.php'); ?>", "<?php comments_template(); ?>", $line);

				fwrite($f, "{$line}\n");
			}
			fclose($f);
		}
	}

	// Add a theme header.
	$header = "/*\nTheme Name: $theme_name\nTheme URI: " . __get_option('siteurl') . "\nDescription: A theme automatically created by the update.\nVersion: 1.0\nAuthor: Moi\n*/\n";

	$stylelines = file_get_contents("$site_dir/style.css");
	if ($stylelines) {
		$f = fopen("$site_dir/style.css", 'w');

		fwrite($f, $header);
		fwrite($f, $stylelines);
		fclose($f);
	}

	return true;
}

/**
 * Creates a site theme from the default theme.
 *
 * {@internal Missing Long Description}}
 *
 * @since 1.5.0
 *
 * @param string $theme_name The name of the theme.
 * @param string $template   The directory name of the theme.
 * @return null|false
 */
function make_site_theme_from_default($theme_name, $template) {
	$site_dir = gp_CONTENT_DIR . "/themes/$template";
	$default_dir = gp_CONTENT_DIR . '/themes/' . gp_DEFAULT_THEME;

	// Copy files from the default theme to the site theme.
	//$files = array('index.php', 'comments.php', 'comments-popup.php', 'footer.php', 'header.php', 'sidebar.php', 'style.css');

	$theme_dir = @ opendir($default_dir);
	if ($theme_dir) {
		while(($theme_file = readdir( $theme_dir )) !== false) {
			if (is_dir("$default_dir/$theme_file"))
				continue;
			if (! @copy("$default_dir/$theme_file", "$site_dir/$theme_file"))
				return;
			chmod("$site_dir/$theme_file", 0777);
		}
	}
	@closedir($theme_dir);

	// Rewrite the theme header.
	$stylelines = explode("\n", implode('', file("$site_dir/style.css")));
	if ($stylelines) {
		$f = fopen("$site_dir/style.css", 'w');

		foreach ($stylelines as $line) {
			if (strpos($line, 'Theme Name:') !== false) $line = 'Theme Name: ' . $theme_name;
			elseif (strpos($line, 'Theme URI:') !== false) $line = 'Theme URI: ' . __get_option('url');
			elseif (strpos($line, 'Description:') !== false) $line = 'Description: Your theme.';
			elseif (strpos($line, 'Version:') !== false) $line = 'Version: 1';
			elseif (strpos($line, 'Author:') !== false) $line = 'Author: You';
			fwrite($f, $line . "\n");
		}
		fclose($f);
	}

	// Copy the images.
	umask(0);
	if (! mkdir("$site_dir/images", 0777)) {
		return false;
	}

	$images_dir = @ opendir("$default_dir/images");
	if ($images_dir) {
		while(($image = readdir($images_dir)) !== false) {
			if (is_dir("$default_dir/images/$image"))
				continue;
			if (! @copy("$default_dir/images/$image", "$site_dir/images/$image"))
				return;
			chmod("$site_dir/images/$image", 0777);
		}
	}
	@closedir($images_dir);
}

/**
 * Creates a site theme.
 *
 * {@internal Missing Long Description}}
 *
 * @since 1.5.0
 *
 * @return false|string
 */
function make_site_theme() {
	// Name the theme after the blog.
	$theme_name = __get_option('blogname');
	$template = sanitize_title($theme_name);
	$site_dir = gp_CONTENT_DIR . "/themes/$template";

	// If the theme already exists, nothing to do.
	if ( is_dir($site_dir)) {
		return false;
	}

	// We must be able to write to the themes dir.
	if (! is_writable(gp_CONTENT_DIR . "/themes")) {
		return false;
	}

	umask(0);
	if (! mkdir($site_dir, 0777)) {
		return false;
	}

	if (file_exists(ABSPATH . 'gp-layout.css')) {
		if (! make_site_theme_from_oldschool($theme_name, $template)) {
			// TODO: rm -rf the site theme directory.
			return false;
		}
	} else {
		if (! make_site_theme_from_default($theme_name, $template))
			// TODO: rm -rf the site theme directory.
			return false;
	}

	// Make the new site theme active.
	$current_template = __get_option('template');
	if ($current_template == gp_DEFAULT_THEME) {
		update_option('template', $template);
		update_option('stylesheet', $template);
	}
	return $template;
}

/**
 * Translate user level to user role name.
 *
 * @since 2.0.0
 *
 * @param int $level User level.
 * @return string User role name.
 */
function translate_level_to_role($level) {
	switch ($level) {
	case 10:
	case 9:
	case 8:
		return 'administrator';
	case 7:
	case 6:
	case 5:
		return 'editor';
	case 4:
	case 3:
	case 2:
		return 'author';
	case 1:
		return 'contributor';
	case 0:
		return 'subscriber';
	}
}

/**
 * Checks the version of the installed MySQL binary.
 *
 * @since 2.1.0
 */
function gp_check_mysql_version() {
	global $gpdb;
	$result = $gpdb->check_database_version();
	if ( is_gp_error( $result ) )
		die( $result->get_error_message() );
}

/**
 * Disables the Automattic widgets plugin, which was merged into core.
 *
 * @since 2.2.0
 */
function maybe_disable_automattic_widgets() {
	$plugins = __get_option( 'active_plugins' );

	foreach ( (array) $plugins as $plugin ) {
		if ( basename( $plugin ) == 'widgets.php' ) {
			array_splice( $plugins, array_search( $plugin, $plugins ), 1 );
			update_option( 'active_plugins', $plugins );
			break;
		}
	}
}

/**
 * Disables the Link Manager on upgrade if, at the time of upgrade, no links exist in the DB.
 *
 * @since 3.5.0
 */
function maybe_disable_link_manager() {
	global $gp_current_db_version, $gpdb;

	if ( $gp_current_db_version >= 22006 && get_option( 'link_manager_enabled' ) && ! $gpdb->get_var( "SELECT link_id FROM $gpdb->links LIMIT 1" ) )
		update_option( 'link_manager_enabled', 0 );
}

/**
 * Runs before the schema is upgraded.
 *
 * @since 2.9.0
 */
function pre_schema_upgrade() {
	global $gp_current_db_version, $gpdb;

	// Upgrade versions prior to 2.9
	if ( $gp_current_db_version < 11557 ) {
		// Delete duplicate options. Keep the option with the highest option_id.
		$gpdb->query("DELETE o1 FROM $gpdb->options AS o1 JOIN $gpdb->options AS o2 USING (`option_name`) WHERE o2.option_id > o1.option_id");

		// Drop the old primary key and add the new.
		$gpdb->query("ALTER TABLE $gpdb->options DROP PRIMARY KEY, ADD PRIMARY KEY(option_id)");

		// Drop the old option_name index. dbDelta() doesn't do the drop.
		$gpdb->query("ALTER TABLE $gpdb->options DROP INDEX option_name");
	}

	// Multisite schema upgrades.
	if ( $gp_current_db_version < 25448 && is_multisite() && ! defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) && is_main_network() ) {

		// Upgrade verions prior to 3.7
		if ( $gp_current_db_version < 25179 ) {
			// New primary key for signups.
			$gpdb->query( "ALTER TABLE $gpdb->signups ADD signup_id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST" );
			$gpdb->query( "ALTER TABLE $gpdb->signups DROP INDEX domain" );
		}

		if ( $gp_current_db_version < 25448 ) {
			// Convert archived from enum to tinyint.
			$gpdb->query( "ALTER TABLE $gpdb->blogs CHANGE COLUMN archived archived varchar(1) NOT NULL default '0'" );
			$gpdb->query( "ALTER TABLE $gpdb->blogs CHANGE COLUMN archived archived tinyint(2) NOT NULL default 0" );
		}
	}

	if ( $gp_current_db_version < 30133 ) {
		// dbDelta() can recreate but can't drop the index.
		$gpdb->query( "ALTER TABLE $gpdb->terms DROP INDEX slug" );
	}

	// Upgrade versions prior to 4.2.
	if ( $gp_current_db_version < 31351 ) {
		if ( ! is_multisite() ) {
			$gpdb->query( "ALTER TABLE $gpdb->usermeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
		}
		$gpdb->query( "ALTER TABLE $gpdb->terms DROP INDEX slug, ADD INDEX slug(slug(191))" );
		$gpdb->query( "ALTER TABLE $gpdb->terms DROP INDEX name, ADD INDEX name(name(191))" );
		$gpdb->query( "ALTER TABLE $gpdb->commentmeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
		$gpdb->query( "ALTER TABLE $gpdb->postmeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
		$gpdb->query( "ALTER TABLE $gpdb->posts DROP INDEX post_name, ADD INDEX post_name(post_name(191))" );
	}
}

/**
 * Install global terms.
 *
 * @since 3.0.0
 *
 */
if ( !function_exists( 'install_global_terms' ) ) :
function install_global_terms() {
	global $gpdb, $charset_collate;
	$ms_queries = "
CREATE TABLE $gpdb->sitecategories (
  cat_ID bigint(20) NOT NULL auto_increment,
  cat_name varchar(55) NOT NULL default '',
  category_nicename varchar(200) NOT NULL default '',
  last_updated timestamp NOT NULL,
  PRIMARY KEY  (cat_ID),
  KEY category_nicename (category_nicename),
  KEY last_updated (last_updated)
) $charset_collate;
";
// now create tables
	dbDelta( $ms_queries );
}
endif;
