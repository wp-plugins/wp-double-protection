<?php
/**
 * Plugin Name: WP Double Protection
 * Plugin URI: http://www.sourcexpress.com/wp-double-protection/
 * Description: This plugin allows a second password option and thus making your website doubly protected.
 * Version: 1.2
 * Author: Maruti Mohanty
 * Author URI: http://www.sourcexpress.com/
*/

if ( defined( ABSPATH ) )
	die( 'You do not want to here' );

define( 'WPDP_FILE', __FILE__ );
define( 'WPDP_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPDP_URI', plugins_url( '', __FILE__ ) );

require WPDP_PATH . 'inc/wpdp.php';

new WP_double_protection();