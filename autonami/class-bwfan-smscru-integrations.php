<?php

final class BWFAN_SMSCRU_Integration extends BWFAN_Integration {
    private static $ins = null;
    protected $connector_slug = 'bwfco_smscru';
    protected $need_connector = true;

    /**
     * Constructor method.
     *
     * Sets the integration properties, such as the name, group name, group slug, and priority.
     * Also adds the integration as a SMS service.
     */
    private function __construct() {
        $this->action_dir = __DIR__;
        $this->nice_name  = __( 'SMSC.ru', 'autonami-automations-connectors' );
        $this->group_name = __( 'Messaging', 'autonami-automations-connectors' );
        $this->group_slug = 'messaging';
        $this->priority   = 55;

        add_filter( 'bwfan_sms_services', array( $this, 'add_as_sms_service' ), 10, 1 );
    }

    /**
     * Returns the instance of the current class.
     *
     * @return BWFAN_SMSCRU_Integration
     */
    public static function get_instance() {
        if ( null === self::$ins ) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    /**
     * Sets the connector slug for the given action object.
     *
     * This is necessary because some actions, like the Send SMS action, need to know which connector to use when sending the SMS.
     *
     * @param BWFAN_Action $action_object The action object to set the connector slug for.
     *
     * @return void
     */
    protected function do_after_action_registration( BWFAN_Action $action_object ) {
        $action_object->connector = $this->connector_slug;
    }


    /**
     * Adds the current connector as a SMS service.
     *
     * @param array $sms_services The current list of SMS services.
     *
     * @return array The updated list of SMS services.
     */
    public function add_as_sms_service( $sms_services ) {
        $slug = $this->get_connector_slug();
        if ( BWFAN_Core()->connectors->is_connected( $slug ) ) {
            $integration                  = $slug;
            $sms_services[ $integration ] = $this->nice_name;
        }
        return $sms_services;
    }

    /**
     * Sends an SMS using SMSC.ru.
     *
     * @param array $args {
     *     The arguments for sending the SMS.
     *
     *     @type string $to        The phone number to send the SMS to.
     *     @type string $body      The message body.
     *     @type string $image_url The URL of the image to send with the message.
     *     @type bool   $is_test   Whether this is a test message or not.
     * }
     *
     * @return WP_Error|bool Whether the message was sent or not.
     */
    public function send_message( $args ) {
        $args = wp_parse_args( $args, array(
            'phones'        => '',
            'mes'           => '',
            'image_url'     => '',
        ) );

        $to   = $args['phones'];
        $body = $args['mes'];

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

        if ( isset( $args['is_test'] ) && ! empty( $args['is_test'] ) ) {
            $smscru_ins = BWFAN_SMSCRU_Send_Sms::get_instance();
            $smscru_ins->set_progress( true );
        }

        $call_args = array(
            'login'    => $login,
            'password' => $password,
            'mes'      => $body,
            'phones'   => $to,
        );

        $load_connectors = WFCO_Load_Connectors::get_instance();
        $call            = $load_connectors->get_call( 'wfco_smscru_send_sms' );

        $call->set_data( $call_args );

        return $this->validate_send_message_response( $call->process() );
    }

    /**
     * Checks if the message was sent successfully or not.
     *
     * @param array $response The response from the API.
     *
     * @return bool|WP_Error Whether the message was sent or not.
     */
    public function validate_send_message_response( $response ) {
        // Логика валидации ответа от API SMSC.ru
    }
}

BWFAN_Load_Integrations::register( 'BWFAN_SMSCRU_Integration' );