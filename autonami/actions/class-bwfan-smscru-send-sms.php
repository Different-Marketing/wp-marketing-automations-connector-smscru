<?php
//https://smsc.ru/api/#menu

class BWFAN_SMSCRU_Send_Sms extends BWFAN_Action {
    private static $instance = null;
    private $progress = false;
    public $support_language = true;

    /**
     * Initialize the class
     *
     * Sets the action name and description
     * Sets the support for versions
     *
     * @return void
     */
    public function __construct() {
        $this->action_name = __( 'Send Message', 'autonami-automations-connectors' );
        $this->action_desc = __( 'This action sends a message via SMSC.ru', 'autonami-automations-connectors' );
        $this->support_v2  = true;
        $this->support_v1  = false;
    }

    /**
     * Returns the instance of the class.
     *
     * @return BWFAN_SMSCRU_Send_Sms
     * @since 1.0.0
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load hooks for this action.
     *
     * @since 2.0.0
     */
    public function load_hooks() {
        add_filter( 'bwfan_modify_send_sms_body', array( $this, 'shorten_link' ), 15, 2 );
    }


    /**
     * Shorten URLs in the message body.
     *
     * @since 2.0.0
     *
     * @param string $body The message body.
     * @param array  $data The automation data.
     *
     * @return string The modified message body.
     */
    public function shorten_link( $body, $data ) {
        if ( true === $this->progress ) {
            $body = preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array( $this, 'shorten_urls' ), $body );
        }
        return preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array( $this, 'unsubscribe_url_with_mode' ), $body );
    }

	/**
	 * Shortens the given URL by checking if the method for shortening URLs exists in the
	 * BWFAN_Connectors_Common class. If it does, it calls that method to shorten the URL.
	 * If the method does not exist, it uses the do_shortcode function to shorten the URL
	 * by applying the [bwfan_bitly_shorten] shortcode.
	 *
     * TODO: функция аналогичная twillo и smsniaga
	 * Обрабатывает сокращение ссылок в теле сообщения.
     * 
	 * @param array $matches An array containing matches from a regular expression.
	 * @return string The shortened URL.
	 */
    protected function shorten_urls( $matches ) {
        $string = $matches[0];
        if ( method_exists( 'BWFAN_Connectors_Common', 'get_shorten_url' ) ) {
            return BWFAN_Connectors_Common::get_shorten_url( $string );
        }
        return do_shortcode( '[bwfan_bitly_shorten]' . $string . '[/bwfan_bitly_shorten]' );
    }


    /**
     * Make v2 data for action.
     *
     * @param array $automation_data Automation data.
     * @param array $step_data       Step data.
     *
     * @return array v2 data.
     */
    public function make_v2_data( $automation_data, $step_data ) {
        $this->add_action();
        $this->progress = true;
        $sms_body       = isset( $step_data['sms_body_textarea'] ) ? $step_data['sms_body_textarea'] : '';

        $data_to_set = array(
            'name'            => BWFAN_Common::decode_merge_tags( '{{customer_first_name}}' ),
            'promotional_sms' => ( isset( $step_data['promotional_sms'] ) ) ? 1 : 0,
            'append_utm'      => ( isset( $step_data['sms_append_utm'] ) ) ? 1 : 0,
            'sms_utm_source'  => isset($step_data['sms_utm_source']) ? $step_data['sms_utm_source'] : '',
            'sms_utm_medium'  => isset($step_data['sms_utm_medium']) ? $step_data['sms_utm_medium'] : '',
            'sms_utm_campaign'=> isset($step_data['sms_utm_campaign']) ? $step_data['sms_utm_campaign'] : '',
            'sms_utm_term'    => isset($step_data['sms_utm_term']) ? $step_data['sms_utm_term'] : '',
            'number'          => ( isset( $step_data['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $step_data['sms_to'] ) : '',
            'phones'          => ( isset( $step_data['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $step_data['sms_to'] ) : '',
            'event'           => ( isset( $step_data['event_data'] ) && isset( $step_data['event_data']['event_slug'] ) ) ? $step_data['event_data']['event_slug'] : '',
            'mes'            => BWFAN_Common::decode_merge_tags( $sms_body ),
            'step_id'         => isset( $automation_data['step_id'] ) ? $automation_data['step_id'] : '',
            'automation_id'   => isset( $automation_data['automation_id'] ) ? $automation_data['automation_id'] : '',
        );

        $data_to_set['login']    = isset( $step_data['connector_data']['login'] ) ? $step_data['connector_data']['login'] : '';
        $data_to_set['password'] = isset( $step_data['connector_data']['password'] ) ? $step_data['connector_data']['password'] : '';

        // UTM параметры и другие настройки
        if ( isset( $step_data['sms_utm_source'] ) && ! empty( $step_data['sms_utm_source'] ) ) {
			$data_to_set['utm_source'] = BWFAN_Common::decode_merge_tags( $step_data['sms_utm_source'] );
		}
		if ( isset( $step_data['sms_utm_medium'] ) && ! empty( $step_data['sms_utm_medium'] ) ) {
			$data_to_set['utm_medium'] = BWFAN_Common::decode_merge_tags( $step_data['sms_utm_medium'] );
		}
		if ( isset( $step_data['sms_utm_campaign'] ) && ! empty( $step_data['sms_utm_campaign'] ) ) {
			$data_to_set['utm_campaign'] = BWFAN_Common::decode_merge_tags( $step_data['sms_utm_campaign'] );
		}
		if ( isset( $step_data['sms_utm_term'] ) && ! empty( $step_data['sms_utm_term'] ) ) {
			$data_to_set['utm_term'] = BWFAN_Common::decode_merge_tags( $step_data['sms_utm_term'] );
		}

        // TODO: what is global ?
		if ( isset( $automation_data['global'] ) && isset( $automation_data['global']['order_id'] ) ) {
			$data_to_set['order_id'] = $automation_data['global']['order_id'];
		} elseif ( isset( $automation_data['global'] ) && isset( $automation_data['global']['cart_abandoned_id'] ) ) {
			$data_to_set['cart_abandoned_id'] = $automation_data['global']['cart_abandoned_id'];
		}

		/** TODO: If promotional checkbox is not checked, then empty the {{unsubscribe_link}} merge tag */
		if ( isset( $data_to_set['promotional_sms'] ) && 0 === absint( $data_to_set['promotional_sms'] ) ) {
			if (isset($this->data['text']) && !is_null($this->data['text'])) {
                $this->data['text'] = str_replace( '{{unsubscribe_link}}', '', $this->data['text'] );
            } else {
                // Если 'text' не определен или null, установите его значение по умолчанию
                $this->data['text'] = '';
                error_log('SMSC.ru: Text is not set in the data array');
            }
		}

        /**  TODO: Append UTM and Create Conversation (Engagement Tracking) */
        $data_to_set['text'] = stripslashes( $data_to_set['text'] );
        $data_to_set['text'] = BWFAN_Connectors_Common::modify_sms_body( $data_to_set['text'], $data_to_set );

        /** TODO: Validating promotional sms */
		if ( 1 === absint( $data_to_set['promotional_sms'] ) && ( false === apply_filters( 'bwfan_force_promotional_sms', false, $data_to_set ) ) ) {
			$where             = array(
				'recipient' => $data_to_set['phone'],
				'mode'      => 2,
			);
			$check_unsubscribe = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row( $where );

			if ( ! empty( $check_unsubscribe ) ) {
				$this->progress = false;

				$data_to_set['contact_unsubscribed'] = true;
			}
		}

        $this->remove_action();
        return $data_to_set;
    }

    /**
     * Execute the current action.
     * Return 3 for successful execution , 4 for permanent failure.
     * Выполняет отправку SMS
     * @param $action_data
     *
     * @return array
     */
    public function execute_action( $action_data ) {
        error_log('SMSC.ru execute_action data: ' . print_r($action_data, true));
        global $wpdb;
        $this->set_data( $action_data['processed_data'] );
        error_log('SMSC.ru processed_data: ' . print_r($this->data, true));
        $this->data['task_id'] = $action_data['task_id'];

        // Attach track id
        $sql_query         = 'Select meta_value FROM {table_name} WHERE bwfan_task_id = %d AND meta_key = %s';
        $sql_query         = $wpdb->prepare( $sql_query, $this->data['task_id'], 't_track_id' ); //phpcs:ignore WordPress.DB.PreparedSQL
        $gids              = BWFAN_Model_Taskmeta::get_results( $sql_query );
        $this->data['gid'] = '';
        if ( ! empty( $gids ) && is_array( $gids ) ) {
            foreach ( $gids as $gid ) {
                $this->data['gid'] = $gid['meta_value'];
            }
        }
    
        // Validate promotional SMS
        if ( 1 === absint( $this->data['promotional_sms'] ) && ( false === apply_filters( 'bwfan_force_promotional_sms', false, $this->data ) ) ) {
            $where             = array(
                'recipient' => $this->data['number'],
                'mode'      => 2,
            );
            $check_unsubscribe = BWFAN_Model_Message_Unsubscribe::get_message_unsubscribe_row( $where );
    
            if ( ! empty( $check_unsubscribe ) ) {
                $this->progress = false;
                return array(
                    'status'  => 4,
                    'message' => __( 'User is already unsubscribed', 'autonami-automations-connectors' ),
                );
            }
        }
  
        // Modify SMS body Append UTM and Create Conversation (Engagement Tracking)
        $this->data['text'] = BWFAN_Connectors_Common::modify_sms_body( $this->data['text'], $this->data );
        
        // Validate connector
        $load_connector = WFCO_Load_Connectors::get_instance();
        $call_class     = $load_connector->get_call( 'wfco_smscru_send_sms' );
        if ( is_null( $call_class ) ) {
            $this->progress = false;
            return array(
                'status'  => 4,
                'message' => __( 'Send SMS call not found', 'autonami-automations-connectors' ),
            );
        }
  
        $integration            = BWFAN_SMSCRU_Integration::get_instance();
        $call_args = array(
            'login'    => $integration->get_settings( 'login' ),
            'password' => $integration->get_settings( 'password' ),
            'phones'   => isset($this->data['number']) ? $this->data['number'] : (isset($this->data['phone']) ? $this->data['phone'] : ''),
            'mes'      => isset($this->data['text']) ? $this->data['text'] : '',
        );
        error_log('SMSC.ru call_args: ' . print_r($call_args, true));
        $call_class->set_data( $this->data );
        error_log('SMSC.ru data before sending: ' . print_r($call_args, true));
        $response = $call_class->process();
        do_action( 'bwfan_sendsms_action_response', $response, $this->data );
        
        /*
        if ( is_array( $response ) && true === $response['status'] ) {
            $this->progress = false;
            return array(
                'status'  => 3,
                'message' => __( 'SMS sent successfully.', 'autonami-automations-connectors' ),
            );
        }
         */

        // New response validate
        if (is_array($response) && isset($response['body'])) {
            $body = json_decode($response['body'], true);
            
            if (isset($body['id']) && isset($body['cnt'])) {
                $this->progress = false;
                return array(
                    'status'  => 3,
                    'message' => sprintf(__('SMS sent successfully. Message ID: %s', 'autonami-automations-connectors'), $body['id']),
                );
            } elseif (isset($body['error'])) {
                $message = sprintf(__('Message could not be sent. Error: %s', 'autonami-automations-connectors'), $body['error']);
                $status  = 4;
            }
        }
    
        if (!isset($message)) {
            $message = __('Unexpected response from SMSC.ru', 'autonami-automations-connectors');
        }
    
        $this->progress = false;
        return array(
            'status'  => isset($status) ? $status : 4,
            'message' => $message,
        );
        $this->progress = false;
        
        return array(
            'status'  => $status,
            'message' => isset( $response['message'] ) ? $response['message'] : __( 'SMS could not be sent.', 'autonami-automations-connectors' ),
        );
    }

    /**
	 * Добавляет параметр mode=2 к URL отписки.
	 *
	 * @param $matches
	 *
	 * @return string
	 */
	protected function unsubscribe_url_with_mode( $matches ) {
		$string = $matches[0];

		/** if its a unsubscriber link then pass the mode in url */
		if ( strpos( $string, 'unsubscribe' ) !== false ) {
			$string = add_query_arg( array(
				'mode' => 2,
			), $string );
		}

		return $string;
	}

    /**
    * Handle response for V2
    *
    * @param array $response V2 response.
    *
    * @return array
    */
    public function handle_response_v2( $response ) {
        do_action( 'bwfan_sendsms_action_response', $response, $this->data );
        if ( is_array( $response ) && true === $response['status'] ) {
            $this->progress = false;

            return $this->success_message( __( 'SMS sent successfully.', 'autonami-automations-connectors' ) );
        }
    
        $this->progress = false;

        return $this->skipped_response( isset( $response['message'] ) ? $response['message'] : __( 'SMS could not be sent.', 'autonami-automations-connectors' ) );
    }

    /**
     * Returns an array of field schema for the SMSC.ru connector.
     *
     * The schema includes fields for recipient phone number and message body,
     * each with their respective labels, types, classes, and placeholders. Both
     * fields are required.
     *
     * @return array An array of field schema.
     */
    public function get_fields_schema() {
        return [
            [
                'id'          => 'sms_to',
                'label'       => __( "To", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => "",
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( '', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => true,
            ],
            [
                'id'          => 'sms_body_textarea',
                'label'       => __( "Text", 'wp-marketing-automations' ),
                'type'        => 'textarea',
                'placeholder' => "Message Body",
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( '', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => true,
            ],
        ];
    }

    public function send_test_sms($phone, $message) {
        error_log("Sending test SMS to: $phone");
        
        if (empty($this->data['login']) || empty($this->data['password'])) {
            error_log("Missing login or password for SMSC.ru");
            return false;
        }
    
        $url = 'https://smsc.ru/sys/send.php';
        
        $params = array(
            'login'  => $this->data['login'],
            'psw'    => $this->data['password'],
            'phones' => $phone,
            'mes'    => $message,
            'charset' => 'utf-8',
            'fmt'    => 3  // Формат ответа JSON
        );
    
        $url = add_query_arg($params, $url);
    
        $response = wp_remote_get($url);
    
        if (is_wp_error($response)) {
            error_log("SMSC.ru API error: " . $response->get_error_message());
            return false;
        }
    
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
    
        error_log("SMSC.ru API response: " . print_r($result, true));
    
        if (isset($result['error'])) {
            error_log("SMSC.ru API error: " . $result['error']);
            return false;
        }
    
        return true;
    }



    // Добавленный метод add_action
    public function add_action() {
        $this->progress = true;
    }
      
    /**
     * Removes the filters that were added in add_action.
     * 
     * Filters are removed for bwfan_order_billing_address_params and
     * bwfan_order_shipping_address_separator.
     */
    private function remove_action() {
        remove_filter( 'bwfan_order_billing_address_params', array( $this, 'change_br_to_slash_n' ) );
        remove_filter( 'bwfan_order_shipping_address_separator', array( $this, 'change_br_to_slash_n' ) );
    }

    /**
     * Replaces <br /> with \n in the given string.
     * 
     * @param string $params The string to replace <br /> with \n.
     * 
     * @return string The string with <br /> replaced with \n.
     */
    public function change_br_to_slash_n( $params ) {
		
        return "\n";
	}

}

return 'BWFAN_SMSCRU_Send_Sms';