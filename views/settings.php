<?php
$saved_data = WFCO_Common::$connectors_saved_data;
$old_data   = ( isset( $saved_data[ $this->get_slug() ] ) && is_array( $saved_data[ $this->get_slug() ] ) && count( $saved_data[ $this->get_slug() ] ) > 0 ) ? $saved_data[ $this->get_slug() ] : array();
$login      = isset( $old_data['login'] ) ? $old_data['login'] : '';
$password   = isset( $old_data['password'] ) ? $old_data['password'] : '';
?>
<div class="wfco-smscru-wrap">
    <div class="wfco-form-group featured field-input">
        <label for="automation-name"><?php echo esc_html__( 'Enter Login', 'autonami-automations-connectors' ); ?></label>
        <div class="field-wrap">
            <div class="wrapper">
                <input type="text" name="login" placeholder="<?php echo esc_attr__( 'Enter SMSC.ru Login', 'autonami-automations-connectors' ); ?>" class="form-control wfco_smscru_login" required value="<?php echo esc_attr( $login ); ?>">
            </div>
        </div>
    </div>

    <div class="wfco-form-group featured field-input">
        <label for="automation-name"><?php echo esc_html__( 'Enter Password', 'autonami-automations-connectors' ); ?></label>
        <div class="field-wrap">
            <div class="wrapper">
                <input type="password" name="password" placeholder="<?php echo esc_attr__( 'Enter SMSC.ru Password', 'autonami-automations-connectors' ); ?>" class="form-control wfco_smscru_password" required value="<?php echo esc_attr( $password ); ?>">
            </div>
        </div>
    </div>

    <div class="wfco-form-groups wfco_form_submit">
        <?php
        if ( isset( $old_data['id'] ) && (int) $old_data['id'] > 0 ) {
            ?>
            <input type="hidden" name="edit_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wfco-connector-edit' ) ); ?>"/>
            <input type="hidden" name="id" value="<?php echo esc_attr( $old_data['id'] ); ?>"/>
            <input type="hidden" name="wfco_connector" value="<?php echo esc_attr( $this->get_slug() ); ?>"/>
            <button class="wfco_save_btn_style wfco_connect_to_api"><?php esc_attr_e( 'Connect and Update', 'autonami-automations-connectors' ); ?></button>
        <?php } else { ?>
            <input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'wfco-connector' ) ); ?>">
            <input type="hidden" name="wfco_connector" value="<?php echo esc_attr( $this->get_slug() ); ?>"/>
            <button class="wfco_save_btn_style wfco_connect_to_api"><?php esc_attr_e( 'Connect and Save', 'autonami-automations-connectors' ); ?></button>
        <?php } ?>
    </div>
    <div class="wfco_form_response" style="text-align: center;font-size: 15px;margin-top: 10px;"></div>
</div>