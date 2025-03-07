<?php

class Sock_Configurator_Admin_Handler {
    private $settings;

    public function __construct($settings) {
        $this->settings = $settings;
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce', 
            'Sock Configurator Settings', 
            'Sock Configurator', 
            'manage_options', 
            'sock-configurator', 
            [$this->settings, 'settings_page']
        );
    }
}
