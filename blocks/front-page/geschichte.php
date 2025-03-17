<?php
$history = get_field('geschichte_der_gemeinde', 'option');
$sidebar_history = get_field('sidebar_geschichte', 'option');
$logo = get_field('logo', 'option');

?>
<div class="geschichte-main inner-max-width">
<?php if ($history) : ?>
        <div class="geschichte-content">
            <?php echo wp_kses_post($history); ?>
        </div>
    <?php endif; ?>

    <aside class="geschichte-sidebar">
    <?php
    if ($sidebar_history) :
    ?>
        <div class="sidebar-content">
            <img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt']); ?>">
            <?php echo wp_kses_post($sidebar_history); ?>
        </div>
    <?php endif; ?>
</aside>
</div>



