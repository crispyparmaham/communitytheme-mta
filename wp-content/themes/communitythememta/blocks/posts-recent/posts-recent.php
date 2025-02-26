<div class="post-grid" role="list">
    <?php
    $args = [
        'post_type' => 'post',        // Beitragstyp "post"
        'posts_per_page' => 3,
        'orderby' => 'date',       // Sortierung nach Datum
        'order' => 'DESC',       // Neueste Beiträge zuerst
    ];

    // WP_Query initialisieren
    $query = new WP_Query($args);

    // === POSTS LOOP ===
    if ($query->have_posts()):
        while ($query->have_posts()):
            $query->the_post();

            // Beitragstitel und Link
            $post_title = get_the_title();
            $post_link = get_permalink();
            $post_thumbnail = get_post_thumbnail_id();
            // Beitragsauszug
            $excerpt = wp_trim_words(get_the_excerpt(), 30, '...');
            ?>
            <!-- === Beitrag-Item === -->
            <div class="post-item <?php if($post_thumbnail) { echo 'has-thumbnail'; } ?>" role="listitem">
                <div class="post-content">
                    <h3 class="post-title">
                        <a href="<?php echo esc_url($post_link); ?>"><?php echo esc_html($post_title); ?></a>
                    </h3>
                    <div class="post-meta">
                        <span class="post-date"><?php echo get_the_date(); ?></span>
                    </div>
                    <p class="post-excerpt">
                        <?php echo esc_html($excerpt); ?>
                    </p>
                    <a class="post-link" href="<?php echo esc_url($post_link); ?>"
                        title="<?php echo esc_attr($post_title); ?>">Weiterlesen</a>
                </div>
                <?php if($post_thumbnail) : ?>
                <div class="post-thumbnail">
                    <?php imageOutput($post_thumbnail, 'img-640', '600px'); ?>
                </div>
                <?php endif; ?>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
    <?php endif;
    wp_reset_postdata(); ?>
</div>