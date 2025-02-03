<?php 

function theme_enqueue_scripts() {
    // Pfad zu den Skripten mit get_template_directory_uri() (für Themes)
    

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
    register_block_type(__DIR__ . '/blocks/teammitglieder'); // Registrierung des "Teammitglieder"-Blocks
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
require_once(plugin_dir_path(__FILE__) . '/functions/breadcrumbs.php');


add_action('acf/init', 'register_team_member_block');
function register_team_member_block() {
    if( function_exists('acf_register_block_type') ) {
        acf_register_block_type(array(
            'name'              => 'teammitglieder',
            'title'             => __('Teammitglied anzeigen'),
            'description'       => __('Zeigt ein Teammitglied basierend auf einer Auswahl im ACF-Feld an.'),
            'render_template'   => get_template_directory() . '/blocks/teammitglieder/team-members.php',
            'category'          => 'widgets',
            'icon'              => 'admin-users',
            'keywords'          => array( 'team', 'member', 'anzeige' ),
        ));
    }
}



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


//REGISTER TEAMMITGLIEDER CATEGORY
function auto_create_category_based_on_post_title( $post_id ) {
    // Verhindern, dass der Code beim automatischen Speichern von WordPress ausführt
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
    
    // Überprüfen, ob der Post ein "teammitglieder" Post-Typ ist
    if ( get_post_type( $post_id ) == 'teammitglieder' ) {
        // Holen des Posttitels
        $title = get_the_title( $post_id );
        $category_name = sanitize_title( $title ); // Kategorie Name basierend auf dem Titel
        
        // Überprüfen, ob die Kategorie schon existiert
        if ( ! term_exists( $category_name, 'category' ) ) {
            // Kategorie erstellen, falls noch nicht vorhanden
            wp_create_term( $category_name, 'category' );
        }

        // Kategorie dem Post zuweisen
        $term = get_term_by( 'name', $category_name, 'category' );
        wp_set_post_categories( $post_id, array( $term->term_id ) );
    }

    return $post_id;
}

add_action( 'save_post', 'auto_create_category_based_on_post_title' );

