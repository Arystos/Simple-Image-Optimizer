<?php
/**
 * Plugin Name: Image Optimizer
 * Plugin URI: https://www.example.com/image-optimizer
 * Description: This plugin compresses and optimizes images using the TinyPNG API.
 * Version: 1.0.9
 * Author: Arystos
 * Author URI: https://www.example.com
 */

// Load the settings page
require_once plugin_dir_path(__FILE__) . 'settings-page.php';

// Add a link to the settings page in the admin menu
function image_optimizer_add_menu_item() {
    add_menu_page(
        esc_html__('Image Optimizer', 'image-optimizer'),
        esc_html__('Image Optimizer', 'image-optimizer'),
        'manage_options',
        'image_optimizer_settings',
        'image_optimizer_settings_page',
        'dashicons-images-alt2'
    );
}
add_action('admin_menu', 'image_optimizer_add_menu_item');

// Load plugin textdomain
function image_optimizer_load_textdomain() {
    load_plugin_textdomain('image-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'image_optimizer_load_textdomain');

// Optimize images on upload
add_filter('wp_handle_upload', 'image_optimizer_handle_upload');

function image_optimizer_handle_upload($file) {
    $api_key = get_option('image_optimizer_tinypng_api_key');

    if (!$api_key) {
        return $file;
    }

    try {
        // Initialize Tinify client with API key
        \Tinify\setKey($api_key);

        // Check if file type is supported for optimization
        $supported_types = array('image/jpeg', 'image/png');
        if (in_array($file['type'], $supported_types)) {
            $optimized_image_data = \Tinify\fromFile($file['tmp_name'])->toBuffer();

            $optimized_attachment_id = image_optimizer_create_optimized_image($file, $optimized_image_data);

            $msg = __('Image optimized successfully!', 'image-optimizer');
            echo "<script type='text/javascript'>alert('$msg');</script>";

            return image_get_intermediate_size($optimized_attachment_id, 'full');
        }

        return $file;

    // Display error message if API key is invalid or has reached the limit of allowed compressions
    } catch (\Tinify\AccountException $e) {
        $msg = __('Invalid API key or the compression limit has been reached. Please check your TinyPNG API key.', 'image-optimizer');
        echo "<script type='text/javascript'>alert('$msg');</script>";
    } catch (\Tinify\ClientException $e) {
        $msg = __('Error connecting to the TinyPNG API. Please try again.', 'image-optimizer');
        echo "<script type='text/javascript'>alert('$msg');</script>";
    } catch (\Tinify\ServerException $e) {
        $msg = __('Error compressing image on the server. Please try again.', 'image-optimizer');
        echo "<script type='text/javascript'>alert('$msg');</script>";
    } catch (\Tinify\Exception $e) {
        $msg = __('Unknown error occurred while optimizing image. Please try again.', 'image-optimizer');
        echo "<script type='text/javascript'>alert('$msg');</script>";
    }

    return $file;
}

// Create optimized image attachment
function image_optimizer_create_optimized_image($original_file, $optimized_image_data) {
    $file_path = $original_file['file'];
    $file_type = $original_file['type'];
    $file_name = basename($file_path);

    $upload_dir = wp_upload_dir();
    $file_dest = $upload_dir['path'] . '/' . $file_name;

    file_put_contents($file_dest, $optimized_image_data);

    $attachment = array(
        'post_mime_type' => $file_type,
        'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $file_dest);

    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $attach_data = wp_generate_attachment_metadata($attach_id, $file_dest);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}