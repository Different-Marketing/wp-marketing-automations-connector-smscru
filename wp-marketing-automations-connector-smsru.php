<?php

/**
 * Plugin Name: Autonami Marketing Automations Connectors - SMSC.ru
 * Plugin URI: https://my.mamatov.club
 * Description: Now create SMSC.ru based automations with Autonami Marketing Automations for WordPress
 * Version: 2.0.8
 * Author: Evgenii Rezanov, Claude.ai
 * Author URI: https://evgrezanov.github.io
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: autonami-automations-connectors
 *
 * Requires at least: 4.9
 * Tested up to: 6.1.1
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
        define( 'WFCO_SMSCRU_VERSION', '2.0.8' );
        define( 'WFCO_SMSCRU_VERSION', '1.0.5' );
        define( 'WFCO_SMSCRU_VERSION', '1.0.6' );
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

    /**
     * Returns the instance of the class.
     *
     * @return WFCO_SMSCRU|null
     * @since 1.0.0
     */
    public static function get_instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Initialization of the connector.
     *
     * Includes the main connector class and the class of the action.
     *
     * @since 2.0.0
     */
    public function init_smscru() {
        require WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-common.php';
        require WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php';
    }

    // Загрузка классов коннектора
    public function load_connector_classes() {
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-common.php' );
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php' );
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/connector.php' );

        // Регистрируем коннектор
        WFCO_Load_Connectors::register('WFCO_SMSCRU');

        // Загружаем и регистрируем вызовы
        $this->load_and_register_calls();

        do_action( 'wfco_smscru_connector_loaded', $this );
    }

    /**
     * Loads and registers the calls for the SMSCRU connector.
     *
     * This function iterates over an array of call files and classes, 
     * includes the files, instantiates the classes, and registers the calls.
     *
     * @return void
     */
    private function load_and_register_calls() {
        $calls = array(
            'class-wfco-smscru-send-sms.php' => 'WFCO_SMSCRU_Send_Sms',
            'class-wfco-smscru-get-balance.php' => 'WFCO_SMSCRU_Get_Balance'
        );

        foreach ($calls as $file => $class) {
            require_once(WFCO_SMSCRU_PLUGIN_DIR . '/calls/' . $file);
            if (method_exists($class, 'get_instance')) {
                $call_instance = call_user_func(array($class, 'get_instance'));
                WFCO_Load_Connectors::register_calls($call_instance);
            } else {
                error_log("Error: Class $class does not have get_instance method");
            }
        }
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