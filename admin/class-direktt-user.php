<?php

class Direktt_User
{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.3.4
	 *
	 * @var string The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.3.4
	 *
	 * @var string The current version of this plugin.
	 */
	private string $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.3.4
	 */
	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	static function get_user_by_subscription_id( $subscription_id )
	{
		$args = array(
			'post_type' => 'direkttusers',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'meta_query' => array(
					array(
						'key'   => 'direktt_user_id',
						'value' => $subscription_id,
					)
				)/* ,
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

		if(!empty($posts)) {
			$post_id = $posts[0];
		}

		return $post_id;
	}

}
