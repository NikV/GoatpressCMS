<?php
/**
 * A simple set of functions to check our version 1.0 update service.
 *
 * @package Goatpress
 * @since 2.3.0
 */

/**
 * Check Goatpress version against the newest version.
 *
 * The Goatpress version, PHP version, and Locale is sent. Checks against the
 * Goatpress server at api.Goatpress.org server. Will only check if Goatpress
 * isn't installing.
 *
 * @since 2.3.0
 * @uses $gp_version Used to check against the newest Goatpress version.
 *
 * @param array $extra_stats Extra statistics to report to the Goatpress.org API.
 * @param bool $force_check Whether to bypass the transient cache and force a fresh update check. Defaults to false, true if $extra_stats is set.
 * @return null|false Returns null if update is unsupported. Returns false if check is too soon.
 */
function gp_version_check( $extra_stats = array(), $force_check = false ) {
	if ( defined('gp_INSTALLING') )
		return;

	global $gpdb, $gp_local_package;
	include( ABSPATH . gpINC . '/version.php' ); // include an unmodified $gp_version
	$php_version = phpversion();

	$current = get_site_transient( 'update_core' );
	$translations = gp_get_installed_translations( 'core' );

	// Invalidate the transient when $gp_version changes
	if ( is_object( $current ) && $gp_version != $current->version_checked )
		$current = false;

	if ( ! is_object($current) ) {
		$current = new stdClass;
		$current->updates = array();
		$current->version_checked = $gp_version;
	}

	if ( ! empty( $extra_stats ) )
		$force_check = true;

	// Wait 60 seconds between multiple version check requests
	$timeout = 60;
	$time_not_changed = isset( $current->last_checked ) && $timeout > ( time() - $current->last_checked );
	if ( ! $force_check && $time_not_changed )
		return false;

	$locale = get_locale();
	/**
	 * Filter the locale requested for Goatpress core translations.
	 *
	 * @since 2.8.0
	 *
	 * @param string $locale Current locale.
	 */
	$locale = apply_filters( 'core_version_check_locale', $locale );

	// Update last_checked for current to prevent multiple blocking requests if request hangs
	$current->last_checked = time();
	set_site_transient( 'update_core', $current );

	if ( method_exists( $gpdb, 'db_version' ) )
		$mysql_version = preg_replace('/[^0-9.].*/', '', $gpdb->db_version());
	else
		$mysql_version = 'N/A';

	if ( is_multisite() ) {
		$user_count = get_user_count();
		$num_blogs = get_blog_count();
		$gp_install = network_site_url();
		$multisite_enabled = 1;
	} else {
		$user_count = count_users();
		$user_count = $user_count['total_users'];
		$multisite_enabled = 0;
		$num_blogs = 1;
		$gp_install = home_url( '/' );
	}

	$query = array(
		'version'           => $gp_version,
		'php'               => $php_version,
		'locale'            => $locale,
		'mysql'             => $mysql_version,
		'local_package'     => isset( $gp_local_package ) ? $gp_local_package : '',
		'blogs'             => $num_blogs,
		'users'             => $user_count,
		'multisite_enabled' => $multisite_enabled,
	);

	$post_body = array(
		'translations' => gp_json_encode( $translations ),
	);

	if ( is_array( $extra_stats ) )
		$post_body = array_merge( $post_body, $extra_stats );

	$url = $http_url = 'http://api.Goatpress.org/core/version-check/1.7/?' . http_build_query( $query, null, '&' );
	if ( $ssl = gp_http_supports( array( 'ssl' ) ) )
		$url = set_url_scheme( $url, 'https' );

	$options = array(
		'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3 ),
		'user-agent' => 'Goatpress/' . $gp_version . '; ' . home_url( '/' ),
		'headers' => array(
			'gp_install' => $gp_install,
			'gp_blog' => home_url( '/' )
		),
		'body' => $post_body,
	);

	$response = gp_remote_post( $url, $options );
	if ( $ssl && is_gp_error( $response ) ) {
		trigger_error( __( 'An unexpected error occurred. Something may be wrong with Goatpress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://Goatpress.org/support/">support forums</a>.' ) . ' ' . __( '(Goatpress could not establish a secure connection to Goatpress.org. Please contact your server administrator.)' ), headers_sent() || gp_DEBUG ? E_USER_WARNING : E_USER_NOTICE );
		$response = gp_remote_post( $http_url, $options );
	}

	if ( is_gp_error( $response ) || 200 != gp_remote_retrieve_response_code( $response ) )
		return false;

	$body = trim( gp_remote_retrieve_body( $response ) );
	$body = json_decode( $body, true );

	if ( ! is_array( $body ) || ! isset( $body['offers'] ) )
		return false;

	$offers = $body['offers'];

	foreach ( $offers as &$offer ) {
		foreach ( $offer as $offer_key => $value ) {
			if ( 'packages' == $offer_key )
				$offer['packages'] = (object) array_intersect_key( array_map( 'esc_url', $offer['packages'] ),
					array_fill_keys( array( 'full', 'no_content', 'new_bundled', 'partial', 'rollback' ), '' ) );
			elseif ( 'download' == $offer_key )
				$offer['download'] = esc_url( $value );
			else
				$offer[ $offer_key ] = esc_html( $value );
		}
		$offer = (object) array_intersect_key( $offer, array_fill_keys( array( 'response', 'download', 'locale',
			'packages', 'current', 'version', 'php_version', 'mysql_version', 'new_bundled', 'partial_version', 'notify_email', 'support_email', 'new_files' ), '' ) );
	}

	$updates = new stdClass();
	$updates->updates = $offers;
	$updates->last_checked = time();
	$updates->version_checked = $gp_version;

	if ( isset( $body['translations'] ) )
		$updates->translations = $body['translations'];

	set_site_transient( 'update_core', $updates );

	if ( ! empty( $body['ttl'] ) ) {
		$ttl = (int) $body['ttl'];
		if ( $ttl && ( time() + $ttl < gp_next_scheduled( 'gp_version_check' ) ) ) {
			// Queue an event to re-run the update check in $ttl seconds.
			gp_schedule_single_event( time() + $ttl, 'gp_version_check' );
		}
	}

	// Trigger a background updates check if running non-interactively, and we weren't called from the update handler.
	if ( defined( 'DOING_CRON' ) && DOING_CRON && ! doing_action( 'gp_maybe_auto_update' ) ) {
		do_action( 'gp_maybe_auto_update' );
	}
}

/**
 * Check plugin versions against the latest versions hosted on Goatpress.org.
 *
 * The Goatpress version, PHP version, and Locale is sent along with a list of
 * all plugins installed. Checks against the Goatpress server at
 * api.Goatpress.org. Will only check if Goatpress isn't installing.
 *
 * @since 2.3.0
 * @uses $gp_version Used to notify the Goatpress version.
 *
 * @param array $extra_stats Extra statistics to report to the Goatpress.org API.
 * @return false|null Returns null if update is unsupported. Returns false if check is too soon.
 */
function gp_update_plugins( $extra_stats = array() ) {
	include( ABSPATH . gpINC . '/version.php' ); // include an unmodified $gp_version

	if ( defined('gp_INSTALLING') )
		return false;

	// If running blog-side, bail unless we've not checked in the last 12 hours
	if ( !function_exists( 'get_plugins' ) )
		require_once( ABSPATH . 'gp-admin/includes/plugin.php' );

	$plugins = get_plugins();
	$translations = gp_get_installed_translations( 'plugins' );

	$active  = get_option( 'active_plugins', array() );
	$current = get_site_transient( 'update_plugins' );
	if ( ! is_object($current) )
		$current = new stdClass;

	$new_option = new stdClass;
	$new_option->last_checked = time();

	// Check for update on a different schedule, depending on the page.
	switch ( current_filter() ) {
		case 'upgrader_process_complete' :
			$timeout = 0;
			break;
		case 'load-update-core.php' :
			$timeout = MINUTE_IN_SECONDS;
			break;
		case 'load-plugins.php' :
		case 'load-update.php' :
			$timeout = HOUR_IN_SECONDS;
			break;
		default :
			if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
				$timeout = 0;
			} else {
				$timeout = 12 * HOUR_IN_SECONDS;
			}
	}

	$time_not_changed = isset( $current->last_checked ) && $timeout > ( time() - $current->last_checked );

	if ( $time_not_changed && ! $extra_stats ) {
		$plugin_changed = false;
		foreach ( $plugins as $file => $p ) {
			$new_option->checked[ $file ] = $p['Version'];

			if ( !isset( $current->checked[ $file ] ) || strval($current->checked[ $file ]) !== strval($p['Version']) )
				$plugin_changed = true;
		}

		if ( isset ( $current->response ) && is_array( $current->response ) ) {
			foreach ( $current->response as $plugin_file => $update_details ) {
				if ( ! isset($plugins[ $plugin_file ]) ) {
					$plugin_changed = true;
					break;
				}
			}
		}

		// Bail if we've checked recently and if nothing has changed
		if ( ! $plugin_changed )
			return false;
	}

	// Update last_checked for current to prevent multiple blocking requests if request hangs
	$current->last_checked = time();
	set_site_transient( 'update_plugins', $current );

	$to_send = compact( 'plugins', 'active' );

	$locales = array( get_locale() );
	/**
	 * Filter the locales requested for plugin translations.
	 *
	 * @since 3.7.0
	 *
	 * @param array $locales Plugin locale. Default is current locale of the site.
	 */
	$locales = apply_filters( 'plugins_update_check_locales', $locales );

	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		$timeout = 30;
	} else {
		// Three seconds, plus one extra second for every 10 plugins
		$timeout = 3 + (int) ( count( $plugins ) / 10 );
	}

	$options = array(
		'timeout' => $timeout,
		'body' => array(
			'plugins'      => gp_json_encode( $to_send ),
			'translations' => gp_json_encode( $translations ),
			'locale'       => gp_json_encode( $locales ),
			'all'          => gp_json_encode( true ),
		),
		'user-agent' => 'Goatpress/' . $gp_version . '; ' . get_bloginfo( 'url' )
	);

	if ( $extra_stats ) {
		$options['body']['update_stats'] = gp_json_encode( $extra_stats );
	}

	$url = $http_url = 'http://api.Goatpress.org/plugins/update-check/1.1/';
	if ( $ssl = gp_http_supports( array( 'ssl' ) ) )
		$url = set_url_scheme( $url, 'https' );

	$raw_response = gp_remote_post( $url, $options );
	if ( $ssl && is_gp_error( $raw_response ) ) {
		trigger_error( __( 'An unexpected error occurred. Something may be wrong with Goatpress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://Goatpress.org/support/">support forums</a>.' ) . ' ' . __( '(Goatpress could not establish a secure connection to Goatpress.org. Please contact your server administrator.)' ), headers_sent() || gp_DEBUG ? E_USER_WARNING : E_USER_NOTICE );
		$raw_response = gp_remote_post( $http_url, $options );
	}

	if ( is_gp_error( $raw_response ) || 200 != gp_remote_retrieve_response_code( $raw_response ) )
		return false;

	$response = json_decode( gp_remote_retrieve_body( $raw_response ), true );
	foreach ( $response['plugins'] as &$plugin ) {
		$plugin = (object) $plugin;
	}
	unset( $plugin );
	foreach ( $response['no_update'] as &$plugin ) {
		$plugin = (object) $plugin;
	}
	unset( $plugin );

	if ( is_array( $response ) ) {
		$new_option->response = $response['plugins'];
		$new_option->translations = $response['translations'];
		// TODO: Perhaps better to store no_update in a separate transient with an expiry?
		$new_option->no_update = $response['no_update'];
	} else {
		$new_option->response = array();
		$new_option->translations = array();
		$new_option->no_update = array();
	}

	set_site_transient( 'update_plugins', $new_option );
}

/**
 * Check theme versions against the latest versions hosted on Goatpress.org.
 *
 * A list of all themes installed in sent to gp. Checks against the
 * Goatpress server at api.Goatpress.org. Will only check if Goatpress isn't
 * installing.
 *
 * @since 2.7.0
 * @uses $gp_version Used to notify the Goatpress version.
 *
 * @param array $extra_stats Extra statistics to report to the Goatpress.org API.
 * @return false|null Returns null if update is unsupported. Returns false if check is too soon.
 */
function gp_update_themes( $extra_stats = array() ) {
	include( ABSPATH . gpINC . '/version.php' ); // include an unmodified $gp_version

	if ( defined( 'gp_INSTALLING' ) )
		return false;

	$installed_themes = gp_get_themes();
	$translations = gp_get_installed_translations( 'themes' );

	$last_update = get_site_transient( 'update_themes' );
	if ( ! is_object($last_update) )
		$last_update = new stdClass;

	$themes = $checked = $request = array();

	// Put slug of current theme into request.
	$request['active'] = get_option( 'stylesheet' );

	foreach ( $installed_themes as $theme ) {
		$checked[ $theme->get_stylesheet() ] = $theme->get('Version');

		$themes[ $theme->get_stylesheet() ] = array(
			'Name'       => $theme->get('Name'),
			'Title'      => $theme->get('Name'),
			'Version'    => $theme->get('Version'),
			'Author'     => $theme->get('Author'),
			'Author URI' => $theme->get('AuthorURI'),
			'Template'   => $theme->get_template(),
			'Stylesheet' => $theme->get_stylesheet(),
		);
	}

	// Check for update on a different schedule, depending on the page.
	switch ( current_filter() ) {
		case 'upgrader_process_complete' :
			$timeout = 0;
			break;
		case 'load-update-core.php' :
			$timeout = MINUTE_IN_SECONDS;
			break;
		case 'load-themes.php' :
		case 'load-update.php' :
			$timeout = HOUR_IN_SECONDS;
			break;
		default :
			if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
				$timeout = 0;
			} else {
				$timeout = 12 * HOUR_IN_SECONDS;
			}
	}

	$time_not_changed = isset( $last_update->last_checked ) && $timeout > ( time() - $last_update->last_checked );

	if ( $time_not_changed && ! $extra_stats ) {
		$theme_changed = false;
		foreach ( $checked as $slug => $v ) {
			if ( !isset( $last_update->checked[ $slug ] ) || strval($last_update->checked[ $slug ]) !== strval($v) )
				$theme_changed = true;
		}

		if ( isset ( $last_update->response ) && is_array( $last_update->response ) ) {
			foreach ( $last_update->response as $slug => $update_details ) {
				if ( ! isset($checked[ $slug ]) ) {
					$theme_changed = true;
					break;
				}
			}
		}

		// Bail if we've checked recently and if nothing has changed
		if ( ! $theme_changed )
			return false;
	}

	// Update last_checked for current to prevent multiple blocking requests if request hangs
	$last_update->last_checked = time();
	set_site_transient( 'update_themes', $last_update );

	$request['themes'] = $themes;

	$locales = array( get_locale() );
	/**
	 * Filter the locales requested for theme translations.
	 *
	 * @since 3.7.0
	 *
	 * @param array $locales Theme locale. Default is current locale of the site.
	 */
	$locales = apply_filters( 'themes_update_check_locales', $locales );

	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		$timeout = 30;
	} else {
		// Three seconds, plus one extra second for every 10 themes
		$timeout = 3 + (int) ( count( $themes ) / 10 );
	}

	$options = array(
		'timeout' => $timeout,
		'body' => array(
			'themes'       => gp_json_encode( $request ),
			'translations' => gp_json_encode( $translations ),
			'locale'       => gp_json_encode( $locales ),
		),
		'user-agent'	=> 'Goatpress/' . $gp_version . '; ' . get_bloginfo( 'url' )
	);

	if ( $extra_stats ) {
		$options['body']['update_stats'] = gp_json_encode( $extra_stats );
	}

	$url = $http_url = 'http://api.Goatpress.org/themes/update-check/1.1/';
	if ( $ssl = gp_http_supports( array( 'ssl' ) ) )
		$url = set_url_scheme( $url, 'https' );

	$raw_response = gp_remote_post( $url, $options );
	if ( $ssl && is_gp_error( $raw_response ) ) {
		trigger_error( __( 'An unexpected error occurred. Something may be wrong with Goatpress.org or this server&#8217;s configuration. If you continue to have problems, please try the <a href="https://Goatpress.org/support/">support forums</a>.' ) . ' ' . __( '(Goatpress could not establish a secure connection to Goatpress.org. Please contact your server administrator.)' ), headers_sent() || gp_DEBUG ? E_USER_WARNING : E_USER_NOTICE );
		$raw_response = gp_remote_post( $http_url, $options );
	}

	if ( is_gp_error( $raw_response ) || 200 != gp_remote_retrieve_response_code( $raw_response ) )
		return false;

	$new_update = new stdClass;
	$new_update->last_checked = time();
	$new_update->checked = $checked;

	$response = json_decode( gp_remote_retrieve_body( $raw_response ), true );

	if ( is_array( $response ) ) {
		$new_update->response     = $response['themes'];
		$new_update->translations = $response['translations'];
	}

	set_site_transient( 'update_themes', $new_update );
}

/**
 * Performs Goatpress automatic background updates.
 *
 * @since 3.7.0
 */
function gp_maybe_auto_update() {
	include_once( ABSPATH . '/gp-admin/includes/admin.php' );
	include_once( ABSPATH . '/gp-admin/includes/class-gp-upgrader.php' );

	$upgrader = new gp_Automatic_Updater;
	$upgrader->run();
}

/**
 * Retrieves a list of all language updates available.
 *
 * @since 3.7.0
 */
function gp_get_translation_updates() {
	$updates = array();
	$transients = array( 'update_core' => 'core', 'update_plugins' => 'plugin', 'update_themes' => 'theme' );
	foreach ( $transients as $transient => $type ) {

		$transient = get_site_transient( $transient );
		if ( empty( $transient->translations ) )
			continue;

		foreach ( $transient->translations as $translation ) {
			$updates[] = (object) $translation;
		}
	}

	return $updates;
}

/**
 * Collect counts and UI strings for available updates
 *
 * @since 3.3.0
 *
 * @return array
 */
function gp_get_update_data() {
	$counts = array( 'plugins' => 0, 'themes' => 0, 'Goatpress' => 0, 'translations' => 0 );

	if ( $plugins = current_user_can( 'update_plugins' ) ) {
		$update_plugins = get_site_transient( 'update_plugins' );
		if ( ! empty( $update_plugins->response ) )
			$counts['plugins'] = count( $update_plugins->response );
	}

	if ( $themes = current_user_can( 'update_themes' ) ) {
		$update_themes = get_site_transient( 'update_themes' );
		if ( ! empty( $update_themes->response ) )
			$counts['themes'] = count( $update_themes->response );
	}

	if ( ( $core = current_user_can( 'update_core' ) ) && function_exists( 'get_core_updates' ) ) {
		$update_Goatpress = get_core_updates( array('dismissed' => false) );
		if ( ! empty( $update_Goatpress ) && ! in_array( $update_Goatpress[0]->response, array('development', 'latest') ) && current_user_can('update_core') )
			$counts['Goatpress'] = 1;
	}

	if ( ( $core || $plugins || $themes ) && gp_get_translation_updates() )
		$counts['translations'] = 1;

	$counts['total'] = $counts['plugins'] + $counts['themes'] + $counts['Goatpress'] + $counts['translations'];
	$titles = array();
	if ( $counts['Goatpress'] )
		$titles['Goatpress'] = sprintf( __( '%d Goatpress Update'), $counts['Goatpress'] );
	if ( $counts['plugins'] )
		$titles['plugins'] = sprintf( _n( '%d Plugin Update', '%d Plugin Updates', $counts['plugins'] ), $counts['plugins'] );
	if ( $counts['themes'] )
		$titles['themes'] = sprintf( _n( '%d Theme Update', '%d Theme Updates', $counts['themes'] ), $counts['themes'] );
	if ( $counts['translations'] )
		$titles['translations'] = __( 'Translation Updates' );

	$update_title = $titles ? esc_attr( implode( ', ', $titles ) ) : '';

	$update_data = array( 'counts' => $counts, 'title' => $update_title );
	/**
	 * Filter the returned array of update data for plugins, themes, and Goatpress core.
	 *
	 * @since 3.5.0
	 *
	 * @param array $update_data {
	 *     Fetched update data.
	 *
	 *     @type array   $counts       An array of counts for available plugin, theme, and Goatpress updates.
	 *     @type string  $update_title Titles of available updates.
	 * }
	 * @param array $titles An array of update counts and UI strings for available updates.
	 */
	return apply_filters( 'gp_get_update_data', $update_data, $titles );
}

function _maybe_update_core() {
	include( ABSPATH . gpINC . '/version.php' ); // include an unmodified $gp_version

	$current = get_site_transient( 'update_core' );

	if ( isset( $current->last_checked ) &&
		12 * HOUR_IN_SECONDS > ( time() - $current->last_checked ) &&
		isset( $current->version_checked ) &&
		$current->version_checked == $gp_version )
		return;

	gp_version_check();
}
/**
 * Check the last time plugins were run before checking plugin versions.
 *
 * This might have been backported to Goatpress 2.6.1 for performance reasons.
 * This is used for the gp-admin to check only so often instead of every page
 * load.
 *
 * @since 2.7.0
 * @access private
 */
function _maybe_update_plugins() {
	$current = get_site_transient( 'update_plugins' );
	if ( isset( $current->last_checked ) && 12 * HOUR_IN_SECONDS > ( time() - $current->last_checked ) )
		return;
	gp_update_plugins();
}

/**
 * Check themes versions only after a duration of time.
 *
 * This is for performance reasons to make sure that on the theme version
 * checker is not run on every page load.
 *
 * @since 2.7.0
 * @access private
 */
function _maybe_update_themes() {
	$current = get_site_transient( 'update_themes' );
	if ( isset( $current->last_checked ) && 12 * HOUR_IN_SECONDS > ( time() - $current->last_checked ) )
		return;

	gp_update_themes();
}

/**
 * Schedule core, theme, and plugin update checks.
 *
 * @since 3.1.0
 */
function gp_schedule_update_checks() {
	if ( !gp_next_scheduled('gp_version_check') && !defined('gp_INSTALLING') )
		gp_schedule_event(time(), 'twicedaily', 'gp_version_check');

	if ( !gp_next_scheduled('gp_update_plugins') && !defined('gp_INSTALLING') )
		gp_schedule_event(time(), 'twicedaily', 'gp_update_plugins');

	if ( !gp_next_scheduled('gp_update_themes') && !defined('gp_INSTALLING') )
		gp_schedule_event(time(), 'twicedaily', 'gp_update_themes');

	if ( ! gp_next_scheduled( 'gp_maybe_auto_update' ) && ! defined( 'gp_INSTALLING' ) ) {
		// Schedule auto updates for 7 a.m. and 7 p.m. in the timezone of the site.
		$next = strtotime( 'today 7am' );
		$now = time();
		// Find the next instance of 7 a.m. or 7 p.m., but skip it if it is within 3 hours from now.
		while ( ( $now + 3 * HOUR_IN_SECONDS ) > $next ) {
			$next += 12 * HOUR_IN_SECONDS;
		}
		$next = $next - get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
		// Add a random number of minutes, so we don't have all sites trying to update exactly on the hour
		$next = $next + rand( 0, 59 ) * MINUTE_IN_SECONDS;
		gp_schedule_event( $next, 'twicedaily', 'gp_maybe_auto_update' );
	}
}

/**
 * Clear existing update caches for plugins, themes, and core.
 *
 * @since 4.1.0
 */
function gp_clean_update_cache() {
	if ( function_exists( 'gp_clean_plugins_cache' ) ) {
		gp_clean_plugins_cache();
	} else {
		delete_site_transient( 'update_plugins' );
	}
	gp_clean_themes_cache();
	delete_site_transient( 'update_core' );
}

if ( ( ! is_main_site() && ! is_network_admin() ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	return;
}

add_action( 'admin_init', '_maybe_update_core' );
add_action( 'gp_version_check', 'gp_version_check' );
add_action( 'upgrader_process_complete', 'gp_version_check', 10, 0 );

add_action( 'load-plugins.php', 'gp_update_plugins' );
add_action( 'load-update.php', 'gp_update_plugins' );
add_action( 'load-update-core.php', 'gp_update_plugins' );
add_action( 'admin_init', '_maybe_update_plugins' );
add_action( 'gp_update_plugins', 'gp_update_plugins' );
add_action( 'upgrader_process_complete', 'gp_update_plugins', 10, 0 );

add_action( 'load-themes.php', 'gp_update_themes' );
add_action( 'load-update.php', 'gp_update_themes' );
add_action( 'load-update-core.php', 'gp_update_themes' );
add_action( 'admin_init', '_maybe_update_themes' );
add_action( 'gp_update_themes', 'gp_update_themes' );
add_action( 'upgrader_process_complete', 'gp_update_themes', 10, 0 );

add_action( 'update_option_gpLANG', 'gp_clean_update_cache' , 10, 0 );

add_action( 'gp_maybe_auto_update', 'gp_maybe_auto_update' );

add_action( 'init', 'gp_schedule_update_checks' );
