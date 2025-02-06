<?php 

/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// ===  GENERAL LOADING OF STYLES AND JS FILES === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */


function theme_enqueue_framework_assets() {
    wp_enqueue_script(
        'swiper-bundle',
        get_template_directory_uri() . '/assets/js/frameworks/swiper-bundle.min.js',
        [],
        '11.2.1',
        false
    );
    wp_enqueue_style(
        'swiper-bundle-css',
        get_template_directory_uri() . '/assets/css/frameworks/swiper-bundle.min.css',
        [],
        '11.2.1'
    );
}
add_action('wp_enqueue_scripts', 'theme_enqueue_framework_assets');


function theme_enqueue_scripts() {
    // Pfad zu den Skripten mit get_template_directory_uri() (für Themes)
    
    wp_enqueue_script(
        'accordeon-js',
        get_template_directory_uri() . '/assets/js/accordeon.js',
        [], 
        '1.0.2',
        true
    );

    wp_enqueue_script(
        'mobile-menu-js',
        get_template_directory_uri() . '/assets/js/mobile-menu.js',
        [],
        '1.0.3',
        true
    );

    wp_enqueue_script(
        'fixed-menu-js',
        get_template_directory_uri() . '/assets/js/header.js',
        [],
        '1.0.2',
        true
    );

    
    wp_enqueue_script(
        'sliders',
        get_template_directory_uri() . '/assets/js/sliders.js',
        ['swiper-bundle'],
        '1.0.0',
        true
    );
    wp_enqueue_script(
        'main-js',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        '1.0.1',
        true
    );



    wp_enqueue_script(
        'mta-masterpiece-console',
        get_template_directory_uri() . '/assets/js/morethanadsmasterpiece.min.js',
        [],
        '1.0.0',
        true
    );
   
    
}
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts');


/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// === Responsive Images === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */

add_theme_support( 'post-thumbnails' );
add_image_size( 'img-480', 480, 9999 );
add_image_size( 'img-640', 640, 9999 );
add_image_size( 'img-720', 720, 9999 );
add_image_size( 'img-960', 960, 9999 );
add_image_size( 'img-1168', 1168, 9999 );
add_image_size( 'img-1440', 1440, 9999 );
add_image_size( 'img-1920', 1920, 9999 );

function my_custom_sizes( $sizes ) {
	return array_merge( $sizes, array(
		'img-480' => 'img-480',
		'img-640' => 'img-640',
		'img-720' => 'img-720',
		'img-960' => 'img-960',
		'img-1168' => 'img-1168',
		'img-1440' => 'img-1440',
		'img-1920' => 'img-1920',
	) );
}
add_filter( 'image_size_names_choose', 'my_custom_sizes' );


// SRCSET IMAGES

/**
 * Responsive Image Helper Function
 *
 * @param string $image_id the id of the image (from ACF or similar)
 * @param string $image_size the size of the thumbnail image or custom image size
 * @param string $max_width the max width this image will be shown to build the sizes attribute 
 */
function responsive_image($image_id,$image_size,$max_width){

	// check the image ID is not blank
	if($image_id != '') {

		// set the default src image size
		$image_src = wp_get_attachment_image_url( $image_id, $image_size );

		// set the srcset with various image sizes
		$image_srcset = wp_get_attachment_image_srcset( $image_id, $image_size );

		// generate the markup for the responsive image
		echo 'src="'.$image_src.'" srcset="'.$image_srcset.'" sizes="(max-width: '.$max_width.') 100vw, '.$max_width.'"';

	}
}

/**
 * Output an image with responsive attributes
 * @param int $imageId for example 31
 * @param string $imageSize on of the values img-1920, img-1440, img-1168, img-960, img-720, img-640, img-480
 * @param string $max_width for example '1920px'
 * @param boolean $lazy for lazy loading 
 * @return void
 */
function imageOutput($imageId, $imageSize = 'img-1920', $max_width = '1920px', $lazy=true) {
	$image_meta = wp_get_attachment_metadata($imageId);
    if(!$image_meta) return;
	
	$image_dimensions = wp_get_attachment_image_src($imageId, $imageSize);
    $width = $image_dimensions[1];
    $height = $image_dimensions[2];
	
	$alt_text = get_post_meta($imageId, '_wp_attachment_image_alt', true);
	if(empty($alt_text)) {
        $caption = wp_get_attachment_caption($imageId);
        if(!empty($caption)) {
            $alt_text = $caption;
        } else {
            $alt_text = get_the_title($imageId);
        }
    }
    ?>
    <img
		 <?php responsive_image($imageId, $imageSize, $max_width) ?>
		 alt="<?php echo esc_attr($alt_text); ?>"
		 height="<?php echo $height; ?>"
		 width="<?php echo $width; ?>"
		 <?php if($lazy) : ?>loading="lazy" <?php endif; ?>
	>
    <?php 
}



/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// === REGISTER CUSTOM GUTENBERG BLOCKS === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */

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

/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// === POPULATE ACF MENU FIELD === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
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


/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// === INCLUDE EXTERNAL FUNCTION FILES === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */

require_once(plugin_dir_path(__FILE__) . '/functions/css-variables.php'); // Dynamische CSS-Variablen
require_once(plugin_dir_path(__FILE__) . '/functions/load-styles.php'); // Stylesheets laden
require_once(plugin_dir_path(__FILE__) . '/functions/load-acf-fields.php'); // ACF-Felder laden
require_once(plugin_dir_path(__FILE__) . '/functions/breadcrumbs.php');

/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// === ACF BLOCKS FOR GUTRENBERG === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */

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




/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// === LOAD ACF FIELDS FOR THEME  === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */

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
        'position'      => '2.1',
        'icon_url'      => 'dashicons-admin-generic',
        'redirect'      => false,
        'update_button' => __('Theme Einstellungen speichern', 'acf'),
        'updated_message' => __("Theme Einstellungen gespeichert", 'acf'),
    ));

}


/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// === CREATE CATEGORY BASED ON POST TITLE  === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
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



