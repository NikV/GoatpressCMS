<?php
/**
 * Goatpress user administration API.
 *
 * @package Goatpress
 * @subpackage Administration
 */

/**
 * Creates a new user from the "Users" form using $_POST information.
 *
 * @since 2.0.0
 *
 * @return null|gp_Error|int Null when adding user, gp_Error or User ID integer when no parameters.
 */
function add_user() {
	return edit_user();
}

/**
 * Edit user settings based on contents of $_POST
 *
 * Used on user-edit.php and profile.php to manage and process user options, passwords etc.
 *
 * @since 2.0.0
 *
 * @param int $user_id Optional. User ID.
 * @return int user id of the updated user
 */
function edit_user( $user_id = 0 ) {
	global $gp_roles;
	$user = new stdClass;
	if ( $user_id ) {
		$update = true;
		$user->ID = (int) $user_id;
		$userdata = get_userdata( $user_id );
		$user->user_login = gp_slash( $userdata->user_login );
	} else {
		$update = false;
	}

	if ( !$update && isset( $_POST['user_login'] ) )
		$user->user_login = sanitize_user($_POST['user_login'], true);

	$pass1 = $pass2 = '';
	if ( isset( $_POST['pass1'] ) )
		$pass1 = $_POST['pass1'];
	if ( isset( $_POST['pass2'] ) )
		$pass2 = $_POST['pass2'];

	if ( isset( $_POST['role'] ) && current_user_can( 'edit_users' ) ) {
		$new_role = sanitize_text_field( $_POST['role'] );
		$potential_role = isset($gp_roles->role_objects[$new_role]) ? $gp_roles->role_objects[$new_role] : false;
		// Don't let anyone with 'edit_users' (admins) edit their own role to something without it.
		// Multisite super admins can freely edit their blog roles -- they possess all caps.
		if ( ( is_multisite() && current_user_can( 'manage_sites' ) ) || $user_id != get_current_user_id() || ($potential_role && $potential_role->has_cap( 'edit_users' ) ) )
			$user->role = $new_role;

		// If the new role isn't editable by the logged-in user die with error
		$editable_roles = get_editable_roles();
		if ( ! empty( $new_role ) && empty( $editable_roles[$new_role] ) )
			gp_die(__('You can&#8217;t give users that role.'));
	}

	if ( isset( $_POST['email'] ))
		$user->user_email = sanitize_text_field( gp_unslash( $_POST['email'] ) );
	if ( isset( $_POST['url'] ) ) {
		if ( empty ( $_POST['url'] ) || $_POST['url'] == 'http://' ) {
			$user->user_url = '';
		} else {
			$user->user_url = esc_url_raw( $_POST['url'] );
			$protocols = implode( '|', array_map( 'preg_quote', gp_allowed_protocols() ) );
			$user->user_url = preg_match('/^(' . $protocols . '):/is', $user->user_url) ? $user->user_url : 'http://'.$user->user_url;
		}
	}
	if ( isset( $_POST['first_name'] ) )
		$user->first_name = sanitize_text_field( $_POST['first_name'] );
	if ( isset( $_POST['last_name'] ) )
		$user->last_name = sanitize_text_field( $_POST['last_name'] );
	if ( isset( $_POST['nickname'] ) )
		$user->nickname = sanitize_text_field( $_POST['nickname'] );
	if ( isset( $_POST['display_name'] ) )
		$user->display_name = sanitize_text_field( $_POST['display_name'] );

	if ( isset( $_POST['description'] ) )
		$user->description = trim( $_POST['description'] );

	foreach ( gp_get_user_contact_methods( $user ) as $method => $name ) {
		if ( isset( $_POST[$method] ))
			$user->$method = sanitize_text_field( $_POST[$method] );
	}

	if ( $update ) {
		$user->rich_editing = isset( $_POST['rich_editing'] ) && 'false' == $_POST['rich_editing'] ? 'false' : 'true';
		$user->admin_color = isset( $_POST['admin_color'] ) ? sanitize_text_field( $_POST['admin_color'] ) : 'fresh';
		$user->show_admin_bar_front = isset( $_POST['admin_bar_front'] ) ? 'true' : 'false';
	}

	$user->comment_shortcuts = isset( $_POST['comment_shortcuts'] ) && 'true' == $_POST['comment_shortcuts'] ? 'true' : '';

	$user->use_ssl = 0;
	if ( !empty($_POST['use_ssl']) )
		$user->use_ssl = 1;

	$errors = new gp_Error();

	/* checking that username has been typed */
	if ( $user->user_login == '' )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: Please enter a username.' ) );

	/* checking the password has been typed twice */
	/**
	 * Fires before the password and confirm password fields are checked for congruity.
	 *
	 * @since 1.5.1
	 *
	 * @param string $user_login The username.
	 * @param string &$pass1     The password, passed by reference.
	 * @param string &$pass2     The confirmed password, passed by reference.
	 */
	do_action_ref_array( 'check_passwords', array( $user->user_login, &$pass1, &$pass2 ) );

	if ( $update ) {
		if ( empty($pass1) && !empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: You entered your new password only once.' ), array( 'form-field' => 'pass1' ) );
		elseif ( !empty($pass1) && empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: You entered your new password only once.' ), array( 'form-field' => 'pass2' ) );
	} else {
		if ( empty($pass1) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter your password.' ), array( 'form-field' => 'pass1' ) );
		elseif ( empty($pass2) )
			$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter your password twice.' ), array( 'form-field' => 'pass2' ) );
	}

	/* Check for "\" in password */
	if ( false !== strpos( gp_unslash( $pass1 ), "\\" ) )
		$errors->add( 'pass', __( '<strong>ERROR</strong>: Passwords may not contain the character "\\".' ), array( 'form-field' => 'pass1' ) );

	/* checking the password has been typed twice the same */
	if ( $pass1 != $pass2 )
		$errors->add( 'pass', __( '<strong>ERROR</strong>: Please enter the same password in the two password fields.' ), array( 'form-field' => 'pass1' ) );

	if ( !empty( $pass1 ) )
		$user->user_pass = $pass1;

	if ( !$update && isset( $_POST['user_login'] ) && !validate_username( $_POST['user_login'] ) )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ));

	if ( !$update && username_exists( $user->user_login ) )
		$errors->add( 'user_login', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.' ));

	/* checking e-mail address */
	if ( empty( $user->user_email ) ) {
		$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please enter an e-mail address.' ), array( 'form-field' => 'email' ) );
	} elseif ( !is_email( $user->user_email ) ) {
		$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.' ), array( 'form-field' => 'email' ) );
	} elseif ( ( $owner_id = email_exists($user->user_email) ) && ( !$update || ( $owner_id != $user->ID ) ) ) {
		$errors->add( 'email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.'), array( 'form-field' => 'email' ) );
	}

	/**
	 * Fires before user profile update errors are returned.
	 *
	 * @since 2.8.0
	 *
	 * @param array   &$errors An array of user profile update errors, passed by reference.
	 * @param bool    $update  Whether this is a user update.
	 * @param gp_User &$user   gp_User object, passed by reference.
	 */
	do_action_ref_array( 'user_profile_update_errors', array( &$errors, $update, &$user ) );

	if ( $errors->get_error_codes() )
		return $errors;

	if ( $update ) {
		$user_id = gp_update_user( $user );
	} else {
		$user_id = gp_insert_user( $user );
		gp_new_user_notification( $user_id, isset( $_POST['send_password'] ) ? gp_unslash( $pass1 ) : '' );
	}
	return $user_id;
}

/**
 * Fetch a filtered list of user roles that the current user is
 * allowed to edit.
 *
 * Simple function who's main purpose is to allow filtering of the
 * list of roles in the $gp_roles object so that plugins can remove
 * inappropriate ones depending on the situation or user making edits.
 * Specifically because without filtering anyone with the edit_users
 * capability can edit others to be administrators, even if they are
 * only editors or authors. This filter allows admins to delegate
 * user management.
 *
 * @since 2.8.0
 *
 * @return array
 */
function get_editable_roles() {
	global $gp_roles;

	$all_roles = $gp_roles->roles;

	/**
	 * Filter the list of editable roles.
	 *
	 * @since 2.8.0
	 *
	 * @param array $all_roles List of roles.
	 */
	$editable_roles = apply_filters( 'editable_roles', $all_roles );

	return $editable_roles;
}

/**
 * Retrieve user data and filter it.
 *
 * @since 2.0.5
 *
 * @param int $user_id User ID.
 * @return gp_User|bool gp_User object on success, false on failure.
 */
function get_user_to_edit( $user_id ) {
	$user = get_userdata( $user_id );

	if ( $user )
		$user->filter = 'edit';

	return $user;
}

/**
 * Retrieve the user's drafts.
 *
 * @since 2.0.0
 *
 * @param int $user_id User ID.
 * @return array
 */
function get_users_drafts( $user_id ) {
	global $gpdb;
	$query = $gpdb->prepare("SELECT ID, post_title FROM $gpdb->posts WHERE post_type = 'post' AND post_status = 'draft' AND post_author = %d ORDER BY post_modified DESC", $user_id);

	/**
	 * Filter the user's drafts query string.
	 *
	 * @since 2.0.0
	 *
	 * @param string $query The user's drafts query string.
	 */
	$query = apply_filters( 'get_users_drafts', $query );
	return $gpdb->get_results( $query );
}

/**
 * Remove user and optionally reassign posts and links to another user.
 *
 * If the $reassign parameter is not assigned to an User ID, then all posts will
 * be deleted of that user. The action 'delete_user' that is passed the User ID
 * being deleted will be run after the posts are either reassigned or deleted.
 * The user meta will also be deleted that are for that User ID.
 *
 * @since 2.0.0
 *
 * @param int $id User ID.
 * @param int $reassign Optional. Reassign posts and links to new User ID.
 * @return bool True when finished.
 */
function gp_delete_user( $id, $reassign = null ) {
	global $gpdb;

	$id = (int) $id;
	$user = new gp_User( $id );

	if ( !$user->exists() )
		return false;

	// Normalize $reassign to null or a user ID. 'novalue' was an older default.
	if ( 'novalue' === $reassign ) {
		$reassign = null;
	} elseif ( null !== $reassign ) {
		$reassign = (int) $reassign;
	}

	/**
	 * Fires immediately before a user is deleted from the database.
	 *
	 * @since 2.0.0
	 *
	 * @param int      $id       ID of the user to delete.
	 * @param int|null $reassign ID of the user to reassign posts and links to.
	 *                           Default null, for no reassignment.
	 */
	do_action( 'delete_user', $id, $reassign );

	if ( null === $reassign ) {
		$post_types_to_delete = array();
		foreach ( get_post_types( array(), 'objects' ) as $post_type ) {
			if ( $post_type->delete_with_user ) {
				$post_types_to_delete[] = $post_type->name;
			} elseif ( null === $post_type->delete_with_user && post_type_supports( $post_type->name, 'author' ) ) {
				$post_types_to_delete[] = $post_type->name;
			}
		}

		/**
		 * Filter the list of post types to delete with a user.
		 *
		 * @since 3.4.0
		 *
		 * @param array $post_types_to_delete Post types to delete.
		 * @param int   $id                   User ID.
		 */
		$post_types_to_delete = apply_filters( 'post_types_to_delete_with_user', $post_types_to_delete, $id );
		$post_types_to_delete = implode( "', '", $post_types_to_delete );
		$post_ids = $gpdb->get_col( $gpdb->prepare( "SELECT ID FROM $gpdb->posts WHERE post_author = %d AND post_type IN ('$post_types_to_delete')", $id ) );
		if ( $post_ids ) {
			foreach ( $post_ids as $post_id )
				gp_delete_post( $post_id );
		}

		// Clean links
		$link_ids = $gpdb->get_col( $gpdb->prepare("SELECT link_id FROM $gpdb->links WHERE link_owner = %d", $id) );

		if ( $link_ids ) {
			foreach ( $link_ids as $link_id )
				gp_delete_link($link_id);
		}
	} else {
		$post_ids = $gpdb->get_col( $gpdb->prepare( "SELECT ID FROM $gpdb->posts WHERE post_author = %d", $id ) );
		$gpdb->update( $gpdb->posts, array('post_author' => $reassign), array('post_author' => $id) );
		if ( ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id )
				clean_post_cache( $post_id );
		}
		$link_ids = $gpdb->get_col( $gpdb->prepare("SELECT link_id FROM $gpdb->links WHERE link_owner = %d", $id) );
		$gpdb->update( $gpdb->links, array('link_owner' => $reassign), array('link_owner' => $id) );
		if ( ! empty( $link_ids ) ) {
			foreach ( $link_ids as $link_id )
				clean_bookmark_cache( $link_id );
		}
	}

	// FINALLY, delete user
	if ( is_multisite() ) {
		remove_user_from_blog( $id, get_current_blog_id() );
	} else {
		$meta = $gpdb->get_col( $gpdb->prepare( "SELECT umeta_id FROM $gpdb->usermeta WHERE user_id = %d", $id ) );
		foreach ( $meta as $mid )
			delete_metadata_by_mid( 'user', $mid );

		$gpdb->delete( $gpdb->users, array( 'ID' => $id ) );
	}

	clean_user_cache( $user );

	/**
	 * Fires immediately after a user is deleted from the database.
	 *
	 * @since 2.9.0
	 *
	 * @param int      $id       ID of the deleted user.
	 * @param int|null $reassign ID of the user to reassign posts and links to.
	 *                           Default null, for no reassignment.
	 */
	do_action( 'deleted_user', $id, $reassign );

	return true;
}

/**
 * Remove all capabilities from user.
 *
 * @since 2.1.0
 *
 * @param int $id User ID.
 */
function gp_revoke_user($id) {
	$id = (int) $id;

	$user = new gp_User($id);
	$user->remove_all_caps();
}

add_action('admin_init', 'default_password_nag_handler');
/**
 * @since 2.8.0
 */
function default_password_nag_handler($errors = false) {
	global $user_ID;
	// Short-circuit it.
	if ( ! get_user_option('default_password_nag') )
		return;

	// get_user_setting = JS saved UI setting. else no-js-fallback code.
	if ( 'hide' == get_user_setting('default_password_nag') || isset($_GET['default_password_nag']) && '0' == $_GET['default_password_nag'] ) {
		delete_user_setting('default_password_nag');
		update_user_option($user_ID, 'default_password_nag', false, true);
	}
}

add_action('profile_update', 'default_password_nag_edit_user', 10, 2);

/**
 * @since 2.8.0
 */
function default_password_nag_edit_user($user_ID, $old_data) {
	// Short-circuit it.
	if ( ! get_user_option('default_password_nag', $user_ID) )
		return;

	$new_data = get_userdata($user_ID);

	// Remove the nag if the password has been changed.
	if ( $new_data->user_pass != $old_data->user_pass ) {
		delete_user_setting('default_password_nag');
		update_user_option($user_ID, 'default_password_nag', false, true);
	}
}

add_action('admin_notices', 'default_password_nag');

/**
 * @since 2.8.0
 */
function default_password_nag() {
	global $pagenow;
	// Short-circuit it.
	if ( 'profile.php' == $pagenow || ! get_user_option('default_password_nag') )
		return;

	echo '<div class="error default-password-nag">';
	echo '<p>';
	echo '<strong>' . __('Notice:') . '</strong> ';
	_e('You&rsquo;re using the auto-generated password for your account. Would you like to change it to something easier to remember?');
	echo '</p><p>';
	printf( '<a href="%s">' . __('Yes, take me to my profile page') . '</a> | ', get_edit_profile_url() . '#password' );
	printf( '<a href="%s" id="default-password-nag-no">' . __('No thanks, do not remind me again') . '</a>', '?default_password_nag=0' );
	echo '</p></div>';
}
