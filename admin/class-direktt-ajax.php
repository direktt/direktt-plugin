<?php

class Direktt_Ajax
{

	private string $plugin_name;
	private string $version;

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	
	public function ajax_get_mtemplates_taxonomies()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$categories = Direktt_User::get_all_user_categories();
		$tags = Direktt_User::get_all_user_tags();

		$data = array(
			'categories' => $categories,
			'tags' => $tags
		);

		wp_send_json_success($data, 200);
	}

	public function ajax_send_mtemplates_message()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$categories = (isset($_POST['categories'])) ? json_decode(stripslashes($_POST['categories']), true) : false;
		$tags = (isset($_POST['tags'])) ? json_decode(stripslashes($_POST['tags']), true) : false;

		var_dump($categories);
		var_dump($tags);

		$data = array(
			'succ' => true
		);

		wp_send_json_success($data, 200);
	}
	
	public function ajax_get_settings()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$templates = Direktt_Message_Template::get_templates(['all', 'none']);

		$data = array(
			'api_key' => get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '',
			'activation_status' => get_option('direktt_activation_status') ? esc_attr(get_option('direktt_activation_status')) : 'false',
			'direktt_registered_domain' => get_option('direktt_registered_domain') ? esc_attr(get_option('direktt_registered_domain')) : '',
			'direktt_channel_title' => get_option('direktt_channel_title') ? esc_attr(get_option('direktt_channel_title')) : '',
			'direktt_channel_id' => get_option('direktt_channel_id') ? esc_attr(get_option('direktt_channel_id')) : '',

			'redirect_url' => get_option('unauthorized_redirect_url') ? esc_attr(get_option('unauthorized_redirect_url')) : '',
			'pairing_prefix' => get_option('direktt_pairing_prefix') ? esc_attr(get_option('direktt_pairing_prefix')) : '',
			'pairing_succ_template' => get_option('direktt_pairing_succ_template') ? esc_attr(get_option('direktt_pairing_succ_template')) : '',
			'templates' => $templates
		);

		wp_send_json_success($data, 200);
	}

	public function ajax_get_dashboard()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$data = array(
			'activation_status' => get_option('direktt_activation_status') ? esc_attr(get_option('direktt_activation_status')) : 'false',
			'direktt_registered_domain' => get_option('direktt_registered_domain') ? esc_attr(get_option('direktt_registered_domain')) : '',
			'direktt_channel_title' => get_option('direktt_channel_title') ? esc_attr(get_option('direktt_channel_title')) : '',
			'direktt_channel_id' => get_option('direktt_channel_id') ? esc_attr(get_option('direktt_channel_id')) : ''
		);

		wp_send_json_success($data, 200);
	}

	public function ajax_get_marketing_consent()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$post_id = (isset($_POST['postId'])) ? sanitize_text_field($_POST['postId']) : false;

		$data = array(
			'direktt_user_id' => get_post_meta($post_id, "direktt_user_id", true),
			'marketing_consent' => get_post_meta($post_id, "direktt_marketing_consent_status", true),
			'direktt_admin_user_id' => get_post_meta($post_id, "direktt_admin_user_id", true),
		);

		wp_send_json_success($data, 200);
	}

	public function ajax_get_user_events()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$post_id = (isset($_POST['postId'])) ? sanitize_text_field($_POST['postId']) : false;
		$page = (isset($_POST['page'])) ? sanitize_text_field($_POST['page']) : false;

		$direktt_user_id = get_post_meta($post_id, 'direktt_user_id', true);

		global $wpdb;

		$table_name = $wpdb->prefix . 'direktt_events';

		if (intval($page) == 0) {
			$results = $wpdb->get_results("SELECT * FROM $table_name WHERE direktt_user_id = '" . $direktt_user_id . "' ORDER BY ID DESC LIMIT 20");
		} else {
			$results = $wpdb->get_results("SELECT * FROM $table_name WHERE direktt_user_id = '" . $direktt_user_id . "' AND ID < " . intval($page) . " ORDER BY ID DESC LIMIT 20");
		}

		$data = $results;

		wp_send_json_success($data, 200);
	}

	public function ajax_save_settings()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$choice = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : false;

		$url_choice = (isset($_POST['redirect_url'])) ? sanitize_text_field($_POST['redirect_url']) : false;

		$pairing_prefix = (isset($_POST['pairing_prefix'])) ? sanitize_text_field($_POST['pairing_prefix']) : false;

		$pairing_succ_template = (isset($_POST['pairing_succ_template'])) ? sanitize_text_field($_POST['pairing_succ_template']) : false;

		$reset_pairings = (isset($_POST['reset_pairings'])) ? sanitize_text_field($_POST['reset_pairings']) : false;

		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], $this->plugin_name . '-settings')) {

			wp_send_json_error(new WP_Error('Unauthorized', 'Nonce is not valid'), 401);
			exit;
		} else {
			if ($choice) {

				$current_api = get_option('direktt_api_key');

				if ($current_api != $choice) {


					delete_option('direktt_activation_status');
					update_option('direktt_api_key',  $choice);

					// Ovde treba poslati poziv

					$url = 'https://activatechannel-lnkonwpiwa-uc.a.run.app';

					$data = array(
						'domain' => get_site_url(null, '')
					);

					$response = wp_remote_post($url, array(
						'body'    => json_encode($data),
						'headers' => array(
							'Authorization' => 'Bearer ' . $choice,
							'Content-type' => 'application/json',
						),
					));

					//var_dump($response['response']['code']);

					if (is_wp_error($response)) {
						wp_send_json_error($response, 500);
						return;
					}

					if ($response['response']['code'] != '200' && $response['response']['code'] != '201') {
						wp_send_json_error(new WP_Error('Unauthorized', 'API Key validation failed'), 401);
						return;
					}

					update_option('direktt_activation_status', 'true');
					

				}
			} else {
				delete_option('direktt_api_key');
			}

			if ($url_choice) {
				update_option('unauthorized_redirect_url',  $url_choice);
			} else {
				delete_option('unauthorized_redirect_url');
			}

			if ($pairing_prefix) {
				update_option('direktt_pairing_prefix',  $pairing_prefix);
			} else {
				delete_option('direktt_pairing_prefix');
			}

			if ($pairing_succ_template) {
				update_option('direktt_pairing_succ_template',  $pairing_succ_template);
			} else {
				delete_option('direktt_pairing_succ_template');
			}

			if ($reset_pairings && $reset_pairings == "true"){
				$this->delete_user_meta_for_all_users('direktt_user_pair_code');
			}
		}

		$data = array();
		wp_send_json_success($data, 200);
	}

	private function delete_user_meta_for_all_users($meta_key)
	{
		global $wpdb;

		$sql = $wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s",
			$meta_key
		);

		$wpdb->query($sql);
	}
}
