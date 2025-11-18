<?php

defined( 'ABSPATH' ) || exit;

class Direktt_Message {


	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public static function send_message( $messages ) {
		$api_key = get_option( 'direktt_api_key' ) ? esc_attr( get_option( 'direktt_api_key' ) ) : '';
		$url     = 'https://sendbulkmessages-lnkonwpiwa-uc.a.run.app';

		$data = array();

		foreach ( $messages as $key => $value ) {
			$obj                          = new stdClass();
			$obj->subscriptionId          = $key;
			$obj->pushNotificationMessage = $value;
			$data[]                       = $obj;
		}

		$response = wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode(
					array(
						'messages' => $data,
					)
				),
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-type'  => 'application/json',
				),
			)
		);

		return true;
	}

	public static function update_message( $subscription_uid, $message_uid, $content ) {
		$api_key = get_option( 'direktt_api_key' ) ? esc_attr( get_option( 'direktt_api_key' ) ) : '';
		$url     = 'https://updateMessage-lnkonwpiwa-uc.a.run.app';

		$data = array(
			'subscriptionUid' => $subscription_uid,
			'messageUid'      => $message_uid,
			'content'         => $content,
		);

		$response = wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-type'  => 'application/json',
				),
			)
		);
	}

	public static function replace_tags_in_template( $input_string, $replacements, $direktt_user_id = null ) {
		if ( null === $input_string ) {
			return null;
		}

		return preg_replace_callback(
			'/#([^#]+)#/',
			function ( $matches ) use ( $replacements, $direktt_user_id ) {
				$tag = $matches[1];

				// Find replacement or default to the tag.
				$value = array_key_exists( $tag, $replacements ) ? $replacements[ $tag ] : $tag;

				// Apply filter, pass value and user.
				return apply_filters( 'direktt/message/template/' . $tag , $value, $direktt_user_id );
			},
			$input_string
		);
	}



	public static function send_message_template( $direktt_user_ids, $message_template_id, $replacements = array() ) {
		$api_key = get_option( 'direktt_api_key' ) ? esc_attr( get_option( 'direktt_api_key' ) ) : '';

		$url = 'https://sendbulkmessages-lnkonwpiwa-uc.a.run.app';

		$message_template = get_post_meta( $message_template_id, 'direkttMTJson', true );

		if ( $message_template ) {

			$data = array();

			foreach ( $direktt_user_ids as $key => $value ) {

				$messages = self::replace_tags_in_template( $message_template, $replacements, $value );
				$messages = json_decode( $messages );

				foreach ( $messages as $message ) {

					if ( is_array( $message->content ) || is_object( $message->content ) ) {
						$message->content = wp_json_encode( $message->content );
					}
					if ( ! is_null( $message ) ) {
						$obj                          = new stdClass();
						$obj->subscriptionId          = $value;
						$obj->pushNotificationMessage = $message;
						$data[]                       = $obj;
					}
				}
			}

			$response = wp_remote_post(
				$url,
				array(
					'body'    => wp_json_encode(
						array(
							'messages' => $data,
						)
					),
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-type'  => 'application/json',
					),
				)
			);

			return $message;
		}
		return false;
	}

	public function direktt_display_name_filter( $value, $direktt_user_id ) {

		if ( $direktt_user_id ) {
			$direktt_user = Direktt_User::get_user_by_subscription_id( $direktt_user_id );
			$value        = $direktt_user['direktt_display_name'];
		}

		return $value;
	}

	public function direktt_channel_name_filter( $value, $direktt_user_id ) {

		$direktt_channel_title = get_option( 'direktt_channel_title' ) ? esc_attr( get_option( 'direktt_channel_title' ) ) : $value;
		return $direktt_channel_title;
	}

	public static function send_message_to_admin( $message ) {
		$api_key = get_option( 'direktt_api_key' ) ? esc_attr( get_option( 'direktt_api_key' ) ) : '';
		$url     = 'https://sendadminmessage-lnkonwpiwa-uc.a.run.app';

		$data = array(
			'pushNotificationMessage' => $message,
		);

		$response = wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode( $data ),
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-type'  => 'application/json',
				),
			)
		);
	}

	public static function send_message_template_to_admin( $message_template_id, $replacements = array() ) {
		$api_key = get_option( 'direktt_api_key' ) ? esc_attr( get_option( 'direktt_api_key' ) ) : '';
		$url     = 'https://sendadminmessage-lnkonwpiwa-uc.a.run.app';

		$messages = get_post_meta( $message_template_id, 'direkttMTJson', true );

		if ( $messages ) {

			$messages = self::replace_tags_in_template( $messages, $replacements );
			$messages = json_decode( $messages );

			foreach ( $messages as $message ) {
				if ( is_array( $message->content ) || is_object( $message->content ) ) {
					$message->content = wp_json_encode( $message->content );
				}

				if ( ! is_null( $message ) ) {

					$data = array(
						'pushNotificationMessage' => $message,
					);

					$response = wp_remote_post(
						$url,
						array(
							'body'    => wp_json_encode( $data ),
							'headers' => array(
								'Authorization' => 'Bearer ' . $api_key,
								'Content-type'  => 'application/json',
							),
						)
					);
				}
			}

			return true;
		}
		return false;
	}
}
