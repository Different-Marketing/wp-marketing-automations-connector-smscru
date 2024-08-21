<?php

if (!class_exists('WFCO_Call')) {
    require_once WP_PLUGIN_DIR . '/wp-marketing-automations/woofunnels/connector/class-wfco-call.php';
}

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WFCO_SMSCRU_Call {

    protected $data = array();

    /**
     * Constructor
     *
     * @since  1.0.0
     *
     * @return void
     */
    public function __construct() {
        // Constructor
    }

    /**
     * Set data for the call
     *
     * @param array $data Data for the call
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function set_data($data) {
        $this->data = $data;
    }

    /**
     * Process the call
     *
     * @since 1.0.0
     *
     * @return array An associative array containing the result of the call.
     *               The array will have a 'status' key with a boolean value,
     *               a 'message' key with a string value, and a 'data' key with
     *               an array of the response from SMSC.ru.
     */
    public function process() {
        $endpoint = WFCO_SMSCRU_Common::get_api_endpoint();
        $headers = WFCO_SMSCRU_Common::get_headers();
        
        error_log('SMSC.ru API endpoint in call: ' . $endpoint);
        error_log('SMSC.ru headers in call: ' . print_r($headers, true));
        error_log('SMSC.ru data in call: ' . print_r($this->data, true));
        
        $body = array(
            'login'   => isset($this->data['login']) ? $this->data['login'] : '',
            'psw'     => isset($this->data['password']) ? $this->data['password'] : '',
            'phones'  => isset($this->data['phones']) ? $this->data['phones'] : '',
            'mes'     => isset($this->data['mes']) ? $this->data['mes'] : '',
            'charset' => 'utf-8',
            'fmt'     => 3, // JSON response format
        );

        // Логирование для отладки
        error_log('SMSC.ru request body: ' . print_r($body, true));

        $args = array(
            'headers' => $headers,
            'body'    => $body,
            'method'  => 'POST',
        );

        $response = wp_remote_post($endpoint, $args);
        // TODO log $response
        if (is_wp_error($response)) {
            error_log('SMSC.ru API error: ' . $response->get_error_message());
        } else {
            error_log('SMSC.ru API response code: ' . wp_remote_retrieve_response_code($response));
            error_log('SMSC.ru API response body: ' . wp_remote_retrieve_body($response));
        }

        if (is_wp_error($response)) {
            return array(
                'status'  => 'error',
                'message' => WFCO_SMSCRU_Common::handle_error($response),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        error_log('SMSC.ru API response in call: ' . print_r($body, true));
        
        if (isset($body['error'])) {
            return array(
                'status'  => 'error',
                'message' => $body['error'],
            );
        }

        return array(
            'status'  => 'success',
            'message' => __('SMS sent successfully', 'autonami-automations-connectors'),
            'data'    => $body,
        );
    }
}