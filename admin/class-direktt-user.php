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
			),
			/* ,
			'tax_query' => array(
				array(
					'taxonomy' => 'genre',
					'field'    => 'slug',
					'terms'    => 'jazz'
				)
			) */
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

	static function subscribe_user($direktt_user_id)
	{
		// $hierarchical_tax = array( 13, 10 ); // Array of tax ids.
		// $non_hierarchical_terms = 'tax name 1, tax name 2'; // Can use array of ids or string of tax names separated by commas

		$post_arr = array(
			'post_type'		=>	'direkttusers',
			'post_title'   	=> 	$direktt_user_id,
			//'post_content' 	=> 	'Test post content',
			'post_status'  	=> 	'publish',
			//'post_author'  	=> 	get_current_user_id(),
			/* 'tax_input'    	=> 	array(
				'hierarchical_tax'     => $hierarchical_tax,
				'non_hierarchical_tax' => $non_hierarchical_terms,
			), */
			'meta_input'	=>	array(
				'direktt_user_id'	=> $direktt_user_id,
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

			wp_delete_post($user['ID'], true);

			Direktt_User::delete_wp_direktt_user($user['ID']);

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

		// Query users by meta key and value
		$users = get_users(array(
			'meta_key' => 'direktt_user_id',
			'meta_value' => $direktt_user_id,
			'fields' => 'ID' // Return only user IDs
		));

		// Check if users were found
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

	public static function get_or_generate_user_pair_code($user_id)
	{
		// Define the meta key
		$meta_key = 'direktt_user_pair_code';

		// Check if the user already has the meta field set
		$pair_code = get_user_meta($user_id, $meta_key, true);

		// If the meta field is not set or is empty, generate a new 6-digit code
		if (empty($pair_code)) {
			$pair_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

			// Save the new code to the user's meta field
			update_user_meta($user_id, $meta_key, "pair" . $pair_code);
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

		function find_pair_code($string)
		{
			// Define the regular expression pattern
			$pattern = '/pair\d{6}/';

			// Use preg_match to find the code in the string
			if (preg_match($pattern, $string, $matches)) {
				// Return the matched code
				return $matches[0];
			} else {
				// Return false if no match is found
				return false;
			}
		}

		$event_data = strtolower(strip_special_chars($event["event_data"]));
		$pair_code = find_pair_code($event_data);

		if ($pair_code) {

			$users = get_users(array(
				'meta_key' => 'direktt_user_pair_code',
				'meta_value' => $pair_code,
				'fields' => 'ID' // Return only user IDs
			));

			if (!empty($users)) {

				$meta_user_post = Direktt_User::get_user_by_subscription_id($event['direktt_user_id']);

				foreach ($users as $user_id) {
					// Delete the user
					update_user_meta($user_id, 'direktt_user_id', $meta_user_post['ID']);
				}

				$pushNotificationMessage = array(
					"type" =>  "text",
					"content" => 'You have been paired'
				);

				Direktt_Message::send_message(array($event['direktt_user_id']), $pushNotificationMessage);
			}
		}
	}

	static function get_user_by_wp_direktt_user($wp_user)
	{

		$user_id = $wp_user->ID;
		$direktt_user_id = get_user_meta($user_id, 'direktt_user_id', true);

		return Direktt_User::get_user_by_post_id($direktt_user_id);

	}

	static function get_user_by_wp_user($wp_user)
	{

		$user_id = $wp_user->ID;
		$test_user_id = get_user_meta($user_id, 'direktt_test_user_id', true);

		if ($test_user_id) {
			$user = Direktt_User::get_user_by_post_id($test_user_id);
			if ($user) {
				$direktt_user = $user;
			} else {
				$direktt_user = false;
			}
		} else {
			$direktt_user = Direktt_User::get_user_by_wp_direktt_user($wp_user);
		}

		return $direktt_user;

	}
}
