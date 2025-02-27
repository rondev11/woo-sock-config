<?php
/**
 * Plugin Name: WooCommerce Product Sock Configurator
 * Description: Allows customers to customize socks with patterns, colors, and additional options.
 * Version: 1.1
 * Author: Ron Dionaldo
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WooCommerce_Sock_Configurator {
    private $settings;
    private $ajax_handler;
    private $cart_handler;
    private $admin_handler;

    public function __construct() {
        $this->settings = new Sock_Configurator_Settings();
        $this->ajax_handler = new Sock_Configurator_Ajax_Handler();
        $this->cart_handler = new Sock_Configurator_Cart_Handler();
        $this->admin_handler = new Sock_Configurator_Admin_Handler($this->settings);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('woocommerce_before_add_to_cart_button', [$this, 'display_configurator']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('sock-configurator', plugin_dir_url(__FILE__) . 'assets/js/sock-configurator.js', ['jquery'], '1.0', true);
        wp_localize_script('sock-configurator', 'sockConfig', [
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => wp_create_nonce('sock_config_nonce')
        ]);
        wp_enqueue_style('sock-configurator-style', plugin_dir_url(__FILE__) . 'assets/css/sock-configurator.css', [], '1.0');
    }

    public function display_configurator() {
        $product_id = get_the_ID();
        $configured_product_ids = explode(',', get_option('sock_configurator_product_ids', ''));

        if (in_array($product_id, $configured_product_ids)) {
            ?>
            <div id="sock-configurator">
                <h3>Customize Your Socks</h3>
                <label for="sock_pattern">Pattern:</label>
                <select id="sock_pattern" name="sock_pattern">
                    <option value="striped">Striped</option>
                    <option value="dotted">Dotted</option>
                    <option value="checkered">Checkered</option>
                </select>
                <label for="sock_color">Color:</label>
                <select id="sock_color" name="sock_color">
                    <option value="red">Red</option>
                    <option value="blue">Blue</option>
                    <option value="green">Green</option>
                </select>
                <?php wp_nonce_field('sock_config_nonce', 'security'); ?>
                <input type="checkbox" id="extra_thickness" name="extra_thickness"> Extra Thickness (+$5)
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>">
            </div>
            <?php
        }
    }
}

class Sock_Configurator_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_menu() {
        add_submenu_page('woocommerce', 'Sock Configurator Settings', 'Sock Configurator', 'manage_options', 'sock-configurator', [$this, 'settings_page']);
    }

    public function register_settings() {
        register_setting('sock_configurator_settings', 'sock_configurator_product_ids', 'sanitize_text_field');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Sock Configurator Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('sock_configurator_settings'); ?>
                <?php do_settings_sections('sock_configurator_settings'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Product IDs to display Sock Configuration (comma-separated)</th>
                        <td><input type="text" name="sock_configurator_product_ids" value="<?php echo esc_attr(get_option('sock_configurator_product_ids', '')); ?>" style="width: 300px;"></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

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

class Sock_Configurator_Admin_Handler {
    private $settings;

    public function __construct($settings) {
        $this->settings = $settings;
    }
}

new WooCommerce_Sock_Configurator();
