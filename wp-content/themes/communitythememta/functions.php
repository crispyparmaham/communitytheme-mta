<?php 

// === ENQUEUE SCRIPTS === //
function theme_enqueue_scripts() {
    // Pfad zu den Skripten mit get_template_directory_uri() (für Themes)
    wp_enqueue_script(
        'termin-block-js',
        get_template_directory_uri() . '/blocks/termine/index.js',
        [], 
        '1.0.0', 
        false
    );

    wp_enqueue_script(
        'verein-block-js',
        get_template_directory_uri() . '/blocks/vereine/index.js',
        [], 
        '1.0.0', 
        false 
    );

    wp_enqueue_script(
        'verein-block-js',
        get_template_directory_uri() . '/blocks/tourismus/index.js',
        [], 
        '1.0.0', 
        false 
    );

    wp_enqueue_script(
        'gewerbe-block-js',
        get_template_directory_uri() . '/blocks/gewerbe/index.js',
        [], 
        '1.0.0', 
        false 
    );

    wp_enqueue_script(
        'posts-block-js',
        get_template_directory_uri() . '/blocks/posts/index.js',
        [], 
        '1.0.0', 
        false 
    );

    wp_enqueue_script(
        'team-member-block-js',
        get_template_directory_uri() . '/blocks/teammitglieder/index.js',
        [], 
        '1.0.0', 
        false 
    );

    wp_enqueue_script(
        'accordeon-js',
        get_template_directory_uri() . '/assets/js/accordeon.js',
        [], 
        '1.0.2',
        false
    );

    wp_enqueue_script(
        'mobile-menu-js',
        get_template_directory_uri() . '/assets/js/mobile-menu.js',
        [],
        '1.0.3',
        false
    );

    wp_enqueue_script(
        'fixed-menu-js',
        get_template_directory_uri() . '/assets/js/header.js',
        [],
        '1.0.2',
        false
    );
}
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');


// === REGISTER CUSTOM GUTENBERG BLOCKS === //

function register_blocks() {
    register_block_type(__DIR__ . '/blocks/termine'); // Registrierung des "Termine"-Blocks
    register_block_type(__DIR__ . '/blocks/vereine'); // Registrierung des "Vereine"-Blocks
    register_block_type(__DIR__ . '/blocks/posts'); // Registrierung des "Beitrags"-Blocks
    register_block_type(__DIR__ . '/blocks/tourismus'); // Registrierung des "Tourismus"-Blocks
    register_block_type(__DIR__ . '/blocks/gewerbe'); // Registrierung des "Gewerbe"-Blocks
}
add_action('init', 'register_blocks');



// === REGISTER SIDEBARS === //
function theme_register_sidebars() {
    register_sidebar([
        'name' => 'Main Sidebar',
        'id' => 'main-sidebar',
        'before_widget' => '<div class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ]);
}
add_action('widgets_init', 'theme_register_sidebars');

// === POPULATE ACF MENU FIELD === //
// Dynamisches Füllen eines ACF-Felds mit verfügbaren Menüs
function populate_selected_menu_field($field) {
    $field['choices'] = []; // Leere Standardauswahl

    // Abrufen aller registrierten Menüs
    $menus = wp_get_nav_menus();

    if (!empty($menus)) {
        foreach ($menus as $menu) {
            $field['choices'][$menu->term_id] = $menu->name; // Menü-ID und -Name zu den Auswahlmöglichkeiten hinzufügen
        }
    }

    return $field;
}
add_filter('acf/load_field/name=selected_menu', 'populate_selected_menu_field');


// === INCLUDE EXTERNAL FUNCTION FILES === //
require_once(plugin_dir_path(__FILE__) . '/functions/css-variables.php'); // Dynamische CSS-Variablen
require_once(plugin_dir_path(__FILE__) . '/functions/load-styles.php'); // Stylesheets laden
require_once(plugin_dir_path(__FILE__) . '/functions/load-acf-fields.php'); // ACF-Felder laden



function use_custom_template_for_termin($template) {
    if (is_singular('termin')) {
        $custom_template = locate_template('single-termin.php');
        if ($custom_template) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter('template_include', 'use_custom_template_for_termin');




//ACF INTEGRATION

function my_theme_acf_pro_notice() {
    // Überprüfen, ob ACF Pro nicht installiert ist
    if ( ! class_exists( 'ACF' ) ) {
        echo '<div class="notice notice-warning is-dismissible">
            <p><strong>ACF Custom Fields Pro</strong> muss installiert werden, um alle Funktionen des Themes zu nutzen.</p>
            <p><a href="https://www.advancedcustomfields.com/pro/" target="_blank" class="button button-primary">Jetzt ACF Pro installieren</a></p>
        </div>';
    }
}
add_action( 'admin_notices', 'my_theme_acf_pro_notice' );



// Stelle sicher, dass ACF die JSON-Dateien lädt, wenn das Theme aktiviert wird
add_filter('acf/settings/load_json', 'my_theme_acf_json_load_point');
function my_theme_acf_json_load_point( $paths ) {
    // Verzeichnis, in dem du deine JSON-Dateien speicherst
    $paths[] = get_template_directory() . '/acf-json/';
    return $paths;
}


if( function_exists('acf_add_options_page') ) {

    acf_add_options_page(array(
        'page_title'    => 'Theme Settings',
        'menu_title'    => 'Theme Settings',
        'menu_slug'     => 'theme-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false,
    ));

}



