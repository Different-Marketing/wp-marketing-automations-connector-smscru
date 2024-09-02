<?php

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

    /**
     * Load hooks for this action.
     *
     * @since 2.0.0
     */
    public function load_hooks() {
        add_filter( 'bwfan_modify_send_sms_body', array( $this, 'shorten_link' ), 15, 2 );
    }


    /**
     * Shorten URLs in the message body.
     *
     * @since 2.0.0
     *
     * @param string $body The message body.
     * @param array  $data The automation data.
     *
     * @return string The modified message body.
     */
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

        // UTM параметры и другие настройки

        $data_to_set['text'] = stripslashes( $data_to_set['text'] );
        $data_to_set['text'] = BWFAN_Connectors_Common::modify_sms_body( $data_to_set['text'], $data_to_set );

        $this->remove_action();
        return $data_to_set;
    }

    public function execute_action( $action_data ) {
        // Логика выполнения действия
    }

    public function handle_response_v2( $response ) {
        // Обработка ответа от API
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
            // Другие поля...
        ];
    }
}

return 'BWFAN_SMSCRU_Send_Sms';