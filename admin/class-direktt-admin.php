<?php

/**
 * The admin-facing functionality of the plugin.
 *
 * Defines the plugin name, version
 *
 * @author     Enrique Chavez <noone@tmeister.net>
 * @since      1.3.4
 */
class Direktt_Admin
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.3.4
	 *
	 * @var string The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.4
	 *
	 * @var string The current version of this plugin.
	 */
	private string $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.3.4
	 */
	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register a new settings page under Settings main menu
	 * .
	 * @return void
	 * @since 1.3.4
	 */
	public function register_menu_page()
	{
		add_menu_page(
			__('Direktt', 'direktt'),
			__('Direktt', 'direktt'),
			"manage_options",
			"direktt-dashboard",
			[$this, 'render_admin_page'],
			"",
			30
		);

		add_submenu_page(
			'direktt-dashboard',
			__('Dashboard', 'direktt'),
			__('Dashboard', 'direktt'),
			'manage_options',
			'direktt-dashboard'
		);

		add_submenu_page(
			'direktt-dashboard',
			__('Settings', 'direktt'),
			__('Settings', 'direktt'),
			'manage_options',
			'direktt-settings',
			[$this, 'render_admin_page']
		);
	}

	/**
	 * Shows an admin notice on the admin dashboard to notify the new settings page.
	 * This is only shown once and the message is dismissed.
	 *
	 * @return void
	 * @since 1.3.4
	 */
	public function display_admin_notice()
	{
		if (!get_option('jwt_auth_admin_notice')) {
?>
			<div class="notice notice-info is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %s: Link to the JWT Authentication settings page */
						__(
							'Please visit the <a href="%s">JWT Authentication settings page</a> for an important message from the author.',
							'direktt'
						),
						admin_url('options-general.php?page=jwt_authentication')
					);
					?>
				</p>
			</div>
		<?php
			update_option('jwt_auth_admin_notice', true);
		}
	}

	/**
	 * Enqueue the plugin assets only on the plugin settings page.
	 *
	 * @param string $suffix
	 *
	 * @return void|null
	 * @since 1.3.4
	 */
	public function enqueue_plugin_assets(string $suffix)
	{


		if ($suffix !== 'direktt_page_direktt-settings' && $suffix !== 'toplevel_page_direktt-dashboard') {
			return null;
		}

		if ($suffix == 'direktt_page_direktt-settings') {
			wp_enqueue_script(
				$this->plugin_name . '-settings',
				plugin_dir_url(__DIR__) . 'js/settings/direktt-settings.js',
				array('jquery'),
				'',
				[
					'in_footer' => true,
				]
			);

			// Enqueue the style file
			wp_enqueue_style(
				$this->plugin_name . '-settings',
				plugin_dir_url(__DIR__) . 'js/settings/direktt-settings.css',
				[],
				''
			);

			$nonce = wp_create_nonce($this->plugin_name . '-settings');

			wp_localize_script(
				$this->plugin_name . '-settings',
				$this->plugin_name . '_settings_object',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => $nonce
				)
			);
		}

		if ($suffix == 'toplevel_page_direktt-dashboard') {
			wp_enqueue_script(
				$this->plugin_name . '-dashboard',
				plugin_dir_url(__DIR__) . 'js/dashboard/direktt-dashboard.js',
				[],
				'',
				[
					'in_footer' => true,
				]
			);

			// Enqueue the style file
			wp_enqueue_style(
				$this->plugin_name . '-dashboard',
				plugin_dir_url(__DIR__) . 'css/style.js',
				[],
				''
			);
		}
	}

	/**
	 * Register the plugin settings.
	 *
	 * @return void
	 * @since 1.3.4
	 */
	public function register_plugin_settings()
	{
		register_setting('jwt_auth', 'jwt_auth_options', [
			'type'         => 'object',
			'default'      => [
				'share_data' => false,
			],
			'show_in_rest' => [
				'schema' => [
					'type'       => 'object',
					'properties' => [
						'share_data' => [
							'type'    => 'boolean',
							'default' => false,
						],
					],
				],
			]
		]);
	}

	/**
	 * Render the plugin settings page.
	 * This is a React application that will be rendered on the admin page.
	 *
	 * @return void
	 * @since 1.3.4
	 */
	public function render_admin_page()
	{
		?>
		<div id="app"></div>
		<?php
	}

	public function render_admin_page1()
	{

		$action = (isset($_GET['action'])) ? sanitize_text_field($_GET['action']) : false;

		if (!$action) {

			$api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';

		?>

			<form method="POST" action="<?php echo admin_url('admin.php'); ?>" class="bt-form-pb-selection">
				<input type="hidden" name="action" value="direkttoptions" />
				<?php wp_nonce_field('direkttoptions', 'direktt_nonce'); ?>
				<h1><?php echo esc_html__('Direktt settings', 'direktt'); ?></h1>

				<table class="form-table" role="presentation">

					<tbody>
						<tr>

							<th scope="row"><label for="blogname"><?php echo esc_html__('API Key', 'direktt'); ?></label></th>


							<td>
								<input type="text" name="direkttapikey" id="direkttapikey" size="50" placeholder="<?php echo esc_html__('Paste your API Key here', 'direktt'); ?>" value="<?php echo $api_key ?>">
							</td>

						</tr>
					</tbody>
				</table>
				<p>
					<input type="submit" value="<?php echo __('Save Direktt Settings', 'direktt'); ?>" class="button button-primary button-large" />
				</p>
			</form>

<?php

		}
	}

	public function set_page_builder_option()
	{

		$choice = (isset($_POST['direkttapikey'])) ? sanitize_text_field($_POST['direkttapikey']) : false;

		if (!isset($_POST['direktt_nonce']) || !wp_verify_nonce($_POST['direktt_nonce'], 'direkttoptions') || !current_user_can('manage_options')) {
			exit;
		} else {
			if ($choice) {
				update_option('direktt_api_key',  $choice);
				wp_safe_redirect(admin_url('options-general.php?page=direktt'));
				exit();
			} else {
				delete_option('direktt_api_key');
				wp_safe_redirect(admin_url('options-general.php?page=direktt'));
				exit();
			}
		}
	}

	public function ajax_get_settings()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$data = array(
			'api_key' => get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '',
			'activation_status' => get_option('direktt_activation_status') ? esc_attr(get_option('direktt_activation_status')) : 'false'
			//'activation_status' => 'true'
		);

		wp_send_json_success($data, 200);
	}

	public function ajax_save_settings()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$choice = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : false;

		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $this->plugin_name . '-settings')) {

			wp_send_json_error(new WP_Error('Unauthorized', 'Nonce is not valid'), 401);
			exit;
		} else {
			if ($choice) {

				delete_option('direktt_activation_status');
				update_option('direktt_api_key',  $choice);

				// Ovde treba poslati poziv

				$url = 'https://activatechannel-lnkonwpiwa-uc.a.run.app';

				$data = array(
					'domain' => 'https://2f70-82-117-218-70.ngrok-free.app'
					// 'domain' => get_site_url(null, '', 'https')
				);

				$response = wp_remote_post( $url, array(
					'body'    => json_encode( $data ) ,
					'headers' => array(
						'Authorization' => 'Bearer ' . $choice,
						'Content-type' => 'application/json',
					),
				) );

				//var_dump($response['response']['code']);

				if ( is_wp_error( $response ) ) {
					wp_send_json_error( $response , 500);
					return;
				}

				if ( $response['response']['code'] != '200' && $response['response']['code'] != '201' ){
					wp_send_json_error(new WP_Error('Unauthorized', 'API Key validation failed'), 401);
					return;
				}
				
			} else {
				delete_option('direktt_api_key');
			}
		}

		update_option('direktt_activation_status', 'true');
		$data = array();
		wp_send_json_success($data, 200);
	}

}
