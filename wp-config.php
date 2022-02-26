<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'zoomplus' );

/** Database username */
define( 'DB_USER', 'veer' );

/** Database password */
define( 'DB_PASSWORD', 'xFile@76s' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '88sp5kyo3jczrpingywvijdwur0hzkw7umqlaefcdusdx3v0iusd8xb1pati3jwr' );
define( 'SECURE_AUTH_KEY',  '0of9hna72rjmmpjnzxwug8nzaslzuvtwmoeostgy4qpwi5me7ccaix6po9kodqhp' );
define( 'LOGGED_IN_KEY',    'dngcnbt344ehkxheahewdtsqymgsp864tyyxzrganbjwz3gtdwnr9okzn1wgpgwq' );
define( 'NONCE_KEY',        'myoacigohzzdrq0znbqpegm5uve5puunu0ah4m5w62ay1ljwt06ym0vpem90unjd' );
define( 'AUTH_SALT',        'zsho2dupa9deaepjmpjfoyphjcsx1gv9tbg6561rpavwck5ao6hfuvv6y54vp8fn' );
define( 'SECURE_AUTH_SALT', 'bai7rnxhjsjrpf9cnvjoq7k8uxmbkhzycp8ofmknt2wh9eo2ofldz5hgh275fma7' );
define( 'LOGGED_IN_SALT',   '6aujzy9cfayrawvoqdiahnixkjczusrwbqvxgfptvndixj5p9iyf7uk6yxywyayj' );
define( 'NONCE_SALT',       'c9t4sikvvl3xjfmdwntw4wh3ppalc3igdvooytvtt0nbsnwd73q0j5nkttcllebg' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpup_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_MEMORY_LIMIT', '256M' );


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
