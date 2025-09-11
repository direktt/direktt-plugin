<?php

/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://bold-themes.com
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Direktt WordPress plugin
 * Plugin URI:        https://bold-themes.com
 * Description:       Implements the WordPress functionality of the Direktt mobile platform
 * Version:           1.0.0
 * Author:            BoldThemes
 * Author URI:        https://bold-themes.com
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
 * TMP: scss to css compiler
 */
 
$bt_sass_file = plugin_dir_path( __FILE__ ) . 'bt-sass.php';

if ( file_exists( $bt_sass_file ) ) {
    require $bt_sass_file;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-direktt.php';


/**
 * This runs during plugin deactivation.
 */
function deactivate_direktt() {
	// Jwt_Auth_Cron::remove();
}

/**
 * Hook into the action that'll fire during plugin deactivation
 */
// register_deactivation_hook( __FILE__, 'deactivate_jwt_auth' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_direktt() {
	$plugin = new Direktt();
	$plugin->run();
}

run_direktt();
