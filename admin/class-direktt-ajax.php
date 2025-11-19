<?php

defined( 'ABSPATH' ) || exit;

class Direktt_Ajax {


	private string $plugin_name;
	private string $version;
	private Direktt_Api $direktt_api;

	public function __construct( string $plugin_name, string $version, Direktt_Api $direktt_api ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->direktt_api = $direktt_api;
	}

	public function ajax_get_mtemplates_taxonomies() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		$categories = Direktt_User::get_all_user_categories();
		$tags       = Direktt_User::get_all_user_tags();
		$nonce      = wp_create_nonce( 'direkttmtemplates' );

		$data = array(
			'categories' => $categories,
			'tags'       => $tags,
			'nonce'      => $nonce,
		);

		wp_send_json_success( $data, 200 );
	}

	public function ajax_send_mtemplates_message() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		$nonce = ( isset( $_POST['nonce'] ) ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;

		if ( $nonce && wp_verify_nonce( $nonce, 'direkttmtemplates' ) ) {

			$categories = ( isset( $_POST['categories'] ) ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['categories'] ) ), true ) : false;
			$tags       = ( isset( $_POST['tags'] ) ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['tags'] ) ), true ) : false;

			$user_set = ( isset( $_POST['userSet'] ) ) ? sanitize_text_field( wp_unslash( $_POST['userSet'] ) ) : false;
			$consent  = filter_input( INPUT_POST, 'consent', FILTER_VALIDATE_BOOLEAN ) ?? false;

			$message_template_id = ( isset( $_POST['postId'] ) ) ? sanitize_text_field( wp_unslash( $_POST['postId'] ) ) : false;

			if ( $user_set && $message_template_id ) {

				$subscription_ids = array();

				if ( 'all' === $user_set ) {
					$subscription_ids = $this->get_subscription_ids_from_terms( array(), array(), true, $consent );
					Direktt_Message::send_message_template( $subscription_ids, $message_template_id );
				}

				if ( 'selected' === $user_set ) {
					$subscription_ids = $this->get_subscription_ids_from_terms( $categories, $tags, false, $consent );
					Direktt_Message::send_message_template( $subscription_ids, $message_template_id );
				}

				if ( 'admin' === $user_set ) {
					Direktt_Message::send_message_template_to_admin( $message_template_id );
				}
			}

			$data = array(
				'succ' => true,
			);

			wp_send_json_success( $data, 200 );
			return;
		}

		wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
	}

	public function ajax_get_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		$templates = Direktt_Message_Template::get_templates( array( 'all', 'none' ) );

		$data = array(
			'api_key'               => get_option( 'direktt_api_key' ) ? esc_attr( get_option( 'direktt_api_key' ) ) : '',
			'direktt_channel_title' => get_option( 'direktt_channel_title' ) ? esc_attr( get_option( 'direktt_channel_title' ) ) : '',
			'direktt_channel_id'    => get_option( 'direktt_channel_id' ) ? esc_attr( get_option( 'direktt_channel_id' ) ) : '',
			'forceReload'           => wp_rand( 1, 100000 ),

			'isSSL'                 => stripos( get_site_url(), 'https://' ) === 0,
			'redirect_url'          => get_option( 'unauthorized_redirect_url' ) ? esc_attr( get_option( 'unauthorized_redirect_url' ) ) : '',

			'pairing_prefix'        => get_option( 'direktt_pairing_prefix' ) ? esc_attr( get_option( 'direktt_pairing_prefix' ) ) : '',
			'pairing_succ_template' => get_option( 'direktt_pairing_succ_template' ) ? esc_attr( get_option( 'direktt_pairing_succ_template' ) ) : '',

			'qr_code_logo_url'      => get_option( 'direktt_qr_code_logo_url' ) ? esc_attr( get_option( 'direktt_qr_code_logo_url' ) ) : '',
			'qr_code_color'         => get_option( 'direktt_qr_code_color' ) ? esc_attr( get_option( 'direktt_qr_code_color' ) ) : '',
			'qr_code_bckg_color'    => get_option( 'direktt_qr_code_bckg_color' ) ? esc_attr( get_option( 'direktt_qr_code_bckg_color' ) ) : '',

			'templates'             => $templates,
		);

		wp_send_json_success( $data, 200 );
	}

	public function ajax_get_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		$data = array(
			'direktt_channel_title' => get_option( 'direktt_channel_title' ) ? esc_attr( get_option( 'direktt_channel_title' ) ) : '',
			'direktt_channel_id'    => get_option( 'direktt_channel_id' ) ? esc_attr( get_option( 'direktt_channel_id' ) ) : '',
			'isSSL'                 => stripos( get_site_url(), 'https://' ) === 0,
			'forceReload'           => wp_rand( 1, 100000 ),
		);

		wp_send_json_success( $data, 200 );
	}

	public function ajax_get_activation_data() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		$api_key = get_option( 'direktt_api_key' ) ? esc_attr( get_option( 'direktt_api_key' ) ) : '';

		$url = 'https://getDataForChannel-lnkonwpiwa-uc.a.run.app';

		$data = array();

		$response = wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode( $data ),
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-type'  => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['success'] ) && $data['success'] && ! empty( $data['data'] ) ) {

			$id                       = $data['data']['id'] ?? null;
			$title                    = $data['data']['title'] ?? null;
			$domain                   = $data['data']['domain'] ?? null;
			$activated_at             = $data['data']['activatedAt'] ?? null;
			$image                    = $data['data']['image'] ?? null;
			$color                    = $data['data']['color'] ?? null;
			$handle                   = $data['data']['handle'] ?? null;
			$default_subscription_uid = $data['data']['defaultSubscriptionUid'] ?? null;
			$count                    = $data['data']['count'] ?? null;

			if ( ! is_null( $domain ) && ! is_null( $activated_at ) ) {
				$existing_title = get_option( 'direktt_channel_title' );
				if ( ! is_null( $title ) && $title !== $existing_title ) {
					update_option( 'direktt_channel_title', $title );
				}

				$existing_id = get_option( 'direktt_channel_id' );
				if ( ! is_null( $id ) && $id !== $existing_id ) {
					update_option( 'direktt_channel_id', $id );
				}
			}
		}

		$local_post_count = wp_count_posts( 'direkttusers' );
		$local_count      = intval( $local_post_count->publish );

		$ret_data = array(
			'localCount' => $local_count,
		);

		if ( ! is_null( $activated_at ) ) {
			$ret_data['activatedAt'] = $activated_at;
		}
		if ( ! is_null( $domain ) ) {
			$ret_data['domain'] = $domain;
		}
		if ( ! is_null( $count ) ) {
			$ret_data['count'] = $count;
		}
		if ( ! is_null( $title ) ) {
			$ret_data['title'] = $title;
		}
		if ( ! is_null( $id ) ) {
			$ret_data['id'] = $id;
		}

		wp_send_json_success( $ret_data, 200 );
	}

	public function ajax_get_marketing_consent() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		$nonce = ( isset( $_POST['nonce'] ) ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $this->plugin_name . '-direktt-users' ) ) {
			return;
		}

		$post_id = ( isset( $_POST['postId'] ) ) ? sanitize_text_field( wp_unslash( $_POST['postId'] ) ) : false;

		$data = array(
			'direktt_user_id'    => get_post_meta( $post_id, 'direktt_user_id', true ),
			'marketing_consent'  => get_post_meta( $post_id, 'direktt_marketing_consent_status', true ),
			'admin_subscription' => get_post_meta( $post_id, 'direktt_admin_subscription', true ),
			'membership_id'      => get_post_meta( $post_id, 'direktt_membership_id', true ),
			'avatar_url'         => get_post_meta( $post_id, 'direktt_avatar_url', true ),
		);

		wp_send_json_success( $data, 200 );
	}

	public function ajax_get_user_events() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		$nonce = ( isset( $_POST['nonce'] ) ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;

		if ( ! $nonce || ! wp_verify_nonce( $nonce, $this->plugin_name . '-direktt-users' ) ) {
			return;
		}

		$post_id = ( isset( $_POST['postId'] ) ) ? sanitize_text_field( wp_unslash( $_POST['postId'] ) ) : false;
		$page    = ( isset( $_POST['page'] ) ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : false;

		$direktt_user_id = get_post_meta( $post_id, 'direktt_user_id', true );

		global $wpdb;
		$table_name = $wpdb->prefix . 'direktt_events';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Justification: selective query on small dataset
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom database used
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Justification: table name is not prepared
		// phpcs:disable PluginCheck.Security.DirectDB.UnescapedDBParameter -- Justification: table name is not prepared

		if ( intval( $page ) === 0 ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE direktt_user_id = %s ORDER BY ID DESC LIMIT 20",
					$direktt_user_id
				)
			);
		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table_name} WHERE direktt_user_id = %s AND ID < %d ORDER BY ID DESC LIMIT 20",
					$direktt_user_id,
					intval( $page )
				)
			);
		}

		// phpcs:enable

		$data = $results;

		wp_send_json_success( $data, 200 );
	}

	public function ajax_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		$choice = ( isset( $_POST['api_key'] ) ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : false;

		$url_choice = ( isset( $_POST['redirect_url'] ) ) ? sanitize_text_field( wp_unslash( $_POST['redirect_url'] ) ) : false;

		$activation_status = isset( $_POST['activation_status'] ) ? filter_var( wp_unslash( $_POST['activation_status'] ), FILTER_VALIDATE_BOOLEAN ) : false;

		$pairing_prefix = ( isset( $_POST['pairing_prefix'] ) ) ? sanitize_text_field( wp_unslash( $_POST['pairing_prefix'] ) ) : false;

		$pairing_succ_template = ( isset( $_POST['pairing_succ_template'] ) ) ? sanitize_text_field( wp_unslash( $_POST['pairing_succ_template'] ) ) : false;

		$reset_pairings = ( isset( $_POST['reset_pairings'] ) ) ? sanitize_text_field( wp_unslash( $_POST['reset_pairings'] ) ) : false;

		$qr_code_logo_url = ( isset( $_POST['qr_code_logo_url'] ) ) ? sanitize_text_field( wp_unslash( $_POST['qr_code_logo_url'] ) ) : false;

		$qr_code_color = ( isset( $_POST['qr_code_color'] ) ) ? sanitize_text_field( wp_unslash( $_POST['qr_code_color'] ) ) : false;

		$qr_code_bckg_color = ( isset( $_POST['qr_code_bckg_color'] ) ) ? sanitize_text_field( wp_unslash( $_POST['qr_code_bckg_color'] ) ) : false;

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $this->plugin_name . '-settings' ) ) {

			wp_send_json_error( new WP_Error( 'Unauthorized', 'Nonce is not valid' ), 401 );
			exit;
		} else {
			if ( $choice && '' !== $choice ) {

				$current_api = get_option( 'direktt_api_key' );

				if ( $current_api !== $choice || ! $activation_status ) {

					update_option( 'direktt_api_key', $choice );

					$url = 'https://activatechannel-lnkonwpiwa-uc.a.run.app';

					$data = array(
						'domain' => get_site_url( null, '' ),
					);

					$response = wp_remote_post(
						$url,
						array(
							'body'    => wp_json_encode( $data ),
							'timeout' => 30,
							'headers' => array(
								'Authorization' => 'Bearer ' . $choice,
								'Content-type'  => 'application/json',
							),
						)
					);

					if ( is_wp_error( $response ) ) {
						wp_send_json_error( $response, 500 );
						return;
					}

					if ( 200 !== $response['response']['code'] && 201 !== $response['response']['code'] ) {

						wp_send_json_error( new WP_Error( 'Unauthorized', 'API Key validation failed' ), 401 );
						return;
					}
				}
			} else {
				delete_option( 'direktt_api_key' );
			}

			if ( $url_choice ) {
				update_option( 'unauthorized_redirect_url', $url_choice );
			} else {
				delete_option( 'unauthorized_redirect_url' );
			}

			if ( $pairing_prefix ) {
				update_option( 'direktt_pairing_prefix', $pairing_prefix );
			} else {
				delete_option( 'direktt_pairing_prefix' );
			}

			if ( $pairing_succ_template ) {
				update_option( 'direktt_pairing_succ_template', $pairing_succ_template );
			} else {
				delete_option( 'direktt_pairing_succ_template' );
			}

			if ( 'true' === $reset_pairings && $reset_pairings ) {
				$this->delete_user_meta_for_all_users( 'direktt_user_pair_code' );
			}

			if ( $qr_code_logo_url ) {
				update_option( 'direktt_qr_code_logo_url', $qr_code_logo_url );
			} else {
				delete_option( 'direktt_qr_code_logo_url' );
			}

			if ( $qr_code_color ) {
				update_option( 'direktt_qr_code_color', $qr_code_color );
			} else {
				delete_option( 'direktt_qr_code_color' );
			}

			if ( $qr_code_bckg_color ) {
				update_option( 'direktt_qr_code_bckg_color', $qr_code_bckg_color );
			} else {
				delete_option( 'direktt_qr_code_bckg_color' );
			}
		}

		$data = array();
		wp_send_json_success( $data, 200 );
	}

	public function ajax_sync_users() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Access to API is unauthorized.' ), 401 );
			return;
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), $this->plugin_name . '-settings' ) ) {
			wp_send_json_error( new WP_Error( 'Unauthorized', 'Nonce is not valid' ), 401 );
			exit;
		}

		// Default: 20 per batch.
		$batch_size = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 5;
		$offset     = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;

		// Fetch subscriptions list only once, cache in transient for this session.
		$transient_key      = 'direktt_sync_subscriptions_' . get_current_user_id();
		$subscriptions_data = get_transient( $transient_key );
		if ( 0 === $offset || false === $subscriptions_data ) {
			$subscriptions_data = $this->get_remote_subscriptions_full();
			if ( ! $subscriptions_data ) {
				wp_send_json_error( 'Unable to fetch subscriptions data', 500 );
				return;
			}
			// cache for 1 min; enough for a sync session.
			set_transient( $transient_key, $subscriptions_data, 1 * MINUTE_IN_SECONDS );
			$this->cleanup_unsubscribed_users( $subscriptions_data['subscriptions'] );
		}

		$all_subscriptions = isset( $subscriptions_data['subscriptions'] ) ? $subscriptions_data['subscriptions'] : array();
		$total             = count( $all_subscriptions );

		// Safety net.
		if ( $offset >= $total ) {
			// clean up transient.
			delete_transient( $transient_key );
			wp_send_json_success(
				array(
					'finished' => true,
					'current'  => $total,
					'total'    => $total,
				)
			);
			return;
		}

		// Slice out one batch.
		$batch = array_slice( $all_subscriptions, $offset, $batch_size );

		foreach ( $batch as $subscription ) {
			$subscription_id          = $subscription['subscriptionId'] ?? null;
			$display_name             = $subscription['displayName'] ?? null;
			$avatar_url               = $subscription['avatarUrl'] ?? null;
			$admin_subscription       = true === $subscription['adminSubscription'] ?? null;
			$membership_id            = $subscription['membershipId'] ?? null;
			$marketing_consent_ctatus = true === $subscription['marketingConsentStatus'] ?? null;

			$this->direktt_api->subscribe_user(
				$subscription_id,
				$display_name,
				$avatar_url,
				$admin_subscription,
				$membership_id,
				$marketing_consent_ctatus,
				true
			);
		}

		$current  = min( $offset + $batch_size, $total );
		$finished = ( $current >= $total );

		if ( $finished ) {
			delete_transient( $transient_key );
		}

		wp_send_json_success(
			array(
				'finished'  => $finished,
				'current'   => $current,
				'total'     => $total,
				'batchDone' => count( $batch ),
				'details'   => array_map( fn( $s ) => $s['displayName'] ?? null, $batch ),
			)
		);
	}

	/**
	 * Helper to fetch subscriptions and return full response as array
	 */
	private function get_remote_subscriptions_full() {
		$api_key = get_option( 'direktt_api_key' );
		if ( ! $api_key ) {
			return false;
		}

		$url = 'https://getsubscriptionsforchannel-lnkonwpiwa-uc.a.run.app';

		$response = wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode( array() ),
				'timeout' => 30,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-type'  => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return $data;
	}


	private function cleanup_unsubscribed_users( $all_remote_subscriptions ) {
		$remote_user_ids_lookup = array();

		foreach ( $all_remote_subscriptions as $subscription ) {
			if ( ! empty( $subscription['subscriptionId'] ) ) {
				$remote_user_ids_lookup[ $subscription['subscriptionId'] ] = true;
			}
		}

		// Clean up unsubscribed users.
		$args       = array(
			'post_type'      => 'direkttusers',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'fields'         => 'ids',
		);
		$user_query = new WP_Query( $args );

		if ( ! $user_query->have_posts() ) {
			return;
		}

		// 3. Unsubscribe locals not found remotely.
		foreach ( $user_query->posts as $post_id ) {
			$local_id = get_post_meta( $post_id, 'direktt_user_id', true );
			if ( $local_id && ! isset( $remote_user_ids_lookup[ $local_id ] ) ) {
				$this->direktt_api->unsubscribe_user( $local_id );
			}
		}
	}

	private function delete_user_meta_for_all_users( $meta_key ) {
		delete_metadata( 'user', 0, $meta_key, '', true );
	}

	private function has_published_direkttusers_posts() {
		$args  = array(
			'post_type'      => 'direkttusers',
			'post_status'    => 'publish',
			'posts_per_page' => 1, // Just need to check existence.
			'fields'         => 'ids',
		);
		$query = new WP_Query( $args );
		return ( $query->have_posts() );
	}

	private function get_subscription_ids_from_terms( $category_ids = array(), $tag_ids = array(), $empty_allowed = true, $marketing_consent = false ) {
		// Ensure inputs are arrays.
		$category_ids = (array) $category_ids;
		$tag_ids      = (array) $tag_ids;

		// Build tax_query.
		$tax_query = array( 'relation' => 'OR' );

		if ( ! empty( $category_ids ) ) {
			$tax_query[] = array(
				'taxonomy' => 'direkttusercategories',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			);
		}
		if ( ! empty( $tag_ids ) ) {
			$tax_query[] = array(
				'taxonomy' => 'direkttusertags',
				'field'    => 'term_id',
				'terms'    => $tag_ids,
			);
		}

		$meta_query = array(
			'relation' => 'OR',
			// Case 1: Key doesn't exist (so admin subscription isn't set).
			array(
				'key'     => 'direktt_admin_subscription',
				'compare' => 'NOT EXISTS',
			),
			// Case 2: Key exists, but is not true or 1.
			array(
				'key'     => 'direktt_admin_subscription',
				'value'   => array( '1', 'true' ),
				'compare' => 'NOT IN',
			),
		);

		// If marketing consent is required, add it to the meta_query.
		if ( $marketing_consent ) {
			$meta_query = array(
				'relation' => 'AND',
				// Admin subscription rules (wrap previous OR in its own array).
				array(
					'relation' => 'OR',
					array(
						'key'     => 'direktt_admin_subscription',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'direktt_admin_subscription',
						'value'   => array( '1', 'true' ),
						'compare' => 'NOT IN',
					),
				),
				// Must have marketing consent set to '1' (true).
				array(
					'key'     => 'direktt_marketing_consent_status',
					'value'   => '1',
					'compare' => '=',
				),
			);
		}

		$args = array(
			'post_type'              => 'direkttusers',
			'posts_per_page'         => 500,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
			'tax_query'              => $tax_query,     // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Justification: bounded, cached, selective query on small dataset
			'meta_query'             => $meta_query,    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- - Justification: bounded, cached, selective query on small dataset
			'post_status'            => 'publish',
		);

		if ( count( $tax_query ) === 1 && $empty_allowed ) {
			unset( $args['tax_query'] );
		} elseif ( count( $tax_query ) === 1 && ! $empty_allowed ) {
			return array();
		}

		$query    = new WP_Query( $args );
		$post_ids = $query->posts;

		// Get subscriptionId meta values.
		$subscription_ids = array();
		if ( ! empty( $post_ids ) ) {
			foreach ( $post_ids as $post_id ) {
				$sub_id = get_post_meta( $post_id, 'direktt_user_id', true );
				if ( ! empty( $sub_id ) ) {
					$subscription_ids[] = $sub_id;
				}
			}
		}

		return $subscription_ids;
	}

	public function ajax_get_mtemplates_profile_message() {

		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json( array( 'status' => 'post_id_failed' ), 400 );
		}

		$post_id = intval( $_POST['post_id'] );

		$post = get_post( $post_id );

		// Validate that post exists and the current user can perform the action.

		if ( $post && Direktt_Public::direktt_ajax_check_user( $post ) ) {

			// Verify nonce for security against CSRF attacks.

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'direktt_msgsend_nonce' ) ) {
				wp_send_json( array( 'status' => 'nonce_failed' ), 401 );
			}

			$templates = Direktt_Message_Template::get_templates( array( 'all', 'individual' ) );

			wp_send_json_success( $templates, 200 );
		} else {

			// User not authorized or post not found.
			wp_send_json( array( 'status' => 'non_authorized' ), 401 );
		}
	}

	public function ajax_get_users_taxonomy_service() {

		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json( array( 'status' => 'post_id_failed' ), 400 );
		}

		$post_id = intval( $_POST['post_id'] );

		$post = get_post( $post_id );

		if ( $post && Direktt_Public::direktt_ajax_check_user( $post ) ) {

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'user_list_nonce' ) ) {
				wp_send_json( array( 'status' => 'nonce_failed' ), 401 );
			}

			$users = Direktt_User::get_users();

			wp_send_json_success( $users, 200 );
		} else {

			// User not authorized or post not found.
			wp_send_json( array( 'status' => 'non_authorized' ), 401 );
		}
	}
}
