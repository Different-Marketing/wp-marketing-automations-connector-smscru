<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class WFCO_SMSCRU_Call {

    protected $data = array();

    public function __construct() {
        // Constructor
    }

    public function set_data($data) {
        $this->data = $data;
    }

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