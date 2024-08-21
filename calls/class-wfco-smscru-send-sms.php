<?php

if (!class_exists('WFCO_SMSCRU_Call')) {
    require_once WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php';
}

class WFCO_SMSCRU_Send_Sms extends WFCO_SMSCRU_Call {
    private static $instance = null;

    protected function __construct() {
        $this->id = 'wfco_smscru_send_sms';
        $this->group = __('SMSC.ru', 'wp-marketing-automations-connector-smscru');
        $this->required_fields = array('login', 'password', 'phones', 'mes');
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function process() {
        $login = $this->data['login'];
        $password = $this->data['password'];
        $phones = $this->data['phones'];
        $message = $this->data['mes'];

        $url = "https://smsc.ru/sys/send.php?login=".urlencode($login)."&psw=".urlencode($password)."&phones=".urlencode($phones)."&mes=".urlencode($message)."&charset=utf-8";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            error_log("SMSC.ru API error: " . $response->get_error_message());
            return array('status' => false, 'message' => $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        error_log("SMSC.ru API response: " . $body);

        if (strpos($body, 'OK') !== false) {
            return array('status' => true, 'message' => 'SMS sent successfully');
        } else {
            return array('status' => false, 'message' => 'Failed to send SMS: ' . $body);
        }
    }
}