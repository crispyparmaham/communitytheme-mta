<?php
// ACF-Felder abrufen
$portrait = get_field('portrait', 'option'); 
$gruswort = get_field('gruswort', 'option'); 
?>

<div class="gruswort-container inner-max-width">
    <!-- Portrait-Bild -->
    <div class="portrait">
        <?php if ($portrait) : ?>
            <img src="<?php echo esc_url($portrait['url']); ?>" alt="<?php echo esc_attr($portrait['alt']); ?>">
        <?php else : ?>
            <img src="https://via.placeholder.com/150" alt="Platzhalter-Portrait">
        <?php endif; ?>
    </div>

    <!-- Grußwort-Text -->
    <div class="content">
        <?php if ($gruswort) : ?>
            <p><?php echo wp_kses_post($gruswort); ?></p>
        <?php else : ?>
            <p>Kein Grußwort verfügbar.</p>
        <?php endif; ?>
    </div>
</div>
