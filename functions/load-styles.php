<?php

// === LOAD STYLESHEETS === //
function load_stylesheet() {
    // Array mit allen Stylesheets
    $styles = [
        'main' => '/assets/css/base/main.css',
        'variables' => '/assets/css/base/variables.css',
        'icons' => '/assets/css/base/icons.css',
        'grid' => '/assets/css/layout/grid.css',
        'front-page' => '/assets/css/layout/front-page.css',
        'header' => '/assets/css/layout/header.css',
        'footer' => '/assets/css/layout/footer.css',
        'sidebar' => '/assets/css/layout/sidebar.css',
        'fonts' => '/assets/css/theme/fonts.css',
        'theme' => '/assets/css/theme/theme.css',
        'search' => '/assets/css/layout/search.css',
        'gutenberg' => '/assets/css/layout/gutenberg.css',
        'button' => '/assets/css/utils/button.css',
        'links' => '/assets/css/utils/links.css',
        'typography' => '/assets/css/utils/typography.css',
        'spacings' => '/assets/css/utils/spacings.css',
        'termine' => '/assets/css/blocks/termin.css',
        'vereine' => '/assets/css/blocks/accordion.css',
        'frontpage-aktuelles' => '/assets/css/blocks/frontpage-aktuelles.css',
        'daten' => '/assets/css/blocks/daten.css',
        'grusswort' => '/assets/css/blocks/grusswort.css',
        'geschichte' => '/assets/css/blocks/geschichte.css',
        'posts' => '/assets/css/blocks/post.css',
        'infrastructure' => '/assets/css/blocks/infrastructure.css',
        'team-member' => '/assets/css/blocks/team-member.css',
        'breadcrumbs' => '/assets/css/layout/breadcrumbs.css',
        'termin-listing-simple' => '/assets/css/components/termin-listing-simple.css',
        'swipers' => '/assets/css/components/swipers.css',
        'galleries' => '/assets/css/components/galleries.css',
        'accessibillity' => '/assets/css/components/accessibillity.css',
        'pagination' => '/assets/css/layout/pagination.css',
    ];

    // Schleife durch das Stylesheet-Array und enqueue jedes Stylesheet
    foreach ($styles as $handle => $path) {
        wp_register_style($handle, get_template_directory_uri() . $path, [], THEME_VERSION, 'all');
        wp_enqueue_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'load_stylesheet');
add_action('enqueue_block_editor_assets', 'load_stylesheet');


function load_variables_in_block_editor() {
    wp_enqueue_style(
        'variables-style',
		wp_get_upload_dir()['baseurl'] . '/theme-css/dynamic-variables.css',
		[],
		filemtime( wp_get_upload_dir()['basedir'] . '/theme-css/dynamic-variables.css' ),
	);
}
add_action('enqueue_block_editor_assets', 'load_variables_in_block_editor');
