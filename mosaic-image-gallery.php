<?php
/*
Plugin Name: Media Library Mosaic Gallery
Plugin URI: https://example.com/media-library-mosaic-gallery
Description: A plugin that creates an image gallery from media library with category filtering and mosaic grid layout.
Version: 1.0
Author: Your Name
Author URI: https://example.com
License: GPL2
*/

// Enqueue necessary scripts and styles
function mlmg_enqueue_scripts() {
    wp_enqueue_style('mlmg-style', plugin_dir_url(__FILE__) . 'css/mlmg-style.css');
    wp_enqueue_script('mlmg-script', plugin_dir_url(__FILE__) . 'js/mlmg-script.js', array('jquery'), '1.0', true);
    wp_localize_script('mlmg-script', 'mlmg_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'mlmg_enqueue_scripts');

// Shortcode to display the gallery
function mlmg_gallery_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'category' => '',
    ), $atts);

    $output = '<div class="mlmg-gallery">';
    $output .= mlmg_category_dropdown();
    $output .= '<div class="mlmg-content">' . do_shortcode($content) . '</div>';
    $output .= '<div class="mlmg-grid">';
    $output .= mlmg_get_gallery_items($atts['category']);
    $output .= '</div></div>';

    return $output;
}
add_shortcode('mosaic_gallery', 'mlmg_gallery_shortcode');

// Function to generate category dropdown
function mlmg_category_dropdown() {
    $categories = get_categories(array(
        'taxonomy' => 'category',
        'hide_empty' => true,
    ));

    $output = '<select id="mlmg-category-filter">';
    $output .= '<option value="">All Categories</option>';

    foreach ($categories as $category) {
        $output .= '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
    }

    $output .= '</select>';

    return $output;
}

// Function to get gallery items
function mlmg_get_gallery_items($category = '') {
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
    );

    if (!empty($category)) {
        $args['category_name'] = $category;
    }

    $query_images = new WP_Query($args);

    $output = '';

    if ($query_images->have_posts()) {
        while ($query_images->have_posts()) {
            $query_images->the_post();
            $image_url = wp_get_attachment_image_src(get_the_ID(), 'medium')[0];
            $output .= '<div class="mlmg-item">';
            $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr(get_the_title()) . '">';
            $output .= '</div>';
        }
    }

    wp_reset_postdata();

    return $output;
}

// AJAX handler for category filtering
function mlmg_filter_gallery() {
    $category = $_POST['category'];
    echo mlmg_get_gallery_items($category);
    wp_die();
}
add_action('wp_ajax_mlmg_filter_gallery', 'mlmg_filter_gallery');
add_action('wp_ajax_nopriv_mlmg_filter_gallery', 'mlmg_filter_gallery');
