<?php

class BWFCO_SMSCRU extends BWF_CO {

    public static $instance = null;
    public $v2 = true;

    public function __construct() {
        $this->connector_url     = WFCO_SMSCRU_PLUGIN_URL;
        $this->dir               = __DIR__;
        $this->nice_name         = __('SMSC.ru', 'autonami-automations-connectors');
        $this->autonami_int_slug = 'BWFAN_SMSCRU_Integration';

        $this->keys_to_track = array(
            'login',
            'password',
        );
        $this->form_req_keys = array(
            'login',
            'password',
        );

        add_filter('wfco_connectors_loaded', array($this, 'add_card'));
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_fields_schema() {
        return array(
            array(
                'id'          => 'login',
                'label'       => __('Login', 'autonami-automations-connectors'),
                'type'        => 'text',
                'class'       => 'bwfan_smscru_login',
                'placeholder' => __('Enter your SMSC.ru login', 'autonami-automations-connectors'),
                'required'    => true,
            ),
            array(
                'id'          => 'password',
                'label'       => __('Password', 'autonami-automations-connectors'),
                'type'        => 'password',
                'class'       => 'bwfan_smscru_password',
                'placeholder' => __('Enter your SMSC.ru password', 'autonami-automations-connectors'),
                'required'    => true,
            ),
        );
    }

    public function get_settings_fields_values() {
        $saved_data = WFCO_Common::$connectors_saved_data;
        $old_data   = isset($saved_data[$this->get_slug()]) ? $saved_data[$this->get_slug()] : array();
        
        return array(
            'login'    => isset($old_data['login']) ? $old_data['login'] : '',
            'password' => isset($old_data['password']) ? $old_data['password'] : '',
        );
    }

    protected function get_api_data($posted_data) {
        $login    = isset($posted_data['login']) ? $posted_data['login'] : '';
        $password = isset($posted_data['password']) ? $posted_data['password'] : '';

        WFCO_SMSCRU_Common::set_headers($login, $password);

        $call_class = new WFCO_SMSCRU_Call();
        $call_class->set_data(array(
            'phone'   => '71234567890', // Test phone number
            'message' => 'Test message',
        ));

        $response = $call_class->process();

        if ($response['status'] === 'success') {
            return array(
                'status'   => 'success',
                'api_data' => array(
                    'login'    => $login,
                    'password' => $password,
                ),
            );
        } else {
            return array(
                'status'  => 'failed',
                'message' => $response['message'],
            );
        }
    }

    public function add_card($available_connectors) {
        $available_connectors['autonami']['connectors']['bwfco_smscru'] = array(
            'name'            => 'SMSC.ru',
            'desc'            => __('Send SMS via SMSC.ru', 'autonami-automations-connectors'),
            'connector_class' => 'BWFCO_SMSCRU',
            'image'           => $this->get_image(),
            'source'          => '',
            'file'            => '',
        );

        return $available_connectors;
    }
}

WFCO_Load_Connectors::register('BWFCO_SMSCRU');