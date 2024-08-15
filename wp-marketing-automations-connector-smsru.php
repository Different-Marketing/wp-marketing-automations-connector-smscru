<?php

/**
 * Plugin Name: Autonami Marketing Automations Connectors - SMSC.ru
 * Plugin URI: https://my.mamatov.club
 * Description: Now create SMSC.ru based automations with Autonami Marketing Automations for WordPress
 * Version: 1.0.3
 * Author: Evgenii Rezanov, Claude Ai
 * Author URI: https://evgrezanov.github.io
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: autonami-automations-connectors
 *
 * Requires at least: 4.9
 * Tested up to: 5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class WFCO_SMSCRU {
    /**
     * @var WFCO_SMSCRU
     */
    public static $_instance = null;

    private function __construct() {
        // Загрузка важных переменных и констант
        $this->define_plugin_properties();

        // Загрузка общих файлов
        $this->load_commons();

        // Загрузка файла для тестовой интеграции
        $this->load_test_integration();
    }

    // Определение констант
    public function define_plugin_properties() {
        define( 'WFCO_SMSCRU_VERSION', '1.0.3' );
        define( 'WFCO_SMSCRU_FULL_NAME', 'Autonami Marketing Automations Connectors : SMSC.ru' );
        define( 'WFCO_SMSCRU_PLUGIN_FILE', __FILE__ );
        define( 'WFCO_SMSCRU_PLUGIN_DIR', __DIR__ );
        define( 'WFCO_SMSCRU_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_SMSCRU_PLUGIN_FILE ) ) );
        define( 'WFCO_SMSCRU_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        define( 'WFCO_SMSCRU_MAIN', 'autonami-automations-connectors' );
    }

    // Загрузка общих хуков
    public function load_commons() {
        add_action( 'wfco_load_connectors', [ $this, 'load_connector_classes' ] );
        add_action( 'bwfan_automations_loaded', [ $this, 'load_autonami_classes' ] );
        add_action( 'bwfan_loaded', [ $this, 'init_smscru' ] );
    }

    // Загрузка файла для тестовой интеграции
    public function load_test_integration() {
        require_once plugin_dir_path(__FILE__) . 'class-bwfan-smscru-test-integration.php';
    }

    public static function get_instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function init_smscru() {
        require WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-common.php';
        require WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php';
    }

    // Загрузка классов коннектора
    public function load_connector_classes() {
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-common.php' );
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php' );
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/connector.php' );

        do_action( 'wfco_smscru_connector_loaded', $this );
    }

    // Загрузка классов интеграции Autonami
    public function load_autonami_classes() {
        $integration_dir = WFCO_SMSCRU_PLUGIN_DIR . '/autonami';
        foreach ( glob( $integration_dir . '/class-*.php' ) as $_field_filename ) {
            require_once( $_field_filename );
        }
        do_action( 'wfco_smscru_integrations_loaded', $this );
    }
}

if ( ! function_exists( 'WFCO_SMSCRU_Core' ) ) {
    function WFCO_SMSCRU_Core() {
        return WFCO_SMSCRU::get_instance();
    }
}

WFCO_SMSCRU_Core();