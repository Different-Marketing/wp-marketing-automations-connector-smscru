<?php

final class BWFAN_SMSCRU_Integration extends BWFAN_Integration {
    private static $ins = null;
    protected $connector_slug = 'bwfco_smscru';
    protected $need_connector = true;

    public function __construct() {
        $this->action_dir = __DIR__;
        $this->nice_name  = __( 'SMSC.ru', 'autonami-automations-connectors' );
        $this->group_name = __( 'Messaging', 'autonami-automations-connectors' );
        $this->group_slug = 'messaging';
        $this->priority   = 35;

        add_filter( 'bwfan_sms_services', array( $this, 'add_as_sms_service' ), 10, 1 );
        // TODO: этот фильтр нужен?
        //add_filter( 'bwfan_available_actions', array( $this, 'register_actions' ) );
    }

    public static function get_instance() {
        if ( null === self::$ins ) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    /**
     * Sets the connector slug for the given action object.
     *
     * @param BWFAN_Action $action_object The action object.
     */
    protected function do_after_action_registration( BWFAN_Action $action_object ) {
        $action_object->connector = $this->connector_slug;
    }


    /**
     * Adds the SMSC.ru connector as a valid SMS service in the list of available services.
     *
     * Основные причины различий:
     *
     * Разные подходы к проверке подключения:
     * Первый вариант использует централизованный метод через BWFAN_Core()->connectors.
     * Второй вариант получает конкретный экземпляр коннектора и вызывает его метод.
     * Безопасность и гибкость:
     * Второй вариант более безопасен, так как проверяет существование объекта коннектора и наличие метода is_connected().
     * Это позволяет избежать ошибок, если структура классов изменится или метод будет отсутствовать.
     * Совместимость:
     * Второй вариант может быть более совместимым с различными версиями плагина или разными реализациями коннекторов.
     * Производительность:
     * Первый вариант может быть немного быстрее, так как выполняет меньше проверок.
     * Второй вариант выполняет дополнительные проверки, что может слегка повлиять на производительность, но повышает надежность.
     * 
     * @param array $sms_services The array of available SMS services.
     * @return array The updated array of available SMS services.
     */
    public function add_as_sms_service( $sms_services ) {
        $slug = $this->get_connector_slug();
        if ( BWFAN_Core()->connectors->is_connected( $slug ) ) {
            $integration = $slug;
            $sms_services[$integration] = $this->nice_name;
        }
        return $sms_services;
    }

    /*public function register_actions( $actions ) {
        $actions['smscru_send_sms'] = 'BWFAN_SMSCRU_Send_Sms';
        return $actions;
    }*/

    /**
     * Sends an SMS message using SMSC.ru service.
     *
     * Sends an SMS message using the SMSC.ru service.
     * Validates the input data and checks if the connector data is valid.
     * If the input data is missing or invalid, returns an error.
     * If the connector data is missing or invalid, returns an error.
     * If the input data is valid and the connector data is valid,
     * sends the SMS message and returns the result of the call.
     *
     * @param array $args An associative array of parameters:
     *                    to: The recipient's phone number.
     *                    body: The body of the message.
     *                    image_url: The URL of the image to be sent with the message.
     *                    is_test: Set to true if this is a test message.
     * @return mixed The result of the call to the SMSC.ru service or a WP_Error object.
     */
    public function send_message( $args ) {
        $args = wp_parse_args( $args, array(
            'to'        => '',
            'body'      => '',
            'image_url' => '',
        ) );

        $to   = $args['to'];
        $body = $args['body'];

        if ( empty( $to ) || empty( $body ) ) {
            return new WP_Error( 400, 'Data missing to send SMSC.ru SMS' );
        }

        WFCO_Common::get_connectors_data();
        $settings = WFCO_Common::$connectors_saved_data[ $this->get_connector_slug() ];
        $login    = $settings['login'];
        $password = $settings['password'];

        if ( empty( $login ) || empty( $password ) ) {
            return new WP_Error( 404, 'Invalid / Missing saved connector data' );
        }
        // TODO: это нужно?
        /*if ( isset( $args['is_test'] ) && ! empty( $args['is_test'] ) ) {
            $smscru_ins = BWFAN_SMSCRU_Send_Sms::get_instance();
            $smscru_ins->set_progress( true );
        }*/

        $call_args = array(
            'login'    => $login,
            'password' => $password,
            'phones'   => $to,
            'mes'      => $body,
        );

        // Добавляем поддержку изображений, если API SMSC.ru это поддерживает
        $image_url = $args['image_url'];
        if ( ! empty( $image_url ) && filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
            $call_args['image'] = $image_url;
        }

        $load_connectors = WFCO_Load_Connectors::get_instance();
        $call            = $load_connectors->get_call( 'wfco_smscru_send_sms' );

        $call->set_data( $call_args );

        return $this->validate_send_message_response( $call->process() );
    }

    /**
     * Validates the response from the SMSC.ru API after sending an SMS message.
     *
     * Checks if the response is an error from WordPress, decodes the JSON response,
     * checks if the message was successfully sent, and returns the result accordingly.
     *
     * @param mixed $response The response from the SMSC.ru API after sending an SMS message.
     *
     * @return bool|WP_Error Returns true if the message was successfully sent, a WP_Error object if there was an error, or a general error if the response was neither.
     */
    /*public function validate_send_message_response( $response ) {
        // Проверяем, является ли ответ ошибкой WordPress
        if ( is_wp_error( $response ) ) {
            return new WP_Error( 'smscru_api_error', $response->get_error_message() );
        }
    
        // Проверяем код ответа HTTP
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            return new WP_Error( 'smscru_api_error', 'HTTP Error: ' . $response_code );
        }
    
        // Получаем тело ответа
        $body = wp_remote_retrieve_body( $response );
    
        // SMSC.ru может возвращать ответ в разных форматах, проверяем JSON
        $result = json_decode( $body, true );
        if ( json_last_error() === JSON_ERROR_NONE ) {
            // Это JSON ответ
            if ( isset( $result['error'] ) ) {
                return new WP_Error( 'smscru_api_error', $result['error'] );
            }
            if ( isset( $result['id'] ) ) {
                return true; // Успешная отправка
            }
        } else {
            // Это может быть текстовый ответ
            if ( strpos( $body, 'ERROR' ) !== false ) {
                return new WP_Error( 'smscru_api_error', $body );
            }
            if ( strpos( $body, 'OK' ) !== false ) {
                return true; // Успешная отправка
            }
        }
    
        // Если ни одно из условий не выполнено, возвращаем общую ошибку
        return new WP_Error( 'smscru_unknown_error', 'Unknown error occurred while sending SMS' );
    }*/

    public function validate_send_message_response( $response ) {
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( is_array( $response ) && isset( $response['status'] ) && $response['status'] === true ) {
            return true;
        }

        $message = isset( $response['message'] ) ? $response['message'] : __( 'SMS could not be sent.', 'autonami-automations-connectors' );
        return new WP_Error( 500, $message );
    }
}

/**
 * Register this class as an integration.
 */
BWFAN_Load_Integrations::register( 'BWFAN_SMSCRU_Integration' );