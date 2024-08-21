<?php

class BWFCO_SMSCRU extends BWF_CO {
    public static $instance = null;
    public $v2 = true;

    public function __construct() {
        $this->keys_to_track = [
            'login',
            'password'
        ];
        $this->form_req_keys = [
            'login',
            'password'
        ];

        $this->connector_url     = WFCO_SMSCRU_PLUGIN_URL;
        $this->dir               = __DIR__;
        $this->nice_name         = __( 'SMSC.ru', 'autonami-automations-connectors' );
        $this->autonami_int_slug = 'BWFAN_SMSCRU_Integration';

        add_filter( 'wfco_connectors_loaded', array( $this, 'add_card' ) );
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load_calls() {
        require_once WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php';
        require_once WFCO_SMSCRU_PLUGIN_DIR . '/calls/class-wfco-smscru-send-sms.php';
        require_once WFCO_SMSCRU_PLUGIN_DIR . '/calls/class-wfco-smscru-get-balance.php';
    
        WFCO_Load_Connectors::register_calls(WFCO_SMSCRU_Send_Sms::get_instance());
        WFCO_Load_Connectors::register_calls(WFCO_SMSCRU_Get_Balance::get_instance());
    }

    public function get_fields_schema() {
        return array(
            array(
                'id'          => 'login',
                'label'       => __( 'Login', 'wp-marketing-automations-connectors' ),
                'type'        => 'text',
                'class'       => 'bwfan_smscru_login',
                'placeholder' => __( 'Login', 'wp-marketing-automations-connectors' ),
                'required'    => true,
                'toggler'     => array(),
            ),
            array(
                'id'          => 'password',
                'label'       => __( 'Password', 'wp-marketing-automations-connectors' ),
                'type'        => 'password',
                'class'       => 'bwfan_smscru_password',
                'placeholder' => __( 'Password', 'wp-marketing-automations-connectors' ),
                'required'    => true,
                'toggler'     => array(),
            ),
        );
    }

    public function get_settings_fields_values() {
        $saved_data = WFCO_Common::$connectors_saved_data;
        $old_data   = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
        $vals       = array();
        if ( isset( $old_data['login'] ) ) {
            $vals['login'] = $old_data['login'];
        }
        if ( isset( $old_data['password'] ) ) {
            $vals['password'] = $old_data['password'];
        }
        return $vals;
    }

    protected function get_api_data( $posted_data ) {
        // Implement API data retrieval logic here
    }

    public function add_card( $available_connectors ) {
        $available_connectors['autonami']['connectors']['bwfco_smscru'] = array(
            'name'            => 'SMSC.ru',
            'desc'            => __( 'Send SMS', 'autonami-automations-connectors' ),
            'connector_class' => 'BWFCO_SMSCRU',
            'image'           => $this->get_image(),
            'source'          => '',
            'file'            => '',
        );
        return $available_connectors;
    }
}

WFCO_Load_Connectors::register( 'BWFCO_SMSCRU' );