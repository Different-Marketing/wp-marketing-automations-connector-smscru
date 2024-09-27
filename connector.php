<?php

class BWFCO_SMSCRU extends BWF_CO {
    public static $instance = null;
    public $v2 = true;

    /**
     * Constructor.
     *
     * @since 2.0.0
     */
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

    protected function get_api_data($posted_data) {
        $login    = isset($posted_data['login']) ? $posted_data['login'] : '';
        $password = isset($posted_data['password']) ? $posted_data['password'] : '';

        WFCO_SMSCRU_Common::set_headers($login, $password);

        $call_class = new WFCO_SMSCRU_Call();
        $call_class->set_data(array(
            'phones'   => '79119387283', // Test phone number
            'mes' => 'Test message',
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

    /**
     * Adds the SMSC.ru connector to the available connectors array
     *
     * @param array $available_connectors The array of available connectors
     * @return array The updated array of available connectors
     */
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

    /**
     * ? Добавьте этот метод
     * Returns the settings for the SMSC.ru connector.
     *
     * This function reads the saved data for the connector from the common settings
     * array and returns the settings for the current connector. If the settings
     * are not found, an empty array is returned.
     *
     * @return array The settings for the SMSC.ru connector.
     */
    public function get_settings() {
        $saved_data = WFCO_Common::$connectors_saved_data;
        return isset($saved_data[$this->get_slug()]) ? $saved_data[$this->get_slug()] : array();
    }

    // ? Добавьте этот метод
    public function is_connected() {
        $settings = $this->get_settings();
        return !empty($settings['login']) && !empty($settings['password']);
    }
}

WFCO_Load_Connectors::register( 'BWFCO_SMSCRU' );