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
			'post__in' => array( $direktt_user_post_id )
		);
		
		$posts = get_posts($args);

		$post_obj = false;

		if (!empty($posts)) {
			$post_id = $posts[0];

			$post_obj = array (
				'ID'=> $post_id,
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

			$post_obj = array (
				'ID'=> $post_id,
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

			$post_obj = array (
				'ID'=> $post_id,
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

			do_action( 'direktt_subscribe_user', $direktt_user_id );

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
			do_action( 'direktt_subscribe_admin', $admin_id );
			return $post_id;
		}
	}

	static function unsubscribe_user($direktt_user_id)
	{
		$user = Direktt_User::get_user_by_subscription_id( $direktt_user_id );

		if ($user) {

			wp_delete_post($user['ID'], true);

			Direktt_Event::insert_event(
				array(
					"direktt_user_id" => $direktt_user_id,
					"event_target" => "user",
					"event_type" => "unsubscribe"
				)
			);

			do_action( 'direktt_unsubscribe_user', $direktt_user_id );

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
}
