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

    public function __construct() {
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
            //'phones'   => $this->data['phones'],
            //'mes'      => $this->data['mes'],
            'phones'   => '79119387283',
            'mes'      => 'TEST',
            'charset'  => 'utf-8',
            'fmt'      => 3, // JSON response format
            'cost'     => 3, // Return cost info
        );

        if (!empty($this->data['sender'])) {
            $params['sender'] = $this->data['sender'];
        }

        if (!empty($this->data['translit'])) {
            $params['translit'] = $this->data['translit'];
        }

        $url = add_query_arg($params, $this->api_endpoint);

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return array(
                'status' => false,
                'message' => $response->get_error_message(),
            );
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (isset($result['error'])) {
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
    }
}

return 'WFCO_SMSCRU_Send_Sms';