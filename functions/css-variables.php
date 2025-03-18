<?php

// === ENQUEUE THEME STYLES === //
function enqueue_theme_styles() {
	// Haupt-Stylesheet
	wp_enqueue_style( 'main-style', get_stylesheet_uri() , [], THEME_VERSION);

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
function hex_to_hsl( string $hex ) : array  {
    if ( ! is_string( $hex ) ) {
        return ['0', '0', '0']; // Fallback zu Schwarz, falls der Wert ungültig ist
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
        return ['0', '0', '0']; // Fallback zu Schwarz, falls der Wert ungültig ist
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

/* === FUNCTION TO GENERATE HSL SHADES === */
// function generate_hsl_shades($h, $s, $l) {
//     $shades = [];
//     for ($i = 1; $i <= 6; $i++) {
//         $newL = min(100, max(0, $l + ($i - 2.6) * 9)); // Schattierungen zwischen 0% und 100%
//         $shades[] = "{$h}, {$s}%, {$newL}%";
//     }
//     return $shades;
// }

function generate_hsl_shades(int $h, int $s, int $l) {
    $shades = [];

    for ($i = 1; $i <= 6; $i++) {
        // Dynamic factor based on the initial lightness
        if ($l > 80) {
            $factor = 6; // For very light colors, smaller adjustments
        } elseif ($l > 50) {
            $factor = 9; // Mid-range colors get standard adjustments
        } else {
            $factor = 12; // Darker colors get stronger changes
        }

        // Apply a non-linear transformation
        $adjustment = ($i - 3) * $factor;
        $newL = min(98, max(5, $l + $adjustment)); // Keep within 5%-98% range

        $shades[] = "{$h}, {$s}%, {$newL}%";
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
    $tertiaryColorContrastHex = get_field( 'accent_color_contrast', 'option' );

    // Sicherstellen, dass es sich um gültige Hex-Werte handelt
    $primaryColorHex = $primaryColorHex?: "#000000";
    $secondaryColorHex = $secondaryColorHex ?: "#c4c4c4";
    $tertiaryColorHex = $tertiaryColorHex ?: "#00a7f5";
    $tertiaryColorContrastHex = $tertiaryColorContrastHex ?: "#FFFFFF";

    $primaryColorHSL = hex_to_hsl( $primaryColorHex );
    $secondaryColorHSL = hex_to_hsl( $secondaryColorHex );
    $tertiaryColorHSL = hex_to_hsl( $tertiaryColorHex );
    $teriaryColorContrastHSL = hex_to_hsl( $tertiaryColorContrastHex );

    $primaryShades = generate_hsl_shades($primaryColorHSL[0], $primaryColorHSL[1], $primaryColorHSL[2]);
    $secondaryShades = generate_hsl_shades($secondaryColorHSL[0], $secondaryColorHSL[1], $secondaryColorHSL[2]);
    $tertiaryShades = generate_hsl_shades($tertiaryColorHSL[0], $tertiaryColorHSL[1], $tertiaryColorHSL[2]);

    // === FONT SETTINGS === //
    $bodyText = get_field( 'body_text', 'option' ) ? get_field( 'body_text', 'option' ) : 18;
   


    // === BUILD CSS CONTENT === //
    $css_content = "
:root {
    /* PRIMARY COLORS */
    --color-foreground-values: {$primaryShades[0]};
    --color-foreground: hsl(var(--color-foreground-values));
    --color-background-values: {$secondaryShades[0]};
    --color-background: hsl(var(--color-background-values));
    --color-accent-values: {$tertiaryShades[0]};
    --color-accent: hsl(var(--color-accent-values));

    /* COLOR SHADES */
    --foreground-01-values: {$primaryShades[1]};
    --foreground-02-values: {$primaryShades[2]};
    --foreground-03-values: {$primaryShades[3]};
    --foreground-04-values: {$primaryShades[4]};
    --foreground-05-values: {$primaryShades[5]};
    --foreground-01: hsl(var(--foreground-01-values));
    --foreground-02: hsl(var(--foreground-02-values));
    --foreground-03: hsl(var(--foreground-03-values));
    --foreground-04: hsl(var(--foreground-04-values));
    --foreground-05: hsl(var(--foreground-05-values));

    --background-01-values: {$secondaryShades[1]};
    --background-02-values: {$secondaryShades[2]};
    --background-03-values: {$secondaryShades[3]};
    --background-04-values: {$secondaryShades[4]};
    --background-05-values: {$secondaryShades[5]};
    --background-01: hsl(var(--background-01-values));
    --background-02: hsl(var(--background-02-values));
    --background-03: hsl(var(--background-03-values));
    --background-04: hsl(var(--background-04-values));
    --background-05: hsl(var(--background-05-values));

    --accent-01-values: {$tertiaryShades[1]};
    --accent-02-values: {$tertiaryShades[2]};
    --accent-03-values: {$tertiaryShades[3]};
    --accent-04-values: {$tertiaryShades[4]};
    --accent-05-values: {$tertiaryShades[5]};
    --accent-01: hsl(var(--accent-01-values));
    --accent-02: hsl(var(--accent-02-values));
    --accent-03: hsl(var(--accent-03-values));
    --accent-04: hsl(var(--accent-04-values));
    --accent-05: hsl(var(--accent-05-values));

    --accent-contrast-values: {$teriaryColorContrastHSL[0]}deg, {$teriaryColorContrastHSL[1]}%, {$teriaryColorContrastHSL[2]}%; 
    --accent-contrast: hsl(var(--accent-contrast-values));
    
    /* FONT SIZES */
    --body-text-size: {$bodyText}px;
    --headline-xs: clamp(var(--body-text-size), 3vw, calc(var(--body-text-size) * 1.2));
    --headline-s: clamp(calc(var(--body-text-size) * 1.2), 3.5vw, calc(var(--body-text-size) * 1.4));
    --headline-m: clamp(calc(var(--body-text-size) * 1.6), 4vw, calc(var(--body-text-size) * 2));
    --headline-l: clamp(calc(var(--body-text-size) * 2), 5vw, calc(var(--body-text-size) * 2.6));
    --headline-xl: clamp(calc(var(--body-text-size) * 2.2), 6vw, calc(var(--body-text-size) * 3.16666));
    --headline-xxl: clamp(calc(var(--body-text-size) * 2.6), 8vw, calc(var(--body-text-size) * 3.2));

    /* GRID WIDTH */
    --inner-content-width: {$innerContentWidth};
    --inner-header-width: {$innerHeaderWidth};
    --inner-footer-width: {$innerFooterWidth};

    /* Border RADIUS */
    --border-radius-s: 4px;
    --border-radius-m: 6px;
    --border-radius-l: 10px;
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
