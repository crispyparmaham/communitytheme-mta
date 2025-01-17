<?php 

// === GUTENBERG BLOCKS SCRIPTS === //
wp_enqueue_script(
    'termin-block-js',
    plugin_dir_url(__FILE__) . '/blocks/termine/index.js',
    [], // Keine Abhängigkeiten
    '1.0.0', // Versionsnummer
    true // Script wird im Footer geladen
);


// === REGISTER CUSTOM GUTENBERG BLOCKS === //
function register_blocks() {
    register_block_type(__DIR__ . '/blocks/termine'); // Registrierung des "Termine"-Blocks
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


