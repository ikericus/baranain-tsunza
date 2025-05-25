<?php
define( 'WP_CACHE', true );
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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u329673490_Xns29' );

/** Database username */
define( 'DB_USER', 'u329673490_RMEol' );

/** Database password */
define( 'DB_PASSWORD', '0hFQGt5a11' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'Hp?<Z@Lsx^SP]jf8z!a!F9iv}(r5yY<?>s?]bWMEjghc~g#^/VJ:cpo<7jtkJO#0' );
define( 'SECURE_AUTH_KEY',   'SJrC>26fvP[mhaR(ALc6h7+emt7Tbx0JrI<Q+4/s, x4v5*$^eEl?FGQbJPF. O/' );
define( 'LOGGED_IN_KEY',     'ZhQ8{mq2N,jux=:H:>;Y-#Rg4O>g~`YsqF)ZcTT*Sq|6R  UU7S|V]8Ja-?ei[W6' );
define( 'NONCE_KEY',         'Y0sECJ?EACt(=kbCfH 3/_2bMte;_4`u2YyR`W|FHyS+r;RC5RL~w?{).9}(9`+^' );
define( 'AUTH_SALT',         'Q:6A[$h:#c 4N3/1-bK%UD-Fp~7<|vAB=3Pq-i)nhE(o(HDe G@,kx+niAtbRoP+' );
define( 'SECURE_AUTH_SALT',  '0Pn<:I#uD.L=1m!fYCOX*{AyLf/]4R7`;N=P!_yX0qP{dDv&daHkuzGX4i>CP8i-' );
define( 'LOGGED_IN_SALT',    'GwSAtZhQc;6TW{@?b^cg8+g0x:~/)SM@cjexq]nEU}JKJd?ozy1&igF}hV/FfT>R' );
define( 'NONCE_SALT',        'W8i.~I*!2v8RjJaEI[&p~YL(aw>vtF)wVlM5&T9rSly:qelN$-/Gr88 !K1`@@I5' );
define( 'WP_CACHE_KEY_SALT', 'C:G^7d<}YjUvCsr4@ZcpqRRsDl76A)DlWyAXg}x{bYR#!lg%!3n8-*i.q4y%2TkD' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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


/* Add any custom values between this line and the "stop editing" line. */



define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
