<?php
// Query für den CPT "teammitglieder"

global $post;

$args = array(
    'post_type' => 'teammitglieder',
    'posts_per_page' => -1,
);

$query = new WP_Query($args);

if ($query->have_posts()) :
    echo '<div class="team-members">';
    while ($query->have_posts()) : $query->the_post();
        // Hole die ACF-Felder des aktuellen Posts
        $team_image = get_field('team_image'); // Bildfeld
        $team_name = get_field('team_name'); // Name
        $team_position = get_field('team_position'); // Position
        $team_phone = get_field('team_phone'); // Telefon
        $team_mail = get_field('team_mail'); // E-Mail
        ?>
        <div class="team-member">
            <?php if ($team_image): ?>
                <div class="team-member-img">
                    <img src="<?php echo esc_url($team_image['url']); ?>" alt="<?php echo esc_attr($team_name); ?>">
                </div>
            <?php endif; ?>
            <div class="team-member-info">
                <?php if ($team_name): ?>
                    <h3 class="team-member-name"><?php echo esc_html($team_name); ?></h3>
                <?php endif; ?>
                <?php if ($team_position): ?>
                    <p class="team-member-position"><?php echo esc_html($team_position); ?></p>
                <?php endif; ?>
                <?php if ($team_phone): ?>
                    <p class="team-member-phone">📞 <?php echo esc_html($team_phone); ?></p>
                <?php endif; ?>
                <?php if ($team_mail): ?>
                    <p class="team-member-email">✉️ <a href="mailto:<?php echo esc_attr(antispambot($team_mail)); ?>"><?php echo esc_html($team_mail); ?></a></p>
                <?php endif; ?>
            </div>
        </div>
    <?php
    endwhile;
    echo '</div>';
endif;

wp_reset_postdata();
?>
