<?php

class Direktt_Message_Template
{
	private string $plugin_name;
	private string $version;

	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	static function get_templates( $event_types = [] )
	{
		$template_args = [
			'post_type'      => 'direkttmtemplates',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		if( $event_types ){
			$template_args['meta_query'] = [
				[
					'key'     => 'direkttMTType',
					'value'   => $event_types,
					'compare' => 'IN',
				]
			];
		}
		
		$template_posts = get_posts($template_args);

		$templates = [];

		foreach ($template_posts as $post) {
			$templates[] = array(
				"value" => $post->ID,
				"title" => 	$post->post_title
			);
		}

		return $templates;
	}
}
