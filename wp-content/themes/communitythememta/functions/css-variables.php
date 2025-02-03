<?php

// === ENQUEUE THEME STYLES === //
function enqueue_theme_styles() {
	// Haupt-Stylesheet
	wp_enqueue_style( 'main-style', get_stylesheet_uri() );

	// Variables CSS
	wp_enqueue_style(
		'variables-style',
		get_template_directory_uri() . '/assets/css/dynamic-variables.css',
		[],
		filemtime( get_template_directory() . '/assets/css/dynamic-variables.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'enqueue_theme_styles' );


// === FUNCTION TO CONVERT HEX TO HSL === //
function hex_to_hsl( $hex ) {
    if ( ! is_string( $hex ) ) {
        return "0, 0%, 0%"; // Fallback zu Schwarz, falls der Wert ungültig ist
    }

    $hex = str_replace( "#", "", $hex );
    if ( strlen( $hex ) === 3 ) {
        $r = hexdec( str_repeat( substr( $hex, 0, 1 ), 2 ) ) / 255;
        $g = hexdec( str_repeat( substr( $hex, 1, 1 ), 2 ) ) / 255;
        $b = hexdec( str_repeat( substr( $hex, 2, 1 ), 2 ) ) / 255;
    } elseif ( strlen( $hex ) === 6 ) {
        $r = hexdec( substr( $hex, 0, 2 ) ) / 255;
        $g = hexdec( substr( $hex, 2, 2 ) ) / 255;
        $b = hexdec( substr( $hex, 4, 2 ) ) / 255;
    } else {
        return "0, 0%, 0%"; // Fallback zu Schwarz, falls der Wert ungültig ist
    }

    $max = max( $r, $g, $b );
    $min = min( $r, $g, $b );
    $delta = $max - $min;

    $h = 0;
    if ( $delta > 0 ) {
        if ( $max === $r ) {
            $h = 60 * ( ( $g - $b ) / $delta % 6 );
        } elseif ( $max === $g ) {
            $h = 60 * ( ( $b - $r ) / $delta + 2 );
        } elseif ( $max === $b ) {
            $h = 60 * ( ( $r - $g ) / $delta + 4 );
        }
    }
    $h = $h < 0 ? $h + 360 : $h;

    $l = ( $max + $min ) / 2;
    $s = $delta === 0 ? 0 : $delta / ( 1 - abs( 2 * $l - 1 ) );

    $h = round( $h );
    $s = round( $s * 100 );
    $l = round( $l * 100 );

    return [$h, $s, $l]; // Rückgabe als Array
}

// === FUNCTION TO GENERATE HSL SHADES === //
function generate_hsl_shades($h, $s, $l) {
    $shades = [];
    for ($i = 1; $i <= 6; $i++) {
        $factor = $i * 0.1;
        $newL = min(100, max(0, $l + ($i - 2.6) * 9)); // Schattierungen zwischen 0% und 100%
        $shades[] = "hsl({$h}, {$s}%, {$newL}%)";
    }
    return $shades;
}

// === GENERATE DYNAMIC CSS === //
function generate_dynamic_css() {
    // Pfad zur variables.css
    $css_file = get_template_directory() . '/assets/css/dynamic-variables.css';

    // === GRID SETTINGS === //
    $innerContentWidth = get_field( 'inner_content_width', 'option' ) ? get_field( 'inner_content_width', 'option' ) . "px" : "1120px";
    $innerHeaderWidth = get_field( 'inner_header_width', 'option' ) ? get_field( 'inner_header_width', 'option' ) . "px" : "1120px";
    $innerFooterWidth = get_field( 'inner_footer_width', 'option' ) ? get_field( 'inner_footer_width', 'option' ) . "px" : "1120px";

    // === COLOR SETTINGS === //
    $primaryColorHex = get_field( 'foreground_color', 'option' );
    $secondaryColorHex = get_field( 'background_color', 'option' );
    $tertiaryColorHex = get_field( 'accent_color', 'option' );

    // Sicherstellen, dass es sich um gültige Hex-Werte handelt
    $primaryColorHex = is_string( $primaryColorHex ) ? $primaryColorHex : "#000000";
    $secondaryColorHex = is_string( $secondaryColorHex ) ? $secondaryColorHex : "#FFFFFF";
    $tertiaryColorHex = is_string( $tertiaryColorHex ) ? $tertiaryColorHex : "#CCCCCC";

    $primaryColorHSL = hex_to_hsl( $primaryColorHex );
    $secondaryColorHSL = hex_to_hsl( $secondaryColorHex );
    $tertiaryColorHSL = hex_to_hsl( $tertiaryColorHex );

    $primaryShades = generate_hsl_shades($primaryColorHSL[0], $primaryColorHSL[1], $primaryColorHSL[2]);
    $secondaryShades = generate_hsl_shades($secondaryColorHSL[0], $secondaryColorHSL[1], $secondaryColorHSL[2]);
    $tertiaryShades = generate_hsl_shades($tertiaryColorHSL[0], $tertiaryColorHSL[1], $tertiaryColorHSL[2]);

    // === FONT SETTINGS === //
    $bodyText = get_field( 'body_text', 'option' ) ? get_field( 'body_text', 'option' ) : 18;

    // Dynamische Überschrift-Größen basierend auf bodyText
    $headlineXS = "clamp(" . ( $bodyText * 1 ) . "px, 3vw, " . ( $bodyText * 1.2 ) . "px)";
    $headlineS = "clamp(" . ( $bodyText * 1.2 ) . "px, 3.5vw, " . ( $bodyText * 1.4 ) . "px)";
    $headlineM = "clamp(" . ( $bodyText * 1.6 ) . "px, 4vw, " . ( $bodyText * 2 ) . "px)";
    $headlineL = "clamp(" . ( $bodyText * 2 ) . "px, 5vw, " . ( $bodyText * 2.6 ) . "px)";
    $headlineXL = "clamp(" . ( $bodyText * 2.2 ) . "px, 6vw, " . ( $bodyText * 3 ) . "px)";
    $headlineXXL = "clamp(" . ( $bodyText * 2.6 ) . "px, 8vw, " . ( $bodyText * 3.2 ) . "px)";


    // === BUILD CSS CONTENT === //
    $css_content = "
:root {
    /* PRIMARY COLORS */
    --color-foreground: {$primaryShades[0]};
    --color-background: {$secondaryShades[0]};
    --color-accent: {$tertiaryShades[0]};

    /* COLOR SHADES */
    --foreground-01: {$primaryShades[1]};
    --foreground-02: {$primaryShades[2]};
    --foreground-03: {$primaryShades[3]};
    --foreground-04: {$primaryShades[4]};
    --foreground-05: {$primaryShades[5]};

    --background-01: {$secondaryShades[1]};
    --background-02: {$secondaryShades[2]};
    --background-03: {$secondaryShades[3]};
    --background-04: {$secondaryShades[4]};
    --background-05: {$secondaryShades[5]};

    --accent-01: {$tertiaryShades[1]};
    --accent-02: {$tertiaryShades[2]};
    --accent-03: {$tertiaryShades[3]};
    --accent-04: {$tertiaryShades[4]};
    --accent-05: {$tertiaryShades[5]};
    
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

}";

    // === WRITE CSS TO FILE === //
    file_put_contents( $css_file, $css_content );
}


// === TRIGGER CSS GENERATION ON ACF SAVE === //
add_action( 'acf/save_post', function ($post_id) {
	if ( $post_id === 'options' ) {
		generate_dynamic_css();
	}
} );
