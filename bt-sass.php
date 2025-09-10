<?php

/**
 * Plugin Name: BT TMP Sass
 * Description: BT TMP Sass compiler for Direktt chat
 * Version: 1.0.0
 * Author: BoldThemes
 * Author URI: http://codecanyon.net/user/boldthemes
 */

// https://sass-lang.com/documentation/cli/dart-sass
// admin show sass version

add_action( 'init', 'bt_direktt_sass' );

function bt_direktt_sass() {
	
	if ( is_user_logged_in() ) {
		
		$template_dir = plugin_dir_path( __FILE__ );
		
		$style_scss = $template_dir . 'public/scss/direktt-profile.scss';
		$style_css = $template_dir . 'public/css/direktt-profile.css';
		
		$scss_mtime_now = filemtime( $style_scss );
		$scss_mtime_compile = get_site_option( 'bt_sass_scss_direktt_mtime_compile' );
				
		if ( md5( $scss_mtime_now ) != $scss_mtime_compile ) {

			$output = null;
			$retval = null;

			exec( "/sbin/sass --no-charset $style_scss:$style_css", $output, $retval );
		
			var_dump( md5( $scss_mtime_now ) );
			var_dump( $scss_mtime_compile );
			var_dump( '----- css generisan -----' );

			if ( $retval !== 0 ) {
				$css = file_get_contents( $style_css );
				echo '<pre>' . htmlspecialchars( $css ) . '</pre>';
				die();
			}
			
			update_site_option( 'bt_sass_scss_direktt_mtime_compile', md5( $scss_mtime_now ) );
		
		}

	}
	
}