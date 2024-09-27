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
        $old_data   = isset($saved_data[$this->get_slug()]) ? $saved_data[$this->get_slug()] : array();
        
        return array(
            'login'    => isset($old_data['login']) ? $old_data['login'] : '',
            'password' => isset($old_data['password']) ? $old_data['password'] : '',
        );
    }

    /**
     * Retrieves API data based on the provided login and password.
     *
     * @param array $posted_data An array containing the login and password.
     * @return array An array containing the API data or an error message.
     */
    protected function get_api_data($posted_data) {
        $resp_array = array(
            'api_data' => $posted_data,
            'status'   => 'failed',
            'message'  => __('There was a problem authenticating your account. Please confirm your entered details.', 'autonami-automations-connectors'),
        );
    
        if (empty($posted_data['login']) || empty($posted_data['password'])) {
            $resp_array['message'] = __('Login and password are required.', 'autonami-automations-connectors');
            return $resp_array;
        }
    
        $login = $posted_data['login'];
        $password = $posted_data['password'];
    
        // Проверка баланса для аутентификации
        // https://smsc.ru/sys/balance.php?login=mamatov&psw=sdrijgwyg460u7&fmt=3
        $balance_url = "https://smsc.ru/sys/balance.php?login=" . urlencode($login) . "&psw=" . urlencode($password) . "&fmt=3";
        $response = wp_remote_get($balance_url);
    
        if (is_wp_error($response)) {
            $resp_array['message'] = $response->get_error_message();
            return $resp_array;
        }
    
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
    
        if (isset($result['error'])) {
            $resp_array['message'] = $result['error'];
            return $resp_array;
        }
    
        if (isset($result['balance'])) {
            $resp_array['status'] = 'success';
            $resp_array['message'] = sprintf(__('Successfully connected to SMSC.ru. Current balance: %s', 'autonami-automations-connectors'), $result['balance']);
            $resp_array['api_data']['balance'] = $result['balance'];
            $resp_array['api_data']['login'] = $login;
            $resp_array['api_data']['password'] = $password;
    
            return $resp_array;
        }
    
        $resp_array['message'] = __('Unknown error occurred while connecting to SMSC.ru', 'autonami-automations-connectors');
        
        return $resp_array;
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Adds the SMSC.ru connector to the list of available connectors.
     *
     * @param array $available_connectors The list of available connectors.
     * @return array The updated list of available connectors with the SMSC.ru connector added.
     */
    public function add_card($available_connectors) {
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
        $resp_array = array(
            'api_data' => $posted_data,
            'status'   => 'failed',
            'message'  => __('There was a problem authenticating your account. Please confirm your entered details.', 'autonami-automations-connectors'),
        );
    
        if (empty($posted_data['login']) || empty($posted_data['password'])) {
            $resp_array['message'] = __('Login and password are required.', 'autonami-automations-connectors');
            return $resp_array;
        }
    
        $login = $posted_data['login'];
        $password = $posted_data['password'];
    
        // Проверка баланса для аутентификации
        // https://smsc.ru/sys/balance.php?login=mamatov&psw=sdrijgwyg460u7&fmt=3
        $balance_url = "https://smsc.ru/sys/balance.php?login=" . urlencode($login) . "&psw=" . urlencode($password) . "&fmt=3";
        $response = wp_remote_get($balance_url);
    
        if (is_wp_error($response)) {
            $resp_array['message'] = $response->get_error_message();
            return $resp_array;
        }
    
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
    
        if (isset($result['error'])) {
            $resp_array['message'] = $result['error'];
            return $resp_array;
        }
    
        if (isset($result['balance'])) {
            $resp_array['status'] = 'success';
            $resp_array['message'] = sprintf(__('Successfully connected to SMSC.ru. Current balance: %s', 'autonami-automations-connectors'), $result['balance']);
            $resp_array['api_data']['balance'] = $result['balance'];
            $resp_array['api_data']['login'] = $login;
            $resp_array['api_data']['password'] = $password;
    
            return $resp_array;
        }
    
        $resp_array['message'] = __('Unknown error occurred while connecting to SMSC.ru', 'autonami-automations-connectors');
        
        return $resp_array;
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
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