<?php if (!defined('ABSPATH')) exit; ?>

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

    <label for="extra_thickness">
        <input type="checkbox" id="extra_thickness" name="extra_thickness">
        Extra Thickness (+$5)
    </label>

    <input type="hidden" name="product_id" value="<?php echo esc_attr(get_the_ID()); ?>">
    <?php wp_nonce_field('sock_config_nonce', 'security'); ?>
</div>
