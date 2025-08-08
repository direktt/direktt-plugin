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

	const supported_algorithms = [
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

	public function direktt_enqueue_public_scripts()
	{

		global $post;
		global $direktt_user;

		if (!$post) {
			return;
		}

		wp_enqueue_script(
			'direktt_public',
			plugin_dir_url(__FILE__) . 'js/direktt-public.js',
			[],
			'',
			[
				'in_footer' => true,
			]
		);

		wp_localize_script(
			'direktt_public',
			'direktt_public',
			array(
				'direktt_user' => $direktt_user,
				'direktt_post_id' => get_the_ID(),
				'direktt_wp_rest_nonce' => wp_create_nonce( 'wp_rest' )
			)
		);

		do_action('direktt_enqueue_public_scripts');
	}

	static private function set_direktt_auth_cookie($cookie_value)
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

	static private function remove_direktt_auth_cookie()
	{
		Direktt_Public::set_direktt_auth_cookie('');
	}

	static function not_auth_redirect()
	{
		global $direktt_user;

		$direktt_user = false;
		Direktt_Public::remove_direktt_auth_cookie();

		$redirect_url = get_option('unauthorized_redirect_url');

		if ($redirect_url) {

			nocache_headers();
			wp_safe_redirect($redirect_url);
			exit;
		} else {
			header('HTTP/1.1 403 Unauthorized');
			exit();
		}
	}

	static function validate_direktt_token($token)
	{

		if (!$token) {
			return false;
		}

		$api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';

		$algorithm = Direktt_Public::get_algorithm();

		if ($api_key == '' || $algorithm === false) {
			return false;
		}

		try {
			Direktt\Firebase\JWT\JWT::$leeway = 60 * 10; // ten minutes
			$decoded_token = Direktt\Firebase\JWT\JWT::decode($token, new Direktt\Firebase\JWT\Key($api_key, $algorithm));
		} catch (Exception $e) {
			return false;
		}

		if ( property_exists($decoded_token, 'subscriptionUid') || property_exists($decoded_token, 'adminUid') ) {

			if( property_exists($decoded_token, 'adminUid') ){
				$direktt_user_id_tocheck = sanitize_text_field($decoded_token->adminUid);
			}

			if( property_exists($decoded_token, 'subscriptionUid') ){
				$direktt_user_id_tocheck = sanitize_text_field($decoded_token->subscriptionUid);
			}

			$user = Direktt_User::get_user_by_subscription_id($direktt_user_id_tocheck);

		//} else if (! property_exists($decoded_token, 'subscriptionUid') && property_exists($decoded_token, 'channelUid') && property_exists($decoded_token, 'adminUid')) {

			//$direktt_admin_id_tocheck = sanitize_text_field($decoded_token->adminUid);
			//$user = Direktt_User::get_user_by_admin_id($direktt_admin_id_tocheck);
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

	public function direktt_check_token()
	{

		$token = (isset($_GET['token'])) ? sanitize_text_field($_GET['token']) : false;

		if ($token) {

			if (is_user_logged_in()) {

				$current_user = wp_get_current_user();

				if (! Direktt_User::is_wp_user_direktt_role($current_user)) {
					return;
				}
			}

			wp_logout();

			$direktt_user = Direktt_Public::validate_direktt_token($token);

			if (!$direktt_user) {
				return;
			}

			//nadji usera koji je direktt user i koji je i uloguj ga
			$direktt_wp_user = Direktt_User::get_wp_direktt_user_by_post_id($direktt_user['ID']);

			if ($direktt_wp_user) {
				// Log the user in
				wp_set_current_user($direktt_wp_user->ID);
				wp_set_auth_cookie($direktt_wp_user->ID);
				do_action('wp_login', $direktt_wp_user->login, $direktt_wp_user);

				$this->redirect_without_token();
			}
		}
	}

	private function redirect_without_token()
	{
		// Get current URL
		$current_url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
		$current_url .= "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		$new_url = $current_url;

		$parsed_url = wp_parse_url($current_url);

		if (isset($parsed_url['query'])) {
			parse_str($parsed_url['query'], $query_args);
			if (isset($query_args['token'])) {
				unset($query_args['token']);
				$new_query_string = http_build_query($query_args);
				$new_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
				$new_url .= isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
				$new_url .= isset($parsed_url['path']) ? $parsed_url['path'] : '';
				$new_url .= $new_query_string ? '?' . $new_query_string : '';
				$new_url .= isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
			}
		}

		wp_safe_redirect($new_url);
		exit;
	}

	static function is_restricted($post)
	{
		$for_users = Direktt_Public::is_post_for_direktt_user($post);
		$for_admins = Direktt_Public::is_post_for_direktt_admin($post);

		$for_direktt_user_categories = Direktt_Public::is_post_for_direktt_user_categories($post);
		$for_direktt_user_tags = Direktt_Public::is_post_for_direktt_user_tags($post);

		if( !$for_admins && !$for_users && !$for_direktt_user_categories && !$for_direktt_user_tags ){
			return false;
		} else {
			return true;
		}
	}

	public function direktt_check_user()
	{
		global $post;
		
		global $direktt_user;
		$direktt_user = false;

		if (!$post) {
			return;
		}

		if (!Direktt_Public::is_restricted($post) ) {
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				$direktt_user = Direktt_User::get_direktt_user_by_wp_user($current_user);
			}
			return;
		} else {
			if (! is_user_logged_in()) {
				Direktt_Public::not_auth_redirect();
			}
		}

		$current_user = wp_get_current_user();

		$direktt_user = Direktt_User::get_direktt_user_by_wp_user($current_user);

		if ($direktt_user && Direktt_Public::check_user_access_rights($direktt_user, $post)) {
			show_admin_bar(false);
		} else {
			Direktt_Public::not_auth_redirect();
		}
	}

	static function direktt_ajax_check_user($post)
	{
		if (!$post) {
			return false;
		}

		$current_user = wp_get_current_user();

		$direktt_user = Direktt_User::get_direktt_user_by_wp_user($current_user);

		if (! Direktt_Public::is_restricted($post)) {
			return true;
		} else {
			if (! is_user_logged_in()) {
				return false;
			}
		}

		if ($direktt_user && Direktt_Public::check_user_access_rights($direktt_user, $post)) {
			return true;
		} else {
			return false;
		}
	}

	static private function get_algorithm()
	{
		$algorithm = apply_filters('jwt_auth_algorithm', 'HS256');
		if (!in_array($algorithm, Direktt_Public::supported_algorithms)) {
			return false;
		}

		return $algorithm;
	}

	static private function is_post_for_direktt_user($post)
	{
		if ($post) {
			return intval(get_post_meta($post->ID, 'direktt_custom_box', true)) == 1;
		}
	}

	static private function is_post_for_direktt_user_categories($post)
	{
		if ($post) {
			return get_post_meta($post->ID, 'direktt_user_categories', true);
		}
	}

	static private function is_post_for_direktt_user_tags($post)
	{
		if ($post) {
			return get_post_meta($post->ID, 'direktt_user_tags', true);
		}
	}

	static private function is_post_for_direktt_admin($post)
	{
		if ($post) {
			return intval(get_post_meta($post->ID, 'direktt_custom_admin_box', true)) == 1;
		}
	}

	static private function check_user_access_rights($direktt_user, $post)
	{
		$rights = false;

		if ((Direktt_Public::is_post_for_direktt_user($post) && $direktt_user['direktt_user_id']) || (Direktt_Public::is_post_for_direktt_admin($post) && $direktt_user['direktt_admin_subscription'])) {
			$rights = true;
		} else {
			$allowed_categories = Direktt_Public::is_post_for_direktt_user_categories($post);

			if ($allowed_categories) {

				$term_ids = Direktt_User::get_user_categories($direktt_user['ID']);
	
				$has_allowed_category = ! empty(array_intersect($term_ids, $allowed_categories));
				if ($has_allowed_category) {
					$rights = true;
				}
			}

			$allowed_tags = Direktt_Public::is_post_for_direktt_user_tags($post);

			if ($allowed_tags) {

				$term_ids = Direktt_User::get_user_tags($direktt_user['ID']);

				$has_allowed_tag = ! empty(array_intersect($term_ids, $allowed_tags));
				if ($has_allowed_tag) {
					$rights = true;
				}
				
			}
		}
		return $rights;
	}

	public function direktt_pairing_code_shortcode() {
		ob_start();
		if ( ! is_user_logged_in() ) {
			?>
			<p><?php echo esc_html__( 'You have to login.', 'direktt' ); ?></p>
			<?php
		} else {
			$wp_user = wp_get_current_user();
			if ( Direktt_User::get_direktt_user_by_wp_user( $wp_user ) ) {
				?>
				<p><?php echo esc_html__( 'You have already been paired.', 'direktt' ); ?></p>
				<?php
			} else {
				$code = get_user_meta( $wp_user->ID, 'direktt_user_pair_code', true );
				?>
				<div class="direktt-paring-code">
					<h2><?php echo esc_html__( 'Direktt Pairing Code', 'direktt' ); ?></h2>
					<p><?php echo esc_html( $code ); ?></p>
				</div>
				<?php
			}
		}
		return ob_get_clean();
	}

	public function direktt_register_pairing_code_shortcode() {
		add_shortcode( 'direktt_pairing_code', [$this, 'direktt_pairing_code_shortcode'] );
	}

	public function direktt_qr_pairing_code_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'size_in_px' => '200', // default size
		), $atts );
		$size_in_px = intval( $atts['size_in_px'] );
		ob_start();
		if ( ! is_user_logged_in() ) {
			?>
			<p><?php echo esc_html__( 'You have to login.', 'direktt' ); ?></p>
			<?php
		} else {
			$wp_user = wp_get_current_user();
			if ( Direktt_User::get_direktt_user_by_wp_user( $wp_user ) ) {
				?>
				<p><?php echo esc_html__( 'You have already been paired.', 'direktt' ); ?></p>
				<?php
			} else {
				$code = get_user_meta( $wp_user->ID, 'direktt_user_pair_code', true );
				wp_enqueue_script( 'qrcode-generator' );
				wp_enqueue_script( 'direktt-pair-code-qr-js' );
				?>
				<div class="direktt-qr-paring-code" data-pair-code="<?php echo esc_attr( $code ); ?>" data-size-in-px="<?php echo esc_attr( $size_in_px ); ?>">
					<h2><?php echo esc_html__( 'Direktt Pairing Code', 'direktt' ); ?></h2>
					<div id="qrcode"></div>
				</div>
				<?php
			}
		}
		return ob_get_clean();
	}

	public function direktt_register_qr_pairing_code_shortcode() {
		add_shortcode( 'direktt_qr_pairing_code', [$this, 'direktt_qr_pairing_code_shortcode'] );
	}

	public function direktt_pair_code_action( $pair_code ) {
		$users = get_users(array(
			'meta_key' => 'direktt_user_pair_code',
			'meta_value' => $pair_code,
			'fields' => 'ID' 
		));

		if (!empty($users)) {
			global $direktt_user;
			$meta_user_post = Direktt_User::get_user_by_subscription_id($direktt_user['direktt_user_id']);

			$users_to_update = get_users(array(
				'meta_key' => 'direktt_user_id',
				'meta_value' => $meta_user_post['ID'],
				'fields' => 'ID' 
			));

			$pairing_message_template = get_option('direktt_pairing_succ_template', false);

			foreach ($users as $user_id) {

				foreach ($users_to_update as $user_id_to_update) {
					update_user_meta($user_id_to_update, 'direktt_wp_user_id', $user_id);
					delete_user_meta($user_id_to_update, 'direktt_user_pair_code');
				}

				delete_user_meta($user_id, 'direktt_user_pair_code');

				if ($pairing_message_template) {

					Direktt_Message::send_message_template(
						array($event['direktt_user_id']),
						$pairing_message_template,
						[
							"wp_user" =>  get_user_by('id', $user_id)->user_login
						]
					);
				} else {

					$pushNotificationMessage = array(
						"type" =>  "text",
						"content" => 'You have been paired'
					);

					Direktt_Message::send_message( array( $event['direktt_user_id'] => $pushNotificationMessage ) );
				}
			}
		}
	}

	public function direktt_register_pairing_code_scripts() {
		wp_register_script(
			'qrcode-generator',
			'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js',
			array(),
			'1.0.0',
			true
		);
		wp_register_script(
			'direktt-pair-code-qr-js',
			plugins_url( 'js/direktt-pair-code-qr.js', __FILE__ ),
			array( 'jquery' ),
			null,
			true
		);
	}

	public function direktt_add_body_class( $classes ) {
		global $direktt_user;
		if( $direktt_user ){
			$classes[] = 'direktt-app';
		}
		return $classes;
	}
}
