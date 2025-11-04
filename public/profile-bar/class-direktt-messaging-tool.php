<?php

defined( 'ABSPATH' ) || exit;

class Direktt_Messaging_Tool {


	private string $plugin_name;
	private string $version;

	public function __construct( string $plugin_name, string $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	public function setup_profile_tools_messaging() {
		Direktt_Profile::add_profile_bar(
			array(
				'id'              => 'send-user-message',
				'label'           => esc_html__( 'Send Message', 'direktt' ),
				'callback'        => array( $this, 'render_user_messages' ),
				'categories'      => array(),
				'tags'            => array(),
				'priority'        => 1,
				'cssEnqueueArray' => array(
					array(
						'handle' => 'direktt-profile-autocomplete-style',
						'src'    => plugins_url( '../css/autoComplete.01.css', __FILE__ ),
						'ver'    => '10.2.9',
					),
					array(
						'handle' => 'direktt-profile-message-style',
						'src'    => plugins_url( '../css/direktt-profile-message.css', __FILE__ ),
						'ver'    => $this->version,
					),
				),
				'jsEnqueueArray'  => array(
					array(
						'handle' => 'direktt-profile-autocomplete-script',
						'src'    => plugins_url( '../js/autoComplete.min.js', __FILE__ ),
						'ver'    => '10.2.9',
					),
					array(
						'handle' => 'direktt-profile-message-script',
						'src'    => plugins_url( '../js/direktt-profile-message.js', __FILE__ ),
						'deps'   => array( 'direktt-profile-autocomplete-script', 'jquery' ),
						'ver'    => $this->version,
					),
				),
			)
		);
	}

	public function render_user_messages() {
		$subscription_id = isset( $_GET['subscriptionId'] ) ? sanitize_text_field( wp_unslash( $_GET['subscriptionId'] ) ) : false;
		$profile_user   = Direktt_User::get_user_by_subscription_id( $subscription_id );

		if ( false === $subscription_id || false === $profile_user ) {
			return;
		}

		if ( isset( $_POST['send_user_message'] ) ) {

			if (
				! isset( $_POST['send_user_message_nonce'] )
				|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['send_user_message_nonce'] ) ), 'send_user_message_nonce' )
			) {
				return;
			}

			if ( ! Direktt_User::is_direktt_admin() ) {
				return;
			}

			$template_id = isset( $_POST['templateID'] ) ? sanitize_text_field( wp_unslash( $_POST['templateID'] ) ) : false;

			if ( $template_id && Direktt_Message::send_message_template(
				array( $subscription_id ),
				$template_id,
				array()
			) ) {
				$redirect_url = add_query_arg( 'status_flag', '1' );
			} else {
				$redirect_url = add_query_arg( 'status_flag', '2' );
			}

			wp_safe_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}

		$status_flag    = isset( $_GET['status_flag'] ) ? intval( $_GET['status_flag'] ) : 0;
		$status_message = '';
		if ( 1 === $status_flag ) {
			$status_message = esc_html__( 'Message sent.', 'direktt' );
		}
		if ( 2 === $status_flag ) {
			$status_message = esc_html__( 'There was an error while sending the message.', 'direktt' );
		}

		$allowed_html = wp_kses_allowed_html( 'post' );

		echo wp_kses( Direktt_Public::direktt_render_confirm_popup( 'send-message-tool-confirm', esc_html__( 'Are you sure that you want to send the message?', 'direktt' ) ), $allowed_html );
		echo wp_kses( Direktt_Public::direktt_render_loader( esc_html__( 'Sending message', 'direktt' ) ), $allowed_html );

		?>

		<form method="post">
			<div class="send-message-tool-wrapper">
				<?php if ( $status_message ) : ?>
					<div class="send-message-tool-info notice">
						<p class="send-message-tool-status"><?php echo esc_html( $status_message ); ?></p>
					</div>
				<?php endif; ?>

				<input id="autoComplete" aria-autocomplete="none" autocomplete="off">
				<input type="hidden" id="templateID" name="templateID">
				<input type="hidden" id="templateNonce" name="templateNonce" value="<?php echo esc_attr( wp_create_nonce( 'direktt_msgsend_nonce' ) ); ?>">
				<input type="hidden" name="send_user_message" id="send_user_message" value="true">

				<div class="send-message-tool-submit">
					<p>
						<input type="submit" id="sendMessageBtn" value="<?php echo esc_html__( 'Send the message', 'direktt' ); ?>" class="button button-primary button-large">
						<input type="hidden" name="send_user_message_nonce" value="<?php echo esc_attr( wp_create_nonce( 'send_user_message_nonce' ) ); ?>">
					</p>
				</div>
			</div>
		</form>

		<?php
	}
}
