<?php

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

        $body = array(
            'phones'  => $this->data['phone'],
            'mes'     => $this->data['message'],
            'charset' => 'utf-8',
            'fmt'     => 3, // JSON response format
        );

        $args = array(
            'headers' => $headers,
            'body'    => $body,
            'method'  => 'POST',
        );

        $response = wp_remote_post($endpoint, $args);

        if (is_wp_error($response)) {
            return array(
                'status'  => 'error',
                'message' => WFCO_SMSCRU_Common::handle_error($response),
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

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