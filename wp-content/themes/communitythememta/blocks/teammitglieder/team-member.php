<?php
// ACF Felder abrufen
$image = get_field('team_image');
$name = get_field('team_name');
$position = get_field('team_position');
$phone = get_field('team_phone');
$mail = get_field('team_mail');
?>

<div class="team-member">
    <?php if ( $image ) : ?>
        <div class="team-member-image">
            <img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>">
        </div>
    <?php endif; ?>

    <div class="team-member-content">
        <?php if ( $name ) : ?>
            <h3 class="team-member-name"><?php echo esc_html( $name ); ?></h3>
        <?php endif; ?>

        <?php if ( $position ) : ?>
            <p class="team-member-position"><?php echo esc_html( $position ); ?></p>
        <?php endif; ?>

        <?php if ( $phone ) : ?>
            <div class="team-member-phone">
                <p>Telefon: <?php echo wp_kses_post( $phone ); ?></p>
            </div>
        <?php endif; ?>

        <?php if ( $mail ) : ?>
            <div class="team-member-phone">
                <p>E-Mail: <?php echo wp_kses_post( $mail ); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
