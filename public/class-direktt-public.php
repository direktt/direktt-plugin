<?php

defined('ABSPATH') || exit;

class Direktt_Public
{

	private string $plugin_name;
	private string $version;

	private string $namespace;

	private ?WP_Error $jwt_error = null;

	const SUPPORTED_ALGORITHMS = array(
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
		'PS512',
	);

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->namespace   = $this->plugin_name . '/v' . intval($this->version);
	}

	public function direktt_enqueue_public_scripts()
	{

		global $post;
		$direktt_user = Direktt_User::direktt_get_current_user();

		if (! $post) {
			return;
		}

		wp_enqueue_script(
			'direktt_public',
			plugin_dir_url(__FILE__) . 'js/direktt-public.js',
			array(),
			$this->version,
			array(
				'in_footer' => true,
			)
		);

		wp_localize_script(
			'direktt_public',
			'direktt_public',
			array(
				'direktt_user'               => $direktt_user,
				'direktt_post_id'            => get_the_ID(),
				'direktt_ajax_url'           => admin_url('admin-ajax.php'),
				'direktt_rest_base'          => get_rest_url(null, 'direktt/v1/'),
				'direktt_wp_rest_nonce'      => wp_create_nonce('wp_rest'),
				'direktt_qr_code_logo_url'   => get_option('direktt_qr_code_logo_url'),
				'direktt_qr_code_color'      => get_option('direktt_qr_code_color'),
				'direktt_qr_code_bckg_color' => get_option('direktt_qr_code_bckg_color'),
			)
		);

		wp_register_style('direktt-profile-style', plugins_url('css/direktt-profile.css', __FILE__), array(), $this->version);

		do_action('direktt_enqueue_public_scripts');
	}

	private static function set_direktt_auth_cookie($token)
	{

		if (headers_sent() || '' === (string) $token) {
			return false;
		}

		$domain = wp_parse_url(home_url(), PHP_URL_HOST);

		return setcookie(
			'direktt_auth_token',
			$token,
			array(
				'expires'  => 0,          // Session cookie, expires when the browser closes.
				'path'     => '/',
				'domain'   => $domain,    // Only for this site’s domain.
				'secure'   => true,       // Sent only over HTTPS.
				'httponly' => true,       // Unreadable by JavaScript.
				'samesite' => 'Strict',   // Not sent with cross-site requests.
			)
		);
	}

	private static function remove_direktt_auth_cookie()
	{
		self::set_direktt_auth_cookie('');
	}

	private static function read_direktt_auth_cookie()
	{
		if (empty($_COOKIE['direktt_auth_token'])) {
			return false;
		}

		// Sanitize/unslash if you expect special characters to be escaped.
		$token = sanitize_text_field(wp_unslash($_COOKIE['direktt_auth_token']));

		$direktt_user_retrieved = self::validate_direktt_token($token);

		return $direktt_user_retrieved;
	}

	public static function not_auth_redirect()
	{
		global $direktt_user;
		$direktt_user = false;
		self::remove_direktt_auth_cookie();

		$redirect_url = get_option('direktt_unauthorized_redirect_url');

		if ($redirect_url) {
			nocache_headers();
			wp_safe_redirect($redirect_url);
			exit;
		} else {
			header('HTTP/1.1 403 Unauthorized');
			exit();
		}
	}

	public static function validate_direktt_token($token)
	{

		if (! $token) {
			return false;
		}

		$api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';

		$algorithm = self::get_algorithm();

		if ('' === $api_key || false === $algorithm) {
			return false;
		}

		try {
			Direktt\Dependencies\Firebase\JWT\JWT::$leeway = 60 * 10; // ten minutes.
			$decoded_token                                 = Direktt\Dependencies\Firebase\JWT\JWT::decode($token, new Direktt\Dependencies\Firebase\JWT\Key($api_key, $algorithm));
		} catch (Exception $e) {
			return false;
		}

		if (property_exists($decoded_token, 'subscriptionUid') || property_exists($decoded_token, 'adminUid')) {

			if (property_exists($decoded_token, 'adminUid')) {
				$direktt_user_id_tocheck = sanitize_text_field($decoded_token->adminUid);
			}

			if (property_exists($decoded_token, 'subscriptionUid')) {
				$direktt_user_id_tocheck = sanitize_text_field($decoded_token->subscriptionUid);
			}

			$user = Direktt_User::get_user_by_subscription_id($direktt_user_id_tocheck);
		} else {
			return false;
		}

		if (time() > intval($decoded_token->exp)) {
			return false;
		}

		if ($user) {
			return $user;
		} else {
			return false;
		}
	}

	public function direktt_check_token()
	{
		
		global $direktt_user;

		$direktt_user = false;

		$token = (isset($_GET['token'])) ? sanitize_text_field(wp_unslash($_GET['token'])) : false;     // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Justification: not a form processing, token based router for content rendering

		// 1. Check for valid token and auth and set Direktt user

		if ($token) {

			$direktt_user_retrieved = self::validate_direktt_token($token);

			if ($direktt_user_retrieved) {

				$direktt_user = $direktt_user_retrieved;

				self::set_direktt_auth_cookie($token);

				return;
			}
		}

		// 2. Look for the Direktt test or paired user of current WP user (if any)

		$current_wp_user = wp_get_current_user();

		if ($current_wp_user) {

			$direktt_user_retrieved = Direktt_User::get_direktt_user_by_wp_user($current_wp_user);

			if ($direktt_user_retrieved) {

				$direktt_user = $direktt_user_retrieved;

				return;
			}
		}

		// 3. Set auth Direktt user

		$direktt_user_retrieved = self::read_direktt_auth_cookie();

		if ($direktt_user_retrieved) {

			$direktt_user = $direktt_user_retrieved;

			return;
		}

	}

	private function redirect_without_token()
	{
		$current_url = (isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http');
		if (! isset($_SERVER['HTTP_HOST']) || ! isset($_SERVER['REQUEST_URI'])) {
			return;
		}

		$http_host   = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']));
		$request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));

		$current_url .= "://$http_host$request_uri";

		$new_url = $current_url;

		$parsed_url = wp_parse_url($current_url);

		if (isset($parsed_url['query'])) {
			parse_str($parsed_url['query'], $query_args);
			if (isset($query_args['token'])) {
				unset($query_args['token']);
				$new_query_string = http_build_query($query_args);
				$new_url          = $parsed_url['scheme'] . '://' . $parsed_url['host'];
				$new_url         .= isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
				$new_url         .= isset($parsed_url['path']) ? $parsed_url['path'] : '';
				$new_url         .= $new_query_string ? '?' . $new_query_string : '';
				$new_url         .= isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
			}
		}

		wp_safe_redirect($new_url);
		exit;
	}

	public static function is_restricted($post)
	{
		$for_users  = self::is_post_for_direktt_user($post);
		$for_admins = self::is_post_for_direktt_admin($post);

		$for_direktt_user_categories = self::is_post_for_direktt_user_categories($post);
		$for_direktt_user_tags       = self::is_post_for_direktt_user_tags($post);

		if (! $for_admins && ! $for_users && ! $for_direktt_user_categories && ! $for_direktt_user_tags) {
			return false;
		} else {
			return true;
		}
	}

	public function direktt_check_user()
	{
		global $post;

		if (! $post) {
			return;
		}

		$direktt_user = Direktt_User::direktt_get_current_user();

		if (!self::is_restricted($post)) {
			wp_enqueue_style('direktt-profile-style');
			return;
		}

		if (!$direktt_user) {
			self::not_auth_redirect();
			return;
		}

		if ($direktt_user && self::check_user_access_rights($direktt_user, $post)) {
			show_admin_bar(false);
			wp_enqueue_style('direktt-profile-style');
		} else {
			self::not_auth_redirect();
		}
	}

	public static function direktt_ajax_check_user($post)
	{
		if (! $post) {
			return false;
		}

		$direktt_user = Direktt_User::direktt_get_current_user();

		if (! self::is_restricted($post)) {
			return true;
		} elseif (! $direktt_user) {
			return false;
		}

		if ($direktt_user && self::check_user_access_rights($direktt_user, $post)) {
			return true;
		} else {
			return false;
		}
	}

	private static function get_algorithm()
	{
		$algorithm = apply_filters('direktt_jwt_auth_algorithm', 'HS256');
		if (! in_array($algorithm, self::SUPPORTED_ALGORITHMS, true)) {
			return false;
		}

		return $algorithm;
	}

	private static function is_post_for_direktt_user($post)
	{
		if ($post) {
			return intval(get_post_meta($post->ID, 'direktt_custom_box', true)) === 1;
		}
	}

	private static function is_post_for_direktt_user_categories($post)
	{
		if ($post) {
			return get_post_meta($post->ID, 'direktt_user_categories', true);
		}
	}

	private static function is_post_for_direktt_user_tags($post)
	{
		if ($post) {
			return get_post_meta($post->ID, 'direktt_user_tags', true);
		}
	}

	private static function is_post_for_direktt_admin($post)
	{
		if ($post) {
			return intval(get_post_meta($post->ID, 'direktt_custom_admin_box', true)) === 1;
		}
	}

	private static function check_user_access_rights($direktt_user, $post)
	{
		$rights = false;

		if ((self::is_post_for_direktt_user($post) && $direktt_user['direktt_user_id']) || (self::is_post_for_direktt_admin($post) && $direktt_user['direktt_admin_subscription'])) {
			$rights = true;
		} else {
			$allowed_categories = self::is_post_for_direktt_user_categories($post);

			if ($allowed_categories) {

				$term_ids = Direktt_User::get_user_categories($direktt_user['ID']);

				$has_allowed_category = ! empty(array_intersect($term_ids, $allowed_categories));
				if ($has_allowed_category) {
					$rights = true;
				}
			}

			$allowed_tags = self::is_post_for_direktt_user_tags($post);

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

	public function direktt_pairing_code_shortcode()
	{
		ob_start();
		if (! is_user_logged_in()) {
?>
			<p><?php echo esc_html__('You have to login.', 'direktt'); ?></p>
			<?php
		} else {
			$wp_user = wp_get_current_user();

			$related_user = Direktt_User::get_related_user($wp_user->ID);

			if ($related_user) {
			?>
				<p><?php echo esc_html__('You have already been paired.', 'direktt'); ?></p>
			<?php
			} else {
				$code = get_user_meta($wp_user->ID, 'direktt_user_pair_code', true);
			?>
				<div class="direktt-paring-code">
					<h2><?php echo esc_html__('Direktt Pairing Code', 'direktt'); ?></h2>
					<p><?php echo esc_html($code); ?></p>
				</div>
			<?php
			}
		}
		return ob_get_clean();
	}

	public function direktt_register_pairing_code_shortcode()
	{
		add_shortcode('direktt_pairing_code', array($this, 'direktt_pairing_code_shortcode'));
	}

	public function direktt_qr_pairing_code_shortcode($atts)
	{
		$atts       = shortcode_atts(
			array(
				'size_in_px' => '200', // default size.
			),
			$atts
		);
		$size_in_px = intval($atts['size_in_px']);
		ob_start();
		if (! is_user_logged_in()) {
			?>
			<p><?php echo esc_html__('You have to login.', 'direktt'); ?></p>
			<?php
		} else {

			$wp_user = wp_get_current_user();

			$related_user = Direktt_User::get_related_user($wp_user->ID);

			if ($related_user) {
			?>
				<p><?php echo esc_html__('You have already been paired.', 'direktt'); ?></p>
			<?php
			} else {
				$code = get_user_meta($wp_user->ID, 'direktt_user_pair_code', true);
				wp_enqueue_script('qrcode-generator');
				wp_enqueue_script('direktt-pair-code-qr-js');
			?>
				<div class="direktt-qr-paring-code" data-pair-code="<?php echo esc_attr($code); ?>" data-size-in-px="<?php echo esc_attr($size_in_px); ?>">
					<h2><?php echo esc_html__('Direktt Pairing Code', 'direktt'); ?></h2>
					<div id="qrcode"></div>
				</div>
		<?php
			}
		}
		return ob_get_clean();
	}

	public function direktt_register_qr_pairing_code_shortcode()
	{
		add_shortcode('direktt_qr_pairing_code', array($this, 'direktt_qr_pairing_code_shortcode'));
	}

	public function direktt_pair_code_action($pair_code)
	{

		$direktt_user = Direktt_User::direktt_get_current_user();

		Direktt_User::pair_wp_user_by_code($pair_code, $direktt_user['direktt_user_id']);
	}

	public function direktt_register_pairing_code_scripts()
	{
		wp_register_script(
			'qrcode-generator',
			plugin_dir_url(__FILE__) . 'js/qrcode.min.js',
			array(),
			'1.0.0',
			true
		);
		wp_register_script(
			'direktt-pair-code-qr-js',
			plugin_dir_url(__FILE__) . 'js/direktt-pair-code-qr.js',
			array('jquery'),
			$this->version,
			true
		);
	}

	public function direktt_add_body_class($classes)
	{
		$direktt_user =  Direktt_User::direktt_get_current_user();

		if ($direktt_user) {
			$classes[] = 'direktt-app';
		}
		return $classes;
	}

	public static function direktt_render_alert_popup($id, $text)
	{
		ob_start();
		?>
		<div class="direktt-popup direktt-alert-popup" <?php echo $id ? 'id="' . esc_attr($id) . '"' : ''; ?>>
			<div class="direktt-popup-content">
				<div class="direktt-popup-header">
					<h3><?php echo esc_html__('Alert', 'direktt'); ?></h3>
				</div>
				<div class="direktt-popup-text">
					<p><?php echo esc_html($text); ?></p>
				</div>
				<div class="direktt-popup-actions">
					<button class="direktt-popup-ok"><?php echo esc_html__('OK', 'direktt'); ?></button>
				</div>
			</div>
		</div>
	<?php
		return ob_get_clean();
	}

	public static function direktt_render_confirm_popup($id, $text)
	{
		ob_start();
	?>
		<div class="direktt-popup direktt-confirm-popup" <?php echo $id ? 'id="' . esc_attr($id) . '"' : ''; ?>>
			<div class="direktt-popup-content">
				<div class="direktt-popup-header">
					<h3><?php echo esc_html__('Confirm', 'direktt'); ?></h3>
				</div>
				<div class="direktt-popup-text">
					<p><?php echo esc_html($text); ?></p>
				</div>
				<div class="direktt-popup-actions">
					<button class="direktt-popup-yes"><?php echo esc_html__('Yes', 'direktt'); ?></button>
					<button class="direktt-popup-no"><?php echo esc_html__('No', 'direktt'); ?></button>
				</div>
			</div>
		</div>
	<?php
		return ob_get_clean();
	}

	public static function direktt_render_loader($text = '')
	{
		ob_start();
	?>
		<div class="direktt-loader-overlay">
			<div class="direktt-loader-container">
				<p class="direktt-loader-text"><?php echo $text ? esc_html($text) : esc_html__('Don\'t refresh the page', 'direktt'); ?></p>
				<div class="direktt-loader"></div>
			</div>
		</div>
<?php
		return ob_get_clean();
	}
}
