<?php

defined( 'ABSPATH' ) || exit;

class Direktt_Message_Template {

	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	static function get_templates( $event_types = array() ) {
		$template_args = array(
			'post_type'              => 'direkttmtemplates',
			'post_status'            => 'publish',
			'posts_per_page'         => 500,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
		);

		if ( $event_types ) {
			$template_args['meta_query'] = array(        //	phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Justification: bounded, cached, selective query on small dataset
				array(
					'key'     => 'direkttMTType',
					'value'   => $event_types,
					'compare' => 'IN',
				),
			);
		}

		$template_posts = get_posts( $template_args );

		$templates = array();

		foreach ( $template_posts as $post ) {
			$templates[] = array(
				'value' => $post->ID,
				'title' => $post->post_title,
			);
		}

		return $templates;
	}
}
