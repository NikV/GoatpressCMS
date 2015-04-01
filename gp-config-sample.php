<?php
/**
 * The base configurations of the Goatpress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link http://codex.Goatpress.org/Editing_gp-config.php Editing gp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the gp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "gp-config.php" and fill in the values.
 *
 * @package Goatpress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for Goatpress */
define('DB_NAME', 'database_name_here');

/** MySQL database username */
define('DB_USER', 'username_here');

/** MySQL database password */
define('DB_PASSWORD', 'password_here');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.Goatpress.org/secret-key/1.1/salt/ Goatpress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

/**#@-*/

/**
 * Goatpress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'gp_';

/**
 * For developers: Goatpress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use gp_DEBUG
 * in their development environments.
 */
define('gp_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the Goatpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up Goatpress vars and included files. */
require_once(ABSPATH . 'gp-settings.php');
