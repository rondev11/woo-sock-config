<?php

class Sock_Configurator_Cart_Handler {
    public function __construct() {
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_customization_to_cart'], 10, 2);
        add_filter('woocommerce_get_item_data', [$this, 'display_customization_in_cart'], 10, 2);
        add_action('woocommerce_before_calculate_totals', [$this, 'update_cart_price']);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'save_customization_to_order'], 10, 4);
    }

    public function add_customization_to_cart($cart_item_data, $product_id) {
        if (isset($_POST['sock_pattern'])) {
            $cart_item_data['sock_pattern'] = sanitize_text_field($_POST['sock_pattern']);
        }
        if (isset($_POST['sock_color'])) {
            $cart_item_data['sock_color'] = sanitize_text_field($_POST['sock_color']);
        }
        if (isset($_POST['extra_thickness'])) {
            $cart_item_data['extra_thickness'] = true;
        }
        return $cart_item_data;
    }

    public function display_customization_in_cart($item_data, $cart_item) {
        if (isset($cart_item['sock_pattern'])) {
            $item_data[] = ['name' => 'Pattern', 'value' => esc_html(ucfirst($cart_item['sock_pattern']))];
        }
        if (isset($cart_item['sock_color'])) {
            $item_data[] = ['name' => 'Color', 'value' => esc_html(ucfirst($cart_item['sock_color']))];
        }
        if (!empty($cart_item['extra_thickness'])) {
            $item_data[] = ['name' => 'Extra Thickness', 'value' => 'Yes (+$5)'];
        }
        return $item_data;
    }

    public function update_cart_price($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $base_price = floatval($cart_item['data']->get_price());
            $extra_price = 0;
    
            if (!empty($cart_item['extra_thickness'])) {
                $extra_price += 5;
            }
    
            if (!empty($cart_item['sock_pattern'])) {
                $pattern_prices = [
                    'striped' => 0,
                    'dotted' => 3,
                    'checkered' => 4
                ];
                $pattern = $cart_item['sock_pattern'];
                $extra_price += isset($pattern_prices[$pattern]) ? $pattern_prices[$pattern] : 0;
            }
    
            $new_price = $base_price + $extra_price;
    
            // Ensure price updates properly in cart
            $cart_item['data']->set_price($new_price);
        }
    }
    
    public function save_customization_to_order($item, $cart_item_key, $values, $order) {
        if (isset($values['sock_pattern'])) {
            $item->add_meta_data('Sock Pattern', esc_html(ucfirst($values['sock_pattern'])), true);
        }
        if (isset($values['sock_color'])) {
            $item->add_meta_data('Sock Color', esc_html(ucfirst($values['sock_color'])), true);
        }
        if (!empty($values['extra_thickness'])) {
            $item->add_meta_data('Extra Thickness', 'Yes (+$5)', true);
        }
    }
}
