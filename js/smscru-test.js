jQuery(document).ready(function($) {
    $('#smscru-test-form').on('submit', function(e) {
        e.preventDefault();
        var phone = $('#test-phone').val();
        var message = $('#test-message').val();
        $.ajax({
            url: smscru_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'smscru_send_test_sms',
                phone: phone,
                message: message,
                security: smscru_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#result-message').html(response.data);
                } else {
                    $('#result-message').html('Error: ' + response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#result-message').html('An error occurred while sending the request: ' + textStatus);
            }
        });
    });
});