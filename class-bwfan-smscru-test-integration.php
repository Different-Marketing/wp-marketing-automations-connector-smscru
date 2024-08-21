<?php
require_once plugin_dir_path(__FILE__) . 'autonami/actions/class-bwfan-smscru-send-sms.php';

class BWFAN_SMSCRU_Test_Integration {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', array($this, 'add_test_menu'));
        add_action('wp_ajax_smscru_send_test_sms', array($this, 'ajax_send_test_sms'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts($hook) {
        if ('tools_page_smscru-test-sms' !== $hook) {
            return;
        }
    
        wp_enqueue_script('jquery');
        wp_enqueue_script('smscru-test-script', plugin_dir_url(__FILE__) . 'js/smscru-test.js', array('jquery'), time(), true);
        $smscru_ajax = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smscru_test_sms')
        );
        wp_localize_script('smscru-test-script', 'smscru_ajax', $smscru_ajax);
    }

    public function add_test_menu() {
        add_submenu_page(
            'tools.php',
            'Test SMSC.ru SMS',
            'Test SMSC.ru SMS',
            'manage_options',
            'smscru-test-sms',
            array($this, 'render_test_page')
        );
    }

    public function render_test_page() {
        ?>
        <div class="wrap">
            <h1>Test SMSC.ru SMS Sending</h1>
            <form id="smscru-test-form">
                <label for="test-phone">Phone Number:</label>
                <input type="text" id="test-phone" name="test-phone" required>
                <label for="test-message">Message:</label>
                <textarea id="test-message" name="test-message" required></textarea>
                <button type="submit" class="button button-primary">Send Test SMS</button>
            </form>
            <div id="result-message" style="margin-top: 15px;"></div>
        </div>
        <?php
    }

    public function ajax_send_test_sms() {
        check_ajax_referer('smscru_test_sms', 'security');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You do not have permission to perform this action');
        }
    
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    
        if (empty($phone) || empty($message)) {
            wp_send_json_error("Please fill in all fields.");
        }
    
        $smscru_settings = WFCO_Common::get_single_connector_data('bwfco_smscru');
    
        if (empty($smscru_settings) || empty($smscru_settings['login']) || empty($smscru_settings['password'])) {
            wp_send_json_error("SMSC.ru settings not found or incomplete.");
        }
    
        $smscru_sender = BWFAN_SMSCRU_Send_Sms::get_instance();
        $smscru_sender->set_data($smscru_settings);
    
        $result = $smscru_sender->send_test_sms($phone, $message);
    
        if ($result) {
            wp_send_json_success("Test SMS successfully sent to $phone");
        } else {
            wp_send_json_error("Error sending test SMS. Check logs for more information.");
        }
    }
}