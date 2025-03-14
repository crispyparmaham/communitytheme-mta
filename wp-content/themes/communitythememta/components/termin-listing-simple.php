<div class="termin-listing-simple" role="list">
    <?php
    // === INITIALISIERUNG === //
    global $post;

    $gemeindeName = get_field('gemeindename', 'option');

    // Aktuelles Datum und Pagination-Einstellungen
    $today = date('Ymd');
    $count = get_option('posts_per_page', 4);
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $offset = ($paged - 1) * $count;

    // WP_Query-Argumente für "Termin"-Posts
    $args = [
        'post_type' => 'termin',
        'posts_per_page' => 3,
        'orderby' => 'meta_value',
        'order' => 'ASC',
        'meta_key' => 'startdatum',
        'meta_query' => [
            [
                'key' => 'startdatum',
                'compare' => '>=',
                'value' => $today,
            ],
        ],
    ];

    $custom_query = new WP_Query($args);

    // === POSTS LOOP === //
    if ($custom_query->have_posts()):
        while ($custom_query->have_posts()):
            $custom_query->the_post();

            $termin_data = get_termin_data($post->ID);
            $termin_content = get_the_content($post->ID);
            $termin_description = get_field('beschreibung', $post->ID);

            ?>
            <!-- === POST ITEM === -->
            <div id="child-<?php the_ID(); ?>" class="child-page-list-item post-item-startseite post-listing-item termin-item"
                role="listitem">

                <!-- Kategorien -->
                <?php
                $categories = get_the_category();
                if ($categories):
                    echo '<ul class="post-categories" role="list">';
                    foreach ($categories as $category): ?>
                        <li class="post-category" role="listitem"><?php echo esc_html($category->name); ?></li>
                    <?php endforeach;
                    echo '</ul>';
                endif; ?>

                <!-- Text -->
                <div class="listing-text-wrap post-listing-item-text">
                    <h3 class="post-listings-item-heading">

                        <?php the_title(); ?>

                    </h3>

                    <?php 
                        echo get_termin_data_formatted_simple($termin_data);
                    ?>
                    <?php if($termin_content || $termin_description) :?>
                    <a class="termin-link" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"
                        aria-label="Mehr über <?php the_title(); ?> lesen" class="post-link">zum Termin</a>
                    <?php endif; ?>
                </div>
               
            </div>
        <?php endwhile; ?>

        <?php
        $args = [
            'post_type' => 'termin',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'startdatum',
                    'compare' => '>=',
                    'value' => $today,
                    'type' => 'DATE'
                ],
            ],
        ];
        $termine_query = new WP_Query($args);
        $total_termine = $termine_query->found_posts;
    
        if ($total_termine > 3):
        ?>
        <a href="/aktuelles/#termine-veranstaltungen" class="mt-3 block">Alle Termine ansehen</a>
        <?php endif; ?>
    <?php else: ?>
        <h3 class="hsmall" aria-live="polite">Aktuell gibt es leider keine geplanten Termine</h3>
    <?php
    endif;
    wp_reset_postdata();
    ?>

</div>