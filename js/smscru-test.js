jQuery(document).ready(function($) {
    console.log('SMSCRU Test Script Loaded');
    console.log('AJAX URL:', smscru_ajax.ajax_url);
    
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
                console.log('AJAX Response:', response);
                if (response.success) {
                    $('#result-message').html(response.data);
                } else {
                    $('#result-message').html('Ошибка: ' + response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown);
                $('#result-message').html('Произошла ошибка при отправке запроса: ' + textStatus);
            }
        });
    });
});