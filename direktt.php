<?php
/**
 * Direktt plugin main file
 *
 * @package     Direktt
 * @author      Direktt
 * @copyright   2025 Hexxu Services ltd
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name:       Direktt
 * Plugin URI:        https://direktt.com
 * Description:       Direktt Plugin implements the functionality of the Direktt mobile platform
 * Version:           1.0
 * Author:            Direktt
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       direktt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// scss to css compiler.
$direktt_sass_file = plugin_dir_path( __FILE__ ) . 'bt-sass.php';

if ( file_exists( $direktt_sass_file ) ) {
	require $direktt_sass_file;
}

// skip scoped vendor libraries
add_filter(
	'wp_plugin_check_ignore_directories',
	function ( $directories ) {
		$directories[] = 'includes/action-scheduler';
		$directories[] = 'includes/dependencies';
		return $directories;
	}
);

// skip .gitignore
add_filter(
	'wp_plugin_check_ignore_files',
	function ( $files ) {
		$files[] = '.gitignore';
		return $files;
	}
);

require plugin_dir_path( __FILE__ ) . 'includes/class-direktt.php';

function direktt_run_direktt() {
	$plugin = new Direktt();
	$plugin->run();
}

direktt_run_direktt();
