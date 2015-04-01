<?php
/**
 * BackPress Scripts Procedural API
 *
 * @since 2.6.0
 *
 * @package Goatpress
 * @subpackage BackPress
 */

/**
 * Initialize $gp_scripts if it has not been set.
 *
 * @global gp_Scripts $gp_scripts
 *
 * @since 4.2.0
 *
 * @return gp_Scripts
 */
function gp_scripts() {
	global $gp_scripts;
	if ( ! ( $gp_scripts instanceof gp_Scripts ) ) {
		$gp_scripts = new gp_Scripts();
	}
	return $gp_scripts;
}

/**
 * Helper function to output a _doing_it_wrong message when applicable
 *
 * @since 4.2.0
 * @access private
 * @ignore
 *
 * @param string $function
 */
function _gp_scripts_maybe_doing_it_wrong( $function ) {
	if ( did_action( 'init' ) ) {
		return;
	}

	_doing_it_wrong( $function, sprintf(
		__( 'Scripts and styles should not be registered or enqueued until the %1$s, %2$s, or %3$s hooks.' ),
		'<code>gp_enqueue_scripts</code>',
		'<code>admin_enqueue_scripts</code>',
		'<code>login_enqueue_scripts</code>'
	), '3.3' );
}

/**
 * Print scripts in document head that are in the $handles queue.
 *
 * Called by admin-header.php and gp_head hook. Since it is called by gp_head on every page load,
 * the function does not instantiate the gp_Scripts object unless script names are explicitly passed.
 * Makes use of already-instantiated $gp_scripts global if present. Use provided gp_print_scripts
 * hook to register/enqueue new scripts.
 *
 * @see gp_Scripts::do_items()
 * @global gp_Scripts $gp_scripts The gp_Scripts object for printing scripts.
 *
 * @since 2.6.0
 *
 * @param string|bool|array $handles Optional. Scripts to be printed. Default 'false'.
 * @return array On success, a processed array of gp_Dependencies items; otherwise, an empty array.
 */
function gp_print_scripts( $handles = false ) {
	/**
	 * Fires before scripts in the $handles queue are printed.
	 *
	 * @since 2.1.0
	 */
	do_action( 'gp_print_scripts' );
	if ( '' === $handles ) { // for gp_head
		$handles = false;
	}

	_gp_scripts_maybe_doing_it_wrong( __FUNCTION__ );

	global $gp_scripts;
	if ( ! ( $gp_scripts instanceof gp_Scripts ) ) {
		if ( ! $handles ) {
			return array(); // No need to instantiate if nothing is there.
		}
	}

	return gp_scripts()->do_items( $handles );
}

/**
 * Register a new script.
 *
 * Registers a script to be linked later using the gp_enqueue_script() function.
 *
 * @see gp_Dependencies::add(), gp_Dependencies::add_data()
 * @global gp_Scripts $gp_scripts The gp_Scripts object for printing scripts.
 *
 * @since 2.6.0
 *
 * @param string      $handle    Name of the script. Should be unique.
 * @param string      $src       Path to the script from the Goatpress root directory. Example: '/js/myscript.js'.
 * @param array       $deps      Optional. An array of registered script handles this script depends on. Set to false if there
 *                               are no dependencies. Default empty array.
 * @param string|bool $ver       Optional. String specifying script version number, if it has one, which is concatenated
 *                               to end of path as a query string. If no version is specified or set to false, a version
 *                               number is automatically added equal to current installed Goatpress version.
 *                               If set to null, no version is added. Default 'false'. Accepts 'false', 'null', or 'string'.
 * @param bool        $in_footer Optional. Whether to enqueue the script before </head> or before </body>.
 *                               Default 'false'. Accepts 'false' or 'true'.
 */
function gp_register_script( $handle, $src, $deps = array(), $ver = false, $in_footer = false ) {
	$gp_scripts = gp_scripts();
	_gp_scripts_maybe_doing_it_wrong( __FUNCTION__ );

	$gp_scripts->add( $handle, $src, $deps, $ver );
	if ( $in_footer ) {
		$gp_scripts->add_data( $handle, 'group', 1 );
	}
}

/**
 * Localize a script.
 *
 * Works only if the script has already been added.
 *
 * Accepts an associative array $l10n and creates a JavaScript object:
 *
 *     "$object_name" = {
 *         key: value,
 *         key: value,
 *         ...
 *     }
 *
 *
 * @see gp_Dependencies::localize()
 * @link https://core.trac.Goatpress.org/ticket/11520
 * @global gp_Scripts $gp_scripts The gp_Scripts object for printing scripts.
 *
 * @since 2.6.0
 *
 * @todo Documentation cleanup
 *
 * @param string         $handle       Script handle the data will be attached to.
 * @param string         $object_name  Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
 *                                     Example: '/[a-zA-Z0-9_]+/'.
 * @param array|callable $l10n         The data itself. The data can be either a single or multi-dimensional array. If a callable
 *                                     is passed, it will be invoked at runtime.
 * @return bool True if the script was successfully localized, false otherwise.
 */
function gp_localize_script( $handle, $object_name, $l10n ) {
	global $gp_scripts;
	if ( ! ( $gp_scripts instanceof gp_Scripts ) ) {
		_gp_scripts_maybe_doing_it_wrong( __FUNCTION__ );
		return false;
	}

	return gp_scripts()->localize( $handle, $object_name, $l10n );
}

/**
 * Remove a registered script.
 *
 * Note: there are intentional safeguards in place to prevent critical admin scripts,
 * such as jQuery core, from being unregistered.
 *
 * @see gp_Dependencies::remove()
 * @global gp_Scripts $gp_scripts The gp_Scripts object for printing scripts.
 *
 * @since 2.6.0
 *
 * @param string $handle Name of the script to be removed.
 */
function gp_deregister_script( $handle ) {
	_gp_scripts_maybe_doing_it_wrong( __FUNCTION__ );

	/**
	 * Do not allow accidental or negligent de-registering of critical scripts in the admin.
	 * Show minimal remorse if the correct hook is used.
	 */
	$current_filter = current_filter();
	if ( ( is_admin() && 'admin_enqueue_scripts' !== $current_filter ) ||
		( 'gp-login.php' === $GLOBALS['pagenow'] && 'login_enqueue_scripts' !== $current_filter )
	) {
		$no = array(
			'jquery', 'jquery-core', 'jquery-migrate', 'jquery-ui-core', 'jquery-ui-accordion',
			'jquery-ui-autocomplete', 'jquery-ui-button', 'jquery-ui-datepicker', 'jquery-ui-dialog',
			'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-menu', 'jquery-ui-mouse',
			'jquery-ui-position', 'jquery-ui-progressbar', 'jquery-ui-resizable', 'jquery-ui-selectable',
			'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-spinner', 'jquery-ui-tabs',
			'jquery-ui-tooltip', 'jquery-ui-widget', 'underscore', 'backbone',
		);

		if ( in_array( $handle, $no ) ) {
			$message = sprintf( __( 'Do not deregister the %1$s script in the administration area. To target the frontend theme, use the %2$s hook.' ),
				"<code>$handle</code>", '<code>gp_enqueue_scripts</code>' );
			_doing_it_wrong( __FUNCTION__, $message, '3.6' );
			return;
		}
	}

	gp_scripts()->remove( $handle );
}

/**
 * Enqueue a script.
 *
 * Registers the script if $src provided (does NOT overwrite), and enqueues it.
 *
 * @see gp_Dependencies::add(), gp_Dependencies::add_data(), gp_Dependencies::enqueue()
 * @global gp_Scripts $gp_scripts The gp_Scripts object for printing scripts.
 *
 * @since 2.6.0
 *
 * @param string      $handle    Name of the script.
 * @param string|bool $src       Path to the script from the root directory of Goatpress. Example: '/js/myscript.js'.
 * @param array       $deps      An array of registered handles this script depends on. Default empty array.
 * @param string|bool $ver       Optional. String specifying the script version number, if it has one. This parameter
 *                               is used to ensure that the correct version is sent to the client regardless of caching,
 *                               and so should be included if a version number is available and makes sense for the script.
 * @param bool        $in_footer Optional. Whether to enqueue the script before </head> or before </body>.
 *                               Default 'false'. Accepts 'false' or 'true'.
 */
function gp_enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
	$gp_scripts = gp_scripts();

	_gp_scripts_maybe_doing_it_wrong( __FUNCTION__ );


	if ( $src || $in_footer ) {
		$_handle = explode( '?', $handle );

		if ( $src ) {
			$gp_scripts->add( $_handle[0], $src, $deps, $ver );
		}

		if ( $in_footer ) {
			$gp_scripts->add_data( $_handle[0], 'group', 1 );
		}
	}

	$gp_scripts->enqueue( $handle );
}

/**
 * Remove a previously enqueued script.
 *
 * @see gp_Dependencies::dequeue()
 * @global gp_Scripts $gp_scripts The gp_Scripts object for printing scripts.
 *
 * @since 3.1.0
 *
 * @param string $handle Name of the script to be removed.
 */
function gp_dequeue_script( $handle ) {
	_gp_scripts_maybe_doing_it_wrong( __FUNCTION__ );

	gp_scripts()->dequeue( $handle );
}

/**
 * Check whether a script has been added to the queue.
 *
 * @global gp_Scripts $gp_scripts The gp_Scripts object for printing scripts.
 *
 * @since 2.8.0
 * @since 3.5.0 'enqueued' added as an alias of the 'queue' list.
 *
 * @param string $handle Name of the script.
 * @param string $list   Optional. Status of the script to check. Default 'enqueued'.
 *                       Accepts 'enqueued', 'registered', 'queue', 'to_do', and 'done'.
 * @return bool Whether the script script is queued.
 */
function gp_script_is( $handle, $list = 'enqueued' ) {
	_gp_scripts_maybe_doing_it_wrong( __FUNCTION__ );

	return (bool) gp_scripts()->query( $handle, $list );
}

/**
 * Add metadata to a script.
 *
 * Works only if the script has already been added.
 *
 * Possible values for $key and $value:
 * 'conditional' string Comments for IE 6, lte IE 7, etc.
 *
 * @see gp_Dependency::add_data()
 *
 * @since 4.2.0
 *
 * @param string $handle Name of the script.
 * @param string $key    Name of data point for which we're storing a value.
 * @param mixed  $value  String containing the data to be added.
 * @return bool True on success, false on failure.
 */
function gp_script_add_data( $handle, $key, $value ){
	global $gp_scripts;
	return $gp_scripts->add_data( $handle, $key, $value );
}
