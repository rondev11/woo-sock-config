<?php

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
        wp_enqueue_script('sock-configurator', SOCK_CONFIGURATOR_URL . 'assets/js/sock-configurator.js', ['jquery'], '1.0', true);
        wp_localize_script('sock-configurator', 'sockConfig', [
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => wp_create_nonce('sock_config_nonce')
        ]);
        wp_enqueue_style('sock-configurator-style', SOCK_CONFIGURATOR_URL . 'assets/css/sock-configurator.css', [], '1.0');
    }

    public function display_configurator() {
        $product_id = get_the_ID();
        $configured_product_ids = explode(',', get_option('sock_configurator_product_ids', ''));
        if (in_array($product_id, $configured_product_ids)) {
            include SOCK_CONFIGURATOR_PATH . 'templates/sock-configurator-template.php';
        }
    }
}
