<?php

final class BWFAN_SMSCRU_Integration extends BWFAN_Integration {
    private static $ins = null;
    protected $connector_slug = 'bwfco_smscru';
    protected $need_connector = true;

    private function __construct() {
        $this->action_dir = __DIR__;
        $this->nice_name  = __( 'SMSC.ru', 'autonami-automations-connectors' );
        $this->group_name = __( 'Messaging', 'autonami-automations-connectors' );
        $this->group_slug = 'messaging';
        $this->priority   = 55;

        add_filter( 'bwfan_sms_services', array( $this, 'add_as_sms_service' ), 10, 1 );
    }

    public static function get_instance() {
        if ( null === self::$ins ) {
            self::$ins = new self();
        }
        return self::$ins;
    }

    protected function do_after_action_registration( BWFAN_Action $action_object ) {
        $action_object->connector = $this->connector_slug;
    }

    public function add_as_sms_service( $sms_services ) {
        $slug = $this->get_connector_slug();
        if ( BWFAN_Core()->connectors->is_connected( $slug ) ) {
            $integration                  = $slug;
            $sms_services[ $integration ] = $this->nice_name;
        }
        return $sms_services;
    }

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

        if ( isset( $args['is_test'] ) && ! empty( $args['is_test'] ) ) {
            $smscru_ins = BWFAN_SMSCRU_Send_Sms::get_instance();
            $smscru_ins->set_progress( true );
        }

        $call_args = array(
            'login'    => $login,
            'password' => $password,
            'text'     => $body,
            'number'   => $to,
        );

        $load_connectors = WFCO_Load_Connectors::get_instance();
        $call            = $load_connectors->get_call( 'wfco_smscru_send_sms' );

        $call->set_data( $call_args );

        return $this->validate_send_message_response( $call->process() );
    }

    public function validate_send_message_response( $response ) {
        // Логика валидации ответа от API SMSC.ru
    }
}

BWFAN_Load_Integrations::register( 'BWFAN_SMSCRU_Integration' );