<?php

class WFCO_SMSCRU_Common {
    private static $instance = null;

    /**
     * Set the headers array with the Authorization and Content-Type headers.
     *
     * @param string $login  The login to use for the Authorization header.
     * @param string $password  The password to use for the Authorization header.
     *
     * @return void
     */
    public static function set_headers($login, $password) {
        self::$headers = array(
            'Authorization' => 'Basic ' . base64_encode($login . ':' . $password),
            'Content-Type'  => 'application/json',
        );
        error_log('SMSC.ru headers set: ' . print_r(self::$headers, true));
    }

    /**
     * Get the headers array set by set_headers.
     *
     * @return array The headers array.
     */
    public static function get_headers() {
        return self::$headers;
    }

    /**
     * Get the API endpoint URL for SMSC.ru.
     *
     * @return string The API endpoint URL.
     */
    public static function get_api_endpoint() {
        return 'https://smsc.ru/sys/send.php';
    }

    /**
     * Handle errors from API responses.
     *
     * @param array|WP_Error $response The API response to handle the error for.
     *
     * @return string The error message if an error occurred, otherwise an empty string.
     */
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

WFCO_SMSCRU_Common::get_instance();