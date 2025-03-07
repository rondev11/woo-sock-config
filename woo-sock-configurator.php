<?php
/**
 * Plugin Name: WooCommerce Product Sock Configurator
 * Description: Allows customers to customize socks with patterns, colors, and additional options.
 * Version: 1.3
 * Author: Ron Dionaldo
 */

if (!defined('ABSPATH')) {
    exit; 
}

//constants
define('SOCK_CONFIGURATOR_PATH', plugin_dir_path(__FILE__));
define('SOCK_CONFIGURATOR_URL', plugin_dir_url(__FILE__));

require_once SOCK_CONFIGURATOR_PATH . 'includes/class-sock-configurator.php';
require_once SOCK_CONFIGURATOR_PATH . 'includes/class-sock-configurator-settings.php';
require_once SOCK_CONFIGURATOR_PATH . 'includes/class-sock-configurator-ajax-handler.php';
require_once SOCK_CONFIGURATOR_PATH . 'includes/class-sock-configurator-cart-handler.php';
require_once SOCK_CONFIGURATOR_PATH . 'includes/class-sock-configurator-admin-handler.php';

function run_sock_configurator() {
    new WooCommerce_Sock_Configurator();
}
add_action('plugins_loaded', 'run_sock_configurator');

