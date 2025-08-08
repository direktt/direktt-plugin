<?php

class Direktt_Ajax
{

	private string $plugin_name;
	private string $version;
	private Direktt_Api $direktt_api;

	public function __construct(string $plugin_name, string $version, Direktt_Api $direktt_api)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->direktt_api     = $direktt_api;
	}


	public function ajax_get_mtemplates_taxonomies()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$categories = Direktt_User::get_all_user_categories();
		$tags = Direktt_User::get_all_user_tags();
		$nonce = wp_create_nonce('direkttmtemplates');

		$data = array(
			'categories' => $categories,
			'tags' => $tags,
			'nonce' => $nonce
		);

		wp_send_json_success($data, 200);
	}

	public function ajax_send_mtemplates_message()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
			return;
		}

		$nonce = (isset($_POST['nonce'])) ? sanitize_text_field($_POST['nonce']) : false;

		if ($nonce && wp_verify_nonce($nonce, 'direkttmtemplates')) {

			$categories = (isset($_POST['categories'])) ? json_decode(stripslashes($_POST['categories']), true) : false;
			$tags = (isset($_POST['tags'])) ? json_decode(stripslashes($_POST['tags']), true) : false;

			$userSet = (isset($_POST['userSet'])) ? sanitize_text_field($_POST['userSet']) : false;

			$message_template_id = (isset($_POST['postId'])) ? sanitize_text_field($_POST['postId']) : false;

			if ($userSet && $message_template_id) {

				$subscription_ids = array();

				if ($userSet == 'all') {
					$subscription_ids = $this->get_subscription_ids_from_terms([], []);
					Direktt_Message::send_message_template($subscription_ids, $message_template_id);
				}

				if ($userSet == 'selected') {
					$subscription_ids = $this->get_subscription_ids_from_terms($categories, $tags, false);
					Direktt_Message::send_message_template($subscription_ids, $message_template_id);
				}

				if ($userSet == 'admin') {
					Direktt_Message::send_message_template_to_admin($message_template_id);
				}
			}

			$data = array(
				'succ' => true
			);

			wp_send_json_success($data, 200);
			return;
		}

		wp_send_json_error(new WP_Error('Unauthorized', 'Access to API is unauthorized.'), 401);
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
			'admin_subscription' => get_post_meta($post_id, "direktt_admin_subscription", true),
			'membership_id' => get_post_meta($post_id, "direktt_membership_id", true),
			'avatar_url' => get_post_meta($post_id, "direktt_avatar_url", true),
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
			if ($choice && $choice != "") {

				$current_api = get_option('direktt_api_key');

				if ($current_api != $choice) {

					update_option('direktt_api_key',  $choice);

					// Ovde treba poslati poziv

					$url = 'https://activatechannel-lnkonwpiwa-uc.a.run.app';

					$data = array(
						'domain' => get_site_url(null, '')
					);

					$response = wp_remote_post($url, array(
						'body'    => json_encode($data),
						'timeout'     => 30,
						'headers' => array(
							'Authorization' => 'Bearer ' . $choice,
							'Content-type' => 'application/json',
						),
					));

					if (is_wp_error($response)) {
						wp_send_json_error($response, 500);
						return;
					}

					if ($response['response']['code'] != '200' && $response['response']['code'] != '201') {

						delete_option('direktt_activation_status');

						wp_send_json_error(new WP_Error('Unauthorized', 'API Key validation failed'), 401);
						return;
					}

					update_option('direktt_activation_status', 'true');

					// ovde treba da ide poziv za dovlacenje subscription-a

					if (!$this->has_published_direkttusers_posts()) {
						$this->get_all_existing_subscriptions();
					}
				}
			} else {
				delete_option('direktt_api_key');
				delete_option('direktt_activation_status');
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

			if ($reset_pairings && $reset_pairings == "true") {
				$this->delete_user_meta_for_all_users('direktt_user_pair_code');
			}
		}

		$data = array();
		wp_send_json_success($data, 200);
	}

	private function get_all_existing_subscriptions()
	{

		$api_key = (isset($_POST['api_key'])) ? sanitize_text_field($_POST['api_key']) : false;

		$url = 'https://getsubscriptionsforchannel-lnkonwpiwa-uc.a.run.app';

		$data = array();

		$response = wp_remote_post($url, array(
			'body'    => json_encode($data),
			'timeout'     => 30,
			'headers' => array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-type' => 'application/json',
			),
		));

		if (is_wp_error($response)) {
			return;
		}

		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

		if (isset($data['success']) && $data['success'] && !empty($data['subscriptions'])) {
			foreach ($data['subscriptions'] as $subscription) {
				$subscriptionId    = $subscription['subscriptionId']   ?? null;
				$displayName       = $subscription['displayName']      ?? null;
				$avatarUrl         = $subscription['avatarUrl']        ?? null;
				$adminSubscription = $subscription['adminSubscription'] == 'true' ?? null;
				$membershipId         = $subscription['membershipId']        ?? null;
				$marketingConsentStatus         = $subscription['marketingConsentStatus'] == 'true'  ?? null;

				$this->direktt_api->subscribe_user(
					$subscriptionId,
					$displayName,
					$avatarUrl,
					$adminSubscription,
					$membershipId,
					$marketingConsentStatus,
					true
				);
			}
		}
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

	private function has_published_direkttusers_posts()
	{
		$args = array(
			'post_type'      => 'direkttusers',
			'post_status'    => 'publish',
			'posts_per_page' => 1, // Just need to check existence
			'fields'         => 'ids',
		);
		$query = new WP_Query($args);
		return ($query->have_posts());
	}

	private function get_subscription_ids_from_terms($category_ids = array(), $tag_ids = array(), $empty_allowed = true)
	{
		// Ensure inputs are arrays
		$category_ids = (array) $category_ids;
		$tag_ids      = (array) $tag_ids;

		// Build tax_query
		$tax_query = array('relation' => 'OR');

		if (! empty($category_ids)) {
			$tax_query[] = array(
				'taxonomy' => 'direkttusercategories',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			);
		}
		if (! empty($tag_ids)) {
			$tax_query[] = array(
				'taxonomy' => 'direkttusertags',
				'field'    => 'term_id',
				'terms'    => $tag_ids,
			);
		}

		$meta_query = array(
			'relation' => 'OR',
			// Case 1: Key doesn't exist (so admin subscription isn't set)
			array(
				'key'     => 'direktt_admin_subscription',
				'compare' => 'NOT EXISTS',
			),
			// Case 2: Key exists, but is not true or 1
			array(
				'key'     => 'direktt_admin_subscription',
				'value'   => array('1', 'true'),
				'compare' => 'NOT IN',
			),
		);

		$args = array(
			'post_type'      => 'direkttusers',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'tax_query'      => $tax_query,
			'meta_query'     => $meta_query,
			'post_status'    => 'any',
		);

		if (count($tax_query) === 1 && $empty_allowed) {
			unset($args['tax_query']);
		} else if (count($tax_query) === 1 && !$empty_allowed) {
			return array();
		}

		$query = new WP_Query($args);
		$post_ids = $query->posts;

		// Get subscriptionId meta values
		$subscription_ids = array();
		if (! empty($post_ids)) {
			foreach ($post_ids as $post_id) {
				$sub_id = get_post_meta($post_id, 'direktt_user_id', true);
				if (! empty($sub_id)) {
					$subscription_ids[] = $sub_id;
				}
			}
		}

		return $subscription_ids;
	}

	public function ajax_get_mtemplates_profile_message()
	{

		if (!isset($_POST['post_id'])) {
			wp_send_json(['status' => 'post_id_failed'], 400);
		}

		$post_id = intval($_POST['post_id']);

		$post = get_post($post_id);

		// Validate that post exists and the current user can perform the action.


		if ($post && Direktt_Public::direktt_ajax_check_user($post)) {

			// Verify nonce for security against CSRF attacks.


			if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'direktt_msgsend_nonce')) {
				wp_send_json(['status' => 'nonce_failed'], 401);
			}

			$templates = Direktt_Message_Template::get_templates(['all', 'individual']);

			wp_send_json_success($templates, 200);

		} else {

			// User not authorized or post not found.
			wp_send_json(['status' => 'non_authorized'], 401);
		}
	}
}
