<?php
/**
 * Helper functions for displaying a list of items in an ajaxified HTML table.
 *
 * @package Goatpress
 * @subpackage List_Table
 * @since 3.1.0
 */

/**
 * Fetch an instance of a gp_List_Table class.
 *
 * @access private
 * @since 3.1.0
 *
 * @param string $class The type of the list table, which is the class name.
 * @param array $args Optional. Arguments to pass to the class. Accepts 'screen'.
 * @return object|bool Object on success, false if the class does not exist.
 */
function _get_list_table( $class, $args = array() ) {
	$core_classes = array(
		//Site Admin
		'gp_Posts_List_Table' => 'posts',
		'gp_Media_List_Table' => 'media',
		'gp_Terms_List_Table' => 'terms',
		'gp_Users_List_Table' => 'users',
		'gp_Comments_List_Table' => 'comments',
		'gp_Post_Comments_List_Table' => 'comments',
		'gp_Links_List_Table' => 'links',
		'gp_Plugin_Install_List_Table' => 'plugin-install',
		'gp_Themes_List_Table' => 'themes',
		'gp_Theme_Install_List_Table' => array( 'themes', 'theme-install' ),
		'gp_Plugins_List_Table' => 'plugins',
		// Network Admin
		'gp_MS_Sites_List_Table' => 'ms-sites',
		'gp_MS_Users_List_Table' => 'ms-users',
		'gp_MS_Themes_List_Table' => 'ms-themes',
	);

	if ( isset( $core_classes[ $class ] ) ) {
		foreach ( (array) $core_classes[ $class ] as $required )
			require_once( ABSPATH . 'gp-admin/includes/class-gp-' . $required . '-list-table.php' );

		if ( isset( $args['screen'] ) )
			$args['screen'] = convert_to_screen( $args['screen'] );
		elseif ( isset( $GLOBALS['hook_suffix'] ) )
			$args['screen'] = get_current_screen();
		else
			$args['screen'] = null;

		return new $class( $args );
	}

	return false;
}

/**
 * Register column headers for a particular screen.
 *
 * @since 2.7.0
 *
 * @param string $screen The handle for the screen to add help to. This is usually the hook name returned by the add_*_page() functions.
 * @param array $columns An array of columns with column IDs as the keys and translated column names as the values
 * @see get_column_headers(), print_column_headers(), get_hidden_columns()
 */
function register_column_headers($screen, $columns) {
	$gp_list_table = new _gp_List_Table_Compat($screen, $columns);
}

/**
 * Prints column headers for a particular screen.
 *
 * @since 2.7.0
 */
function print_column_headers($screen, $id = true) {
	$gp_list_table = new _gp_List_Table_Compat($screen);

	$gp_list_table->print_column_headers($id);
}

/**
 * Helper class to be used only by back compat functions
 *
 * @since 3.1.0
 */
class _gp_List_Table_Compat extends gp_List_Table {
	public $_screen;
	public $_columns;

	public function __construct( $screen, $columns = array() ) {
		if ( is_string( $screen ) )
			$screen = convert_to_screen( $screen );

		$this->_screen = $screen;

		if ( !empty( $columns ) ) {
			$this->_columns = $columns;
			add_filter( 'manage_' . $screen->id . '_columns', array( $this, 'get_columns' ), 0 );
		}
	}

	protected function get_column_info() {
		$columns = get_column_headers( $this->_screen );
		$hidden = get_hidden_columns( $this->_screen );
		$sortable = array();

		return array( $columns, $hidden, $sortable );
	}

	public function get_columns() {
		return $this->_columns;
	}
}
