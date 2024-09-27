<?php
//https://smsc.ru/api/#menu


class BWFAN_SMSCRU_Send_Sms extends BWFAN_Action {
    private static $instance = null;
    private $progress = false;
    public $support_language = true;

    public function __construct() {
        $this->action_name = __( 'Send Message', 'autonami-automations-connectors' );
        $this->action_desc = __( 'This action sends a message via SMSC.ru', 'autonami-automations-connectors' );
        $this->support_v2  = true;
        $this->support_v1  = false;
        //$this->integration_type = 'smscru';
    }

    /**
     * Returns the instance of the class.
     *
     * @return BWFAN_SMSCRU_Send_Sms
     * @since 1.0.0
     */
        //$this->integration_type = 'smscru';
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load_hooks() {
        add_filter( 'bwfan_modify_send_sms_body', array( $this, 'shorten_link' ), 15, 2 );
    }

    public function shorten_link( $body, $data ) {
        if ( true === $this->progress ) {
            $body = preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array( $this, 'shorten_urls' ), $body );
        }
        return preg_replace_callback( '/((\w+:\/\/\S+)|(\w+[\.:]\w+\S+))[^\s,\.]/i', array( $this, 'unsubscribe_url_with_mode' ), $body );
    }

    protected function shorten_urls( $matches ) {
        $string = $matches[0];
        if ( method_exists( 'BWFAN_Connectors_Common', 'get_shorten_url' ) ) {
            return BWFAN_Connectors_Common::get_shorten_url( $string );
        }
        return do_shortcode( '[bwfan_bitly_shorten]' . $string . '[/bwfan_bitly_shorten]' );
    }

    /**
     * Creates an array of data to send to the SMSCRU API.
     * Подготавливает данные для отправки SMS.
     * @param array $automation_data The automation data.
     * @param array $step_data The step data.
     *
     * @return array The data to send to the SMSCRU API.
     */
    public function make_v2_data( $automation_data, $step_data ) {
        $this->add_action();
        $this->progress = true;
        $sms_body       = isset( $step_data['sms_body_textarea'] ) ? $step_data['sms_body_textarea'] : '';

        $data_to_set = array(
            'name'            => BWFAN_Common::decode_merge_tags( '{{customer_first_name}}' ),
            'promotional_sms' => ( isset( $step_data['promotional_sms'] ) ) ? 1 : 0,
            'append_utm'      => ( isset( $step_data['sms_append_utm'] ) ) ? 1 : 0,
            'number'          => ( isset( $step_data['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $step_data['sms_to'] ) : '',
            'phone'           => ( isset( $step_data['sms_to'] ) ) ? BWFAN_Common::decode_merge_tags( $step_data['sms_to'] ) : '',
            'event'           => ( isset( $step_data['event_data'] ) && isset( $step_data['event_data']['event_slug'] ) ) ? $step_data['event_data']['event_slug'] : '',
            'text'            => BWFAN_Common::decode_merge_tags( $sms_body ),
            'step_id'         => isset( $automation_data['step_id'] ) ? $automation_data['step_id'] : '',
            'automation_id'   => isset( $automation_data['automation_id'] ) ? $automation_data['automation_id'] : '',
        );

        $data_to_set['login']    = isset( $step_data['connector_data']['login'] ) ? $step_data['connector_data']['login'] : '';
        $data_to_set['password'] = isset( $step_data['connector_data']['password'] ) ? $step_data['connector_data']['password'] : '';

        // Handle UTM parameters
		$utm_params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term'];
		foreach ( $utm_params as $param ) {
			if ( isset( $step_data["sms_$param"] ) && ! empty( $step_data["sms_$param"] ) ) {
				$data_to_set[$param] = BWFAN_Common::decode_merge_tags( $step_data["sms_$param"] );
			}
		}

        if ( isset( $automation_data['global'] ) && isset( $automation_data['global']['order_id'] ) ) {
			$data_to_set['order_id'] = $automation_data['global']['order_id'];
		} elseif ( isset( $automation_data['global'] ) && isset( $automation_data['global']['cart_abandoned_id'] ) ) {
			$data_to_set['cart_abandoned_id'] = $automation_data['global']['cart_abandoned_id'];
		}

		if ( isset( $data_to_set['promotional_sms'] ) && 0 === absint( $data_to_set['promotional_sms'] ) ) {
			$data_to_set['text'] = str_replace( '{{unsubscribe_link}}', '', $data_to_set['text'] );
		}

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

		if ( isset( $data_to_set['promotional_sms'] ) && 0 === absint( $data_to_set['promotional_sms'] ) ) {
			$data_to_set['text'] = str_replace( '{{unsubscribe_link}}', '', $data_to_set['text'] );
		}

        $data_to_set['text'] = stripslashes( $data_to_set['text'] );
        $data_to_set['text'] = BWFAN_Connectors_Common::modify_sms_body( $data_to_set['text'], $data_to_set );

        $this->remove_action();

        return $data_to_set;
    }

    private function add_action() {
		add_filter( 'bwfan_order_billing_address_separator', array( $this, 'change_br_to_slash_n' ) );
		add_filter( 'bwfan_order_shipping_address_separator', array( $this, 'change_br_to_slash_n' ) );
	}

    private function remove_action() {
		remove_filter( 'bwfan_order_billing_address_params', array( $this, 'change_br_to_slash_n' ) );
		remove_filter( 'bwfan_order_shipping_address_separator', array( $this, 'change_br_to_slash_n' ) );
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
		global $wpdb;
		$this->set_data( $action_data['processed_data'] );
		$this->data['task_id'] = $action_data['task_id'];

		// Attach track id
		$sql_query         = 'Select meta_value FROM {table_name} WHERE bwfan_task_id = %d AND meta_key = %s';
		$sql_query         = $wpdb->prepare( $sql_query, $this->data['task_id'], 't_track_id' );
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

		// Modify SMS body
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
		$this->data['login']    = $integration->get_settings( 'login' );
		$this->data['password'] = $integration->get_settings( 'password' );

		$call_class->set_data( $this->data );
		$response = $call_class->process();
		do_action( 'bwfan_sendsms_action_response', $response, $this->data );

		if ( is_array( $response ) && true === $response['status'] ) {
			$this->progress = false;
			return array(
				'status'  => 3,
				'message' => __( 'SMS sent successfully.', 'autonami-automations-connectors' ),
			);
		}

		$this->progress = false;
		return array(
			'status'  => 4,
			'message' => isset( $response['message'] ) ? $response['message'] : __( 'SMS could not be sent.', 'autonami-automations-connectors' ),
		);
	}

    public function handle_response_v2( $response ) {
		do_action( 'bwfan_sendsms_action_response', $response, $this->data );
		if ( is_array( $response ) && true === $response['status'] ) {
			$this->progress = false;
			return $this->success_message( __( 'SMS sent successfully.', 'autonami-automations-connectors' ) );
		}

		$this->progress = false;
		return $this->skipped_response( isset( $response['message'] ) ? $response['message'] : __( 'SMS could not be sent.', 'autonami-automations-connectors' ) );
	}

    public function before_executing_task() {
		add_filter( 'bwfan_change_tasks_retry_limit', array( $this, 'modify_retry_limit' ), 99 );
		add_filter( 'bwfan_unsubscribe_link', array( $this, 'add_unsubscribe_query_args' ) );
		add_filter( 'bwfan_skip_name_email_from_unsubscribe_link', array( $this, 'skip_name_email' ) );
	}

	public function after_executing_task() {
		remove_filter( 'bwfan_change_tasks_retry_limit', array( $this, 'modify_retry_limit' ), 99 );
		remove_filter( 'bwfan_unsubscribe_link', array( $this, 'add_unsubscribe_query_args' ) );
		remove_filter( 'bwfan_skip_name_email_from_unsubscribe_link', array( $this, 'skip_name_email' ) );
	}

    public function modify_retry_limit( $retry_data ) {
		$retry_data[] = DAY_IN_SECONDS;
		return $retry_data;
	}

	public function change_br_to_slash_n( $params ) {
		return "\n";
	}

	public function set_progress( $progress ) {
		$this->progress = $progress;
	}

    protected function unsubscribe_url_with_mode( $matches ) {
		$string = $matches[0];
		if ( strpos( $string, 'unsubscribe' ) !== false ) {
			$string = add_query_arg( array(
				'mode' => 2,
			), $string );
		}
		return $string;
	}

	public function add_unsubscribe_query_args( $link ) {
		if ( empty( $this->data ) ) {
			return $link;
		}
		if ( isset( $this->data['number'] ) ) {
			$link = add_query_arg( array(
				'subscriber_recipient' => $this->data['number'],
			), $link );
		}
		if ( isset( $this->data['name'] ) ) {
			$link = add_query_arg( array(
				'subscriber_name' => $this->data['name'],
			), $link );
		}
		return $link;
	}

	public function skip_name_email( $flag ) {
		return true;
	}
    public function get_fields_schema() {
        return [
            [
                'id'          => 'sms_to',
                'label'       => __( "To", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => __( "Enter phone number", 'wp-marketing-automations' ),
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( 'Enter the recipient\'s phone number with country code', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => true,
            ],
            [
                'id'          => 'sms_body',
                'label'       => __( "Message", 'wp-marketing-automations' ),
                'type'        => 'textarea',
                'placeholder' => __( "Enter your message", 'wp-marketing-automations' ),
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( 'The content of your SMS message', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => true,
            ],
            [
                'id'          => 'test_sms_to',
                'label'       => __( "Send Test Message", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => __( "Enter test phone number", 'wp-marketing-automations' ),
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( 'Enter a phone number to send a test SMS', 'autonami-automations-connectors' ),
                "description" => __( 'Enter Mobile no with country code', 'autonami-automations-connectors' ),
                "required"    => false,
            ],
            [
                'id'          => 'send_test_sms',
                'type'        => 'send_data',
                'label'       => __( 'Send Test', 'wp-marketing-automations' ),
                'send_action' => 'bwf_test_sms',
                'send_field'  => [
                    'test_sms_to' => 'test_sms_to',
                    'sms_body'    => 'sms_body',
                ],
                "hint"        => __( "Click to send a test SMS", 'wp-marketing-automations' )
            ],
            [
                'id'            => 'promotional_sms',
                'checkboxlabel' => __( "Mark as Promotional", 'wp-marketing-automations' ),
                'type'          => 'checkbox',
                "class"         => '',
                'hint'          => __( 'SMS marked as promotional will not be sent to unsubscribers.', 'wp-marketing-automations' ),
                'description'   => __( 'SMS marked as promotional will not be sent to unsubscribers.', 'autonami-automations-connectors' ),
                "required"      => false,
            ],
            [
                'id'            => 'sms_append_utm',
                'checkboxlabel' => __( "Add UTM parameters to the links", 'wp-marketing-automations' ),
                'type'          => 'checkbox',
                "class"         => '',
                'hint'          => __( 'Add UTM parameters to all links in the SMS.', 'wp-marketing-automations' ),
                'description'   => __( 'Add UTM parameters to all links in the SMS.', 'autonami-automations-connectors' ),
                "required"      => false,
            ],
            [
                'id'          => 'sms_utm_source',
                'label'       => __( "UTM Source", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => __( "Enter UTM source", 'wp-marketing-automations' ),
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( 'The UTM source to add to links', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => false,
                'toggler'     => array(
                    'fields'   => array(
                        array(
                            'id'    => 'sms_append_utm',
                            'value' => true,
                        ),
                    ),
                    'relation' => 'AND',
                ),
            ],
            [
                'id'          => 'sms_utm_medium',
                'label'       => __( "UTM Medium", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => __( "Enter UTM medium", 'wp-marketing-automations' ),
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( 'The UTM medium to add to links', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => false,
                'toggler'     => array(
                    'fields'   => array(
                        array(
                            'id'    => 'sms_append_utm',
                            'value' => true,
                        ),
                    ),
                    'relation' => 'AND',
                ),
            ],
            [
                'id'          => 'sms_utm_campaign',
                'label'       => __( "UTM Campaign", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => __( "Enter UTM campaign", 'wp-marketing-automations' ),
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( 'The UTM campaign to add to links', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => false,
                'toggler'     => array(
                    'fields'   => array(
                        array(
                            'id'    => 'sms_append_utm',
                            'value' => true,
                        ),
                    ),
                    'relation' => 'AND',
                ),
            ],
            [
                'id'          => 'sms_utm_term',
                'label'       => __( "UTM Term", 'wp-marketing-automations' ),
                'type'        => 'text',
                'placeholder' => __( "Enter UTM term", 'wp-marketing-automations' ),
                "class"       => 'bwfan-input-wrapper',
                'tip'         => __( 'The UTM term to add to links', 'autonami-automations-connectors' ),
                "description" => '',
                "required"    => false,
                'toggler'     => array(
                    'fields'   => array(
                        array(
                            'id'    => 'sms_append_utm',
                            'value' => true,
                        ),
                    ),
                    'relation' => 'AND',
                ),
            ],
        ];
    }
    // TODO: нужны функции
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