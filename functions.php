<?php 
define('THEME_VERSION', '1.0.0');

/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// ===  UPDATING THE THEME FROM MTA UPDATE SERVER === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
require __DIR__ . '/vendor/autoload.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$UpdateChecker = PucFactory::buildUpdateChecker(
	'https://update-server.morethanads.de/index.php?action=get_metadata&slug=communitytheme-mta', //Metadata URL.
	__FILE__, 
	'communitytheme-mta' 
);




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
        THEME_VERSION,
        true
    );

    wp_enqueue_script(
        'mobile-menu-js',
        get_template_directory_uri() . '/assets/js/mobile-menu.js',
        [],
        THEME_VERSION,
        true
    );

    wp_enqueue_script(
        'fixed-menu-js',
        get_template_directory_uri() . '/assets/js/header.js',
        [],
        THEME_VERSION,
        true
    );

    
    wp_enqueue_script(
        'sliders',
        get_template_directory_uri() . '/assets/js/sliders.js',
        ['swiper-bundle'],
        THEME_VERSION,
        true
    );
    wp_enqueue_script(
        'main-js',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        THEME_VERSION,
        true
    );
    wp_enqueue_script(
        'accessibility-js',
        get_template_directory_uri() . '/assets/js/accessibility.js',
        [],
        THEME_VERSION,
        true
    );

    wp_enqueue_script(
        'mta-masterpiece-console',
        get_template_directory_uri() . '/assets/js/morethanadsmasterpiece.min.js',
        [],
        THEME_VERSION,
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
function register_mta_block_category($categories) {
    return array_merge(
        $categories,
        [
            [
                'slug'  => 'community-blocks',
                'title' => __('MTA Custom Blocks', 'morethanads'),
            ],
            [
                'slug'  => 'community-privacy',
                'title' => __('Impressum & Datenschutz', 'morethanads'),
            ]
        ]
    );
}
add_filter('block_categories_all', 'register_mta_block_category', 10, 2);
function register_blocks() {
    register_block_type(__DIR__ . '/blocks/termine'); // Registrierung des "Termine"-Blocks
    register_block_type(__DIR__ . '/blocks/vereine'); // Registrierung des "Vereine"-Blocks
    register_block_type(__DIR__ . '/blocks/posts'); // Registrierung des "Beitrags"-Blocks
    register_block_type(__DIR__ . '/blocks/tourismus'); // Registrierung des "Tourismus"-Blocks
    register_block_type(__DIR__ . '/blocks/gewerbe'); // Registrierung des "Gewerbe"-Blocks
    register_block_type(__DIR__ . '/blocks/impressum'); 
    register_block_type(__DIR__ . '/blocks/privacy-policy'); 
    register_block_type(__DIR__ . '/blocks/infrastructure'); 
    register_block_type(__DIR__ . '/blocks/youtube-video'); 
    register_block_type(__DIR__ . '/blocks/vimeo-video'); 
    register_block_type(__DIR__ . '/blocks/external-resources'); 
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

require_once(get_template_directory() . '/functions/css-variables.php'); // Dynamische CSS-Variablen
require_once(get_template_directory() . '/functions/load-styles.php'); // Stylesheets laden
require_once(get_template_directory() . '/functions/load-acf-fields.php'); // ACF-Felder laden
require_once(get_template_directory() . '/functions/breadcrumbs.php');
require_once(get_template_directory() . '/functions/termin-functionality.php');
require_once(get_template_directory() . '/functions/ma-youtube-videos.php');
require_once(get_template_directory() . '/functions/ma-vimeo-videos.php');
require_once(get_template_directory() . '/functions/ma-external-resources.php');
require_once(get_template_directory() . '/capabilities/user-capabilities.php');
require_once(get_template_directory() . '/capabilities/custom-dashboard.php');


add_action('admin_enqueue_scripts', 'enqueue_admin_styles');
function enqueue_admin_styles() {
    wp_enqueue_style('mta-admin-styles', get_template_directory_uri() . '/assets/css/admin/admin.css', [], THEME_VERSION);
}


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


add_action('admin_menu', 'custom_theme_settings_menu');

function custom_theme_settings_menu() {
    add_menu_page(
        'MTA Community Theme - Einstellungen',
        'Theme',
        'manage_options',
        'theme-settings', 
        'custom_theme_settings_page', 
        'dashicons-admin-generic',
        3                         
    );
}

function custom_theme_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('MTA Community Theme - Einstellungen', 'communitytheme'); ?></h1>
        <p><?php _e('Wählen Sie eine der folgenden Optionen:', 'communitytheme'); ?></p>
        <div style="margin-top: 20px;">
            <a href="<?php echo admin_url('admin.php?page=theme-settings-frontpage'); ?>" class="button button-primary" style="margin-right: 10px;"><?php _e('Startseite bearbeiten', 'mta-community'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=theme-settings-content'); ?>" class="button button-primary" style="margin-right: 10px;"><?php _e('Allg. Content bearbeiten', 'mta-community'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=theme-settings-colors'); ?>" class="button button-primary"><?php _e('Farben & Einstellungen bearbeiten', 'mta-community'); ?></a>
        </div>
    </div>
    <?php
}




if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title'    => 'Startseite',
        'menu_title'    => 'Startseite',
        'menu_slug'     => 'theme-settings-frontpage',
        'parent' => 'theme-settings',
        'capability'    => 'edit_posts',
        'position'      => '2.1',
        'icon_url'      => 'dashicons-admin-generic',
        'redirect'      => false,
        'update_button' => __('Startseite speichern', 'acf'),
        'updated_message' => __("Startseite wurde gespeichert", 'acf'),
    ));
    acf_add_options_page(array(
        'page_title'    => 'Allgemeiner Content',
        'menu_title'    => 'Allg. Content',
        'menu_slug'     => 'theme-settings-content',
        'parent' => 'theme-settings',
        'capability'    => 'edit_posts',
        'position'      => '2.1',
        'icon_url'      => 'dashicons-admin-generic',
        'redirect'      => false,
        'update_button' => __('Allgemeiner Content speichern', 'acf'),
        'updated_message' => __("Allgemeiner Content wurde gespeichert", 'acf'),
    ));
    acf_add_options_page(array(
        'page_title'    => 'Farben & Einstellungen',
        'menu_title'    => 'Farben & Einstellungen',
        'menu_slug'     => 'theme-settings-colors',
        'parent' => 'themes.php',
        'capability'    => 'edit_posts',
        'position'      => '2.1',
        'icon_url'      => 'dashicons-admin-generic',
        'redirect'      => false,
        'update_button' => __('Farben & Einstellungen speichern', 'acf'),
        'updated_message' => __("Farben & Einstellungen wurde gespeichert", 'acf'),
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



/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// ===  SET TERMIN POST TYPE TO NO INDEX IF IT HAS NO CONTENT === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */

add_filter('wpseo_robots', 'custom_modify_noindex_for_termin');
function custom_modify_noindex_for_termin($robots) {
    if (is_singular('termin')) {
        global $post;
        if (!empty($post->post_content)) {
            $robots_array = explode(', ', $robots);
            $robots_array = array_diff($robots_array, ['noindex', 'nofollow']);
            return implode(', ', $robots_array);
        }
    }
    return $robots;
}




/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// ===  REDIRECTION OF LINKS === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
add_action('template_redirect', 'custom_redirect_function');
function custom_redirect_function() {
    $redirects = [
        'termin' => 'aktuelles/#termine-veranstaltungen',
    ];

    // Get current requested path
    $current_path = trim($_SERVER['REQUEST_URI'], '/');

    foreach ($redirects as $old_slug => $new_slug) {
        if ($current_path === trim($old_slug, '/')) {
            wp_redirect(home_url($new_slug), 301);
            exit;
        }
    }
}

/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// ===  Make Search also search for custom fields === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */

function modify_search_result_links($url, $post) {
    // Modify links for specific post types
    if ($post->post_type === 'verein') {
        return site_url('/vereine/') . '#verein-' . $post->ID; // Optional: Add ID as an anchor
    } elseif ($post->post_type === 'infrastruktur') {
        return site_url('/') . '#infrastruktur';
    } elseif ($post->post_type === 'gewerbe') {
        return site_url('/gewerbe/') . '#gewerbe-' . $post->ID; // Optional: Add ID as an anchor
    } elseif ($post->post_type === 'tourismus') {
        return site_url('/tourismus/') . '#tourismus-' . $post->ID; // Optional: Add ID as an anchor
    }

    return $url; // Default for other post types
}
add_filter('post_type_link', 'modify_search_result_links', 10, 2);
add_filter('get_permalink', 'modify_search_result_links', 10, 2);

function modify_search_query($where, $query) {
    if (!is_admin() && $query->is_search()) {
        global $wpdb;
        $search_term = esc_sql($query->get('s'));

        $where .= " OR EXISTS (
            SELECT 1 FROM $wpdb->postmeta 
            WHERE $wpdb->postmeta.post_id = $wpdb->posts.ID 
            AND $wpdb->postmeta.meta_value LIKE '%$search_term%'
        )";

        $where .= " AND $wpdb->posts.post_type != 'attachment'";
        $where .= " AND $wpdb->posts.post_type != 'revision'";
        $where .= " AND $wpdb->posts.post_type != 'nav_menu_item'";
        $where .= " AND $wpdb->posts.post_type != 'acf-field-group'";
        $where .= " AND $wpdb->posts.post_type != 'acf-field'";
        $where .= " AND $wpdb->posts.post_type != 'acf-post-type'";
        $where .= " AND $wpdb->posts.post_type != 'teammitglieder'";
        $where .= " AND $wpdb->posts.post_type != 'daten_fakten'";

        $where .= " AND $wpdb->posts.post_status = 'publish'";
    }
    return $where;
}
add_filter('posts_where', 'modify_search_query', 10, 2);



/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// ===  Anonymous Tracking === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
function track_single_page_views() {
    if (!is_admin() && is_singular()) { 
        global $post;
        $views = get_post_meta($post->ID, '_page_views', true);
        $views = empty($views) ? 0 : $views;
        update_post_meta($post->ID, '_page_views', $views + 1);
    }
}
add_action('wp_head', 'track_single_page_views');



/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// ===  Block all iframes === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */


function block_iframes_with_consent($content) {
    $pattern = '/<iframe.*?src=["\'](.*?)["\'].*?<\/iframe>/is';
    preg_match_all($pattern, $content, $matches);

    foreach ($matches[0] as $index => $iframe) {
        $src = esc_url($matches[1][$index]); // Extract and sanitize the iframe source

        $consent_text = "Um den Inhalt von <strong>$src</strong> zu laden, musst du deine Zustimmung erteilen.";
        $consent_wrapped = '[ma-content-consent message="' . esc_attr($consent_text) . '"]' . $iframe . '[/ma-content-consent]';

        // Replace iframe with the wrapped shortcode
        $content = str_replace($iframe, $consent_wrapped, $content);
    }

    // Ensure shortcodes are executed in all content areas
    return do_shortcode($content);
}

// Apply filter to posts, widgets, and excerpts
add_filter('the_content', 'block_iframes_with_consent'); 
add_filter('widget_text', 'block_iframes_with_consent');  
add_filter('widget_text_content', 'block_iframes_with_consent'); 
add_filter('the_excerpt', 'block_iframes_with_consent');    
