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

    static function send_message($messages)
    {
        $api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';
        $url = 'https://sendbulkmessages-lnkonwpiwa-uc.a.run.app';

        $data = [];

        foreach ($messages as $key => $value) {
            $obj = new stdClass();
            $obj->subscriptionId = $key;
            $obj->pushNotificationMessage = $value;
            $data[] = $obj;
        }

        $response = wp_remote_post($url, array(
            'body'    => json_encode(array(
                "messages" => $data
            )),
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-type' => 'application/json',
            ),
        ));
    }

    static function replace_tags_in_template($string, $replacements)
    {
        if (!is_null($string)) {
            return preg_replace_callback('/#([^#]+)#/', function ($matches) use ($replacements) {
                $tag = $matches[1];
                return array_key_exists($tag, $replacements) ? $replacements[$tag] : $matches[0];
            }, $string);
        }

        return null;
    }

    static function send_message_template($direktt_user_ids, $message_template_id, $replacements = [])
    {
        $api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';

        $url = 'https://sendbulkmessages-lnkonwpiwa-uc.a.run.app';

        $message_template = get_post_meta($message_template_id, 'direkttMTJson', true);

        if ($message_template) {

            $data = [];

            foreach ($direktt_user_ids as $key => $value) {

                $message = Direktt_Message::replace_tags_in_template($message_template, $replacements);
                $message = json_decode($message);
                if (is_array($message->content) || is_object($message->content)) {
                    $message->content = json_encode($message->content);
                }
                if (!is_null($message)) {
                    $obj = new stdClass();
                    $obj->subscriptionId = $value;
                    $obj->pushNotificationMessage = $message;
                    $data[] = $obj;
                }
            }

            $response = wp_remote_post($url, array(
                'body'    => json_encode(array(
                    "messages" => $data
                )),
                'headers' => array(
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-type' => 'application/json',
                ),
            ));

            return $message;
        }
        return false;
    }

    static function send_message_to_admin($message)
    {
        $api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';
        $url = 'https://sendadminmessage-lnkonwpiwa-uc.a.run.app';

        $data = array(
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

    static function send_message_template_to_admin($message_template_id, $replacements = [])
    {
        $api_key = get_option('direktt_api_key') ? esc_attr(get_option('direktt_api_key')) : '';
        $url = 'https://sendadminmessage-lnkonwpiwa-uc.a.run.app';

        $message = get_post_meta($message_template_id, 'direkttMTJson', true);

        if ($message) {

            $message = Direktt_Message::replace_tags_in_template($message, $replacements);
            $message = json_decode($message);
            if (is_array($message->content) || is_object($message->content)) {
                $message->content = json_encode($message->content);
            }

            if (!is_null($message)) {

                $data = array(
                    'pushNotificationMessage' => $message
                );

                $response = wp_remote_post($url, array(
                    'body'    => json_encode($data),
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $api_key,
                        'Content-type' => 'application/json',
                    ),
                ));

                return $message;
            }
        }
        return false;
    }
}
