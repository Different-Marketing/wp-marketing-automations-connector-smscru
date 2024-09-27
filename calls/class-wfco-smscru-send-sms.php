<?php

/**
 * Этот класс отвечает за отправку SMS через API SMSC.ru. 
 * Он принимает необходимые параметры 
 * (логин, пароль, номера телефонов, текст сообщения) и отправляет запрос к API. 
 * Затем он обрабатывает ответ и возвращает результат.
 */
class WFCO_SMSCRU_Send_Sms extends WFCO_Call {
    private static $ins = null;
    private $api_endpoint = 'https://smsc.ru/sys/send.php';

    /**
     * Initializes a new instance of the WFCO_SMSCRU_Send_Sms class.
     *
     * @return void
     * @since 1.0.0
     */
    public function __construct() {
        $this->id = 'wfco_smscru_send_sms';
        $this->group = __('SMSC.ru', 'wp-marketing-automations-connector-smscru');
        $this->required_fields = array('login', 'password', 'phones', 'mes');
    }

    public static function get_instance() {
        if (null === self::$ins) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function process() {
        $params = array(
            'login'    => $this->data['login'],
            'psw'      => $this->data['password'],
            'phones'   => $this->data['phones'],
            'mes'      => $this->data['mes'],
            //'phones'   => '79119387283',
            //'mes'      => 'TEST',
            'charset'  => 'utf-8',
            'fmt'      => 3, // JSON response format
            'cost'     => 3, // Return cost info
            'sender'   => 'Mamatov',
        );

        error_log('SMSC.ru params in send-sms: ' . print_r($params, true));

        if (!empty($this->data['sender'])) {
            $params['sender'] = $this->data['sender'];
        }

        if (!empty($this->data['translit'])) {
            $params['translit'] = $this->data['translit'];
        }

        $url = add_query_arg($params, $this->api_endpoint);

        error_log('SMSC.ru full URL: ' . $url);

        $login = $this->request_data['login'];
        $password = $this->request_data['password'];
        $phones = $this->request_data['phones'];
        $message = $this->request_data['mes'];

        $url = "https://smsc.ru/sys/send.php?login=".urlencode($login)."&psw=".urlencode($password)."&phones=".urlencode($phones)."&mes=".urlencode($message)."&charset=utf-8";
        $response = wp_remote_get($url);
    
        if (is_wp_error($response)) {
            error_log('SMSC.ru API error: ' . $response->get_error_message());
            return array(
                'status' => false,
                'message' => $response->get_error_message(),
            );
            error_log("SMSC.ru API error: " . $response->get_error_message());
            return array('status' => false, 'message' => $response->get_error_message());
        }
    
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        error_log("SMSC.ru API response: " . $body);

        if (isset($result['error'])) {
            error_log('SMSC.ru API error from response: ' . $result['error']);
            return array(
                'status' => false,
                'message' => $result['error'],
            );
        }

        return array(
            'status' => true,
            'message' => 'SMS sent successfully',
            'data' => $result,
        );
        if (strpos($body, 'OK') !== false) {
            return array('status' => true, 'message' => 'SMS sent successfully');
        } else {
            return array('status' => false, 'message' => 'Unknown error occurred');
        }
    }
}