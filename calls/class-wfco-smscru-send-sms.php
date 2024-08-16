<?php

/**
 * Этот класс отвечает за отправку SMS через API SMSC.ru. 
 * Он принимает необходимые параметры 
 * (логин, пароль, номера телефонов, текст сообщения) и отправляет запрос к API. 
 * Затем он обрабатывает ответ и возвращает результат.
 */
class WFCO_SMSCRU_Send_Sms extends WFCO_Call {
    public $request_data = array();
    private static $instance = null;

    public function __construct() {
        $this->id = 'wfco_smscru_send_sms';
        $this->group = __( 'SMSC.ru', 'wp-marketing-automations' );
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_data() {
        return $this->request_data;
    }

    public function set_data( $data ) {
        $this->request_data = $data;
    }

    public function process() {
        $login = $this->request_data['login'];
        $password = $this->request_data['password'];
        $phones = $this->request_data['phones'];
        $message = $this->request_data['mes'];

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

return 'WFCO_SMSCRU_Send_Sms';