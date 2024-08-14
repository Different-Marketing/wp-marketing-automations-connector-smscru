<?php
/**
 * Этот класс отвечает за проверку баланса аккаунта SMSC.ru. 
 * Он принимает логин и пароль, отправляет запрос к API 
 * и возвращает текущий баланс.
 */
class WFCO_SMSCRU_Get_Balance extends WFCO_Call {
    private static $ins = null;
    private $api_endpoint = 'https://smsc.ru/sys/balance.php';

    /**
     * Initializes a new instance of the WFCO_SMSCRU_Get_Balance class.
     *
     * @return void
     */
    public function __construct() {
        $this->required_fields = array('login', 'password');
    }

    public static function get_instance() {
        if (null === self::$ins) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    public function process() {
        $params = array(
            'login' => $this->data['login'],
            'psw'   => $this->data['password'],
            'fmt'   => 3, // JSON response format
        );

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
            'message' => 'Balance retrieved successfully',
            'data' => $result,
        );
    }
}

return 'WFCO_SMSCRU_Get_Balance';
