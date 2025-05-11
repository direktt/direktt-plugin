<?php

class Direktt_Api
{
	private string $plugin_name;
	private string $version;

	// namespace for api calls
	private string $namespace;

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->namespace   = $this->plugin_name . '/v' . intval($this->version);
	}

	public function api_register_routes()
	{
		register_rest_route('direktt/v1', '/activateChannel/', array(
			'methods' => 'POST',
			'callback' => array($this, 'activate_channel'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));

		register_rest_route('direktt/v1', '/onNewSubscription/', array(
			'methods' => 'POST',
			'callback' => array($this, 'on_new_subscription'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));

		register_rest_route('direktt/v1', '/onChangeAvatarUrl/', array(
			'methods' => 'POST',
			'callback' => array($this, 'on_change_avatar_url'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));

		register_rest_route('direktt/v1', '/onChangeDisplayName/', array(
			'methods' => 'POST',
			'callback' => array($this, 'on_change_display_name'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));

		register_rest_route('direktt/v1', '/doAction/', array(
			'methods' => 'POST',
			'callback' => array($this, 'do_direktt_action'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));

		register_rest_route('direktt/v1', '/onSetAdminUser/', array(
			'methods' => 'POST',
			'callback' => array($this, 'on_set_admin_user'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));

		register_rest_route('direktt/v1', '/onUnsubscribe/', array(
			'methods' => 'POST',
			'callback' => array($this, 'on_unsubscribe'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));

		register_rest_route('direktt/v1', '/onMarketingConsentUpdate/', array(
			'methods' => 'POST',
			'callback' => array($this, 'on_marketing_consent_update'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));

		register_rest_route('direktt/v1', '/recordEvent/', array(
			'methods' => 'POST',
			'callback' => array($this, 'record_event'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));
	}

	public function activate_channel(WP_REST_Request $request)
	{

		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('domain', $parameters) && array_key_exists('title', $parameters) && array_key_exists('uid', $parameters)) {

			$direktt_registered_domain = sanitize_text_field($parameters['domain']);
			update_option('direktt_registered_domain', $direktt_registered_domain);

			$direktt_channel_title = sanitize_text_field($parameters['title']);
			update_option('direktt_channel_title', $direktt_channel_title);

			$direktt_channel_id = sanitize_text_field($parameters['uid']);
			update_option('direktt_channel_id', $direktt_channel_id);

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Either domain or title or uid missing'), 400);
		}
	}

	public function on_new_subscription(WP_REST_Request $request)
	{
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters)) {
			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);

			$avatar_url = null;
			$display_name = null;

			if (array_key_exists('avatarUrl', $parameters)) {
				$avatar_url = sanitize_text_field($parameters['avatarUrl']);
			}

			if (array_key_exists('displayName', $parameters)) {
				$display_name = sanitize_text_field($parameters['displayName']);
			}

			$result = $this->subscribe_user($direktt_user_id, $display_name, $avatar_url );

			if (is_wp_error($result)) {
				wp_send_json_error($result, 500);
			} else {
				$data = array();
				wp_send_json_success($data, 200);
			}
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id missing'), 400);
		}
	}

	private function subscribe_user($direktt_user_id, $direktt_user_title = null, $direktt_user_avatar_url = null)
	{
		$usr_title = $direktt_user_id;
		if (!is_null($direktt_user_title) && $direktt_user_title != '') {
			$usr_title = $direktt_user_title;
		}

		$meta_input = array(
			'direktt_user_id'	=> $direktt_user_id,
		);

		if (!is_null($direktt_user_avatar_url) && $direktt_user_avatar_url != '') {
			$meta_input['direktt_avatar_url'] = $direktt_user_avatar_url;
		}

		$post_arr = array(
			'post_type'		=>	'direkttusers',
			'post_title'   	=> 	$usr_title,
			'post_status'  	=> 	'publish',
			'meta_input'	=>	$meta_input,
		);

		$wp_error = false;

		$post_id = wp_insert_post($post_arr, $wp_error);

		if ($wp_error) {
			return $wp_error;
		} else {

			$wp_user_id = $this->create_wp_direktt_user($post_id);
			if (is_wp_error($wp_user_id)) {
				return $wp_user_id;
			}

			do_action('direktt/user/subscribe', $direktt_user_id);

			Direktt_Event::insert_event(
				array(
					"direktt_user_id" => $direktt_user_id,
					"event_target" => "user",
					"event_type" => "subscribe"
				)
			);

			return $post_id;
		}
	}

	private function create_wp_direktt_user($user_id)
	{
		$username = 'direktt_' . $user_id;
		$email = $this->generate_random_string(8) . '@direktt.com';
		$password = $this->generate_random_string(12);

		while (email_exists($email)) {
			$email = $this->generate_random_string(8) . '@example.com';
		}

		$wp_user_id = wp_create_user($username, $password, $email);

		if (is_wp_error($wp_user_id)) {
			return $wp_user_id;
		}

		$user = new WP_User($wp_user_id);
		$user->set_role('direktt');

		update_user_meta($wp_user_id, 'direktt_user_id', $user_id);
	}

	public function on_change_avatar_url(WP_REST_Request $request)
	{
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters)) {

			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);
			$user = Direktt_User::get_user_by_subscription_id($direktt_user_id);

			if (array_key_exists('imageUrl', $parameters)) {
				$avatar_url = sanitize_text_field($parameters['imageUrl']);
			} else {
				$avatar_url = '';
			}			

			if ($user) {
				if ( $avatar_url != ''){
					update_post_meta($user['ID'], "direktt_avatar_url", $avatar_url);
				} else {
					delete_post_meta($user['ID'], "direktt_avatar_url");
				}
			}

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id or Image Url is missing'), 400);
		}
	}

	public function on_change_display_name(WP_REST_Request $request)
	{
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters)) {

			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);
			$user = Direktt_User::get_user_by_subscription_id($direktt_user_id);

			if (array_key_exists('displayName', $parameters)) {

				$direktt_display_name = sanitize_text_field($parameters['displayName']);
				if ($direktt_display_name == '') {
					$direktt_display_name = $direktt_user_id;
				}
			} else {
				$direktt_display_name = $direktt_user_id;
			}

			if ($user) {
				$post_data = array(
					'ID'         => $user['ID'],
					'post_title' => $direktt_display_name
				);
				wp_update_post($post_data);
			}

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id missing'), 400);
		}
	}

	public function do_direktt_action(WP_REST_Request $request)
	{
		global $direktt_user;
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('token', $parameters)) {

			$token = sanitize_text_field($parameters['token']);

			$direktt_user = Direktt_Public::validate_direktt_token($token);

			if (!$direktt_user) {
				wp_send_json_error(new WP_Error('Auth', 'Token is not valid'), 401);
				return;
			}

			if (array_key_exists('actionType', $parameters)) {
				$action_type = sanitize_text_field($parameters['actionType']);

				do_action("direktt/action/" . $action_type, $parameters);

				$data = array();
				wp_send_json_success($data, 200);
			} else {
				wp_send_json_error(new WP_Error('Missing param', 'Action Type is missing'), 400);
			}
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Token is missing'), 400);
		}
	}

	// Called once user scans QR code and becomes the app admin user

	public function on_set_admin_user(WP_REST_Request $request)
	{
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('adminId', $parameters)) {

			$admin_id = sanitize_text_field($parameters['adminId']);

			$user = Direktt_User::get_user_by_admin_id($admin_id);

			if (!$user) {

				if (array_key_exists('subscriptionId', $parameters)) {

					$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);
					$user = Direktt_User::get_user_by_subscription_id($direktt_user_id);

					if ($user) {

						$this->promote_to_admin($direktt_user_id, $admin_id);
					} else {

						$result = $this->subscribe_user($direktt_user_id);

						if (is_wp_error($result)) {
							wp_send_json_error($result, 500);
							return;
						}

						$this->promote_to_admin($direktt_user_id, $admin_id);
					}
				} else {

					$result = $this->subscribe_admin($admin_id);

					if (is_wp_error($result)) {
						wp_send_json_error($result, 500);
						return;
					}
				}
			} else {
				if (array_key_exists('subscriptionId', $parameters)) {

					$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);
					$this->pair_user_with_admin($direktt_user_id, $admin_id);
				}
			}

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Admin Id is missing'), 400);
		}
	}

	private function subscribe_admin($admin_id)
	{

		$post_arr = array(
			'post_type'		=>	'direkttusers',
			'post_title'   	=> 	'Admin - ' . $admin_id,
			'post_status'  	=> 	'publish',
			'meta_input'	=>	array(
				'direktt_admin_user_id'	=> $admin_id,
			),
		);

		$wp_error = false;

		$post_id = wp_insert_post($post_arr, $wp_error);

		if ($wp_error) {
			return $wp_error;
		} else {

			$wp_user_id = $this->create_wp_direktt_user($post_id);
			if (is_wp_error($wp_user_id)) {
				return $wp_user_id;
			}

			do_action('direktt/admin/subscribe', $admin_id);
			return $post_id;
		}
	}

	private function promote_to_admin($direktt_user_id, $admin_id)
	{
		$user = Direktt_User::get_user_by_subscription_id($direktt_user_id);

		update_post_meta($user['ID'], "direktt_admin_user_id", $admin_id);

		Direktt_Event::insert_event(
			array(
				"direktt_user_id" => $direktt_user_id,
				"event_target" => "user",
				"event_type" => "admin",
				"event_value" => "true"
			)
		);
	}

	private function pair_user_with_admin($direktt_user_id, $admin_id)
	{
		$user = Direktt_User::get_user_by_admin_id($admin_id);

		update_post_meta($user['ID'], "direktt_user_id", $direktt_user_id);

		Direktt_Event::insert_event(
			array(
				"direktt_user_id" => $direktt_user_id,
				"event_target" => "user",
				"event_type" => "admin",
				"event_value" => "true"
			)
		);
	}

	public function on_unsubscribe(WP_REST_Request $request)
	{
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters)) {

			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);

			$this->unsubscribe_user($direktt_user_id);

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id missing'), 400);
		}
	}

	private function unsubscribe_user($direktt_user_id)
	{
		$user = Direktt_User::get_user_by_subscription_id($direktt_user_id);

		if ($user) {

			$this->delete_wp_direktt_user($user['ID']);
			wp_trash_post($user['ID']);

			Direktt_Event::insert_event(
				array(
					"direktt_user_id" => $direktt_user_id,
					"event_target" => "user",
					"event_type" => "unsubscribe"
				)
			);
			do_action('direktt/user/unsubscribe', $direktt_user_id);
		}
	}

	private function delete_wp_direktt_user($direktt_user_id)
	{
		require_once(ABSPATH . 'wp-admin/includes/user.php');

		// Delete users which were assoicated with the Direktt User with subscriptionId equal to $direktt_user_id but only with role direktt
		$users = get_users(array(
			'meta_key' => 'direktt_user_id',
			'meta_value' => $direktt_user_id,
			'role'       => 'direktt',
			'fields' => 'ID' // Return only user IDs
		));

		if (!empty($users)) {
			foreach ($users as $user_id) {
				// Delete the user
				wp_delete_user(intval($user_id));
			}
		}
	}

	public function on_marketing_consent_update(WP_REST_Request $request)
	{
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters) && array_key_exists('marketingConsentStatus', $parameters)) {

			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);

			$marketing_consent_status = (sanitize_text_field($parameters['marketingConsentStatus']) == '1');

			$user = Direktt_User::get_user_by_subscription_id($direktt_user_id);

			if ($user) {
				update_post_meta($user['ID'], "direktt_marketing_consent_status", $marketing_consent_status);
			}

			Direktt_Event::insert_event(
				array(
					"direktt_user_id" => $direktt_user_id,
					"event_target" => "user",
					"event_type" => "marketing_consent",
					"event_value" => $marketing_consent_status ? 'true' : 'false'
				)
			);

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id or Marketing Consent Status is missing'), 400);
		}
	}

	public function record_event(WP_REST_Request $request)
	{
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters) && array_key_exists('eventTarget', $parameters) && array_key_exists('eventType', $parameters)) {

			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);
			$event_target = sanitize_text_field($parameters['eventTarget']);
			$event_type = sanitize_text_field($parameters['eventType']);

			$event = array(
				'direktt_user_id' => $direktt_user_id,
				'event_target' => $event_target,
				'event_type' => $event_type
				//'event_time' => time()
			);

			if (array_key_exists('campaignId', $parameters)) {
				$event['direktt_campaign_id'] = sanitize_text_field($parameters['campaignId']);
			}

			if (array_key_exists('eventData', $parameters)) {
				$event['event_data'] = sanitize_text_field($parameters['eventData']);
			}

			if (array_key_exists('eventValue', $parameters)) {
				$event['event_value'] = sanitize_text_field($parameters['eventValue']);
			}

			Direktt_Event::insert_event($event);

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id, Event Target or Event Type is missing'), 400);
		}
	}

	public function api_validate_api_key()
	{
		$auth_header = !empty($_SERVER['HTTP_AUTHORIZATION']) ? sanitize_text_field($_SERVER['HTTP_AUTHORIZATION']) : false;
		/* Double check for different auth header string (server dependent) */
		if (!$auth_header) {
			$auth_header = !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? sanitize_text_field($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) : false;
		}

		if (!$auth_header) {
			return false;
		}

		/**
		 * Check if the auth header is not bearer, if so, return the user
		 */
		if (strpos($auth_header, 'Bearer') !== 0) {
			return false;
		}

		[$token] = sscanf($auth_header, 'Bearer %s');

		$api_key = get_option('direktt_api_key');

		if ($api_key && $api_key == $token) {
			return true;
		} else {
			return false;
		}
	}

	private function generate_random_string($length = 12)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return $randomString;
	}

	private function api_log($request)
	{
		$location = $_SERVER['REQUEST_URI'];
		$time = date("F jS Y, H:i", time() + 25200);
		$debug_info = var_export($request, true);
		$ban = "#$time\r\n$location\r\n$debug_info\r\n";
		$file = plugin_dir_path(__FILE__) . '/errors.txt';
		$open = fopen($file, "a");
		$write = fputs($open, $ban);
		fclose($open);
	}
}
