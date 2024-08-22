<?php
/**
 * Plugin Name: Autonami Marketing Automations Connectors - SMSC.ru
 * Plugin URI: https://my.mamatov.club
 * Description: Now create SMSC.ru based automations with Autonami Marketing Automations for WordPress
 * Version: 1.2.0
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
    public static $_instance = null;

    private function __construct() {
        $this->define_plugin_properties();
        $this->load_commons();
    }

    public function define_plugin_properties() {
        define( 'WFCO_SMSCRU_VERSION', '1.2.0' );
        define( 'WFCO_SMSCRU_FULL_NAME', 'Autonami Marketing Automations Connectors : SMSC.ru' );
        define( 'WFCO_SMSCRU_PLUGIN_FILE', __FILE__ );
        define( 'WFCO_SMSCRU_PLUGIN_DIR', __DIR__ );
        define( 'WFCO_SMSCRU_PLUGIN_URL', untrailingslashit( plugin_dir_url( WFCO_SMSCRU_PLUGIN_FILE ) ) );
        define( 'WFCO_SMSCRU_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        define( 'WFCO_SMSCRU_MAIN', 'autonami-automations-connectors' );
    }

    public function load_commons() {
        add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
        add_action('wfco_load_connectors', array($this, 'load_connector_classes'));
        add_action('bwfan_loaded', array($this, 'load_autonami_classes'));
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain('wp-marketing-automations-connector-smscru', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function load_autonami_classes() {
        require_once(WFCO_SMSCRU_PLUGIN_DIR . '/autonami/class-bwfan-smscru-integrations.php');
        require_once(WFCO_SMSCRU_PLUGIN_DIR . '/autonami/actions/class-bwfan-smscru-send-sms.php');
        do_action('wfco_smscru_integrations_loaded', $this);
    }

    public static function get_instance() {
        if ( null === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function init_smscru() {
        require_once WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php';
    }

    public function load_connector_classes() {
        require_once(WFCO_SMSCRU_PLUGIN_DIR . '/includes/class-wfco-smscru-call.php');
        require_once(WFCO_SMSCRU_PLUGIN_DIR . '/calls/class-wfco-smscru-get-balance.php');
        require_once(WFCO_SMSCRU_PLUGIN_DIR . '/calls/class-wfco-smscru-send-sms.php');
        require_once(WFCO_SMSCRU_PLUGIN_DIR . '/connector.php');
        WFCO_Load_Connectors::register('BWFCO_SMSCRU');
        do_action('wfco_smscru_connector_loaded', $this);
    }

    public function register_smscru_action( $actions ) {
        $actions['smscru_send_sms'] = 'BWFAN_SMSCRU_Send_Sms';
        return $actions;
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