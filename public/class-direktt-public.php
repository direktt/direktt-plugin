<?php

use Tmeister\Firebase\JWT\JWT;
use Tmeister\Firebase\JWT\Key;

class Direktt_Public
{
	private string $plugin_name;
	private string $version;

	// namespace for api calls
	private string $namespace;

	private ?WP_Error $jwt_error = null;

	private array $supported_algorithms = [
		'HS256',
		'HS384',
		'HS512',
		'RS256',
		'RS384',
		'RS512',
		'ES256',
		'ES384',
		'ES512',
		'PS256',
		'PS384',
		'PS512'
	];

	public function __construct(string $plugin_name, string $version)
	{
		global $direktt_user;

		$direktt_user = false;

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->namespace   = $this->plugin_name . '/v' . intval($this->version);
	}

	private function set_direktt_auth_cookie($cookie_value)
	{

		$arr_cookie_options = array(
			'expires' => 0, // session cookie
			'path' => '/',
			'domain' => parse_url(get_site_url(), PHP_URL_HOST), 
			'secure' => is_ssl(),     
			'httponly' => true,    
			'samesite' => 'Strict' 
		);

		setcookie('DirekttAuthToken', $cookie_value, $arr_cookie_options);
	}

	private function remove_direktt_auth_cookie()
	{
		$this->set_direktt_auth_cookie('');
	}

	private function not_auth_redirect()
	{
		global $direktt_user;

		$direktt_user = false;
		$this->remove_direktt_auth_cookie();

		$redirect_url = get_option('unauthorized_redirect_url');

		if( $redirect_url ) {
			
			nocache_headers();
			wp_safe_redirect( $redirect_url );
			exit;

		} else {
			header('HTTP/1.1 403 Unauthorized');
			exit();
		}
	}

	private function validate_direktt_token($token)
	{
		if (!$token) {
			return false;
		}

		$api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';

		$algorithm = $this->get_algorithm();

		if ($api_key == '' || $algorithm === false) {
			return false;
		}

		try {
			Direktt\Firebase\JWT\JWT::$leeway = 60 * 10; // ten minutes
			$decoded_token = Direktt\Firebase\JWT\JWT::decode($token, new Direktt\Firebase\JWT\Key($api_key, $algorithm));
			
		} catch (Exception $e) {
			return false;
		}

		if( property_exists($decoded_token, 'subscriptionUid') ){
			
			$direktt_user_id_tocheck = sanitize_text_field($decoded_token->subscriptionUid);
			$user = Direktt_User::get_user_by_subscription_id($direktt_user_id_tocheck);

		} else if( ! property_exists($decoded_token, 'subscriptionUid') && property_exists($decoded_token, 'channelUid') && property_exists($decoded_token, 'adminUid')) {
			
			$direktt_admin_id_tocheck = sanitize_text_field($decoded_token->adminUid);
			$user = Direktt_User::get_user_by_admin_id($direktt_admin_id_tocheck);

		} else {
			return false;
		}

		//Da li je istekao
		if (time() > intval($decoded_token->exp)) {
			return false;
		}

		// todo sta se desava ukoliko ga nemamo u bazi - treba poslati zahtev api-ju da proverimo usera i da nam on posalje zahtev da ga registruje i ako sve prodje kako treba ponovo ga validiramo

		if ($user) {
			return $user;
		} else {
			return false;
		}
	}

	private function generate_direktt_token($direktt_user)
	{
		$api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';
		$algorithm = $this->get_algorithm();

		/** First thing, check the secret key if not exist return an error*/
		if (!$api_key || !$algorithm) {
			return false;
		}

		$issuedAt  = time();
		$expire    = $issuedAt + 30 * 60; // 30 minutes;

		$token = [
			'iat'  => $issuedAt,
			'exp'  => $expire
		];

		if ( $direktt_user['direktt_user_id'] ){
			$token['subscriptionUid'] = $direktt_user['direktt_user_id'];
		}

		if ( $direktt_user['direktt_admin_user_id'] ){
			$token['adminUid'] = $direktt_user['direktt_admin_user_id'];
		}

		$token = Direktt\Firebase\JWT\JWT::encode(
			$token,
			$api_key,
			$algorithm
		);

		return $token;
	}

	public function direktt_check_user()
	{
		global $post;
		global $direktt_user;

		if( !$post ){
			return;
		}

		$for_users = Direktt_Public::is_post_for_direktt_user();
		$for_admins = Direktt_Public::is_post_for_direktt_admin();

		if ( !$for_admins && !$for_users ) {
			return;
		}

		$token = (isset($_GET['token'])) ? sanitize_text_field($_GET['token']) : false;

		$this->public_log($token);

		if ($token) {

			$direktt_user = $this->validate_direktt_token($token);

			if ($direktt_user && Direktt_Public::check_user_access_rights()) {

				$token = $this->generate_direktt_token($direktt_user);
				$this->set_direktt_auth_cookie($token);

				show_admin_bar(false);
			} else {
				$this->not_auth_redirect();
			}
		} else {

			if (is_user_logged_in()) {

				$current_user = wp_get_current_user();

				$test_user_id = get_user_meta($current_user->ID, 'direktt_test_user_id', true);

				if ( $test_user_id ) {
					$user = Direktt_User::get_user_by_post_id($test_user_id);
					if( $user ){
						$direktt_user = $user;
					} else {
						$direktt_user = false;
					}
				} else {
					$this->not_auth_redirect();
				}

				if ($direktt_user && Direktt_Public::check_user_access_rights()) {

					$token = $this->generate_direktt_token($direktt_user);
					$this->set_direktt_auth_cookie($token);
					show_admin_bar(false);
				} else {
					$this->not_auth_redirect();
				}
			} else {

				$token = (isset($_COOKIE['DirekttAuthToken'])) ? sanitize_text_field($_COOKIE['DirekttAuthToken']) : false;

				$direktt_user = $this->validate_direktt_token($token);

				if ($direktt_user && Direktt_Public::check_user_access_rights()) {

					show_admin_bar(false);

				} else {
					$this->not_auth_redirect();
				}
			}
		}
	}


	// todo verovatno ne treba, ukloniti
	public function add_cors_support()
	{
		$enable_cors = defined('JWT_AUTH_CORS_ENABLE') && JWT_AUTH_CORS_ENABLE;
		if ($enable_cors) {
			$headers = apply_filters(
				'jwt_auth_cors_allow_headers',
				'Access-Control-Allow-Headers, Content-Type, Authorization'
			);
			header(sprintf('Access-Control-Allow-Headers: %s', $headers));
		}
	}

	// todo verovatno ne treba, ukloniti i napraviti mogucnost kreiranja tokena bez da cimamo dareta
	public function generate_token(WP_REST_Request $request)
	{
		$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
		$username   = $request->get_param('username');
		$password   = $request->get_param('password');

		/** First thing, check the secret key if not exist return an error*/
		if (!$secret_key) {
			return new WP_Error(
				'jwt_auth_bad_config',
				__('JWT is not configured properly, please contact the admin', 'direktt'),
				[
					'status' => 403,
				]
			);
		}
		/** Try to authenticate the user with the passed credentials*/
		$user = wp_authenticate($username, $password);

		/** If the authentication fails return an error*/
		if (is_wp_error($user)) {
			$error_code = $user->get_error_code();

			return new WP_Error(
				'[jwt_auth] ' . $error_code,
				$user->get_error_message($error_code),
				[
					'status' => 403,
				]
			);
		}

		/** Valid credentials, the user exists create the according Token */
		$issuedAt  = time();
		$notBefore = apply_filters('jwt_auth_not_before', $issuedAt, $issuedAt);
		$expire    = apply_filters('jwt_auth_expire', $issuedAt + (DAY_IN_SECONDS * 7), $issuedAt);

		$token = [
			'iss'  => get_bloginfo('url'),
			'iat'  => $issuedAt,
			'nbf'  => $notBefore,
			'exp'  => $expire,
			'data' => [
				'user' => [
					'id' => $user->data->ID,
				],
			],
		];

		/** Let the user modify the token data before the sign. */
		$algorithm = $this->get_algorithm();

		if ($algorithm === false) {
			return new WP_Error(
				'jwt_auth_unsupported_algorithm',
				__(
					'Algorithm not supported, see https://www.rfc-editor.org/rfc/rfc7518#section-3',
					'direktt'
				),
				[
					'status' => 403,
				]
			);
		}

		$token = JWT::encode(
			apply_filters('jwt_auth_token_before_sign', $token, $user),
			$secret_key,
			$algorithm
		);

		/** The token is signed, now create the object with no sensible user data to the client*/
		$data = [
			'token'             => $token,
			'user_email'        => $user->data->user_email,
			'user_nicename'     => $user->data->user_nicename,
			'user_display_name' => $user->data->display_name,
		];

		/** Let the user modify the data before send it back */
		return apply_filters('jwt_auth_token_before_dispatch', $data, $user);
	}

	// todo ukloniti, verovatno ne treba, ali je korisno ako nam bude trebalo da logjemo korisnika
	public function determine_current_user($user)
	{
		/**
		 * This hook only should run on the REST API requests to determine
		 * if the user in the Token (if any) is valid, for any other
		 * normal call ex. wp-admin/.* return the user.
		 *
		 * @since 1.2.3
		 **/
		$rest_api_slug = rest_get_url_prefix();
		$requested_url = sanitize_url($_SERVER['REQUEST_URI']);
		// if we already have a valid user, or we have an invalid url, don't attempt to validate token
		$is_rest_request_constant_defined = defined('REST_REQUEST') && REST_REQUEST;
		$is_rest_request                  = $is_rest_request_constant_defined || strpos(
			$requested_url,
			$rest_api_slug
		);
		if ($is_rest_request && $user) {
			return $user;
		}

		/*
		 * if the request URI is for validate the token don't do anything,
		 * this avoids double calls.
		 */
		$validate_uri = strpos($requested_url, 'token/validate');
		if ($validate_uri > 0) {
			return $user;
		}

		/**
		 * We still need to get the Authorization header and check for the token.
		 */
		$auth_header = !empty($_SERVER['HTTP_AUTHORIZATION']) ? sanitize_text_field($_SERVER['HTTP_AUTHORIZATION']) : false;
		/* Double check for different auth header string (server dependent) */
		if (!$auth_header) {
			$auth_header = !empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? sanitize_text_field($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) : false;
		}

		if (!$auth_header) {
			return $user;
		}

		/**
		 * Check if the auth header is not bearer, if so, return the user
		 */
		if (strpos($auth_header, 'Bearer') !== 0) {
			return $user;
		}

		/*
		 * Check the token from the headers.
		 */
		$token = $this->validate_token(new WP_REST_Request(), $auth_header);

		if (is_wp_error($token)) {
			if ($token->get_error_code() != 'jwt_auth_no_auth_header') {
				/** If there is an error, store it to show it after see rest_pre_dispatch */
				$this->jwt_error = $token;
			}

			return $user;
		}

		/** Everything is ok, return the user ID stored in the token*/
		return $token->data->user->id;
	}

	// todo verovatno moze da se ukloni, validacija tokena
	public function validate_token(WP_REST_Request $request, $custom_token = false)
	{
		/*
		 * Looking for the Authorization header
		 *
		 * There is two ways to get the authorization token
		 *  1. via WP_REST_Request
		 *  2. via custom_token, we get this for all the other API requests
		 *
		 * The get_header( 'Authorization' ) checks for the header in the following order:
		 * 1. HTTP_AUTHORIZATION
		 * 2. REDIRECT_HTTP_AUTHORIZATION
		 *
		 * @see https://core.trac.wordpress.org/ticket/47077
		 */

		$auth_header = $custom_token ?: $request->get_header('Authorization');

		if (!$auth_header) {
			return new WP_Error(
				'jwt_auth_no_auth_header',
				'Authorization header not found.',
				[
					'status' => 403,
				]
			);
		}

		/*
		 * Extract the authorization header
		 */
		[$token] = sscanf($auth_header, 'Bearer %s');

		/**
		 * if the format is not valid return an error.
		 */
		if (!$token) {
			return new WP_Error(
				'jwt_auth_bad_auth_header',
				'Authorization header malformed.',
				[
					'status' => 403,
				]
			);
		}

		/** Get the Secret Key */
		$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
		if (!$secret_key) {
			return new WP_Error(
				'jwt_auth_bad_config',
				'JWT is not configured properly, please contact the admin',
				[
					'status' => 403,
				]
			);
		}

		/** Try to decode the token */
		try {
			$algorithm = $this->get_algorithm();
			if ($algorithm === false) {
				return new WP_Error(
					'jwt_auth_unsupported_algorithm',
					__(
						'Algorithm not supported, see https://www.rfc-editor.org/rfc/rfc7518#section-3',
						'direktt'
					),
					[
						'status' => 403,
					]
				);
			}

			$token = JWT::decode($token, new Key($secret_key, $algorithm));

			/** The Token is decoded now validate the iss */
			if ($token->iss !== get_bloginfo('url')) {
				/** The iss do not match, return error */
				return new WP_Error(
					'jwt_auth_bad_iss',
					'The iss do not match with this server',
					[
						'status' => 403,
					]
				);
			}

			/** So far so good, validate the user id in the token */
			if (!isset($token->data->user->id)) {
				/** No user id in the token, abort!! */
				return new WP_Error(
					'jwt_auth_bad_request',
					'User ID not found in the token',
					[
						'status' => 403,
					]
				);
			}

			/** Everything looks good return the decoded token if we are using the custom_token */
			if ($custom_token) {
				return $token;
			}

			/** This is for the /toke/validate endpoint*/
			return [
				'code' => 'jwt_auth_valid_token',
				'data' => [
					'status' => 200,
				],
			];
		} catch (Exception $e) {
			/** Something were wrong trying to decode the token, send back the error */
			return new WP_Error(
				'jwt_auth_invalid_token',
				$e->getMessage(),
				[
					'status' => 403,
				]
			);
		}
	}

	// todo ukloniti
	public function rest_pre_dispatch($request)
	{
		if (is_wp_error($this->jwt_error)) {
			return $this->jwt_error;
		}

		return $request;
	}

	private function get_algorithm()
	{
		$algorithm = apply_filters('jwt_auth_algorithm', 'HS256');
		if (!in_array($algorithm, $this->supported_algorithms)) {
			return false;
		}

		return $algorithm;
	}

	private function public_log($request)
	{
		$location = $_SERVER['REQUEST_URI'];
		$time = date("F jS Y, H:i", time() + 25200);
		$debug_info = var_export($request, true);
		$ban = "#$time\r\n$location\r\n$debug_info\r\n";
		$file = plugin_dir_path(__FILE__) . '/public.txt';
		$open = fopen($file, "a");
		$write = fputs($open, $ban);
		fclose($open);
	}

	static function is_post_for_direktt_user(){
		global $post;

		if($post){
			return intval(get_post_meta($post->ID, 'direktt_custom_box', true)) == 1;
		}
	}
	
	static function is_post_for_direktt_admin(){
		global $post;

		if($post){
			return intval(get_post_meta($post->ID, 'direktt_custom_admin_box', true)) == 1;
		}
	}

	static function check_user_access_rights(){
		$rights = false;
		global $direktt_user;

		if( ( Direktt_Public::is_post_for_direktt_user() && $direktt_user['direktt_user_id'] ) || ( Direktt_Public::is_post_for_direktt_admin() && $direktt_user['direktt_admin_user_id'] ) ){
			$rights = true;
		}
		return $rights;
	}
}
