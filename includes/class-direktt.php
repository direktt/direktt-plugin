<?php

class Direktt {

	protected Direktt_Loader $loader;

	protected string $plugin_name;

	protected string $version;

	public function __construct() {

		$this->plugin_name = 'direktt';
		$this->version     = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_public_hooks();
		$this->define_admin_hooks();

		$this->define_api_hooks();

		$this->define_event_hooks();
		$this->define_user_hooks();
		$this->define_ajax_hooks();
		$this->define_message_hooks();
	}

	private function load_dependencies() {
		/**
		 * Load dependencies managed by composer.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/autoload.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-direktt-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-direktt-i18n.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-direktt-wrapper.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-direktt-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-direktt-api.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-event.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-user.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-ajax.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-message.php';

		$this->loader = new Direktt_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new Direktt_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function define_public_hooks() {
		$plugin_public = new Direktt_Public( $this->get_plugin_name(), $this->get_version() );
		// $this->loader->add_action( 'rest_api_init', $plugin_public, 'add_api_routes' ); api_register_routes 
		// $this->loader->add_filter( 'rest_api_init', $plugin_public, 'add_cors_support' );
		//$this->loader->add_filter( 'rest_pre_dispatch', $plugin_public, 'rest_pre_dispatch', 10, 2 );
		// $this->loader->add_filter( 'determine_current_user', $plugin_public, 'determine_current_user' );

		//$this->loader->add_action( 'template_redirect', $plugin_public, 'direktt_check_user' );
		$this->loader->add_action( 'init', $plugin_public, 'direktt_check_token' );
		$this->loader->add_action( 'wp', $plugin_public, 'direktt_check_user' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'direktt_enqueue_public_scripts' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_plugin_assets' );
	}

	private function define_api_hooks() {

		$plugin_api = new Direktt_Api( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'rest_api_init', $plugin_api, 'api_register_routes' );
	}
	
	private function define_admin_hooks() {
		$plugin_admin = new Direktt_Admin( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu_page', 9 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu_page_end');
		$this->loader->add_action( 'parent_file', $plugin_admin, 'highlight_direktt_submenu');
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_plugin_assets' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_custom_post_types', 5 );
		//$this->loader->add_action( 'admin_notices', $plugin_admin, 'display_admin_notice' );

		$this->loader->add_action( 'add_meta_boxes_page', $plugin_admin, 'page_direktt_custom_box' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_direktt_custom_box', 10, 2 );
		
		$this->loader->add_action( 'edit_form_after_editor', $plugin_admin, 'render_meta_panel' );

		// User Test meta related
		
		$this->loader->add_action( 'show_user_profile', $plugin_admin, 'render_user_meta_panel' );
		$this->loader->add_action( 'edit_user_profile', $plugin_admin, 'render_user_meta_panel' );
		//$this->loader->add_action( 'user_new_form', $plugin_admin, 'render_user_meta_panel' );

		$this->loader->add_action( 'personal_options_update', $plugin_admin, 'save_user_meta_panel' );
		$this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'save_user_meta_panel' );
		//$this->loader->add_action( 'user_register', $plugin_admin, 'save_user_meta_panel' );

	}

	private function define_event_hooks() {

		$plugin_event = new Direktt_Event( $this->get_plugin_name(), $this->get_version() );
		register_activation_hook( WP_PLUGIN_DIR . '/direktt-plugin/direktt.php' , array('Direktt_Event', 'create_database_table') );
	}

	private function define_user_hooks() {

		$plugin_user = new Direktt_User( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'direktt/event/chat/message_sent', $plugin_user, 'pair_wp_user_by_code' );
	}

	private function define_message_hooks() {

		$plugin_message = new Direktt_Message( $this->get_plugin_name(), $this->get_version() );
	}

	private function define_ajax_hooks() {

		$plugin_ajax = new Direktt_Ajax( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_ajax_direktt_get_settings', $plugin_ajax, 'ajax_get_settings' );
		$this->loader->add_action( 'wp_ajax_direktt_get_dashboard', $plugin_ajax, 'ajax_get_dashboard' );
		$this->loader->add_action( 'wp_ajax_direktt_save_settings', $plugin_ajax, 'ajax_save_settings' );
		$this->loader->add_action( 'wp_ajax_direktt_get_marketing_consent', $plugin_ajax, 'ajax_get_marketing_consent' );
		$this->loader->add_action( 'wp_ajax_direktt_get_user_events', $plugin_ajax, 'ajax_get_user_events' );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	public function get_loader(): Direktt_Loader {
		return $this->loader;
	}

	public function get_version(): string {
		return $this->version;
	}
}
