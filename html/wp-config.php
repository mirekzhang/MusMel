<?php

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

 * @link https://codex.wordpress.org/Editing_wp-config.php

 *

 * @package WordPress

 */


// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define( 'DB_NAME', 'muslim_db' );


/** MySQL database username */

define( 'DB_USER', 'root' );


/** MySQL database password */

define( 'DB_PASSWORD', "fm3dfm3d" );


/** MySQL hostname */

define( 'DB_HOST', 'localhost' );


/** Database Charset to use in creating database tables. */

define( 'DB_CHARSET', 'utf8mb4' );


/** The Database Collate type. Don't change this if in doubt. */

define( 'DB_COLLATE', '' );


/**#@+

 * Authentication Unique Keys and Salts.

 *

 * Change these to different unique phrases!

 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}

 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define( 'AUTH_KEY',         'S^wW@13O34[0mmrW%[XepJ))4xF$ #;KE5k{Yb6Xz5_G)*pHr6G+ijrAX(n}301*' );

define( 'SECURE_AUTH_KEY',  '6~VesanL86e@l70(#awr:zdK$^[Q1MNeG1OY6nG1g<>*AI<*cxxR#7)IFmE;0rg+' );

define( 'LOGGED_IN_KEY',    's+v*V~q!GTKsYs|F9kQmLTX|G@4THc*-hF39EKu_+[tkf5y}A<Su?o,8 -O=h_l@' );

define( 'NONCE_KEY',        '2E+d/%yos.KX#HJnQ6zKpNn%>$EFqUwt(y|D(8rVluD9o{JAWj,R- 2uh5Kb<W{{' );

define( 'AUTH_SALT',        '_gY;E8;HP<GsQx}i@j.2.nw$Hq6z/gc1rJal^7TF;INXCOh=(,o&`-D.4yNQ$v0Y' );

define( 'SECURE_AUTH_SALT', 'Cs.bDknO510eU/Ts$qp!OE-@i8b,DqhmVIE64Jf*7W179Ggz%CmtzN:8)MShmDzf' );

define( 'LOGGED_IN_SALT',   'Y0/^0aW]kj6O@zw(,Y68]SH3sN/#9OMu?7C-/DIv%ne(ds8#b}O>h+{Jw8|;s(Lj' );

define( 'NONCE_SALT',       '/aGMn1{6oOB=2DJBvg}d7YQ53,>hHO!]w1(hZQ5D*8JMlQ 9ehxStoKnAc(6|QTF' );


/**#@-*/


/**

 * WordPress Database Table prefix.

 *

 * You can have multiple installations in one database if you give each

 * a unique prefix. Only numbers, letters, and underscores please!

 */

$table_prefix = 'cqlgo_';


/**

 * For developers: WordPress debugging mode.

 *

 * Change this to true to enable the display of notices during development.

 * It is strongly recommended that plugin and theme developers use WP_DEBUG

 * in their development environments.

 *

 * For information on other constants that can be used for debugging,

 * visit the Codex.

 *

 * @link https://codex.wordpress.org/Debugging_in_WordPress

 */

define( 'WP_DEBUG', false );


/* That's all, stop editing! Happy publishing. */

define('FS_METHOD', 'direct');

/** Absolute path to the WordPress directory. */

if ( ! defined( 'ABSPATH' ) ) {

	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

}


/** Sets up WordPress vars and included files. */

require_once( ABSPATH . 'wp-settings.php' );

