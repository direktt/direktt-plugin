<?php

class Direktt_Profile
{
	private string $plugin_name;
	private string $version;

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function setup_profile_tools()
	{
		do_action('direktt_setup_profile_tools');
	}

	public function profile_shortcode()
	{
		add_shortcode('direktt_user_profile', [$this, 'direktt_user_profile']);
	}

	public function direktt_user_profile($atts)
	{
		global $direktt_user;

		ob_start();

		$active_tab = isset($_GET['subpage']) ? $_GET['subpage'] : '';

		if ($active_tab == '') {
			if ($direktt_user) {
				$subscriptionId   = isset($_GET['subscriptionId']) ? sanitize_text_field(wp_unslash($_GET['subscriptionId'])) : false;

				$profile_user = Direktt_User::get_user_by_subscription_id($subscriptionId);
				if ($profile_user) {
					echo ('User ID: ' . $profile_user['ID'] . '<br>');
					echo ('Direktt User Id: ' . $profile_user['direktt_user_id'] . '<br>');
					echo ('Direktt User Admin Id: ' . $profile_user['direktt_admin_user_id'] . '<br>');
					echo ('Direktt User Marketing Consent: ' . $profile_user['direktt_marketing_consent_status'] . '<br><br><br>');
				}
			}
		} else {
			foreach (Direktt::$profile_tools_array as $item) {

				//!!! TODO provera prava da li smemo da ispisemo

				if (isset($item['id']) && $active_tab == $item['id']) {
					call_user_func($item['callback']);
				}
			}
		}

		$url = $_SERVER['REQUEST_URI'];
		$parts = parse_url($url);

		if (!empty(Direktt::$profile_tools_array)) {
			parse_str($parts['query'] ?? '', $params);
			unset($params['subpage']);
			$newQuery = http_build_query($params);
			$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
			echo ('<p><a href="' . $newUri . '">' . __('Profile', 'direktt') . '</a></p>');
		}

		foreach (Direktt::$profile_tools_array as $item) {
			if (isset($item['label'])) {

				//!!! TODO provera prava da li smemo da ispisemo

				parse_str($parts['query'] ?? '', $params);
				$params['subpage'] = $item['id'];
				$newQuery = http_build_query($params);
				$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
				echo ('<p><a href="' . $newUri . '">' . $item['label'] . '</a></p>');
			}
		}

		return ob_get_clean();
	}

	static function add_profile_tool($params)
	{
		Direktt::$profile_tools_array[] = $params;
	}
}
