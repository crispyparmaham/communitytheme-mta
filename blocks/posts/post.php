<div class="post-grid" role="list">
    <?php
    // === WP_Query Argumente ===
    $count = get_option('posts_per_page', 10);
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $offset = ($paged - 1) * $count;
    $args = [
        'post_type' => 'post',        // Beitragstyp "post"
        'posts_per_page' => $count,
        'paged' => $paged,
        'offset' => $offset,
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
        <?php
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1): ?>
            <div class="mta-ct-pagination"><?php endif; ?>
            <?php
            if ($total_pages > 1) {

                $current_page = max(1, get_query_var('paged'));

                echo paginate_links(array(
                    'base' => get_pagenum_link(1) . '%_%',
                    'format' => '/page/%#%',
                    'current' => $current_page,
                    'total' => $total_pages,
                    'prev_text' => __('Vorherige Seite'),
                    'next_text' => __('Nächste Seite'),
                    'add_args' => array()
                ));
            }
            ?>
            <?php if ($total_pages > 1): ?>
            </div><?php endif; ?>

    <?php else: ?>
        <p><?php _e('Es wurden keine Beiträge gefunden.', 'textdomain'); ?></p>
    <?php endif;
    wp_reset_postdata(); ?>
</div>