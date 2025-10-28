<?php

defined('ABSPATH') || exit;

class Direktt {

	static $settings_array = array();
	static $profile_tools_array = array();
	static $profile_bar_array = array();

	protected Direktt_Loader $loader;
	protected Direktt_Api $direktt_api;

	protected string $plugin_name;

	protected string $version;

	public function __construct() {

		$this->plugin_name = 'direktt';
		$this->version     = '1.0.0';

		$this->load_dependencies();
		$this->define_public_hooks();
		$this->define_admin_hooks();

		$this->define_api_hooks();
		$this->define_profile_hooks();

		$this->define_event_hooks();
		$this->define_user_hooks();
		$this->define_ajax_hooks();
		$this->define_message_hooks();
		$this->define_automation_hooks();
	}

	private function load_dependencies() {
		
		/**
		 * Load dependencies managed by composer.
		 */

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/autoload.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/action-scheduler/action-scheduler.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/html2text/Html2Text.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-direktt-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-direktt-wrapper.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-direktt-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-direktt-api.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-direktt-profile.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/profile-bar/class-direktt-taxonomies-tool.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/profile-bar/class-direktt-messaging-tool.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/profile-bar/class-direktt-notes-tool.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/tools-services/class-direktt-taxonomies-service.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/tools-services/class-direktt-bulk-messaging-service.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-event.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-user.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-ajax.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-message.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-message-template.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-direktt-automation.php';
		

		$this->loader = new Direktt_Loader();
		$this->direktt_api = new Direktt_Api( $this->get_plugin_name(), $this->get_version() );

		// skip action_scheduler

		add_filter ('wp_plugin_check_ignore_directories', function ($directories) {
    		$directories[] = 'action-scheduler';
    		return $directories;
		});
	}

	private function define_public_hooks() {
		$plugin_public = new Direktt_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'direktt_check_token' );
		$this->loader->add_action( 'init', $plugin_public, 'direktt_check_user_ajax' );
		$this->loader->add_action( 'wp', $plugin_public, 'direktt_check_user' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'direktt_enqueue_public_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'direktt_register_pairing_code_shortcode' );
		$this->loader->add_action( 'init', $plugin_public, 'direktt_register_qr_pairing_code_shortcode' );
		
		$this->loader->add_action( 'direktt/action/pair_code', $plugin_public, 'direktt_pair_code_action' );
		$this->loader->add_action( 'direktt_enqueue_public_scripts', $plugin_public, 'direktt_register_pairing_code_scripts' );

		$this->loader->add_filter( 'body_class', $plugin_public, 'direktt_add_body_class' );
	}

	private function define_api_hooks() {

		//$plugin_api = new Direktt_Api( $this->get_plugin_name(), $this->get_version() );
		//$this->loader->add_action( 'rest_api_init', $plugin_api, 'api_register_routes' );
		
		$this->loader->add_action( 'rest_api_init', $this->direktt_api, 'api_register_routes' );
	}

	private function define_profile_hooks() {

		$plugin_profile = new Direktt_Profile( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'init', $plugin_profile, 'profile_shortcode' );
		$this->loader->add_action( 'init', $plugin_profile, 'setup_profile_tools');
		$this->loader->add_action( 'init', $plugin_profile, 'setup_profile_bar');
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_profile, 'enqueue_profile_scripts' );

		$messaging_tool = new Direktt_Messaging_Tool( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'direktt_setup_profile_bar', $messaging_tool, 'setup_profile_tools_messaging' );

		$taxonomies_tool = new Direktt_Taxonomies_Tool( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'direktt_setup_profile_bar', $taxonomies_tool, 'setup_profile_tools_taxonomies' );

		$notes_tool = new Direktt_Notes_Tool( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'direktt_setup_profile_bar', $notes_tool, 'setup_profile_tools_notes' );
		$this->loader->add_action( 'wp_ajax_direktt_quill_upload_image', $notes_tool, 'direktt_quill_upload_image_handler' );

		$taxonomies_service = new Direktt_Taxonomies_Service( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'direktt_enqueue_public_scripts', $taxonomies_service, 'direktt_register_taxonomies_service_scripts' );
		$this->loader->add_action( 'init', $taxonomies_service, 'direktt_taxonomies_service_add_shortcode' );

		$bulk_messaging_service = new Direktt_Bulk_Messaging_Service( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'direktt_enqueue_public_scripts', $bulk_messaging_service, 'direktt_register_bulk_messaging_service_scripts' );
		$this->loader->add_action( 'init', $bulk_messaging_service, 'direktt_bulk_messaging_service_add_shortcode' );
	}
	
	private function define_admin_hooks() {
		
		$plugin_admin = new Direktt_Admin( $this->get_plugin_name(), $this->get_version() );
		
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu_page', 9 );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'setup_admin_menu', 10);
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menu_page_end', 20);
		
		$this->loader->add_action( 'direktt_setup_admin_menu', $plugin_admin, 'register_cpt_menus', 9);

		$this->loader->add_action( 'parent_file', $plugin_admin, 'highlight_direktt_submenu');
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_plugin_assets' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_custom_post_types', 5 );

		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'direkttmtemplates_add_custom_box' );

		$this->loader->add_action( 'add_meta_boxes_page', $plugin_admin, 'page_direktt_custom_box' );
		
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_direktt_custom_box', 10, 2 );
		$this->loader->add_action( 'save_post', $plugin_admin, 'direkttmtemplates_save_meta_box_data');
		
		$this->loader->add_action( 'edit_form_after_editor', $plugin_admin, 'render_meta_panel' );

		// Pairing related

		$this->loader->add_action( 'direktt/event/chat/message_sent', $plugin_admin, 'pair_wp_user_by_code' );

		// User Test meta related
		
		$this->loader->add_action( 'show_user_profile', $plugin_admin, 'render_user_meta_panel' );
		$this->loader->add_action( 'edit_user_profile', $plugin_admin, 'render_user_meta_panel' );

		$this->loader->add_action( 'personal_options_update', $plugin_admin, 'save_user_meta_panel' );
		$this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'save_user_meta_panel' );

		$this->loader->add_action( 'pre_wp_mail', $plugin_admin, 'pre_wp_mail_handler', 10, 2 );

	}

	private function define_event_hooks() {

		$plugin_event = new Direktt_Event( $this->get_plugin_name(), $this->get_version() );
		
		register_activation_hook( WP_PLUGIN_DIR . '/direktt/direktt.php' , array('Direktt_Event', 'create_database_table') );
	}

	private function define_user_hooks() {

		$plugin_user = new Direktt_User( $this->get_plugin_name(), $this->get_version() );

	}

	private function define_message_hooks() {

		$plugin_message = new Direktt_Message( $this->get_plugin_name(), $this->get_version() );

		//Filters for default template tags:
		
			$this->loader->add_filter( 'direktt/message/template/direktt_display_name', $plugin_message, 'direktt_display_name_filter', 10, 2 );
			$this->loader->add_filter( 'direktt/message/template/direktt_channel_name', $plugin_message, 'direktt_channel_name_filter', 10, 2 );
	}

	private function define_automation_hooks() {

		$plugin_automation_worker = new Direktt_Automation_Worker( );
		$plugin_automation_recurring_worker = new Direktt_Automation_RecurringWorker( );

		register_activation_hook( WP_PLUGIN_DIR . '/direktt/direktt.php' , array('Direktt_Automation_DB', 'install') );
		
		$this->loader->add_filter( 'direktt_automation_process_queue_item', $plugin_automation_worker, 'process_queue_item', 10, 1 );
		$this->loader->add_filter( 'direktt_automation_fallback_process_queue_item', $plugin_automation_worker, 'process_queue_item', 10, 1 );

		$this->loader->add_filter( 'direktt_automation_process_recurrence', $plugin_automation_recurring_worker, 'process_recurrence', 10, 1 );
		$this->loader->add_filter( 'direktt_automation_fallback_process_recurrence', $plugin_automation_recurring_worker, 'process_recurrence', 10, 1 );

		$this->loader->add_filter( 'direktt_automation_cancel_recurrence', $plugin_automation_recurring_worker, 'cancel_recurrence_async', 10, 1 );
		$this->loader->add_filter( 'direktt_automation_fallback_cancel_recurrence', $plugin_automation_recurring_worker, 'cancel_recurrence_async', 10, 1 );

		

	}

	private function define_ajax_hooks() {

		$plugin_ajax = new Direktt_Ajax( $this->get_plugin_name(), $this->get_version(), $this->direktt_api );

		$this->loader->add_action( 'wp_ajax_direktt_get_settings', $plugin_ajax, 'ajax_get_settings' );
		$this->loader->add_action( 'wp_ajax_direktt_get_dashboard', $plugin_ajax, 'ajax_get_dashboard' );
		$this->loader->add_action( 'wp_ajax_direktt_get_activation_data', $plugin_ajax, 'ajax_get_activation_data' );
		$this->loader->add_action( 'wp_ajax_direktt_save_settings', $plugin_ajax, 'ajax_save_settings' );
		$this->loader->add_action( 'wp_ajax_direktt_sync_users', $plugin_ajax, 'ajax_sync_users' );
		$this->loader->add_action( 'wp_ajax_direktt_get_marketing_consent', $plugin_ajax, 'ajax_get_marketing_consent' );
		$this->loader->add_action( 'wp_ajax_direktt_get_user_events', $plugin_ajax, 'ajax_get_user_events' );

		// direkttmtemplates
		$this->loader->add_action( 'wp_ajax_direktt_get_mtemplates_taxonomies', $plugin_ajax, 'ajax_get_mtemplates_taxonomies' );
		$this->loader->add_action( 'wp_ajax_direktt_send_mtemplates_message', $plugin_ajax, 'ajax_send_mtemplates_message' );

		// messaging on profile
		$this->loader->add_action( 'wp_ajax_direktt_get_mtemplates_profile_message', $plugin_ajax, 'ajax_get_mtemplates_profile_message' );
		$this->loader->add_action( 'wp_ajax_direktt_get_users_taxonomy_service', $plugin_ajax, 'ajax_get_users_taxonomy_service' );
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

	static function add_settings_page( $params ) {
		Direktt::$settings_array[] = $params;
	}
}
