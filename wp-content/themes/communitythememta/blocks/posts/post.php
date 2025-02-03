<div class="post-grid" role="list">
    <?php
    // === WP_Query Argumente ===
    $args = [
        'post_type'      => 'post',        // Beitragstyp "post"
        'posts_per_page' => 8,            // Anzahl der Beiträge pro Seite
        'paged'          => get_query_var('paged') ?: 1, // Paginierung
        'orderby'        => 'date',       // Sortierung nach Datum
        'order'          => 'DESC',       // Neueste Beiträge zuerst
    ];

    // WP_Query initialisieren
    $query = new WP_Query($args);

    // === POSTS LOOP ===
    if ($query->have_posts()) :
        while ($query->have_posts()) :
            $query->the_post();

            // Beitragstitel und Link
            $post_title = get_the_title();
            $post_link = get_permalink();

            // Beitragsauszug
            $excerpt = wp_trim_words(get_the_excerpt(), 30, '...');
    ?>
            <!-- === Beitrag-Item === -->
            <div class="post-item" role="listitem">
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
                    <a class="post-link" href="<?php echo esc_url($post_link); ?>" title="<?php echo esc_attr($post_title); ?>">Weiterlesen</a>
                </div>
            </div>
    <?php
        endwhile;

        // Pagination
        the_posts_pagination([
            'prev_text' => __('« Vorherige', 'textdomain'),
            'next_text' => __('Nächste »', 'textdomain'),
        ]);
    else :
    ?>
        <p><?php _e('Es wurden keine Beiträge gefunden.', 'textdomain'); ?></p>
    <?php
    endif;

    // WP_Query zurücksetzen
    wp_reset_postdata();
    ?>
</div>
