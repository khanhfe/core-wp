<?php

// BEGIN iThemes Security - Do not modify or remove this line
// iThemes Security Config Details: 2
define('DISALLOW_FILE_EDIT', true); // Disable File Editor - Security > Settings > WordPress Tweaks > File Editor
// END iThemes Security - Do not modify or remove this line

/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

// define('DISALLOW_FILE_MODS', true);
// define('ALLOW_UNFILTERED_UPLOADS', true);
// define('DISABLE_WP_CRON', true);
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'core_wp');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'xU&1+)=dls[^/LZc$V=VhDo4:@xdJoE^,4^1!Hhp<io/1X+khe{}XJs*HK>rACaZ');
define('SECURE_AUTH_KEY',  'q/PI9} >C3*E{|S+WsvCYpL01:L&E!di{n1lQ,W_1#PgP-AdpM|okJU0R!k.)Y$N');
define('LOGGED_IN_KEY',    'X^1>.%kY&BsLc,0D|S ,7SuYE7>,jxBc&v+7##G<z]0Pf-uP^(=c^Q`oqD1@dKZ{');
define('NONCE_KEY',        'Ym=/d?|g^=g@P5oO5ds0_`JjtEqY_~6DZy-:xNb;P5K~OG9xQ:L/l0SmL}k56MR)');
define('AUTH_SALT',        'NWVJw:(h]~o6Es ~<9]|,?[<6!3D mS7ac!5Y]K)>lQmZkZlCa? 4?[]ta7jnt<j');
define('SECURE_AUTH_SALT', '!+ts`pL-CtabS;^Hm4L@pT=buO^XyY(RHMIb-up`ZJL0^$(#ZYRo1U$$Y_<71v@K');
define('LOGGED_IN_SALT',   'RKjt@4N}auW4{LrY9%0uUi90LgbLyACVrswLfbxqeZl}2i:|}l;fb}2zoIxC{]S@');
define('NONCE_SALT',       'D(m`R{ZdNg><hyA(s6Hv0b1((q/b#!yzBb!m;g=Li;Tkg9<#E|w8gwjJY_dIFU(L');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'core_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
