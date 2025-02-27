jQuery(document).ready(function ($) {
    if (typeof sockConfig === 'undefined') {
        console.error('sockConfig is not defined.');
        return;
    }
    var originalPrice = parseFloat($('.woocommerce-Price-amount').first().text().replace(/[^0-9.]/g, '')); // Extract number on first load

    function updatePrice() {
        var extraThickness = $('#extra_thickness').is(':checked') ? 5 : 0;
        var selectedPattern = $('#sock_pattern').val(); // Get selected sock pattern
        var productId = $('form.cart').find('input[name="product_id"]').val(); // Get WooCommerce product ID
        var securityNonce = sockConfig.nonce; // Retrieve nonce

        if (typeof sockConfig === 'undefined' || typeof sockConfig.ajax_url === 'undefined') {
            console.error('sockConfig or sockConfig.ajax_url is not defined.');
            return;
        }

        console.log('Sending AJAX request to:', sockConfig.ajax_url);

        $.ajax({
            type: 'POST',
            url: sockConfig.ajax_url,
            data: {
                action: 'calculate_price',
                product_id: productId,
                extra_thickness: extraThickness,
                sock_pattern: selectedPattern, // Include selected pattern in the request
                security: securityNonce
            },
            dataType: 'json',
            success: function (response) {
                console.log('AJAX Response:', response);
                if (response.success && response.data.price) {
                    $('.woocommerce-Price-amount').first().text('$' + response.data.price.toFixed(2)); // Ensure two decimal places
                    $('body').trigger('update_checkout'); // Trigger WooCommerce cart update
                } else {
                    $('.woocommerce-Price-amount').first().text('$' + originalPrice.toFixed(2)); // Reset to original price
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
            }
        });
    }

    $('#extra_thickness, #sock_pattern').change(updatePrice); // Trigger update on pattern change
});
