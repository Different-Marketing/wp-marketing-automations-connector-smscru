<?php

class BWFAN_SMSCRU_Test_Integration {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_test_menu'));
        add_action('wp_ajax_smscru_send_test_sms', array($this, 'ajax_send_test_sms'));
    }

    public function add_test_menu() {
        add_submenu_page(
            'bwfan-dashboard',  // Родительское меню Autonami
            'Тест SMSC.ru SMS',
            'Тест SMSC.ru SMS',
            'manage_options',
            'smscru-test-sms',
            array($this, 'render_test_page')
        );
    }

    public function render_test_page() {
        ?>
        <div class="wrap">
            <h1>Тестирование отправки SMS через SMSC.ru</h1>
            <form id="smscru-test-form">
                <label for="test-phone">Номер телефона:</label>
                <input type="text" id="test-phone" name="test-phone" required>
                <label for="test-message">Сообщение:</label>
                <textarea id="test-message" name="test-message" required></textarea>
                <button type="submit" class="button button-primary">Отправить тестовое SMS</button>
            </form>
            <div id="result-message" style="margin-top: 15px;"></div>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#smscru-test-form').on('submit', function(e) {
                e.preventDefault();
                var phone = $('#test-phone').val();
                var message = $('#test-message').val();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'smscru_send_test_sms',
                        phone: phone,
                        message: message
                    },
                    success: function(response) {
                        $('#result-message').html(response);
                    },
                    error: function() {
                        $('#result-message').html('Произошла ошибка при отправке запроса.');
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function ajax_send_test_sms() {
        check_ajax_referer('smscru_test_sms', 'security');

        if (!current_user_can('manage_options')) {
            wp_die('У вас нет прав для выполнения этого действия');
        }

        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        if (empty($phone) || empty($message)) {
            echo "Пожалуйста, заполните все поля.";
            wp_die();
        }

        $smscru_sender = BWFAN_SMSCRU_Send_Sms::get_instance();
        $settings = BWFAN_Core()->connectors->get_connector_by_slug('bwfco_smscru')->get_settings();

        $smscru_sender->set_data([
            'login' => $settings['login'],
            'password' => $settings['password'],
        ]);

        $result = $smscru_sender->send_test_sms($phone, $message);

        if ($result) {
            echo "Тестовое SMS успешно отправлено на номер $phone";
        } else {
            echo "Ошибка при отправке тестового SMS. Проверьте логи для получения дополнительной информации.";
        }

        wp_die();
    }
}

// Инициализация класса
add_action('plugins_loaded', array('BWFAN_SMSCRU_Test_Integration', 'get_instance'));
?>