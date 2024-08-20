<?php

class WFCO_SMSCRU_Connector extends BWF_CO {

    public function __construct() {
        $this->slug = 'wfco_smscru';
        $this->name = __('SMSC.ru', 'wp-marketing-automations-connector-smscru');
        parent::__construct();
    }

    public static function get_instance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
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

    public function setting_view() {
        // Implement settings view if needed
    }
}