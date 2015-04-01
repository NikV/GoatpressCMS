<?php
/**
 * Install plugin administration panel.
 *
 * @package Goatpress
 * @subpackage Administration
 */
// TODO route this pages via a specific iframe handler instead of the do_action below
if ( !defined( 'IFRAME_REQUEST' ) && isset( $_GET['tab'] ) && ( 'plugin-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

/**
 * Goatpress Administration Bootstrap.
 */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('install_plugins') )
	gp_die(__('You do not have sufficient permissions to install plugins on this site.'));

if ( is_multisite() && ! is_network_admin() ) {
	gp_redirect( network_admin_url( 'plugin-install.php' ) );
	exit();
}

$gp_list_table = _get_list_table('gp_Plugin_Install_List_Table');
$pagenum = $gp_list_table->get_pagenum();

if ( ! empty( $_REQUEST['_gp_http_referer'] ) ) {
	$location = remove_query_arg( '_gp_http_referer', gp_unslash( $_SERVER['REQUEST_URI'] ) );

	if ( ! empty( $_REQUEST['paged'] ) ) {
		$location = add_query_arg( 'paged', (int) $_REQUEST['paged'], $location );
	}

	gp_redirect( $location );
	exit;
}

$gp_list_table->prepare_items();

$total_pages = $gp_list_table->get_pagination_arg( 'total_pages' );

if ( $pagenum > $total_pages && $total_pages > 0 ) {
	gp_redirect( add_query_arg( 'paged', $total_pages ) );
	exit;
}

$title = __( 'Add Plugins' );
$parent_file = 'plugins.php';

gp_enqueue_script( 'plugin-install' );
if ( 'plugin-information' != $tab )
	add_thickbox();

$body_id = $tab;

gp_enqueue_script( 'updates' );

/**
 * Fires before each tab on the Install Plugins screen is loaded.
 *
 * The dynamic portion of the action hook, `$tab`, allows for targeting
 * individual tabs, for instance 'install_plugins_pre_plugin-information'.
 *
 * @since 2.7.0
 */
do_action( "install_plugins_pre_$tab" );

get_current_screen()->add_help_tab( array(
'id'		=> 'overview',
'title'		=> __('Overview'),
'content'	=>
	'<p>' . sprintf(__('Plugins hook into Goatpress to extend its functionality with custom features. Plugins are developed independently from the core Goatpress application by thousands of developers all over the world. All plugins in the official <a href="%s" target="_blank">Goatpress.org Plugin Directory</a> are compatible with the license Goatpress uses. You can find new plugins to install by searching or browsing the Directory right here in your own Plugins section.'), 'https://Goatpress.org/plugins/') . '</p>'
) );
get_current_screen()->add_help_tab( array(
'id'		=> 'adding-plugins',
'title'		=> __('Adding Plugins'),
'content'	=>
	'<p>' . __('If you know what you&#8217;re looking for, Search is your best bet. The Search screen has options to search the Goatpress.org Plugin Directory for a particular Term, Author, or Tag. You can also search the directory by selecting popular tags. Tags in larger type mean more plugins have been labeled with that tag.') . '</p>' .
	'<p>' . __('If you just want to get an idea of what&#8217;s available, you can browse Featured and Popular plugins by using the links in the upper left of the screen. These sections rotate regularly.') . '</p>' .
	'<p>' . __('You can also browse a user&#8217;s favorite plugins, by using the Favorites link in the upper left of the screen and entering their Goatpress.org username.') . '</p>' .
	'<p>' . __('If you want to install a plugin that you&#8217;ve downloaded elsewhere, click the Upload link in the upper left. You will be prompted to upload the .zip package, and once uploaded, you can activate the new plugin.') . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.Goatpress.org/Plugins_Add_New_Screen" target="_blank">Documentation on Installing Plugins</a>') . '</p>' .
	'<p>' . __('<a href="https://Goatpress.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

/**
 * Goatpress Administration Template Header.
 */
include(ABSPATH . 'gp-admin/admin-header.php');
?>
<div class="wrap">
<h2>
	<?php
	echo esc_html( $title );
	if ( ! empty( $tabs['upload'] ) && current_user_can( 'upload_plugins' ) ) {
		if ( $tab === 'upload' ) {
			$href = self_admin_url( 'plugin-install.php' );
			$text = _x( 'Browse', 'plugins' );
		} else {
			$href = self_admin_url( 'plugin-install.php?tab=upload' );
			$text = __( 'Upload Plugin' );
		}
		echo ' <a href="' . $href . '" class="upload add-new-h2">' . $text . '</a>';
	}
	?>
</h2>

<?php
if ( $tab !== 'upload' ) {
	$gp_list_table->views();
	echo '<br class="clear" />';
}

/**
 * Fires after the plugins list table in each tab of the Install Plugins screen.
 *
 * The dynamic portion of the action hook, `$tab`, allows for targeting
 * individual tabs, for instance 'install_plugins_plugin-information'.
 *
 * @since 2.7.0
 *
 * @param int $paged The current page number of the plugins list table.
 */
do_action( "install_plugins_$tab", $paged ); ?>
</div>

<?php 
gp_print_request_filesystem_credentials_modal();

/**
 * Goatpress Administration Template Footer.
 */
include(ABSPATH . 'gp-admin/admin-footer.php');
