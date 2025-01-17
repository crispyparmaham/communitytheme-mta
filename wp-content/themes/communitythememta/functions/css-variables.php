<?php

// === ENQUEUE THEME STYLES === //
function enqueue_theme_styles() {
    // Haupt-Stylesheet
    wp_enqueue_style('main-style', get_stylesheet_uri());

    // Variables CSS
    wp_enqueue_style(
        'variables-style',
        get_template_directory_uri() . '/assets/css/variables.css',
        [],
        filemtime(get_template_directory() . '/assets/css/variables.css')
    );
}
add_action('wp_enqueue_scripts', 'enqueue_theme_styles');

// === GENERATE DYNAMIC CSS === //
function generate_dynamic_css() {
    // Pfad zur variables.css
    $css_file = get_template_directory() . '/assets/css/variables.css';

    // === GRID SETTINGS === //
    $innerContentWidth = get_field('inner_content_width', 'option') ? get_field('inner_content_width', 'option') . "px" : "1120px";
    $innerHeaderWidth = get_field('inner_header_width', 'option') ? get_field('inner_header_width', 'option') . "px" : "1120px";
    $innerFooterWidth = get_field('inner_footer_width', 'option') ? get_field('inner_footer_width', 'option') . "px" : "1120px";

    // === COLOR SETTINGS === //
    $primaryColor = get_field('primary_color', 'option');
    $secondaryColor = get_field('secondary_color', 'option');

    $backgroundBodyColor = get_field('background_body_color', 'option');
    $backgroundInnerContentColor = get_field('background_inner_content_color', 'option');
    $backgroundHeaderColor = get_field('background_header_color', 'option');
    $backgroundFooterColor = get_field('background_footer_color', 'option');

    $buttonRootColor = get_field('button_color', 'option');
    $buttonHoverColor = get_field('button_hover_color', 'option');

    // === FONT SETTINGS === //
    $bodyText = get_field('body_text', 'option') ? get_field('body_text', 'option') : 18;

    // Dynamische Überschrift-Größen basierend auf bodyText
    $headlineXS = "clamp(" . ($bodyText * 1.2) . "px, 3vw, " . ($bodyText * 1.5) . "px)";
    $headlineS = "clamp(" . ($bodyText * 1.3) . "px, 3.5vw, " . ($bodyText * 1.6) . "px)";
    $headlineM = "clamp(" . ($bodyText * 1.5) . "px, 4vw, " . ($bodyText * 1.8) . "px)";
    $headlineL = "clamp(" . ($bodyText * 1.8) . "px, 5vw, " . ($bodyText * 2.2) . "px)";
    $headlineXL = "clamp(" . ($bodyText * 2.2) . "px, 6vw, " . ($bodyText * 2.8) . "px)";
    $headlineXXL = "clamp(" . ($bodyText * 2.5) . "px, 8vw, " . ($bodyText * 3.3) . "px)";

    $fontFamilyHeading = get_field('font_heading', 'option');
    $fontFamilyText = get_field('font_text', 'option');

    $fontColorHeading = get_field('font_color_heading', 'option');
    $fontColorText = get_field('font_color_text', 'option');
    $fontColorHeader = get_field('font_color_header', 'option');
    $fontColorFooter = get_field('font_color_footer', 'option');
    $fontColorHeaderHover = get_field('font_color_header_hover', 'option');
    $fontColorFooterHover = get_field('font_color_footer_hover', 'option');

    // === MENU SETTINGS === //
    $fontMenuSize = "clamp(" . ($bodyText * 1.0) . "px, 3vw, " . ($bodyText * 1.2) . "px)";
    $marginMenuSize = get_field('margin_size_menu', 'option') . "px";

    // === BUILD CSS CONTENT === //
    $css_content = "
:root {
    /* PRIMARY COLORS */
    --primary-color: {$primaryColor};
    --secondary-color: {$secondaryColor};

    /* BACKGROUND COLORS */
    --body-background-color: {$backgroundBodyColor};
    --inner-content-background-color: {$backgroundInnerContentColor};
    --header-background-color: {$backgroundHeaderColor};
    --footer-background-color: {$backgroundFooterColor};

    /* BUTTON COLORS */
    --button-root-color: {$buttonRootColor};
    --button-hover-color: {$buttonHoverColor};

    /* FONT SIZES */
    --headline-xs: {$headlineXS};
    --headline-s: {$headlineS};
    --headline-m: {$headlineM};
    --headline-l: {$headlineL};
    --headline-xl: {$headlineXL};
    --headline-xxl: {$headlineXXL};
    --body-text-size: {$bodyText}px;

    /* GRID WIDTH */
    --inner-content-width: {$innerContentWidth};
    --inner-header-width: {$innerHeaderWidth};
    --inner-footer-width: {$innerFooterWidth};

    /* FONT SETTINGS */
    --font-family-heading: {$fontFamilyHeading};
    --font-family-text: {$fontFamilyText};

    /* FONT COLORS */
    --font-color-heading: {$fontColorHeading};
    --font-color-text: {$fontColorText};
    --font-color-header: {$fontColorHeader};
    --font-color-footer: {$fontColorFooter};
    --font-color-header-hover: {$fontColorHeaderHover};
    --font-color-footer-hover: {$fontColorFooterHover};

    /* MENU SETTINGS */
    --font-menu-size: {$fontMenuSize};
    --margin-menu-size: {$marginMenuSize};
}";

    // === WRITE CSS TO FILE === //
    file_put_contents($css_file, $css_content);
}

// === TRIGGER CSS GENERATION ON ACF SAVE === //
add_action('acf/save_post', function ($post_id) {
    if ($post_id === 'options') {
        generate_dynamic_css();
    }
});
