<?php

class Direktt_Bulk_Messaging_Service {

	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function direktt_bulk_messaging_service_add_shortcode() {
		add_shortcode( 'direktt_bulk_messaging_service', array( $this, 'direktt_bulk_messaging_service_shortcode' ) );
	}
	public function direktt_register_bulk_messaging_service_scripts() {
	}

	public function direktt_bulk_messaging_service_shortcode() {
		if ( ! Direktt_User::is_direktt_admin() ) {
			return;
		}

		ob_start();

		echo 'Bulk Messaging';

		return ob_get_clean();
	}
}
