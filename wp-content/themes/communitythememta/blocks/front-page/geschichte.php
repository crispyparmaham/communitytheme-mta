<div class="geschichte-main inner-max-width">
    <?php
    // Hauptinhalt "Geschichte der Gemeinde" abrufen
    $geschichte_der_gemeinde = get_field('geschichte_der_gemeinde', 'option');

    if ($geschichte_der_gemeinde) :
    ?>
        <div class="geschichte-content">
            <?php echo wp_kses_post($geschichte_der_gemeinde); ?>
        </div>
    <?php endif; ?>

    <aside class="geschichte-sidebar">
    <?php
    $sidebar_geschichte = get_field('sidebar_geschichte', 'option');
    $logo = get_field('logo', 'option');
    if ($sidebar_geschichte) :
    ?>
        <div class="sidebar-content">
            <img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt']); ?>">
            <?php echo wp_kses_post($sidebar_geschichte); ?>
        </div>
    <?php endif; ?>
</aside>
</div>



