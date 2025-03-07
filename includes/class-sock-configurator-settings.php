<?php

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
