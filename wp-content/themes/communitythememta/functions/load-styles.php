<?php

// === LOAD STYLESHEETS === //
function load_stylesheet() {
    // === BASE STYLES === //
    wp_register_style('main', get_template_directory_uri() . '/assets/css/base/main.css', [], false, 'all');
    wp_enqueue_style('main');

    // === LAYOUT STYLES === //
    wp_register_style('grid', get_template_directory_uri() . '/assets/css/layout/grid.css', [], false, 'all');
    wp_enqueue_style('grid');

    wp_register_style('header', get_template_directory_uri() . '/assets/css/layout/header.css', [], false, 'all');
    wp_enqueue_style('header');

    wp_register_style('footer', get_template_directory_uri() . '/assets/css/layout/footer.css', [], false, 'all');
    wp_enqueue_style('footer');

    wp_register_style('sidebar', get_template_directory_uri() . '/assets/css/layout/sidebar.css', [], false, 'all');
    wp_enqueue_style('sidebar');

    wp_register_style('theme', get_template_directory_uri() . '/assets/css/layout/theme.css', [], false, 'all');
    wp_enqueue_style('theme');

    // === UTILITIES === //
    wp_register_style('button', get_template_directory_uri() . '/assets/css/utils/button.css', [], false, 'all');
    wp_enqueue_style('button');

    wp_register_style('typography', get_template_directory_uri() . '/assets/css/utils/typography.css', [], false, 'all');
    wp_enqueue_style('typography');

    // === GUTENBERG BLOCKS === //
    wp_register_style('termine', get_template_directory_uri() . '/assets/css/blocks/termin.css', [], false, 'all');
    wp_enqueue_style('termine');
}
add_action('wp_enqueue_scripts', 'load_stylesheet');

// === LOAD CUSTOM JQUERY === //
function load_jquery() {
    // Entferne das Standard-jQuery von WordPress
    wp_deregister_script('jquery');

    // Füge benutzerdefiniertes jQuery hinzu
    wp_enqueue_script('jquery', get_template_directory_uri() . '/js/jquery-3-4.js', [], '3.4.0', true);
}
add_action('wp_enqueue_scripts', 'load_jquery');
