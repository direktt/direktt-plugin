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

?>

        <input id="autoComplete">
        <input type="hidden" id="templateNonce" name="templateNonce" value="<?php echo esc_attr(wp_create_nonce('direktt_msgsend_nonce')); ?>">
        <button id="sendMessage" >Send the message</button>

<?php
    }
}
