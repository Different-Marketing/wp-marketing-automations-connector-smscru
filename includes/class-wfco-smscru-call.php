<?php

abstract class WFCO_SMSCRU_Call extends WFCO_Call {
    public function check_fields( $data, $required_fields ) {
        $check_required_fields = parent::check_fields( $data, $required_fields );
        if ( false === $check_required_fields ) {
            return false;
        }
        if ( isset( $data['connector_initialising'] ) && true === $data['connector_initialising'] ) {
            return true;
        }
        return true;
    }
}