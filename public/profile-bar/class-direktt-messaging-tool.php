<?php

class Direktt_Messaging_Tool
{
    public function setup_profile_tools_messaging()
    {
        Direktt_Profile::add_profile_bar(
            array(
                "id" => "send-user-message",
                "label" => esc_html__('Send Message to User', 'direktt'),
                "callback" => [$this, 'render_user_messages'],
                "categories" => [],
                "tags" => [],
                "priority" => 1,
                "cssEnqueueArray" => [
                    array(
                        "handle" => "direktt-profile-autocomplete-style",
                        "src" => plugins_url('../css/autoComplete.01.css', __FILE__)
                    ),
                    array(
                        "handle" => "direktt-profile-message-style",
                        "src" => plugins_url('../css/direktt-profile-message.css', __FILE__)
                    )
                ],
                "jsEnqueueArray" => [
                    array(
                        "handle" => "direktt-profile-autocomplete-script",
                        "src" => plugins_url('../js/autoComplete.min.js', __FILE__)
                    ),
                    array(
                        "handle" => "direktt-profile-message-script",
                        "src" => plugins_url('../js/direktt-profile-message.js', __FILE__),
                        "deps" => array("direktt-profile-autocomplete-script")
                    )
                ]
            )
        );
    }

    public function render_user_messages()
    {
        $subscriptionId = isset($_GET['subscriptionId']) ? sanitize_text_field(wp_unslash($_GET['subscriptionId'])) : false;
        $profile_user   = Direktt_User::get_user_by_subscription_id($subscriptionId);

        if ($subscriptionId === false || $profile_user === false) {
            return;
        }

        if (isset($_POST['send_user_message'])) {

            if (
                ! isset($_POST['send_user_message_nonce'])
                || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['send_user_message_nonce'])), 'send_user_message_nonce')
            ) {
                return;
            }
            
            $template_id = sanitize_text_field($_POST['templateID']);

            if (Direktt_Message::send_message_template(
                array($subscriptionId),
                $template_id,
                []
            )) {
                $redirect_url = add_query_arg('status_flag', '1', $_SERVER['REQUEST_URI']);
            } else {
                 $redirect_url = add_query_arg('status_flag', '2', $_SERVER['REQUEST_URI']);
            }

            wp_safe_redirect(esc_url_raw($redirect_url));
            exit;
        }

        $status_flag    = isset($_GET['status_flag']) ? intval($_GET['status_flag']) : 0;
        $status_message = '';
        if ($status_flag === 1) {
            $status_message = esc_html__('Message sent.', 'direktt');
        }
        if ($status_flag === 2) {
            $status_message = esc_html__('There was an error while sending the message.', 'direktt');
        }

?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var statusEl = document.querySelector('.send-message-tool-status');
                var submitBtn = document.querySelector('.direktt-taxonomies-tool-submit input[type="submit"]');
                if (submitBtn && statusEl) {
                    submitBtn.addEventListener('click', function() {
                        statusEl.textContent = '<?php echo esc_js(__('Saving...', 'direktt')); ?>';
                    });
                }
            });
        </script>
		<div class="direktt-profile-data">
			<form method="post" action="">
				<div class="send-message-tool-wrapper">
					<div class="send-message-tool-info">
						<p class="send-message-tool-status"><?php echo $status_message; ?></p>
					</div>

					<input id="autoComplete" aria-autocomplete="none" autocomplete="off">
					<input type="hidden" id="templateID" name="templateID">
					<input type="hidden" id="templateNonce" name="templateNonce" value="<?php echo esc_attr(wp_create_nonce('direktt_msgsend_nonce')); ?>">

					<div class="send-message-tool-submit">
						<input type="submit" name="send_user_message" id="sendMessageBtn" value="<?php echo esc_html__('Send the message', 'direktt'); ?>" class="button button-primary">
						<input type="hidden" name="send_user_message_nonce" value="<?php echo esc_attr(wp_create_nonce('send_user_message_nonce')); ?>">

					</div>
				</div>

			</form>	
		</div>

<?php
    }
}
