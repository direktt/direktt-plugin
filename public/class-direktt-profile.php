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
		$atts = shortcode_atts(
			array(
				'categories' => '',
				'tags' => ''
			),
			$atts,
			'direktt_user_profile' 
		);

		$categories = array_filter(array_map('trim', explode(',', $atts['categories'])));
		$tags = array_filter(array_map('trim', explode(',', $atts['tags'])));

		global $direktt_user;

		ob_start();

		$active_tab = isset($_GET['subpage']) ? $_GET['subpage'] : '';

		if ($active_tab == '') {

			$subscriptionId   = isset($_GET['subscriptionId']) ? sanitize_text_field(wp_unslash($_GET['subscriptionId'])) : false;

			$profile_user = Direktt_User::get_user_by_subscription_id($subscriptionId);

			if ($profile_user) {
				if ((Direktt_User::has_direktt_taxonomies($direktt_user, $categories, $tags) || Direktt_User::is_direktt_admin()) || ($direktt_user['ID'] == $profile_user['ID'])) {
					echo ('User ID: ' . $profile_user['ID'] . '<br>');
					echo ('Direktt User Id: ' . $profile_user['direktt_user_id'] . '<br>');
					echo ('Admin Subscription: ' . $profile_user['direktt_admin_subscription'] . '<br>');
					echo ('Direktt User Marketing Consent: ' . $profile_user['direktt_marketing_consent_status'] . '<br><br><br>');
				}
			}
		} else {
			foreach (Direktt::$profile_tools_array as $item) {
				if (isset($item['id']) && $active_tab == $item['id']) {
					if ($this->direktt_user_has_term_slugs($item, $direktt_user) || Direktt_User::is_direktt_admin()) {
						call_user_func($item['callback']);
					}
				}
			}
		}

		$url = $_SERVER['REQUEST_URI'];
		$parts = parse_url($url);

		Direktt::$profile_tools_array = array_filter(Direktt::$profile_tools_array, function ($item) use ($direktt_user) {
			return ($this->direktt_user_has_term_slugs($item, $direktt_user) || Direktt_User::is_direktt_admin());
		});

		// Print out the Profile label and link

		if (!empty(Direktt::$profile_tools_array)) {
			parse_str($parts['query'] ?? '', $params);
			unset($params['subpage']);
			$newQuery = http_build_query($params);
			$newUri = $parts['path'] . ($newQuery ? '?' . $newQuery : '');
			echo ('<p><a href="' . $newUri . '">' . __('Profile', 'direktt') . '</a></p>');
		}

		// Sort links by priority asc

		usort(Direktt::$profile_tools_array, function ($a, $b) {
			return $a['priority'] <=> $b['priority'];
		});

		// Print out all other labels and links

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

	private function arrCategories($data)
	{
		$hasCategories = isset($data['categories']) && is_array($data['categories']) && !empty($data['categories']);
		if ($hasCategories) {
			return $data['categories'];
		} else {
			return array();
		}
	}

	private function arrTags($data)
	{
		$hasTags = isset($data['tags']) && is_array($data['tags']) && !empty($data['tags']);
		if ($hasTags) {
			return $data['tags'];
		} else {
			return array();
		}
	}

	function direktt_user_has_term_slugs($item, $direktt_user)
	{

		$categories = $this->arrCategories($item);
		$tags = $this->arrTags($item);

		// Get assigned category and tag slugs
		$assigned_categories = wp_get_post_terms($direktt_user['ID'], 'direkttusercategories', array('fields' => 'slugs'));
		$assigned_tags       = wp_get_post_terms($direktt_user['ID'], 'direkttusertags', array('fields' => 'slugs'));

		// If any input category matches assigned categories
		if (! empty($categories) && ! empty($assigned_categories)) {
			if (array_intersect($categories, $assigned_categories)) {
				return true;
			}
		}

		// If any input tag matches assigned tags
		if (! empty($tags) && ! empty($assigned_tags)) {
			if (array_intersect($tags, $assigned_tags)) {
				return true;
			}
		}

		return false;
	}

	static function add_profile_tool($params)
	{
		Direktt::$profile_tools_array[] = $params;
	}
}
