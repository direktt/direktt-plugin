<?php

defined( 'ABSPATH' ) || exit;

class Direktt_User {


	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public static function get_user_by_post_id( $direktt_user_post_id ) {
		$args = array(
			'post_type'      => 'direkttusers',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post__in'       => array( $direktt_user_post_id ),
		);

		$posts = get_posts( $args );

		$post_obj = false;

		if ( ! empty( $posts ) ) {
			$post_id = $posts[0];

			$assigned_categories = wp_get_post_terms( $post_id, 'direkttusercategories', array( 'fields' => 'names' ) );
			$assigned_tags       = wp_get_post_terms( $post_id, 'direkttusertags', array( 'fields' => 'names' ) );

			$post_obj = array(
				'ID'                               => $post_id,
				'direktt_display_name'             => get_the_title( $post_id ),
				'direktt_membership_id'            => get_post_meta( $post_id, 'direktt_membership_id', true ),
				'direktt_user_id'                  => get_post_meta( $post_id, 'direktt_user_id', true ),
				'direktt_admin_subscription'       => ( get_post_meta( $post_id, 'direktt_admin_subscription', true ) === '1' ),
				'direktt_marketing_consent_status' => ( get_post_meta( $post_id, 'direktt_marketing_consent_status', true ) === '1' ),
				'direktt_avatar_url'               => get_post_meta( $post_id, 'direktt_avatar_url', true ),
				'direktt_user_categories'          => $assigned_categories,
				'direktt_user_tags'                => $assigned_tags,
				'direktt_notes'                    => get_post_field( 'post_content', $post_id ),
			);
		}

		return $post_obj;
	}

	public static function direktt_get_current_user() {
		global $direktt_user;

		return $direktt_user;
	}

	public static function get_user_by_subscription_id( $direktt_user_id ) {
		$args = array(
			'post_type'      => 'direkttusers',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'fields'         => 'ids',
			'meta_query'     => array(      // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Justification: bounded, cached, selective query on small dataset
				array(
					'key'   => 'direktt_user_id',
					'value' => $direktt_user_id,
				),
			),
		);

		$posts = get_posts( $args );

		$post_obj = false;

		if ( ! empty( $posts ) ) {
			$post_id = $posts[0];

			$assigned_categories = wp_get_post_terms( $post_id, 'direkttusercategories', array( 'fields' => 'names' ) );
			$assigned_tags       = wp_get_post_terms( $post_id, 'direkttusertags', array( 'fields' => 'names' ) );

			$post_obj = array(
				'ID'                               => $post_id,
				'direktt_display_name'             => get_the_title( $post_id ),
				'direktt_membership_id'            => get_post_meta( $post_id, 'direktt_membership_id', true ),
				'direktt_user_id'                  => $direktt_user_id,
				'direktt_admin_subscription'       => ( get_post_meta( $post_id, 'direktt_admin_subscription', true ) === '1' ),
				'direktt_marketing_consent_status' => ( get_post_meta( $post_id, 'direktt_marketing_consent_status', true ) === '1' ),
				'direktt_avatar_url'               => get_post_meta( $post_id, 'direktt_avatar_url', true ),
				'direktt_user_categories'          => $assigned_categories,
				'direktt_user_tags'                => $assigned_tags,
				'direktt_notes'                    => get_post_field( 'post_content', $post_id ),
			);
		}

		return $post_obj;
	}

	public static function get_user_by_membership_id( $direktt_membership_id ) {
		$args = array(
			'post_type'      => 'direkttusers',
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'fields'         => 'ids',
			'meta_query'     => array(      // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Justification: bounded, cached, selective query on small dataset
				array(
					'key'   => 'direktt_membership_id',
					'value' => $direktt_membership_id,
				),
			),
		);

		$posts = get_posts( $args );

		$post_obj = false;

		if ( ! empty( $posts ) ) {
			$post_id = $posts[0];

			$assigned_categories = wp_get_post_terms( $post_id, 'direkttusercategories', array( 'fields' => 'names' ) );
			$assigned_tags       = wp_get_post_terms( $post_id, 'direkttusertags', array( 'fields' => 'names' ) );

			$post_obj = array(
				'ID'                               => $post_id,
				'direktt_display_name'             => get_the_title( $post_id ),
				'direktt_membership_id'            => $direktt_membership_id,
				'direktt_user_id'                  => get_post_meta( $post_id, 'direktt_user_id', true ),
				'direktt_admin_subscription'       => ( get_post_meta( $post_id, 'direktt_admin_subscription', true ) === '1' ),
				'direktt_marketing_consent_status' => ( get_post_meta( $post_id, 'direktt_marketing_consent_status', true ) === '1' ),
				'direktt_avatar_url'               => get_post_meta( $post_id, 'direktt_avatar_url', true ),
				'direktt_user_categories'          => $assigned_categories,
				'direktt_user_tags'                => $assigned_tags,
				'direktt_notes'                    => get_post_field( 'post_content', $post_id ),
			);
		}

		return $post_obj;
	}

	public static function is_direktt_admin() {

		$direktt_user = self::direktt_get_current_user();

		if ( isset( $direktt_user['direktt_admin_subscription'] ) && $direktt_user['direktt_admin_subscription'] ) {
			return true;
		}

		return false;
	}

	public static function get_all_user_categories() {

		$category_terms = get_terms(
			array(
				'taxonomy'   => 'direkttusercategories',
				'hide_empty' => false,
			)
		);

		$all_categories = array();

		foreach ( $category_terms as $term ) {
			$all_categories[] = array(
				'value' => $term->term_id,
				'name'  => $term->name,
			);
		}

		return $all_categories;
	}

	public static function get_user_categories( $direktt_user_post_id ) {

		$term_ids     = array();
		$term_objects = get_the_terms( $direktt_user_post_id, 'direkttusercategories' );
		if ( ! is_wp_error( $term_objects ) && ! empty( $term_objects ) ) {
			$term_ids = wp_list_pluck( $term_objects, 'term_id' );
		}

		return $term_ids;
	}

	public static function get_all_user_tags() {

		$tag_terms = get_terms(
			array(
				'taxonomy'   => 'direkttusertags',
				'hide_empty' => false,
			)
		);

		$all_tags = array();

		foreach ( $tag_terms as $term ) {
			$all_tags[] = array(
				'value' => $term->term_id,
				'name'  => $term->name,
			);
		}
		return $all_tags;
	}

	public static function get_user_tags( $direktt_user_post_id ) {

		$term_ids     = array();
		$term_objects = get_the_terms( $direktt_user_post_id, 'direkttusertags' );
		if ( ! is_wp_error( $term_objects ) && ! empty( $term_objects ) ) {
			$term_ids = wp_list_pluck( $term_objects, 'term_id' );
		}

		return $term_ids;
	}

	public static function get_direktt_user_by_wp_user( $wp_user ) {

		$direktt_user_id = false;

		$direktt_user_id = get_user_meta( $wp_user->ID, 'direktt_test_user_id', true );

		if ( ! $direktt_user_id ) {
			$direktt_user_id = get_user_meta( $wp_user->ID, 'direktt_user_id', true );
		}

		if ( $direktt_user_id ) {
			return self::get_user_by_post_id( $direktt_user_id );
		} else {
			return false;
		}
	}

	public static function has_direktt_taxonomies( $direktt_user, $categories, $tags ) {
		if ( empty( $direktt_user ) || ! isset( $direktt_user['ID'] ) ) {
			return false;
		}

		// Get assigned category and tag slugs.
		$assigned_categories = wp_get_post_terms( $direktt_user['ID'], 'direkttusercategories', array( 'fields' => 'slugs' ) );
		$assigned_tags       = wp_get_post_terms( $direktt_user['ID'], 'direkttusertags', array( 'fields' => 'slugs' ) );

		// If any input category matches assigned categories.
		if ( ! empty( $categories ) && ! empty( $assigned_categories ) ) {
			if ( array_intersect( $categories, $assigned_categories ) ) {
				return true;
			}
		}

		// If any input tag matches assigned tags.
		if ( ! empty( $tags ) && ! empty( $assigned_tags ) ) {
			if ( array_intersect( $tags, $assigned_tags ) ) {
				return true;
			}
		}

		return false;
	}

	public static function get_users( $include_admin = false ) {
		$user_args = array(
			'post_type'              => 'direkttusers',
			'post_status'            => 'publish',
			'posts_per_page'         => 1000,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
		);

		if ( ! $include_admin ) {
			$user_args['meta_query'] = array(            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Justification: bounded, cached, selective query on small dataset
				'relation' => 'OR', // so posts with no meta or meta not 1 are included.
				array(
					'key'     => 'direktt_admin_subscription',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'direktt_admin_subscription',
					'value'   => '1',
					'compare' => '!=',
				),
			);
		}

		$user_posts = get_posts( $user_args );

		$users = array();

		foreach ( $user_posts as $post ) {
			$users[] = array(
				'value' => $post->ID,
				'title' => $post->post_title,
			);
		}

		return $users;
	}

	public static function get_related_user( $wp_user_id ) {
		$direktt_user_id      = get_user_meta( $wp_user_id, 'direktt_user_id', true );
		$direktt_user_related = false;
		if ( $direktt_user_id ) {
			$direktt_user_related = self::get_user_by_post_id( $direktt_user_id );
		}
		return $direktt_user_related;
	}

	public static function get_related_wp_user_id( $direktt_user ) {
		$wp_user = get_users(
			array(
				'meta_key'       => 'direktt_user_id',             // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Justification: bounded, selective query on small dataset
				'meta_value'     => $direktt_user['ID'],                         // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Justification: bounded, selective query on small dataset
				'posts_per_page' => 1,
				'fields'         => 'ID',
			)
		);
		if ( ! empty( $wp_user ) ) {
			return ( $wp_user[0] );
		}
		return false;
	}

	public static function pair_wp_user_by_code( $pair_code, $subscription_id ) {

		$wp_user = get_users(
			array(
				'meta_key'       => 'direktt_user_pair_code',             // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Justification: bounded, selective query on small dataset
				'meta_value'     => $pair_code,                         // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Justification: bounded, selective query on small dataset
				'posts_per_page' => 1,
				'fields'         => 'ID',
			)
		);

		if ( ! empty( $wp_user ) ) {

			$direktt_user_post = self::get_user_by_subscription_id( $subscription_id );

			update_user_meta( $wp_user[0], 'direktt_user_id', $direktt_user_post['ID'] );

			delete_user_meta( $wp_user[0], 'direktt_user_pair_code' );

			$pairing_message_template = get_option( 'direktt_pairing_succ_template', false );

			if ( $pairing_message_template ) {

				Direktt_Message::send_message_template(
					array( $subscription_id ),
					$pairing_message_template,
					array(
						'wp_user' => get_user_by( 'id', $wp_user[0] )->user_login,
					)
				);
			} else {

				$push_notification_message = array(
					'type'    => 'text',
					'content' => 'Your WP user have been successfuly paired with your Direktt user',
				);

				Direktt_Message::send_message( array( $subscription_id => $push_notification_message ) );
			}
		}
	}
}
