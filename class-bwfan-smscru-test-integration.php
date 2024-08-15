<?php
require_once plugin_dir_path(__FILE__) . 'autonami/actions/class-bwfan-smscru-send-sms.php';
class BWFAN_SMSCRU_Test_Integration {
    private static $instance = null;

    /**
     * Получение единственного экземпляра класса (паттерн Singleton)
     *
     * @return BWFAN_SMSCRU_Test_Integration
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Конструктор класса
     * Инициализирует хуки для добавления меню и обработки AJAX-запросов
     */
    private function __construct() {
        add_action('admin_menu', array($this, 'add_test_menu'));
        add_action('wp_ajax_smscru_send_test_sms', array($this, 'ajax_send_test_sms'));
    }

    /**
     * Добавляет пункт меню для тестовой страницы в админ-панель WordPress
     */
    public function add_test_menu() {
        add_submenu_page(
            'tools.php',  // Родительское меню Инструменты
            'Тест SMSC.ru SMS',
            'Тест SMSC.ru SMS',
            'manage_options',
            'smscru-test-sms',
            array($this, 'render_test_page')
        );
    }

    /**
     * Отображает страницу тестирования SMS
     */
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
                            message: message,
                            security: '<?php echo wp_create_nonce("smscru_test_sms"); ?>'
                        },
                        success: function(response) {
                            if(response.success) {
                                $('#result-message').html(response.data);
                            } else {
                                $('#result-message').html('Ошибка: ' + response.data);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $('#result-message').html('Произошла ошибка при отправке запроса: ' + textStatus);
                            console.error(errorThrown);
                        }
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Обрабатывает AJAX-запрос на отправку тестового SMS
     */
    public function ajax_send_test_sms() {
        check_ajax_referer('smscru_test_sms', 'security');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('У вас нет прав для выполнения этого действия');
        }
    
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    
        if (empty($phone) || empty($message)) {
            wp_send_json_error("Пожалуйста, заполните все поля.");
        }
    
        if (!class_exists('BWFAN_SMSCRU_Send_Sms')) {
            wp_send_json_error("Класс BWFAN_SMSCRU_Send_Sms не найден.");
        }
    
        $smscru_sender = BWFAN_SMSCRU_Send_Sms::get_instance();
        
        // Получаем сохраненные данные коннекторов
        $saved_data = WFCO_Common::$connectors_saved_data;
        $smscru_settings = isset($saved_data['bwfco_smscru']) ? $saved_data['bwfco_smscru'] : array();
    
        if (empty($smscru_settings)) {
            wp_send_json_error("Настройки SMSC.ru не найдены.");
        }
    
        $smscru_sender->set_data([
            'login' => $smscru_settings['login'] ?? '',
            'password' => $smscru_settings['password'] ?? '',
        ]);
    
        $result = $smscru_sender->send_test_sms($phone, $message);
    
        if ($result) {
            wp_send_json_success("Тестовое SMS успешно отправлено на номер $phone");
        } else {
            wp_send_json_error("Ошибка при отправке тестового SMS. Проверьте логи для получения дополнительной информации.");
        }
    }
}

/**
 * Инициализация класса при загрузке плагинов
 */
add_action('plugins_loaded', array('BWFAN_SMSCRU_Test_Integration', 'get_instance'));