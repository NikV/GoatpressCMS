<?php
/**
 * Goatpress scripts and styles default loader.
 *
 * Most of the functionality that existed here was moved to
 * {@link http://backpress.automattic.com/ BackPress}. Goatpress themes and
 * plugins will only be concerned about the filters and actions set in this
 * file.
 *
 * Several constants are used to manage the loading, concatenating and compression of scripts and CSS:
 * define('SCRIPT_DEBUG', true); loads the development (non-minified) versions of all scripts and CSS, and disables compression and concatenation,
 * define('CONCATENATE_SCRIPTS', false); disables compression and concatenation of scripts and CSS,
 * define('COMPRESS_SCRIPTS', false); disables compression of scripts,
 * define('COMPRESS_CSS', false); disables compression of CSS,
 * define('ENFORCE_GZIP', true); forces gzip for compression (default is deflate).
 *
 * The globals $concatenate_scripts, $compress_scripts and $compress_css can be set by plugins
 * to temporarily override the above settings. Also a compression test is run once and the result is saved
 * as option 'can_compress_scripts' (0/1). The test will run again if that option is deleted.
 *
 * @package Goatpress
 */

/** BackPress: Goatpress Dependencies Class */
require( ABSPATH . gpINC . '/class.gp-dependencies.php' );

/** BackPress: Goatpress Scripts Class */
require( ABSPATH . gpINC . '/class.gp-scripts.php' );

/** BackPress: Goatpress Scripts Functions */
require( ABSPATH . gpINC . '/functions.gp-scripts.php' );

/** BackPress: Goatpress Styles Class */
require( ABSPATH . gpINC . '/class.gp-styles.php' );

/** BackPress: Goatpress Styles Functions */
require( ABSPATH . gpINC . '/functions.gp-styles.php' );

/**
 * Register all Goatpress scripts.
 *
 * Localizes some of them.
 * args order: $scripts->add( 'handle', 'url', 'dependencies', 'query-string', 1 );
 * when last arg === 1 queues the script for the footer
 *
 * @since 2.6.0
 *
 * @param object $scripts gp_Scripts object.
 */
function gp_default_scripts( &$scripts ) {
	include( ABSPATH . gpINC . '/version.php' ); // include an unmodified $gp_version

	$develop_src = false !== strpos( $gp_version, '-src' );

	if ( ! defined( 'SCRIPT_DEBUG' ) ) {
		define( 'SCRIPT_DEBUG', $develop_src );
	}

	if ( ! $guessurl = site_url() ) {
		$guessed_url = true;
		$guessurl = gp_guess_url();
	}

	$scripts->base_url = $guessurl;
	$scripts->content_url = defined('gp_CONTENT_URL')? gp_CONTENT_URL : '';
	$scripts->default_version = get_bloginfo( 'version' );
	$scripts->default_dirs = array('/gp-admin/js/', '/gp-includes/js/');

	$suffix = SCRIPT_DEBUG ? '' : '.min';
	$dev_suffix = $develop_src ? '' : '.min';

	$scripts->add( 'utils', "/gp-includes/js/utils$suffix.js" );
	did_action( 'init' ) && $scripts->localize( 'utils', 'userSettings', array(
		'url' => (string) SITECOOKIEPATH,
		'uid' => (string) get_current_user_id(),
		'time' => (string) time(),
		'secure' => (string) ( 'https' === parse_url( site_url(), PHP_URL_SCHEME ) ),
	) );

	$scripts->add( 'common', "/gp-admin/js/common$suffix.js", array('jquery', 'hoverIntent', 'utils'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'common', 'commonL10n', array(
		'warnDelete' => __("You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete.")
	) );

	$scripts->add( 'gp-a11y', "/gp-includes/js/gp-a11y$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'sack', "/gp-includes/js/tw-sack$suffix.js", array(), '1.6.1', 1 );

	$scripts->add( 'quicktags', "/gp-includes/js/quicktags$suffix.js", array(), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'quicktags', 'quicktagsL10n', array(
		'closeAllOpenTags'      => esc_attr__( 'Close all open tags' ),
		'closeTags'             => esc_attr__( 'close tags' ),
		'enterURL'              => __( 'Enter the URL' ),
		'enterImageURL'         => __( 'Enter the URL of the image' ),
		'enterImageDescription' => __( 'Enter a description of the image' ),
		'fullscreen'            => __( 'fullscreen' ),
		'toggleFullscreen'      => esc_attr__( 'Toggle fullscreen mode' ),
		'textdirection'         => esc_attr__( 'text direction' ),
		'toggleTextdirection'   => esc_attr__( 'Toggle Editor Text Direction' ),
		'dfw'                   => esc_attr__( 'Distraction-free writing mode' )
	) );

	$scripts->add( 'colorpicker', "/gp-includes/js/colorpicker$suffix.js", array('prototype'), '3517m' );

	$scripts->add( 'editor', "/gp-admin/js/editor$suffix.js", array('utils','jquery'), false, 1 );

	$scripts->add( 'gp-fullscreen', "/gp-admin/js/gp-fullscreen$suffix.js", array('jquery'), false, 1 );

	$scripts->add( 'gp-ajax-response', "/gp-includes/js/gp-ajax-response$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'gp-ajax-response', 'gpAjax', array(
		'noPerm' => __('You do not have permission to do that.'),
		'broken' => __('An unidentified error has occurred.')
	) );

	$scripts->add( 'gp-pointer', "/gp-includes/js/gp-pointer$suffix.js", array( 'jquery-ui-widget', 'jquery-ui-position' ), '20111129a', 1 );
	did_action( 'init' ) && $scripts->localize( 'gp-pointer', 'gpPointerL10n', array(
		'dismiss' => __('Dismiss'),
	) );

	$scripts->add( 'autosave', "/gp-includes/js/autosave$suffix.js", array('heartbeat'), false, 1 );

	$scripts->add( 'heartbeat', "/gp-includes/js/heartbeat$suffix.js", array('jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'heartbeat', 'heartbeatSettings',
		/**
		 * Filter the Heartbeat settings.
		 *
		 * @since 3.6.0
		 *
		 * @param array $settings Heartbeat settings array.
		 */
		apply_filters( 'heartbeat_settings', array() )
	);

	$scripts->add( 'gp-auth-check', "/gp-includes/js/gp-auth-check$suffix.js", array('heartbeat'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'gp-auth-check', 'authcheckL10n', array(
		'beforeunload' => __('Your session has expired. You can log in again from this page or go to the login page.'),

		/**
		 * Filter the authentication check interval.
		 *
		 * @since 3.6.0
		 *
		 * @param int $interval The interval in which to check a user's authentication.
		 *                      Default 3 minutes in seconds, or 180.
		 */
		'interval' => apply_filters( 'gp_auth_check_interval', 3 * MINUTE_IN_SECONDS ),
	) );

	$scripts->add( 'gp-lists', "/gp-includes/js/gp-lists$suffix.js", array( 'gp-ajax-response', 'jquery-color' ), false, 1 );

	// Goatpress no longer uses or bundles Prototype or script.aculo.us. These are now pulled from an external source.
	$scripts->add( 'prototype', '//ajax.googleapis.com/ajax/libs/prototype/1.7.1.0/prototype.js', array(), '1.7.1');
	$scripts->add( 'scriptaculous-root', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js', array('prototype'), '1.9.0');
	$scripts->add( 'scriptaculous-builder', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/builder.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous-dragdrop', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/dragdrop.js', array('scriptaculous-builder', 'scriptaculous-effects'), '1.9.0');
	$scripts->add( 'scriptaculous-effects', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/effects.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous-slider', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/slider.js', array('scriptaculous-effects'), '1.9.0');
	$scripts->add( 'scriptaculous-sound', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/sound.js', array( 'scriptaculous-root' ), '1.9.0' );
	$scripts->add( 'scriptaculous-controls', '//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/controls.js', array('scriptaculous-root'), '1.9.0');
	$scripts->add( 'scriptaculous', false, array('scriptaculous-dragdrop', 'scriptaculous-slider', 'scriptaculous-controls') );

	// not used in core, replaced by Jcrop.js
	$scripts->add( 'cropper', '/gp-includes/js/crop/cropper.js', array('scriptaculous-dragdrop') );

	// jQuery
	$scripts->add( 'jquery', false, array( 'jquery-core', 'jquery-migrate' ), '1.11.2' );
	$scripts->add( 'jquery-core', '/gp-includes/js/jquery/jquery.js', array(), '1.11.2' );
	$scripts->add( 'jquery-migrate', "/gp-includes/js/jquery/jquery-migrate$suffix.js", array(), '1.2.1' );

	// full jQuery UI
	$scripts->add( 'jquery-ui-core', "/gp-includes/js/jquery/ui/core$dev_suffix.js", array('jquery'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-core', "/gp-includes/js/jquery/ui/effect$dev_suffix.js", array('jquery'), '1.11.4', 1 );

	$scripts->add( 'jquery-effects-blind', "/gp-includes/js/jquery/ui/effect-blind$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-bounce', "/gp-includes/js/jquery/ui/effect-bounce$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-clip', "/gp-includes/js/jquery/ui/effect-clip$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-drop', "/gp-includes/js/jquery/ui/effect-drop$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-explode', "/gp-includes/js/jquery/ui/effect-explode$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-fade', "/gp-includes/js/jquery/ui/effect-fade$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-fold', "/gp-includes/js/jquery/ui/effect-fold$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-highlight', "/gp-includes/js/jquery/ui/effect-highlight$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-puff', "/gp-includes/js/jquery/ui/effect-puff$dev_suffix.js", array('jquery-effects-core', 'jquery-effects-scale'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-pulsate', "/gp-includes/js/jquery/ui/effect-pulsate$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-scale', "/gp-includes/js/jquery/ui/effect-scale$dev_suffix.js", array('jquery-effects-core', 'jquery-effects-size'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-shake', "/gp-includes/js/jquery/ui/effect-shake$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-size', "/gp-includes/js/jquery/ui/effect-size$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-slide', "/gp-includes/js/jquery/ui/effect-slide$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-effects-transfer', "/gp-includes/js/jquery/ui/effect-transfer$dev_suffix.js", array('jquery-effects-core'), '1.11.4', 1 );

	$scripts->add( 'jquery-ui-accordion', "/gp-includes/js/jquery/ui/accordion$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-autocomplete', "/gp-includes/js/jquery/ui/autocomplete$dev_suffix.js", array('jquery-ui-menu'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-button', "/gp-includes/js/jquery/ui/button$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-datepicker', "/gp-includes/js/jquery/ui/datepicker$dev_suffix.js", array('jquery-ui-core'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-dialog', "/gp-includes/js/jquery/ui/dialog$dev_suffix.js", array('jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-button', 'jquery-ui-position'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-draggable', "/gp-includes/js/jquery/ui/draggable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-droppable', "/gp-includes/js/jquery/ui/droppable$dev_suffix.js", array('jquery-ui-draggable'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-menu', "/gp-includes/js/jquery/ui/menu$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-mouse', "/gp-includes/js/jquery/ui/mouse$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-position', "/gp-includes/js/jquery/ui/position$dev_suffix.js", array('jquery'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-progressbar', "/gp-includes/js/jquery/ui/progressbar$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-resizable', "/gp-includes/js/jquery/ui/resizable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-selectable', "/gp-includes/js/jquery/ui/selectable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-selectmenu', "/gp-includes/js/jquery/ui/selectmenu$dev_suffix.js", array('jquery-ui-menu'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-slider', "/gp-includes/js/jquery/ui/slider$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-sortable', "/gp-includes/js/jquery/ui/sortable$dev_suffix.js", array('jquery-ui-mouse'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-spinner', "/gp-includes/js/jquery/ui/spinner$dev_suffix.js", array( 'jquery-ui-button' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-tabs', "/gp-includes/js/jquery/ui/tabs$dev_suffix.js", array('jquery-ui-core', 'jquery-ui-widget'), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-tooltip', "/gp-includes/js/jquery/ui/tooltip$dev_suffix.js", array( 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-position' ), '1.11.4', 1 );
	$scripts->add( 'jquery-ui-widget', "/gp-includes/js/jquery/ui/widget$dev_suffix.js", array('jquery'), '1.11.4', 1 );

	// deprecated, not used in core, most functionality is included in jQuery 1.3
	$scripts->add( 'jquery-form', "/gp-includes/js/jquery/jquery.form$suffix.js", array('jquery'), '3.37.0', 1 );

	// jQuery plugins
	$scripts->add( 'jquery-color', "/gp-includes/js/jquery/jquery.color.min.js", array('jquery'), '2.1.1', 1 );
	$scripts->add( 'suggest', "/gp-includes/js/jquery/suggest$suffix.js", array('jquery'), '1.1-20110113', 1 );
	$scripts->add( 'schedule', '/gp-includes/js/jquery/jquery.schedule.js', array('jquery'), '20m', 1 );
	$scripts->add( 'jquery-query', "/gp-includes/js/jquery/jquery.query.js", array('jquery'), '2.1.7', 1 );
	$scripts->add( 'jquery-serialize-object', "/gp-includes/js/jquery/jquery.serialize-object.js", array('jquery'), '0.2', 1 );
	$scripts->add( 'jquery-hotkeys', "/gp-includes/js/jquery/jquery.hotkeys$suffix.js", array('jquery'), '0.0.2m', 1 );
	$scripts->add( 'jquery-table-hotkeys', "/gp-includes/js/jquery/jquery.table-hotkeys$suffix.js", array('jquery', 'jquery-hotkeys'), false, 1 );
	$scripts->add( 'jquery-touch-punch', "/gp-includes/js/jquery/jquery.ui.touch-punch.js", array('jquery-ui-widget', 'jquery-ui-mouse'), '0.2.2', 1 );

	// Masonry v2 depended on jQuery. v3 does not. The older jquery-masonry handle is a shiv.
	// It sets jQuery as a dependency, as the theme may have been implicitly loading it this way.
	$scripts->add( 'masonry', "/gp-includes/js/masonry.min.js", array(), '3.1.2', 1 );
	$scripts->add( 'jquery-masonry', "/gp-includes/js/jquery/jquery.masonry$dev_suffix.js", array( 'jquery', 'masonry' ), '3.1.2', 1 );

	$scripts->add( 'thickbox', "/gp-includes/js/thickbox/thickbox.js", array('jquery'), '3.1-20121105', 1 );
	did_action( 'init' ) && $scripts->localize( 'thickbox', 'thickboxL10n', array(
			'next' => __('Next &gt;'),
			'prev' => __('&lt; Prev'),
			'image' => __('Image'),
			'of' => __('of'),
			'close' => __('Close'),
			'noiframes' => __('This feature requires inline frames. You have iframes disabled or your browser does not support them.'),
			'loadingAnimation' => includes_url('js/thickbox/loadingAnimation.gif'),
	) );

	$scripts->add( 'jcrop', "/gp-includes/js/jcrop/jquery.Jcrop.min.js", array('jquery'), '0.9.12');

	$scripts->add( 'swfobject', "/gp-includes/js/swfobject.js", array(), '2.2-20120417');

	// error message for both plupload and swfupload
	$uploader_l10n = array(
		'queue_limit_exceeded' => __('You have attempted to queue too many files.'),
		'file_exceeds_size_limit' => __('%s exceeds the maximum upload size for this site.'),
		'zero_byte_file' => __('This file is empty. Please try another.'),
		'invalid_filetype' => __('This file type is not allowed. Please try another.'),
		'not_an_image' => __('This file is not an image. Please try another.'),
		'image_memory_exceeded' => __('Memory exceeded. Please try another smaller file.'),
		'image_dimensions_exceeded' => __('This is larger than the maximum size. Please try another.'),
		'default_error' => __('An error occurred in the upload. Please try again later.'),
		'missing_upload_url' => __('There was a configuration error. Please contact the server administrator.'),
		'upload_limit_exceeded' => __('You may only upload 1 file.'),
		'http_error' => __('HTTP error.'),
		'upload_failed' => __('Upload failed.'),
		'big_upload_failed' => __('Please try uploading this file with the %1$sbrowser uploader%2$s.'),
		'big_upload_queued' => __('%s exceeds the maximum upload size for the multi-file uploader when used in your browser.'),
		'io_error' => __('IO error.'),
		'security_error' => __('Security error.'),
		'file_cancelled' => __('File canceled.'),
		'upload_stopped' => __('Upload stopped.'),
		'dismiss' => __('Dismiss'),
		'crunching' => __('Crunching&hellip;'),
		'deleted' => __('moved to the trash.'),
		'error_uploading' => __('&#8220;%s&#8221; has failed to upload.')
	);

	$scripts->add( 'plupload', '/gp-includes/js/plupload/plupload.full.min.js', array(), '2.1.1' );
	// Back compat handles:
	foreach ( array( 'all', 'html5', 'flash', 'silverlight', 'html4' ) as $handle ) {
		$scripts->add( "plupload-$handle", false, array( 'plupload' ), '2.1.1' );
	}

	$scripts->add( 'plupload-handlers', "/gp-includes/js/plupload/handlers$suffix.js", array( 'plupload', 'jquery' ) );
	did_action( 'init' ) && $scripts->localize( 'plupload-handlers', 'pluploadL10n', $uploader_l10n );

	$scripts->add( 'gp-plupload', "/gp-includes/js/plupload/gp-plupload$suffix.js", array( 'plupload', 'jquery', 'json2', 'media-models' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'gp-plupload', 'pluploadL10n', $uploader_l10n );

	// keep 'swfupload' for back-compat.
	$scripts->add( 'swfupload', '/gp-includes/js/swfupload/swfupload.js', array(), '2201-20110113');
	$scripts->add( 'swfupload-swfobject', '/gp-includes/js/swfupload/plugins/swfupload.swfobject.js', array('swfupload', 'swfobject'), '2201a');
	$scripts->add( 'swfupload-queue', '/gp-includes/js/swfupload/plugins/swfupload.queue.js', array('swfupload'), '2201');
	$scripts->add( 'swfupload-speed', '/gp-includes/js/swfupload/plugins/swfupload.speed.js', array('swfupload'), '2201');
	$scripts->add( 'swfupload-all', false, array('swfupload', 'swfupload-swfobject', 'swfupload-queue'), '2201');
	$scripts->add( 'swfupload-handlers', "/gp-includes/js/swfupload/handlers$suffix.js", array('swfupload-all', 'jquery'), '2201-20110524');
	did_action( 'init' ) && $scripts->localize( 'swfupload-handlers', 'swfuploadL10n', $uploader_l10n );

	$scripts->add( 'comment-reply', "/gp-includes/js/comment-reply$suffix.js", array(), false, 1 );

	$scripts->add( 'json2', "/gp-includes/js/json2$suffix.js", array(), '2011-02-23' );
	did_action( 'init' ) && $scripts->add_data( 'json2', 'conditional', 'lt IE 8' );

	$scripts->add( 'underscore', "/gp-includes/js/underscore$dev_suffix.js", array(), '1.6.0', 1 );
	$scripts->add( 'backbone', "/gp-includes/js/backbone$dev_suffix.js", array( 'underscore','jquery' ), '1.1.2', 1 );

	$scripts->add( 'gp-util', "/gp-includes/js/gp-util$suffix.js", array('underscore', 'jquery'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'gp-util', '_gpUtilSettings', array(
		'ajax' => array(
			'url' => admin_url( 'admin-ajax.php', 'relative' ),
		),
	) );

	$scripts->add( 'gp-backbone', "/gp-includes/js/gp-backbone$suffix.js", array('backbone', 'gp-util'), false, 1 );

	$scripts->add( 'revisions', "/gp-admin/js/revisions$suffix.js", array( 'gp-backbone', 'jquery-ui-slider', 'hoverIntent' ), false, 1 );

	$scripts->add( 'imgareaselect', "/gp-includes/js/imgareaselect/jquery.imgareaselect$suffix.js", array('jquery'), '0.9.10', 1 );

	$scripts->add( 'mediaelement', "/gp-includes/js/mediaelement/mediaelement-and-player.min.js", array('jquery'), '2.16.2', 1 );
	did_action( 'init' ) && $scripts->localize( 'mediaelement', 'mejsL10n', array(
		'language' => get_bloginfo( 'language' ),
		'strings'  => array(
			'Close'               => __( 'Close' ),
			'Fullscreen'          => __( 'Fullscreen' ),
			'Download File'       => __( 'Download File' ),
			'Download Video'      => __( 'Download Video' ),
			'Play/Pause'          => __( 'Play/Pause' ),
			'Mute Toggle'         => __( 'Mute Toggle' ),
			'None'                => __( 'None' ),
			'Turn off Fullscreen' => __( 'Turn off Fullscreen' ),
			'Go Fullscreen'       => __( 'Go Fullscreen' ),
			'Unmute'              => __( 'Unmute' ),
			'Mute'                => __( 'Mute' ),
			'Captions/Subtitles'  => __( 'Captions/Subtitles' )
		),
	) );


	$scripts->add( 'gp-mediaelement', "/gp-includes/js/mediaelement/gp-mediaelement.js", array('mediaelement'), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'mediaelement', '_gpmejsSettings', array(
		'pluginPath' => includes_url( 'js/mediaelement/', 'relative' ),
	) );

	$scripts->add( 'froogaloop',  "/gp-includes/js/mediaelement/froogaloop.min.js", array(), '2.0' );
	$scripts->add( 'gp-playlist', "/gp-includes/js/mediaelement/gp-playlist.js", array( 'gp-util', 'backbone', 'mediaelement' ), false, 1 );

	$scripts->add( 'zxcvbn-async', "/gp-includes/js/zxcvbn-async$suffix.js", array(), '1.0' );
	did_action( 'init' ) && $scripts->localize( 'zxcvbn-async', '_zxcvbnSettings', array(
		'src' => empty( $guessed_url ) ? includes_url( '/js/zxcvbn.min.js' ) : $scripts->base_url . '/gp-includes/js/zxcvbn.min.js',
	) );

	$scripts->add( 'password-strength-meter', "/gp-admin/js/password-strength-meter$suffix.js", array( 'jquery', 'zxcvbn-async' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'password-strength-meter', 'pwsL10n', array(
		'empty' => __('Strength indicator'),
		'short' => __('Very weak'),
		'bad' => __('Weak'),
		/* translators: password strength */
		'good' => _x('Medium', 'password strength'),
		'strong' => __('Strong'),
		'mismatch' => __('Mismatch')
	) );

	$scripts->add( 'user-profile', "/gp-admin/js/user-profile$suffix.js", array( 'jquery', 'password-strength-meter', 'gp-util' ), false, 1 );
	$scripts->add( 'language-chooser', "/gp-admin/js/language-chooser$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'user-suggest', "/gp-admin/js/user-suggest$suffix.js", array( 'jquery-ui-autocomplete' ), false, 1 );

	$scripts->add( 'admin-bar', "/gp-includes/js/admin-bar$suffix.js", array(), false, 1 );

	$scripts->add( 'gplink', "/gp-includes/js/gplink$suffix.js", array( 'jquery' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'gplink', 'gpLinkL10n', array(
		'title' => __('Insert/edit link'),
		'update' => __('Update'),
		'save' => __('Add Link'),
		'noTitle' => __('(no title)'),
		'noMatchesFound' => __('No results found.')
	) );

	$scripts->add( 'gpdialogs', "/gp-includes/js/gpdialog$suffix.js", array( 'jquery-ui-dialog' ), false, 1 );

	$scripts->add( 'word-count', "/gp-admin/js/word-count$suffix.js", array( 'jquery' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'word-count', 'wordCountL10n', array(
		/* translators: If your word count is based on single characters (East Asian characters),
		   enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */
		'type' => 'characters' == _x( 'words', 'word count: words or characters?' ) ? 'c' : 'w',
	) );

	$scripts->add( 'media-upload', "/gp-admin/js/media-upload$suffix.js", array( 'thickbox', 'shortcode' ), false, 1 );

	$scripts->add( 'hoverIntent', "/gp-includes/js/hoverIntent$suffix.js", array('jquery'), '1.8.1', 1 );

	$scripts->add( 'customize-base',     "/gp-includes/js/customize-base$suffix.js",     array( 'jquery', 'json2', 'underscore', 'gp-a11y' ), false, 1 );
	$scripts->add( 'customize-loader',   "/gp-includes/js/customize-loader$suffix.js",   array( 'customize-base' ), false, 1 );
	$scripts->add( 'customize-preview',  "/gp-includes/js/customize-preview$suffix.js",  array( 'customize-base' ), false, 1 );
	$scripts->add( 'customize-models',   "/gp-includes/js/customize-models.js", array( 'underscore', 'backbone' ), false, 1 );
	$scripts->add( 'customize-views',    "/gp-includes/js/customize-views.js",  array( 'jquery', 'underscore', 'imgareaselect', 'customize-models' ), false, 1 );
	$scripts->add( 'customize-controls', "/gp-admin/js/customize-controls$suffix.js", array( 'customize-base' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'customize-controls', '_gpCustomizeControlsL10n', array(
		'activate'           => __( 'Save &amp; Activate' ),
		'save'               => __( 'Save &amp; Publish' ),
		'saveAlert'          => __( 'The changes you made will be lost if you navigate away from this page.' ),
		'saved'              => __( 'Saved' ),
		'cancel'             => __( 'Cancel' ),
		'close'              => __( 'Close' ),
		'cheatin'            => __( 'Cheatin&#8217; uh?' ),
		'previewIframeTitle' => __( 'Site Preview' ),
		'loginIframeTitle'   => __( 'Session expired' ),

		// Used for overriding the file types allowed in plupload.
		'allowedFiles' => __( 'Allowed Files' ),
	) );

	$scripts->add( 'customize-widgets', "/gp-admin/js/customize-widgets$suffix.js", array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-droppable', 'gp-backbone', 'customize-controls' ), false, 1 );
	$scripts->add( 'customize-preview-widgets', "/gp-includes/js/customize-preview-widgets$suffix.js", array( 'jquery', 'gp-util', 'customize-preview' ), false, 1 );

	$scripts->add( 'accordion', "/gp-admin/js/accordion$suffix.js", array( 'jquery' ), false, 1 );

	$scripts->add( 'shortcode', "/gp-includes/js/shortcode$suffix.js", array( 'underscore' ), false, 1 );
	$scripts->add( 'media-models', "/gp-includes/js/media/models$suffix.js", array( 'gp-backbone' ), false, 1 );
	did_action( 'init' ) && $scripts->localize( 'media-models', '_gpMediaModelsL10n', array(
		'settings' => array(
			'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
			'post' => array( 'id' => 0 ),
		),
	) );

	// To enqueue media-views or media-editor, call gp_enqueue_media().
	// Both rely on numerous settings, styles, and templates to operate correctly.
	$scripts->add( 'media-views',  "/gp-includes/js/media/views$suffix.js",  array( 'utils', 'media-models', 'gp-plupload', 'jquery-ui-sortable', 'gp-mediaelement' ), false, 1 );
	$scripts->add( 'media-editor', "/gp-includes/js/media-editor$suffix.js", array( 'shortcode', 'media-views' ), false, 1 );
	$scripts->add( 'media-audiovideo', "/gp-includes/js/media/audio-video$suffix.js", array( 'media-editor' ), false, 1 );
	$scripts->add( 'mce-view', "/gp-includes/js/mce-view$suffix.js", array( 'shortcode', 'media-models', 'media-audiovideo', 'gp-playlist' ), false, 1 );

	if ( is_admin() ) {
		$scripts->add( 'admin-tags', "/gp-admin/js/tags$suffix.js", array( 'jquery', 'gp-ajax-response' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'admin-tags', 'tagsl10n', array(
			'noPerm' => __('You do not have permission to do that.'),
			'broken' => __('An unidentified error has occurred.')
		));

		$scripts->add( 'admin-comments', "/gp-admin/js/edit-comments$suffix.js", array('gp-lists', 'quicktags', 'jquery-query'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'admin-comments', 'adminCommentsL10n', array(
			'hotkeys_highlight_first' => isset($_GET['hotkeys_highlight_first']),
			'hotkeys_highlight_last' => isset($_GET['hotkeys_highlight_last']),
			'replyApprove' => __( 'Approve and Reply' ),
			'reply' => __( 'Reply' )
		) );

		$scripts->add( 'xfn', "/gp-admin/js/xfn$suffix.js", array('jquery'), false, 1 );

		$scripts->add( 'postbox', "/gp-admin/js/postbox$suffix.js", array('jquery-ui-sortable'), false, 1 );

		$scripts->add( 'tags-box', "/gp-admin/js/tags-box$suffix.js", array( 'jquery', 'suggest' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'tags-box', 'tagsBoxL10n', array(
			'tagDelimiter' => _x( ',', 'tag delimiter' ),
		) );

		$scripts->add( 'post', "/gp-admin/js/post$suffix.js", array( 'suggest', 'gp-lists', 'postbox', 'tags-box' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'post', 'postL10n', array(
			'ok' => __('OK'),
			'cancel' => __('Cancel'),
			'publishOn' => __('Publish on:'),
			'publishOnFuture' =>  __('Schedule for:'),
			'publishOnPast' => __('Published on:'),
			/* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
			'dateFormat' => __('%1$s %2$s, %3$s @ %4$s : %5$s'),
			'showcomm' => __('Show more comments'),
			'endcomm' => __('No more comments found.'),
			'publish' => __('Publish'),
			'schedule' => __('Schedule'),
			'update' => __('Update'),
			'savePending' => __('Save as Pending'),
			'saveDraft' => __('Save Draft'),
			'private' => __('Private'),
			'public' => __('Public'),
			'publicSticky' => __('Public, Sticky'),
			'password' => __('Password Protected'),
			'privatelyPublished' => __('Privately Published'),
			'published' => __('Published'),
			'saveAlert' => __('The changes you made will be lost if you navigate away from this page.'),
			'savingText' => __('Saving Draft&#8230;'),
		) );

		$scripts->add( 'press-this', "/gp-admin/js/press-this$suffix.js", array( 'jquery', 'tags-box' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'press-this', 'pressThisL10n', array(
			'negpost' => __( 'Title' ),
			'unexpectedError' => __( 'Sorry, but an unexpected error occurred.' ),
			'saveAlert' => __( 'The changes you made will be lost if you navigate away from this page.' ),
			/* translators: %d: nth embed found in a post */
			'suggestedEmbedAlt' => __( 'Suggested embed #%d' ),
			/* translators: %d: nth image found in a post */
			'suggestedImgAlt' => __( 'Suggested image #%d' ),
		) );

		$scripts->add( 'editor-expand', "/gp-admin/js/editor-expand$suffix.js", array( 'jquery' ), false, 1 );

		$scripts->add( 'link', "/gp-admin/js/link$suffix.js", array( 'gp-lists', 'postbox' ), false, 1 );

		$scripts->add( 'comment', "/gp-admin/js/comment$suffix.js", array( 'jquery', 'postbox' ) );
		$scripts->add_data( 'comment', 'group', 1 );
		did_action( 'init' ) && $scripts->localize( 'comment', 'commentL10n', array(
			'submittedOn' => __('Submitted on:')
		) );

		$scripts->add( 'admin-gallery', "/gp-admin/js/gallery$suffix.js", array( 'jquery-ui-sortable' ) );

		$scripts->add( 'admin-widgets', "/gp-admin/js/widgets$suffix.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), false, 1 );

		$scripts->add( 'theme', "/gp-admin/js/theme$suffix.js", array( 'gp-backbone' ), false, 1 );

		$scripts->add( 'inline-edit-post', "/gp-admin/js/inline-edit-post$suffix.js", array( 'jquery', 'suggest' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'inline-edit-post', 'inlineEditL10n', array(
			'error' => __('Error while saving the changes.'),
			'ntdeltitle' => __('Remove From Bulk Edit'),
			'notitle' => __('(no title)'),
			'comma' => trim( _x( ',', 'tag delimiter' ) ),
		) );

		$scripts->add( 'inline-edit-tax', "/gp-admin/js/inline-edit-tax$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'inline-edit-tax', 'inlineEditL10n', array(
			'error' => __('Error while saving the changes.')
		) );

		$scripts->add( 'plugin-install', "/gp-admin/js/plugin-install$suffix.js", array( 'jquery', 'thickbox' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'plugin-install', 'plugininstallL10n', array(
			'plugin_information' => __('Plugin Information:'),
			'ays' => __('Are you sure you want to install this plugin?')
		) );

		$scripts->add( 'updates', "/gp-admin/js/updates$suffix.js", array( 'jquery', 'gp-util', 'gp-a11y' ) );
		did_action( 'init' ) && $scripts->localize( 'updates', '_gpUpdatesSettings', array(
			'ajax_nonce' => gp_create_nonce( 'updates' ),
			'l10n'       => array(
				'updating'      => __( 'Updating...' ),
				'updated'       => __( 'Updated!' ),
				'updateFailed'  => __( 'Update failed.' ),
				'updatingMsg'   => __( 'Updating... please wait.' ),
				'updatedMsg'    => __( 'Update completed successfully.' ),
				'updateCancel'  => __( 'Update canceled' ),
			)
		) );

		$scripts->add( 'farbtastic', '/gp-admin/js/farbtastic.js', array('jquery'), '1.2' );

		$scripts->add( 'iris', '/gp-admin/js/iris.min.js', array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), '1.0.7', 1 );
		$scripts->add( 'gp-color-picker', "/gp-admin/js/color-picker$suffix.js", array( 'iris' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'gp-color-picker', 'gpColorPickerL10n', array(
			'clear' => __( 'Clear' ),
			'defaultString' => __( 'Default' ),
			'pick' => __( 'Select Color' ),
			'current' => __( 'Current Color' ),
		) );

		$scripts->add( 'dashboard', "/gp-admin/js/dashboard$suffix.js", array( 'jquery', 'admin-comments', 'postbox' ), false, 1 );

		$scripts->add( 'list-revisions', "/gp-includes/js/gp-list-revisions$suffix.js" );

		$scripts->add( 'media-grid', "/gp-includes/js/media/grid$suffix.js", array( 'media-editor' ), false, 1 );
		$scripts->add( 'media', "/gp-admin/js/media$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'media', 'attachMediaBoxL10n', array(
			'error' => __( 'An error has occurred. Please reload the page and try again.' ),
		));

		$scripts->add( 'image-edit', "/gp-admin/js/image-edit$suffix.js", array('jquery', 'json2', 'imgareaselect'), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'image-edit', 'imageEditL10n', array(
			'error' => __( 'Could not load the preview image. Please reload the page and try again.' )
		));

		$scripts->add( 'set-post-thumbnail', "/gp-admin/js/set-post-thumbnail$suffix.js", array( 'jquery' ), false, 1 );
		did_action( 'init' ) && $scripts->localize( 'set-post-thumbnail', 'setPostThumbnailL10n', array(
			'setThumbnail' => __( 'Use as featured image' ),
			'saving' => __( 'Saving...' ),
			'error' => __( 'Could not set that as the thumbnail image. Try a different attachment.' ),
			'done' => __( 'Done' )
		) );

		// Navigation Menus
		$scripts->add( 'nav-menu', "/gp-admin/js/nav-menu$suffix.js", array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'gp-lists', 'postbox' ) );
		did_action( 'init' ) && $scripts->localize( 'nav-menu', 'navMenuL10n', array(
			'noResultsFound' => __( 'No results found.' ),
			'warnDeleteMenu' => __( "You are about to permanently delete this menu. \n 'Cancel' to stop, 'OK' to delete." ),
			'saveAlert' => __( 'The changes you made will be lost if you navigate away from this page.' ),
			'untitled' => _x( '(no label)', 'missing menu item navigation label' )
		) );

		$scripts->add( 'custom-header', "/gp-admin/js/custom-header.js", array( 'jquery-masonry' ), false, 1 );
		$scripts->add( 'custom-background', "/gp-admin/js/custom-background$suffix.js", array( 'gp-color-picker', 'media-views' ), false, 1 );
		$scripts->add( 'media-gallery', "/gp-admin/js/media-gallery$suffix.js", array('jquery'), false, 1 );

		$scripts->add( 'svg-painter', '/gp-admin/js/svg-painter.js', array( 'jquery' ), false, 1 );
	}
}

/**
 * Assign default styles to $styles object.
 *
 * Nothing is returned, because the $styles parameter is passed by reference.
 * Meaning that whatever object is passed will be updated without having to
 * reassign the variable that was passed back to the same value. This saves
 * memory.
 *
 * Adding default styles is not the only task, it also assigns the base_url
 * property, the default version, and text direction for the object.
 *
 * @since 2.6.0
 *
 * @param object $styles
 */
function gp_default_styles( &$styles ) {
	include( ABSPATH . gpINC . '/version.php' ); // include an unmodified $gp_version

	if ( ! defined( 'SCRIPT_DEBUG' ) )
		define( 'SCRIPT_DEBUG', false !== strpos( $gp_version, '-src' ) );

	if ( ! $guessurl = site_url() )
		$guessurl = gp_guess_url();

	$styles->base_url = $guessurl;
	$styles->content_url = defined('gp_CONTENT_URL')? gp_CONTENT_URL : '';
	$styles->default_version = get_bloginfo( 'version' );
	$styles->text_direction = function_exists( 'is_rtl' ) && is_rtl() ? 'rtl' : 'ltr';
	$styles->default_dirs = array('/gp-admin/', '/gp-includes/css/');

	$open_sans_font_url = '';

	/* translators: If there are characters in your language that are not supported
	 * by Open Sans, translate this to 'off'. Do not translate into your own language.
	 */
	if ( 'off' !== _x( 'on', 'Open Sans font: on or off' ) ) {
		$subsets = 'latin,latin-ext';

		/* translators: To add an additional Open Sans character subset specific to your language,
		 * translate this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language.
		 */
		$subset = _x( 'no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)' );

		if ( 'cyrillic' == $subset ) {
			$subsets .= ',cyrillic,cyrillic-ext';
		} elseif ( 'greek' == $subset ) {
			$subsets .= ',greek,greek-ext';
		} elseif ( 'vietnamese' == $subset ) {
			$subsets .= ',vietnamese';
		}

		// Hotlink Open Sans, for now
		$open_sans_font_url = "//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=$subsets";
	}

	// Register a stylesheet for the selected admin color scheme.
	$styles->add( 'colors', true, array( 'gp-admin', 'buttons', 'open-sans', 'dashicons' ) );

	$suffix = SCRIPT_DEBUG ? '' : '.min';

	// Admin CSS
	$styles->add( 'gp-admin',           "/gp-admin/css/gp-admin$suffix.css", array( 'open-sans', 'dashicons' ) );
	$styles->add( 'login',              "/gp-admin/css/login$suffix.css", array( 'buttons', 'open-sans', 'dashicons' ) );
	$styles->add( 'install',            "/gp-admin/css/install$suffix.css", array( 'buttons', 'open-sans' ) );
	$styles->add( 'gp-color-picker',    "/gp-admin/css/color-picker$suffix.css" );
	$styles->add( 'customize-controls', "/gp-admin/css/customize-controls$suffix.css", array( 'gp-admin', 'colors', 'ie', 'imgareaselect' ) );
	$styles->add( 'customize-widgets',  "/gp-admin/css/customize-widgets$suffix.css", array( 'gp-admin', 'colors' ) );
	$styles->add( 'press-this',         "/gp-admin/css/press-this$suffix.css", array( 'open-sans', 'buttons' ) );

	$styles->add( 'ie',                 "/gp-admin/css/ie$suffix.css" );
	$styles->add_data( 'ie', 'conditional', 'lte IE 7' );

	// Common dependencies
	$styles->add( 'buttons',   "/gp-includes/css/buttons$suffix.css" );
	$styles->add( 'dashicons', "/gp-includes/css/dashicons$suffix.css" );
	$styles->add( 'open-sans', $open_sans_font_url );

	// Includes CSS
	$styles->add( 'admin-bar',      "/gp-includes/css/admin-bar$suffix.css", array( 'open-sans', 'dashicons' ) );
	$styles->add( 'gp-auth-check',  "/gp-includes/css/gp-auth-check$suffix.css", array( 'dashicons' ) );
	$styles->add( 'editor-buttons', "/gp-includes/css/editor$suffix.css", array( 'dashicons' ) );
	$styles->add( 'media-views',    "/gp-includes/css/media-views$suffix.css", array( 'buttons', 'dashicons', 'gp-mediaelement' ) );
	$styles->add( 'gp-pointer',     "/gp-includes/css/gp-pointer$suffix.css", array( 'dashicons' ) );

	// External libraries and friends
	$styles->add( 'imgareaselect',       '/gp-includes/js/imgareaselect/imgareaselect.css', array(), '0.9.8' );
	$styles->add( 'gp-jquery-ui-dialog', "/gp-includes/css/jquery-ui-dialog$suffix.css", array( 'dashicons' ) );
	$styles->add( 'mediaelement',        "/gp-includes/js/mediaelement/mediaelementplayer.min.css", array(), '2.16.2' );
	$styles->add( 'gp-mediaelement',     "/gp-includes/js/mediaelement/gp-mediaelement.css", array( 'mediaelement' ) );
	$styles->add( 'thickbox',            '/gp-includes/js/thickbox/thickbox.css', array( 'dashicons' ) );

	// Deprecated CSS
	$styles->add( 'media',      "/gp-admin/css/deprecated-media$suffix.css" );
	$styles->add( 'farbtastic', '/gp-admin/css/farbtastic.css', array(), '1.3u1' );
	$styles->add( 'jcrop',      "/gp-includes/js/jcrop/jquery.Jcrop.min.css", array(), '0.9.12' );
	$styles->add( 'colors-fresh', false, array( 'gp-admin', 'buttons' ) ); // Old handle.

	// RTL CSS
	$rtl_styles = array(
		// gp-admin
		'gp-admin', 'install', 'gp-color-picker', 'customize-controls', 'customize-widgets', 'ie', 'login', 'press-this',
		// gp-includes
		'buttons', 'admin-bar', 'gp-auth-check', 'editor-buttons', 'media-views', 'gp-pointer',
		'gp-jquery-ui-dialog',
		// deprecated
		'media', 'farbtastic',
	);

	foreach ( $rtl_styles as $rtl_style ) {
		$styles->add_data( $rtl_style, 'rtl', 'replace' );
		if ( $suffix ) {
			$styles->add_data( $rtl_style, 'suffix', $suffix );
		}
	}
}

/**
 * Reorder JavaScript scripts array to place prototype before jQuery.
 *
 * @since 2.3.1
 *
 * @param array $js_array JavaScript scripts array
 * @return array Reordered array, if needed.
 */
function gp_prototype_before_jquery( $js_array ) {
	if ( false === $prototype = array_search( 'prototype', $js_array, true ) )
		return $js_array;

	if ( false === $jquery = array_search( 'jquery', $js_array, true ) )
		return $js_array;

	if ( $prototype < $jquery )
		return $js_array;

	unset($js_array[$prototype]);

	array_splice( $js_array, $jquery, 0, 'prototype' );

	return $js_array;
}

/**
 * Load localized data on print rather than initialization.
 *
 * These localizations require information that may not be loaded even by init.
 *
 * @since 2.5.0
 */
function gp_just_in_time_script_localization() {

	gp_localize_script( 'autosave', 'autosaveL10n', array(
		'autosaveInterval' => AUTOSAVE_INTERVAL,
		'blog_id' => get_current_blog_id(),
	) );

}

/**
 * Administration Screen CSS for changing the styles.
 *
 * If installing the 'gp-admin/' directory will be replaced with './'.
 *
 * The $_gp_admin_css_colors global manages the Administration Screens CSS
 * stylesheet that is loaded. The option that is set is 'admin_color' and is the
 * color and key for the array. The value for the color key is an object with
 * a 'url' parameter that has the URL path to the CSS file.
 *
 * The query from $src parameter will be appended to the URL that is given from
 * the $_gp_admin_css_colors array value URL.
 *
 * @since 2.6.0
 * @uses $_gp_admin_css_colors
 *
 * @param string $src Source URL.
 * @param string $handle Either 'colors' or 'colors-rtl'.
 * @return string URL path to CSS stylesheet for Administration Screens.
 */
function gp_style_loader_src( $src, $handle ) {
	global $_gp_admin_css_colors;

	if ( defined('gp_INSTALLING') )
		return preg_replace( '#^gp-admin/#', './', $src );

	if ( 'colors' == $handle ) {
		$color = get_user_option('admin_color');

		if ( empty($color) || !isset($_gp_admin_css_colors[$color]) )
			$color = 'fresh';

		$color = $_gp_admin_css_colors[$color];
		$parsed = parse_url( $src );
		$url = $color->url;

		if ( ! $url ) {
			return false;
		}

		if ( isset($parsed['query']) && $parsed['query'] ) {
			gp_parse_str( $parsed['query'], $qv );
			$url = add_query_arg( $qv, $url );
		}

		return $url;
	}

	return $src;
}

/**
 * Prints the script queue in the HTML head on admin pages.
 *
 * Postpones the scripts that were queued for the footer.
 * print_footer_scripts() is called in the footer to print these scripts.
 *
 * @since 2.8.0
 *
 * @see gp_print_scripts()
 */
function print_head_scripts() {
	global $gp_scripts, $concatenate_scripts;

	if ( ! did_action('gp_print_scripts') ) {
		/** This action is documented in gp-includes/functions.gp-scripts.php */
		do_action( 'gp_print_scripts' );
	}

	if ( ! ( $gp_scripts instanceof gp_Scripts ) ) {
		$gp_scripts = new gp_Scripts();
	}

	script_concat_settings();
	$gp_scripts->do_concat = $concatenate_scripts;
	$gp_scripts->do_head_items();

	/**
	 * Filter whether to print the head scripts.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $print Whether to print the head scripts. Default true.
	 */
	if ( apply_filters( 'print_head_scripts', true ) ) {
		_print_scripts();
	}

	$gp_scripts->reset();
	return $gp_scripts->done;
}

/**
 * Prints the scripts that were queued for the footer or too late for the HTML head.
 *
 * @since 2.8.0
 */
function print_footer_scripts() {
	global $gp_scripts, $concatenate_scripts;

	if ( ! ( $gp_scripts instanceof gp_Scripts ) ) {
		return array(); // No need to run if not instantiated.
	}
	script_concat_settings();
	$gp_scripts->do_concat = $concatenate_scripts;
	$gp_scripts->do_footer_items();

	/**
	 * Filter whether to print the footer scripts.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $print Whether to print the footer scripts. Default true.
	 */
	if ( apply_filters( 'print_footer_scripts', true ) ) {
		_print_scripts();
	}

	$gp_scripts->reset();
	return $gp_scripts->done;
}

/**
 * Print scripts (internal use only)
 *
 * @ignore
 */
function _print_scripts() {
	global $gp_scripts, $compress_scripts;

	$zip = $compress_scripts ? 1 : 0;
	if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
		$zip = 'gzip';

	if ( $concat = trim( $gp_scripts->concat, ', ' ) ) {

		if ( !empty($gp_scripts->print_code) ) {
			echo "\n<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n"; // not needed in HTML 5
			echo $gp_scripts->print_code;
			echo "/* ]]> */\n";
			echo "</script>\n";
		}

		$concat = str_split( $concat, 128 );
		$concat = 'load%5B%5D=' . implode( '&load%5B%5D=', $concat );

		$src = $gp_scripts->base_url . "/gp-admin/load-scripts.php?c={$zip}&" . $concat . '&ver=' . $gp_scripts->default_version;
		echo "<script type='text/javascript' src='" . esc_attr($src) . "'></script>\n";
	}

	if ( !empty($gp_scripts->print_html) )
		echo $gp_scripts->print_html;
}

/**
 * Prints the script queue in the HTML head on the front end.
 *
 * Postpones the scripts that were queued for the footer.
 * gp_print_footer_scripts() is called in the footer to print these scripts.
 *
 * @since 2.8.0
 */
function gp_print_head_scripts() {
	if ( ! did_action('gp_print_scripts') ) {
		/** This action is documented in gp-includes/functions.gp-scripts.php */
		do_action( 'gp_print_scripts' );
	}

	global $gp_scripts;

	if ( ! ( $gp_scripts instanceof gp_Scripts ) ) {
		return array(); // no need to run if nothing is queued
	}
	return print_head_scripts();
}

/**
 * Private, for use in *_footer_scripts hooks
 *
 * @since 3.3.0
 */
function _gp_footer_scripts() {
	print_late_styles();
	print_footer_scripts();
}

/**
 * Hooks to print the scripts and styles in the footer.
 *
 * @since 2.8.0
 */
function gp_print_footer_scripts() {
	/**
	 * Fires when footer scripts are printed.
	 *
	 * @since 2.8.0
	 */
	do_action( 'gp_print_footer_scripts' );
}

/**
 * Wrapper for do_action('gp_enqueue_scripts')
 *
 * Allows plugins to queue scripts for the front end using gp_enqueue_script().
 * Runs first in gp_head() where all is_home(), is_page(), etc. functions are available.
 *
 * @since 2.8.0
 */
function gp_enqueue_scripts() {
	/**
	 * Fires when scripts and styles are enqueued.
	 *
	 * @since 2.8.0
	 */
	do_action( 'gp_enqueue_scripts' );
}

/**
 * Prints the styles queue in the HTML head on admin pages.
 *
 * @since 2.8.0
 */
function print_admin_styles() {
	global $gp_styles, $concatenate_scripts;

	if ( ! ( $gp_styles instanceof gp_Styles ) ) {
		$gp_styles = new gp_Styles();
	}

	script_concat_settings();
	$gp_styles->do_concat = $concatenate_scripts;
	$gp_styles->do_items(false);

	/**
	 * Filter whether to print the admin styles.
	 *
	 * @since 2.8.0
	 *
	 * @param bool $print Whether to print the admin styles. Default true.
	 */
	if ( apply_filters( 'print_admin_styles', true ) ) {
		_print_styles();
	}

	$gp_styles->reset();
	return $gp_styles->done;
}

/**
 * Prints the styles that were queued too late for the HTML head.
 *
 * @since 3.3.0
 */
function print_late_styles() {
	global $gp_styles, $concatenate_scripts;

	if ( ! ( $gp_styles instanceof gp_Styles ) ) {
		return;
	}

	$gp_styles->do_concat = $concatenate_scripts;
	$gp_styles->do_footer_items();

	/**
	 * Filter whether to print the styles queued too late for the HTML head.
	 *
	 * @since 3.3.0
	 *
	 * @param bool $print Whether to print the 'late' styles. Default true.
	 */
	if ( apply_filters( 'print_late_styles', true ) ) {
		_print_styles();
	}

	$gp_styles->reset();
	return $gp_styles->done;
}

/**
 * Print styles (internal use only)
 *
 * @ignore
 */
function _print_styles() {
	global $gp_styles, $compress_css;

	$zip = $compress_css ? 1 : 0;
	if ( $zip && defined('ENFORCE_GZIP') && ENFORCE_GZIP )
		$zip = 'gzip';

	if ( !empty($gp_styles->concat) ) {
		$dir = $gp_styles->text_direction;
		$ver = $gp_styles->default_version;
		$href = $gp_styles->base_url . "/gp-admin/load-styles.php?c={$zip}&dir={$dir}&load=" . trim($gp_styles->concat, ', ') . '&ver=' . $ver;
		echo "<link rel='stylesheet' href='" . esc_attr($href) . "' type='text/css' media='all' />\n";

		if ( !empty($gp_styles->print_code) ) {
			echo "<style type='text/css'>\n";
			echo $gp_styles->print_code;
			echo "\n</style>\n";
		}
	}

	if ( !empty($gp_styles->print_html) )
		echo $gp_styles->print_html;
}

/**
 * Determine the concatenation and compression settings for scripts and styles.
 *
 * @since 2.8.0
 */
function script_concat_settings() {
	global $concatenate_scripts, $compress_scripts, $compress_css;

	$compressed_output = ( ini_get('zlib.output_compression') || 'ob_gzhandler' == ini_get('output_handler') );

	if ( ! isset($concatenate_scripts) ) {
		$concatenate_scripts = defined('CONCATENATE_SCRIPTS') ? CONCATENATE_SCRIPTS : true;
		if ( ! is_admin() || ( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) )
			$concatenate_scripts = false;
	}

	if ( ! isset($compress_scripts) ) {
		$compress_scripts = defined('COMPRESS_SCRIPTS') ? COMPRESS_SCRIPTS : true;
		if ( $compress_scripts && ( ! get_site_option('can_compress_scripts') || $compressed_output ) )
			$compress_scripts = false;
	}

	if ( ! isset($compress_css) ) {
		$compress_css = defined('COMPRESS_CSS') ? COMPRESS_CSS : true;
		if ( $compress_css && ( ! get_site_option('can_compress_scripts') || $compressed_output ) )
			$compress_css = false;
	}
}
