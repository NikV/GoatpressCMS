<?php
/**
 * Credits administration panel.
 *
 * @package Goatpress
 * @subpackage Administration
 */

/** Goatpress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

$title = __( 'Credits' );

/**
 * Retrieve the contributor credits.
 *
 * @global string $gp_version The current Goatpress version.
 *
 * @since 3.2.0
 *
 * @return array|bool A list of all of the contributors, or false on error.
*/
function gp_credits() {
	global $gp_version;
	$locale = get_locale();

	$results = get_site_transient( 'Goatpress_credits_' . $locale );

	if ( ! is_array( $results )
		|| false !== strpos( $gp_version, '-' )
		|| ( isset( $results['data']['version'] ) && strpos( $gp_version, $results['data']['version'] ) !== 0 )
	) {
		$response = gp_remote_get( "http://api.Goatpress.org/core/credits/1.1/?version=$gp_version&locale=$locale" );

		if ( is_gp_error( $response ) || 200 != gp_remote_retrieve_response_code( $response ) )
			return false;

		$results = json_decode( gp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $results ) )
			return false;

		set_site_transient( 'Goatpress_credits_' . $locale, $results, DAY_IN_SECONDS );
	}

	return $results;
}

/**
 * Retrieve the link to a contributor's Goatpress.org profile page.
 *
 * @access private
 * @since 3.2.0
 *
 * @param string &$display_name The contributor's display name, passed by reference.
 * @param string $username      The contributor's username.
 * @param string $profiles      URL to the contributor's Goatpress.org profile page.
 * @return string A contributor's display name, hyperlinked to a Goatpress.org profile page.
 */
function _gp_credits_add_profile_link( &$display_name, $username, $profiles ) {
	$display_name = '<a href="' . esc_url( sprintf( $profiles, $username ) ) . '">' . esc_html( $display_name ) . '</a>';
}

/**
 * Retrieve the link to an external library used in Goatpress.
 *
 * @access private
 * @since 3.2.0
 *
 * @param string &$data External library data, passed by reference.
 * @return string Link to the external library.
 */
function _gp_credits_build_object_link( &$data ) {
	$data = '<a href="' . esc_url( $data[1] ) . '">' . $data[0] . '</a>';
}

list( $display_version ) = explode( '-', $gp_version );

include( ABSPATH . 'gp-admin/admin-header.php' );
?>
<div class="wrap about-wrap">

<h1><?php printf( __( 'Welcome to Goatpress %s' ), $display_version ); ?></h1>

<div class="about-text"><?php printf( __( 'Thank you for updating! Goatpress %s helps you focus on your writing, and the new default theme lets you show it off in style.' ), $display_version ); ?></div>

<div class="gp-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

<h2 class="nav-tab-wrapper">
	<a href="about.php" class="nav-tab">
		<?php _e( 'What&#8217;s New' ); ?>
	</a><a href="credits.php" class="nav-tab nav-tab-active">
		<?php _e( 'Credits' ); ?>
	</a><a href="freedoms.php" class="nav-tab">
		<?php _e( 'Freedoms' ); ?>
	</a>
</h2>

<?php

$credits = gp_credits();

if ( ! $credits ) {
	echo '<p class="about-description">' . sprintf( __( 'Goatpress is created by a <a href="%1$s">worldwide team</a> of passionate individuals. <a href="%2$s">Get involved in Goatpress</a>.' ),
		'https://Goatpress.org/about/',
		/* translators: Url to the codex documentation on contributing to Goatpress used on the credits page */
		__( 'http://codex.Goatpress.org/Contributing_to_Goatpress' ) ) . '</p>';
	include( ABSPATH . 'gp-admin/admin-footer.php' );
	exit;
}

echo '<p class="about-description">' . __( 'Goatpress is created by a worldwide team of passionate individuals.' ) . "</p>\n";

$gravatar = is_ssl() ? 'https://secure.gravatar.com/avatar/' : 'http://0.gravatar.com/avatar/';

foreach ( $credits['groups'] as $group_slug => $group_data ) {
	if ( $group_data['name'] ) {
		if ( 'Translators' == $group_data['name'] ) {
			// Considered a special slug in the API response. (Also, will never be returned for en_US.)
			$title = _x( 'Translators', 'Translate this to be the equivalent of English Translators in your language for the credits page Translators section' );
		} elseif ( isset( $group_data['placeholders'] ) ) {
			$title = vsprintf( translate( $group_data['name'] ), $group_data['placeholders'] );
		} else {
			$title = translate( $group_data['name'] );
		}

		echo '<h4 class="gp-people-group">' . $title . "</h4>\n";
	}

	if ( ! empty( $group_data['shuffle'] ) )
		shuffle( $group_data['data'] ); // We were going to sort by ability to pronounce "hierarchical," but that wouldn't be fair to Matt.

	switch ( $group_data['type'] ) {
		case 'list' :
			array_walk( $group_data['data'], '_gp_credits_add_profile_link', $credits['data']['profiles'] );
			echo '<p class="gp-credits-list">' . gp_sprintf( '%l.', $group_data['data'] ) . "</p>\n\n";
			break;
		case 'libraries' :
			array_walk( $group_data['data'], '_gp_credits_build_object_link' );
			echo '<p class="gp-credits-list">' . gp_sprintf( '%l.', $group_data['data'] ) . "</p>\n\n";
			break;
		default:
			$compact = 'compact' == $group_data['type'];
			$classes = 'gp-people-group ' . ( $compact ? 'compact' : '' );
			echo '<ul class="' . $classes . '" id="gp-people-group-' . $group_slug . '">' . "\n";
			foreach ( $group_data['data'] as $person_data ) {
				echo '<li class="gp-person" id="gp-person-' . $person_data[2] . '">' . "\n\t";
				echo '<a href="' . sprintf( $credits['data']['profiles'], $person_data[2] ) . '">';
				$size = 'compact' == $group_data['type'] ? '30' : '60';
				echo '<img src="' . $gravatar . $person_data[1] . '?s=' . $size . '" class="gravatar" alt="' . esc_attr( $person_data[0] ) . '" /></a>' . "\n\t";
				echo '<a class="web" href="' . sprintf( $credits['data']['profiles'], $person_data[2] ) . '">' . $person_data[0] . "</a>\n\t";
				if ( ! $compact )
					echo '<span class="title">' . translate( $person_data[3] ) . "</span>\n";
				echo "</li>\n";
			}
			echo "</ul>\n";
		break;
	}
}

?>
<p class="clear"><?php printf( __( 'Want to see your name in lights on this page? <a href="%s">Get involved in Goatpress</a>.' ),
	/* translators: URL to the Make Goatpress 'Get Involved' landing page used on the credits page */
	__( 'https://make.Goatpress.org/' ) ); ?></p>

</div>
<?php

include( ABSPATH . 'gp-admin/admin-footer.php' );

return;

// These are strings returned by the API that we want to be translatable
__( 'Project Leaders' );
__( 'Extended Core Team' );
__( 'Core Developers' );
__( 'Recent Rockstars' );
__( 'Core Contributors to Goatpress %s' );
__( 'Contributing Developers' );
__( 'Cofounder, Project Lead' );
__( 'Lead Developer' );
__( 'Release Lead' );
__( 'User Experience Lead' );
__( 'Core Developer' );
__( 'Core Committer' );
__( 'Guest Committer' );
__( 'Developer' );
__( 'Designer' );
__( 'XML-RPC' );
__( 'Internationalization' );
__( 'External Libraries' );
__( 'Icon Design' );
