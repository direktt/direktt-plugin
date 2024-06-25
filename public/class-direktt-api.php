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

		//todo ukloniti na kraju

		register_rest_route('direktt/v1', '/test/', array(
			'methods' => 'POST',
			'callback' => array($this, 'api_test'),
			'args' => array(),
			'permission_callback' => array($this, 'api_validate_api_key')
		));
	}

	public function activate_channel(WP_REST_Request $request)
	{
		$this->api_log($request);
		$data = array();
		wp_send_json_success($data, 200);
	}

	public function on_new_subscription(WP_REST_Request $request)
	{
		$this->api_log($request);
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters)) {
			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);

			$result = Direktt_User::subscribe_user($direktt_user_id);

			if (is_wp_error( $result )) {
				wp_send_json_error($result, 500);
			} else {
				$data = array();
				wp_send_json_success($data, 200);
			}
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id missing'), 400);
		}
	}

	public function on_unsubscribe(WP_REST_Request $request)
	{
		$this->api_log($request);
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters)) {

			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);
			Direktt_User::unsubscribe_user($direktt_user_id);

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id missing'), 400);
		}
	}

	public function on_marketing_consent_update(WP_REST_Request $request)
	{
		$this->api_log($request);
		$parameters = json_decode($request->get_body(), true);

		if (array_key_exists('subscriptionId', $parameters) && array_key_exists('marketingConsentStatus', $parameters)) {

			$direktt_user_id = sanitize_text_field($parameters['subscriptionId']);
			$marketing_consent_status = (sanitize_text_field($parameters['marketingConsentStatus']) === 'true');

			$post_id = Direktt_User::get_user_by_subscription_id($direktt_user_id);

			if ($post_id) {
				update_post_meta($post_id, "direktt_marketing_consent_status", $marketing_consent_status);
			}

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id or Marketing Consent Status is missing'), 400);
		}
	}

	public function record_event(WP_REST_Request $request)
	{
		$this->api_log($request);
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

			if (array_key_exists('direktt_campaign_id', $parameters)) {
				$event['direktt_campaign_id'] = sanitize_text_field($parameters['direktt_campaign_id']);
			}

			if (array_key_exists('event_data', $parameters)) {
				$event['event_data'] = sanitize_text_field($parameters['event_data']);
			}

			Direktt_Event::insert_event($event);

			$data = array();
			wp_send_json_success($data, 200);
		} else {
			wp_send_json_error(new WP_Error('Missing param', 'Subscription Id, Event Target or Event Type is missing'), 400);
		}
	}

	public function api_test(WP_REST_Request $request)
	{
		$this->api_log($request);
		$parameters = json_decode($request->get_body(), true);

		$data = array(
			'domain' => $parameters['domain']
		);
		wp_send_json_success($data, 200);
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
