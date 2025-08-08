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

			$assigned_categories = wp_get_post_terms( $post_id, 'direkttusercategories', array( 'fields' => 'names' ) );
        	$assigned_tags       = wp_get_post_terms( $post_id, 'direkttusertags', array( 'fields' => 'names' ) );

			$post_obj = array(
				'ID' => $post_id,
				'direkt_display_name' => get_the_title( $post_id ),
				'direktt_membership_id' => get_post_meta($post_id, "direktt_membership_id", true),
				'direktt_user_id' => get_post_meta($post_id, "direktt_user_id", true),
				'direktt_admin_subscription' => ( get_post_meta($post_id, 'direktt_admin_subscription', true) == '1' ),
				'direktt_marketing_consent_status' => ( get_post_meta($post_id, 'direktt_marketing_consent_status', true) == '1'),
				'direktt_avatar_url' => get_post_meta($post_id, "direktt_avatar_url", true),
				'direktt_user_categories' => $assigned_categories,
				'direktt_user_tags' => $assigned_tags,
			);

		}

		return $post_obj;
	}

	static function get_user_by_subscription_id($direktt_user_id)
	{
		$args = array(
			'post_type' => 'direkttusers',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key'   => 'direktt_user_id',
					'value' => $direktt_user_id
				)
			)
		);

		$posts = get_posts($args);

		$post_obj = false;

		if (!empty($posts)) {
			$post_id = $posts[0];

			$assigned_categories = wp_get_post_terms( $post_id, 'direkttusercategories', array( 'fields' => 'names' ) );
        	$assigned_tags       = wp_get_post_terms( $post_id, 'direkttusertags', array( 'fields' => 'names' ) );


			$post_obj = array(
				'ID' => $post_id,
				'direkt_display_name' => get_the_title( $post_id ),
				'direktt_membership_id' => get_post_meta($post_id, "direktt_membership_id", true),
				'direktt_user_id' => $direktt_user_id,
				'direktt_admin_subscription' => ( get_post_meta($post_id, 'direktt_admin_subscription', true) == '1' ),
				'direktt_marketing_consent_status' => ( get_post_meta($post_id, 'direktt_marketing_consent_status', true) == '1'),
				'direktt_avatar_url' => get_post_meta($post_id, "direktt_avatar_url", true),
				'direktt_user_categories' => $assigned_categories,
				'direktt_user_tags' => $assigned_tags,
			);
		}

		return $post_obj;
	}

	static function is_direktt_admin()
	{

		global $direktt_user;

		if ( isset($direktt_user['direktt_admin_subscription']) && $direktt_user['direktt_admin_subscription'] ) {
			return true;
		}

		return false;
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

		if (!empty($users)) {
			return $users[0];
		} else {
			return false;
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

	static function get_user_categories($direktt_user_post_id){

		$term_ids = [];
		$term_objects = get_the_terms($direktt_user_post_id, 'direkttusercategories');
		if (! is_wp_error($term_objects) && ! empty($term_objects)) {
			$term_ids = wp_list_pluck($term_objects, 'term_id');
		}

		return $term_ids;
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

	static function get_user_tags($direktt_user_post_id){

		$term_ids = [];
		$term_objects = get_the_terms($direktt_user_post_id, 'direkttusertags');
		if (! is_wp_error($term_objects) && ! empty($term_objects)) {
			$term_ids = wp_list_pluck($term_objects, 'term_id');
		}

		return $term_ids;
	}

	static function get_direktt_user_by_wp_user($wp_user)
	{

		$direktt_user_id = false;

		if (Direktt_User::is_wp_user_direktt_role($wp_user)) {

			$direktt_user_id = get_user_meta($wp_user->ID, 'direktt_user_id', true);
		} else {

			$direktt_user_id = get_user_meta($wp_user->ID, 'direktt_test_user_id', true);

			if (!$direktt_user_id) {
				$related_users = get_users(array(
					'role__in' => array('direktt'),
					'meta_key' => 'direktt_wp_user_id',
					'meta_value' => $wp_user->ID,
					'fields' => 'ID' 
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

	static function is_wp_user_direktt_role($wp_user)
	{
		if ($wp_user instanceof WP_User) {
			if (in_array('direktt', $wp_user->roles)) {
				return true;
			}
		}
		return false;
	}

	static function has_direktt_taxonomies($direktt_user, $categories, $tags)
	{
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
}
