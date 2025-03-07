<?php

class Sock_Configurator_Ajax_Handler {
    public function __construct() {
        add_action('wp_ajax_calculate_price', [$this, 'calculate_price']);
        add_action('wp_ajax_nopriv_calculate_price', [$this, 'calculate_price']);
    }

    public function calculate_price() {
        // Verify nonce
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'sock_config_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Validate product ID
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error(['message' => 'Invalid product ID']);
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            wp_send_json_error(['message' => 'Product not found']);
        }

        // Base price
        $base_price = floatval($product->get_price());
        $extra_price = !empty($_POST['extra_thickness']) ? 5 : 0;
        
        // Pattern price
        $pattern_prices = [
            'striped' => 0,
            'dotted' => 3,
            'checkered' => 4
        ];
        $pattern = isset($_POST['sock_pattern']) ? sanitize_text_field($_POST['sock_pattern']) : '';
        $pattern_price = isset($pattern_prices[$pattern]) ? $pattern_prices[$pattern] : 0;
    
        // Calculate total price
        $total_price = $base_price + $extra_price + $pattern_price;
    
        wp_send_json_success(['price' => $total_price]);
    }
}
