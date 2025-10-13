<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Direktt
 * Plugin URI:        https://direktt.com
 * Description:       Implements the WordPress functionality of the Direktt mobile platform
 * Version:           1.0.0
 * Author:            Direktt
 * Author URI:        https://direktt.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       direktt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * scss to css compiler
 */
 
$bt_sass_file = plugin_dir_path( __FILE__ ) . 'bt-sass.php';

if ( file_exists( $bt_sass_file ) ) {
    require $bt_sass_file;
}

require plugin_dir_path( __FILE__ ) . 'includes/class-direktt.php';

function run_direktt() {
	$plugin = new Direktt();
	$plugin->run();
}

run_direktt();
