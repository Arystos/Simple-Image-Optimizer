<?php

// Register settings
add_action('admin_init', 'image_optimizer_register_settings');

function image_optimizer_register_settings() {
    register_setting('image_optimizer_settings_group', 'image_optimizer_tinypng_api_key');

    add_settings_section(
        'image_optimizer_tinypng_api_section',
        esc_html__('TinyPNG API Settings', 'image-optimizer'),
        'image_optimizer_tinypng_api_section_callback',
        'image_optimizer_settings'
    );

    add_settings_field(
        'image_optimizer_tinypng_api_key',
        esc_html__('TinyPNG API Key', 'image-optimizer'),
        'image_optimizer_tinypng_api_key_callback',
        'image_optimizer_settings',
        'image_optimizer_tinypng_api_section'
    );
}

function image_optimizer_tinypng_api_section_callback() {
    echo '<p>' . esc_html__('Enter your TinyPNG API key below.', 'image-optimizer') . '</p>';
}

function image_optimizer_tinypng_api_key_callback() {
    $api_key = get_option('image_optimizer_tinypng_api_key');
    printf(
        '<input type="text" name="image_optimizer_tinypng_api_key" value="%s" class="regular-text">',
        esc_attr($api_key)
    );
}

// Add settings page
add_action('admin_menu', 'image_optimizer_add_settings_page');

function image_optimizer_add_settings_page() {
    add_options_page(
        esc_html__('Image Optimizer Settings', 'image-optimizer'),
        esc_html__('Image Optimizer', 'image-optimizer'),
        'manage_options',
        'image_optimizer_settings',
        'image_optimizer_settings_page'
    );
}

function image_optimizer_settings_page() {
    ?>
    <div class="wrap" style="max-width: 800px; margin: 0 auto;">
        <h1><?php echo esc_html__('Image Optimizer Settings', 'image-optimizer'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('image_optimizer_settings_group'); ?>
            <?php do_settings_sections('image_optimizer_settings'); ?>
            <?php submit_button(); ?>
        </form>
        
        <div style="position: absolute; bottom: 0; left: 0;">
            <div style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
                <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">Support the developer</h3>
                <p style="font-size: 14px; margin-bottom: 10px;">If you find this plugin useful, please consider supporting the developer by making a donation.</p>
                <p style="font-size: 14px; margin-bottom: 0;">Support Me Here: <a href="[https://paypal.me/aristidesessa?country.x=IT&locale.x=it_IT]" style="color: #0073aa; text-decoration: none;">Thank You</a></p>
            </div>
        </div>
    </div>
    <?php
}
