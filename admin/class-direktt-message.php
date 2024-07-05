<?php

class Direktt_Message
{

    private string $plugin_name;
    private string $version;

    public function __construct(string $plugin_name, string $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    static function send_message( $direktt_user_ids, $message )
    {
        $api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';
        $url = 'https://sendbulkmessages-lnkonwpiwa-uc.a.run.app';

        $data = array(
            'subscriptionIds' => $direktt_user_ids,
            'pushNotificationMessage' => $message
        );

        $response = wp_remote_post($url, array(
            'body'    => json_encode($data),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-type' => 'application/json',
            ),
        ));
    }
}
