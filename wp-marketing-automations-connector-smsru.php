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
    public static $_instance = null;

    private function __construct() {
        $this->define_plugin_properties();
        $this->load_commons();
    }

    public function define_plugin_properties() {
        define( 'WFCO_SMSCRU_VERSION', '2.0.8' );
        define( 'WFCO_SMSCRU_FULL_NAME', 'Autonami Marketing Automations Connectors : SMSC.ru' );
        define( 'WFCO_SMSCRU_PLUGIN_FILE', __FILE__ );
        define( 'WFCO_SMSCRU_PLUGIN_DIR', __DIR__ );
        define( 'WFCO_SMSCRU_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_SMSCRU_PLUGIN_FILE ) ) );
        define( 'WFCO_SMSCRU_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        define( 'WFCO_SMSCRU_MAIN', 'autonami-automations-connectors' );
    }

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
        require_once WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php';
    }

    public function load_connector_classes() {
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/connector.php' );
        do_action( 'wfco_smscru_connector_loaded', $this );
    }

    public function load_autonami_classes() {
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/autonami/class-bwfan-smscru-integrations.php' );
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/autonami/actions/class-bwfan-smscru-send-sms.php' );
        do_action( 'wfco_smscru_integrations_loaded', $this );
    }

    public function init_test_integration() {
        require_once( WFCO_SMSCRU_PLUGIN_DIR . '/class-bwfan-smscru-test-integration.php' );
        BWFAN_SMSCRU_Test_Integration::get_instance();
    }
}

function WFCO_SMSCRU_Core() {
    return WFCO_SMSCRU::get_instance();
}

WFCO_SMSCRU_Core();