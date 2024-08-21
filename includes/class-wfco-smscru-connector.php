<?php

class WFCO_SMSCRU_Connector {
    public $slug;
    public $name;
    public $nice_name;
    public $connector_url;
    public $dir;
    public $autonami_int_slug;
    public $keys_to_track;
    public $form_req_keys;

    public function __construct() {
        $this->slug = 'wfco_smscru';
        $this->name = __('SMSC.ru', 'wp-marketing-automations-connector-smscru');
        $this->nice_name = __('SMSC.ru', 'wp-marketing-automations-connector-smscru');
        $this->connector_url = WFCO_SMSCRU_PLUGIN_URL;
        $this->dir = WFCO_SMSCRU_PLUGIN_DIR;
        $this->autonami_int_slug = 'BWFAN_SMSCRU_Integration';

        $this->keys_to_track = array('login', 'password');
        $this->form_req_keys = array('login', 'password');

        add_filter('wfco_connectors_loaded', array($this, 'add_connector_card'));
    }

    public function get_slug() {
        return $this->slug;
    }

    public function load_calls() {
        error_log("Loading SMSCRU calls");
        require_once WFCO_SMSCRU_PLUGIN_DIR . '/calls/class-wfco-smscru-send-sms.php';
        require_once WFCO_SMSCRU_PLUGIN_DIR . '/calls/class-wfco-smscru-get-balance.php';

        WFCO_Load_Connectors::register_calls(WFCO_SMSCRU_Send_Sms::get_instance());
        WFCO_Load_Connectors::register_calls(WFCO_SMSCRU_Get_Balance::get_instance());
        error_log("SMSCRU calls loaded: " . print_r(WFCO_Load_Connectors::get_instance()->get_calls(), true));
    }

    public function add_connector_card($connectors) {
        $connectors['autonami']['connectors'][$this->get_slug()] = array(
            'name'            => $this->name,
            'desc'            => __('Send SMS via SMSC.ru', 'wp-marketing-automations-connector-smscru'),
            'connector_class' => get_class($this),
            'image'           => WFCO_SMSCRU_PLUGIN_URL . '/assets/img/smscru-logo.png',
            'source'          => 'autonami',
        );
        return $connectors;
    }

    public function setting_view() {
        // Implement settings view if needed
    }
}