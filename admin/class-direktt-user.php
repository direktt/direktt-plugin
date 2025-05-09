<?php

class Direktt_User
{
	private string $plugin_name;
	private string $version;

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	static function get_user_by_post_id($direktt_user_post_id)
	{
		$args = array(
			'post_type' => 'direkttusers',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'post__in' => array($direktt_user_post_id)
		);

		$posts = get_posts($args);

		$post_obj = false;

		if (!empty($posts)) {
			$post_id = $posts[0];

			$post_obj = array(
				'ID' => $post_id,
				'direktt_user_id' => get_post_meta($post_id, 'direktt_user_id', true),
				'direktt_admin_user_id' => get_post_meta($post_id, 'direktt_admin_user_id', true),
				'direktt_marketing_consent_status' => get_post_meta($post_id, 'direktt_marketing_consent_status', true)
			);
		}

		return $post_obj;
	}

	static function get_user_by_subscription_id($direktt_user_id_tocheck)
	{
		$args = array(
			'post_type' => 'direkttusers',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key'   => 'direktt_user_id',
					'value' => $direktt_user_id_tocheck
				)
			)
		);

		$posts = get_posts($args);

		$post_obj = false;

		if (!empty($posts)) {
			$post_id = $posts[0];

			$post_obj = array(
				'ID' => $post_id,
				'direktt_user_id' => $direktt_user_id_tocheck,
				'direktt_admin_user_id' => get_post_meta($post_id, 'direktt_admin_user_id', true),
				'direktt_marketing_consent_status' => get_post_meta($post_id, 'direktt_marketing_consent_status', true)
			);
		}

		return $post_obj;
	}

	static function is_direktt_admin()
	{

		global $direktt_user;

		if (isset($direktt_user['direktt_admin_user_id']) && $direktt_user['direktt_admin_user_id'] != '') {
			return true;
		}

		return false;
	}

	static function get_user_by_admin_id($direktt_admin_id_tocheck)
	{
		$args = array(
			'post_type' => 'direkttusers',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key'   => 'direktt_admin_user_id',
					'value' => $direktt_admin_id_tocheck
				)
			),
		);

		$posts = get_posts($args);

		$post_obj = false;

		if (!empty($posts)) {
			$post_id = $posts[0];

			$post_obj = array(
				'ID' => $post_id,
				'direktt_user_id' => get_post_meta($post_id, 'direktt_user_id', true),
				'direktt_admin_user_id' => $direktt_admin_id_tocheck,
				'direktt_marketing_consent_status' => get_post_meta($post_id, 'direktt_marketing_consent_status', true)
			);
		}

		return $post_obj;
	}

	static function subscribe_user($direktt_user_id, $direktt_user_title = null, $direktt_user_avatar_url = null)
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

			$wp_user_id = Direktt_User::create_wp_direktt_user($post_id);
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

	static function subscribe_admin($admin_id)
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

			$wp_user_id = Direktt_User::create_wp_direktt_user($post_id);
			if (is_wp_error($wp_user_id)) {
				return $wp_user_id;
			}

			do_action('direktt/admin/subscribe', $admin_id);
			return $post_id;
		}
	}

	static function unsubscribe_user($direktt_user_id)
	{
		$user = Direktt_User::get_user_by_subscription_id($direktt_user_id);

		if ($user) {

			Direktt_User::delete_wp_direktt_user($user['ID']);
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

	static function promote_to_admin($direktt_user_id, $admin_id)
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

	static function pair_user_with_admin($direktt_user_id, $admin_id)
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

	private static function generate_random_string($length = 12)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		return $randomString;
	}

	private static function create_wp_direktt_user($user_id)
	{
		$username = 'direktt_' . $user_id;
		$email = Direktt_User::generate_random_string(8) . '@direktt.com';
		$password = Direktt_User::generate_random_string(12);

		while (email_exists($email)) {
			$email = Direktt_User::generate_random_string(8) . '@example.com';
		}

		$wp_user_id = wp_create_user($username, $password, $email);

		if (is_wp_error($wp_user_id)) {
			return $wp_user_id;
		}

		$user = new WP_User($wp_user_id);
		$user->set_role('direktt');

		update_user_meta($wp_user_id, 'direktt_user_id', $user_id);
	}

	private static function delete_wp_direktt_user($direktt_user_id)
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

	static function get_wp_direktt_user_by_post_id($direktt_user_id)
	{

		$args = array(
			'role'    => 'direktt',
			'meta_query' => array(
				array(
					'key'     => 'direktt_user_id',
					'value'   => $direktt_user_id,
					'compare' => '='
				)
			)
		);

		$user_query = new WP_User_Query($args);

		$users = $user_query->get_results();

		// Check if users were found and return the first one
		if (!empty($users)) {
			return $users[0];
		}
	}

	static function get_all_user_categories(){

		$category_terms = get_terms(array(
			'taxonomy' => 'direkttusercategories',
			'hide_empty' => false,
		));

		$all_categories = [];
		
		foreach ($category_terms as $term) {
			$all_categories[] = [
				'value' => $term->term_id,
				'name' => $term->name
			];
		}

		return $all_categories;

	}

	static function get_all_user_tags(){

		$tag_terms = get_terms(array(
			'taxonomy' => 'direkttusertags',
			'hide_empty' => false,
		));

		$all_tags = [];

		foreach ($tag_terms as $term) {
			$all_tags[] = [
				'value' => $term->term_id,
				'name' => $term->name
			];
		}
		return $all_tags;
	}

	public static function get_or_generate_user_pair_code($user_id)
	{
		// Define the meta key
		$meta_key = 'direktt_user_pair_code';

		// Check if the user already has the meta field set
		$pair_code = get_user_meta($user_id, $meta_key, true);

		// If the meta field is not set or is empty, generate a new 6-digit code
		if (empty($pair_code)) {

			$par_code_prefix = get_option('direktt_pairing_prefix', false);

			if (!$par_code_prefix) {
				$par_code_prefix = 'pair';
			}

			$pair_code = $par_code_prefix . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

			// Save the new code to the user's meta field
			update_user_meta($user_id, $meta_key, $pair_code);
		}

		// Return the existing or newly generated code
		return $pair_code;
	}

	function pair_wp_user_by_code($event)
	{

		function strip_special_chars($input)
		{
			// Use preg_replace to strip all characters except for a-z, A-Z, and 0-9
			return preg_replace('/[^a-zA-Z0-9]/', '', $input);
		}

		function find_pair_code($string, $pair_patt = 'pair')
		{
			// Define the regular expression pattern
			$pattern = '/' . $pair_patt . '\d{6}/';

			// Use preg_match to find the code in the string
			if (preg_match($pattern, $string, $matches)) {
				// Return the matched code
				return $matches[0];
			} else {
				// Return false if no match is found
				return false;
			}
		}

		$par_code_prefix = get_option('direktt_pairing_prefix', false);

		if (!$par_code_prefix) {
			$par_code_prefix = 'pair';
		}

		$event_data = strtolower(strip_special_chars($event["event_data"]));
		$pair_code = find_pair_code($event_data, $par_code_prefix);

		if ($pair_code) {

			$users = get_users(array(
				'meta_key' => 'direktt_user_pair_code',
				'meta_value' => $pair_code,
				'fields' => 'ID' // Return only user IDs
			));

			if (!empty($users)) {

				$meta_user_post = Direktt_User::get_user_by_subscription_id($event['direktt_user_id']);

				$users_to_update = get_users(array(
					'meta_key' => 'direktt_user_id',
					'meta_value' => $meta_user_post['ID'],
					'fields' => 'ID' // Return only user IDs
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

						Direktt_Message::send_message(array($event['direktt_user_id']), $pushNotificationMessage);
					}
				}
			}
		}
	}

	static function get_direktt_user_by_wp_user($wp_user)
	{

		$direktt_user_id = false;

		// ako je rola direktt onda
		if (Direktt_User::is_wp_user_direktt_user($wp_user)) {

			$direktt_user_id = get_user_meta($wp_user->ID, 'direktt_user_id', true);
		} else {

			//ako nije, prvo gledamo test usera. Ako ga nema onda trazimo usera sa rolom direktt i sa direktt_wp_user_id jednakim ovom nasem i onda vadimo ovo gore

			$direktt_user_id = get_user_meta($wp_user->ID, 'direktt_test_user_id', true);

			if (!$direktt_user_id) {
				$related_users = get_users(array(
					'role__in' => array('direktt'),
					'meta_key' => 'direktt_wp_user_id',
					'meta_value' => $wp_user->ID,
					'fields' => 'ID' // Return only user IDs
				));

				if (!empty($related_users)) {
					$direktt_user_id = get_user_meta($related_users[0], 'direktt_user_id', true);
				}
			}
		}

		if ($direktt_user_id) {
			return Direktt_User::get_user_by_post_id($direktt_user_id);
		} else {
			return false;
		}
	}

	static function is_wp_user_direktt_user($wp_user)
	{
		if ($wp_user instanceof WP_User) {
			if (in_array('direktt', $wp_user->roles)) {
				return true;
			}
		}
		return false;
	}

	static function delete_user_meta_for_all_users($meta_key)
	{
		global $wpdb;

		$sql = $wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s",
			$meta_key
		);

		$wpdb->query($sql);
	}
}
