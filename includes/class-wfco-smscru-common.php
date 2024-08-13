<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WFCO_SMSCRU_Common {

    public static $headers = null;

    public static function set_headers($login, $password) {
        self::$headers = array(
            'Authorization' => 'Basic ' . base64_encode($login . ':' . $password),
            'Content-Type'  => 'application/json',
        );
    }

    public static function get_headers() {
        return self::$headers;
    }

    public static function get_api_endpoint() {
        return 'https://smsc.ru/sys/send.php';
    }

    public static function handle_error($response) {
        $error_message = '';

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
        } elseif (isset($response['body'])) {
            $body = json_decode($response['body'], true);
            if (isset($body['error'])) {
                $error_message = $body['error'];
            }
        }

        return $error_message ? $error_message : __('Unknown error occurred', 'autonami-automations-connectors');
    }
}