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

		$post_id = false;

		if (!empty($posts)) {
			$post_id = $posts[0];
		}

		return $post_id;
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
			return $post_id;
		}
	}

	static function unsubscribe_user($direktt_user_id)
	{
		$post_id = Direktt_User::get_user_by_subscription_id($direktt_user_id);

		if ($post_id) {
			wp_delete_post($post_id, true);
		}
	}
}
