<?php

if ( ! class_exists( 'BWFAN_Action' ) ) {
    require_once WP_PLUGIN_DIR . '/wp-marketing-automations/includes/abstracts/class-bwfan-action.php';
}
class BWFAN_SMSCRU_Send_Sms extends BWFAN_Action {
    private static $instance = null;
    private $progress = false;
    public $support_language = true;

    public function __construct() {
        $this->action_name = __( 'Send Message', 'autonami-automations-connectors' );
        $this->action_desc = __( 'This action sends a message via SMSC.ru', 'autonami-automations-connectors' );
        $this->support_v2  = true;
        $this->support_v1  = false;
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load_hooks() {
        add_filter( 'bwfan_modify_send_sms_body', array( $this, 'shorten_link' ), 15, 2 );
    }

    public function shorten_link( $body, $data ) {
        if ( true === $this->progress ) {
            $body = preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array( $this, 'shorten_urls' ), $body );
        }
        return preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array( $this, 'unsubscribe_url_with_mode' ), $body );
    }

    protected function shorten_urls( $matches ) {
        $string = $matches[0];
        if ( method_exists( 'BWFAN_Connectors_Common', 'get_shorten_url' ) ) {
            return BWFAN_Connectors_Common::get_shorten_url( $string );
        }
        return do_shortcode( '[bwfan_bitly_shorten]' . $string . '[/bwfan_bitly_shorten]' );
    }

    public function make_v2_data( $automation_data, $step_data ) {
        $this->add_action();
        $this->progress = true;
        $sms_body       = isset( $step_data['sms_body_textarea'] ) ? $step_data['sms_body_textarea'] : '';

        $data_to_set = array(
            'name'            => BWFAN_Common::decode_merge_tags( '{{customer_first_name}}' ),
            'promotional_sms' => ( isset( $step_data['promotional_sms'] ) ) ? 1 : 0,
            'append_utm'      => ( isset( $step_data['sms_append_utm'] ) ) ? 1 : 0,
            'number'          => ( isset( $step_data['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $step_data['sms_to'] ) : '',
            'phone'           => ( isset( $step_data['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $step_data['sms_to'] ) : '',
            'event'           => ( isset( $step_data['event_data'] ) && isset( $step_data['event_data']['event_slug'] ) ) ? $step_data['event_data']['event_slug'] : '',
            'text'            => BWFAN_Common::decode_merge_tags( $sms_body ),
            'step_id'         => isset( $automation_data['step_id'] ) ? $automation_data['step_id'] : '',
            'automation_id'   => isset( $automation_data['automation_id'] ) ? $automation_data['automation_id'] : '',
        );

        $data_to_set['login']    = isset( $step_data['connector_data']['login'] ) ? $step_data['connector_data']['login'] : '';
        $data_to_set['password'] = isset( $step_data['connector_data']['password'] ) ? $step_data['connector_data']['password'] : '';

        $data_to_set['text'] = stripslashes( $data_to_set['text'] );
        $data_to_set['text'] = BWFAN_Connectors_Common::modify_sms_body( $data_to_set['text'], $data_to_set );

        $this->remove_action();
        return $data_to_set;
    }

    public function execute_action( $action_data ) {
        BWFAN_Core()->logger->log( "Starting execute_action for SMSC.ru SMS", 'smscru_send_sms' );
        
        $this->set_data( $action_data['processed_data'] );

        if ( empty( $this->data['login'] ) || empty( $this->data['password'] ) || empty( $this->data['number'] ) || empty( $this->data['text'] ) ) {
            BWFAN_Core()->logger->log( "Missing required data for SMS send", 'smscru_send_sms' );
            return $this->error_response( __( 'Missing required data for SMS send', 'autonami-automations-connectors' ) );
        }

        if ( 1 === absint( $this->data['promotional_sms'] ) ) {
            $where = array(
                'recipient' => $this->data['number'],
                'mode'      => 2,
            );
            $check_unsubscribe = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row( $where );

            if ( ! empty( $check_unsubscribe ) ) {
                BWFAN_Core()->logger->log( "User is unsubscribed. Skipping SMS send.", 'smscru_send_sms' );
                return $this->error_response( __( 'User is already unsubscribed', 'autonami-automations-connectors' ) );
            }
        }

        $load_connector = WFCO_Load_Connectors::get_instance();
        $call_class     = $load_connector->get_call( 'wfco_smscru_send_sms' );
        
        if ( is_null( $call_class ) ) {
            BWFAN_Core()->logger->log( "Send SMS call not found", 'smscru_send_sms' );
            return $this->error_response( __( 'Send SMS call not found', 'autonami-automations-connectors' ) );
        }

        $call_data = array(
            'login'    => $this->data['login'],
            'password' => $this->data['password'],
            'phones'   => $this->data['number'],
            'mes'      => $this->data['text'],
        );

        BWFAN_Core()->logger->log( "Preparing to send SMS", 'smscru_send_sms' );

        $call_class->set_data( $call_data );
        $response = $call_class->process();

        BWFAN_Core()->logger->log( "SMS send attempt completed", 'smscru_send_sms' );

        return $this->handle_response_v2( $response );
    }

    public function handle_response_v2( $response ) {
        if ( $response['status'] === true ) {
            BWFAN_Core()->logger->log( "SMS sent successfully", 'smscru_send_sms' );
            return array(
                'status'  => 3,
                'message' => __( 'SMS sent successfully.', 'autonami-automations-connectors' ),
            );
        } else {
            $error_message = isset($response['message']) ? $response['message'] : 'Unknown error';
            BWFAN_Core()->logger->log( "SMS send failed. Error: " . $error_message, 'smscru_send_sms' );
            return $this->error_response( sprintf(__( 'SMS send failed: %s', 'autonami-automations-connectors' ), $error_message) );
        }
    }

    public function get_fields_schema() {
        return [
            [
                'id'          => 'sms_to',
                'label'       => __( "To", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => "",
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( '', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => true,
            ],
            [
                'id'          => 'sms_body_textarea',
                'label'       => __( "Text", 'wp-marketing-automations' ),
                'type'        => 'textarea',
                'placeholder' => "Message Body",
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( '', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => true,
            ],
        ];
    }

    public function send_test_sms($phone, $message) {
        error_log("Sending test SMS to: $phone");
        
        if (empty($this->data['login']) || empty($this->data['password'])) {
            error_log("Missing login or password for SMSC.ru");
            return false;
        }

        $load_connector = WFCO_Load_Connectors::get_instance();
        error_log("Connector instance: " . print_r($load_connector, true));
        error_log("Available calls: " . print_r($load_connector->get_calls(), true));
        
        $call_class = $load_connector->get_call('wfco_smscru_send_sms');
        
        if (is_null($call_class)) {
            error_log("Send SMS call not found for test");
            return false;
        }
    
        $call_data = array(
            'login'    => $this->data['login'],
            'password' => $this->data['password'],
            'phones'   => $phone,
            'mes'      => $message,
        );
    
        $call_class->set_data($call_data);
        $response = $call_class->process();
    
        error_log("Test SMS send attempt completed. Response: " . print_r($response, true));
    
        return isset($response['status']) && $response['status'] === true;
    }



    // Добавленный метод add_action
    public function add_action() {
        $this->progress = true;
    }

    // Добавленный метод remove_action
    public function remove_action() {
        $this->progress = false;
    }

    // Вспомогательный метод для возврата ошибки
    public function error_response($message = '') {
        if (empty($message)) {
            $message = __('Unknown error occurred', 'wp-marketing-automations');
        }
    
        return array(
            'status'  => self::$RESPONSE_FAILED,
            'message' => $message,
        );
    }
}

return 'BWFAN_SMSCRU_Send_Sms';