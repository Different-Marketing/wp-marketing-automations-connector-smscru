<?php

class WFCO_SMSCRU_Common {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get_api_endpoint() {
        return 'https://smsc.ru/sys/send.php';
    }

    public static function get_smscru_settings() {
        if ( false === WFCO_Common::$saved_data ) {
            WFCO_Common::get_connectors_data();
        }
        $data = WFCO_Common::$connectors_saved_data;
        $slug = self::get_connector_slug();
        $data = ( isset( $data[ $slug ] ) && is_array( $data[ $slug ] ) ) ? $data[ $slug ] : array();
        return $data;
    }

    public static function get_connector_slug() {
        return sanitize_title( BWFCO_SMSCRU::class );
    }
}

WFCO_SMSCRU_Common::get_instance();