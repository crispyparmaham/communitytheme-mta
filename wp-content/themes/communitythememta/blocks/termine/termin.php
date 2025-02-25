<div class="termin-list-start" role="list">
    <?php
    // === INITIALISIERUNG === //
    global $post;

    // Aktuelles Datum und Pagination-Einstellungen
    $today = date('Ymd');
    $count = get_option('posts_per_page', 4);
    $paged = get_query_var('paged') ? get_query_var('paged') : 1;
    $offset = ($paged - 1) * $count;

    // WP_Query-Argumente für "Termin"-Posts
    $args = [
        'post_type' => 'termin',
        'posts_per_page' => -1, //$count,
        // 'paged'          => $paged,
        // 'offset'         => $offset,
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

    $query = new WP_Query($args);
    $gemeindeName = get_field('gemeindename', 'option');

    // === POSTS LOOP === //
    if ($query->have_posts()):
        $current_month = '';
        while ($query->have_posts()):
            $query->the_post();

            // Post-Daten
            $post_image = get_post_thumbnail_id();
            $post_image_array = wp_get_attachment_image_src($post_image, 'post-listing');
            $post_image_width = $post_image_array ? $post_image_array[1] : 310;
            $post_image_height = $post_image_array ? $post_image_array[2] : 200;
            $placeholder_image = get_field('platzhalter_bild_termine', 'option') ? get_field('platzhalter_bild_termine', 'option')['id'] : false;

            // Adresse
            $strasse = get_field('strasse', $post->ID);
            $hausnummer = get_field('hausnummer', $post->ID);
            $plz = get_field('plz', $post->ID);
            $ort = get_field('ort', $post->ID);
            $adresseParts = array_filter([$strasse . ' ' . $hausnummer, $plz . ' ' . $ort], function ($value) {
                return trim($value) !== '';
            });
            $adresse = !empty($adresseParts) ? implode(', ', $adresseParts) : '';
            $adresslink = $adresse ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode("$strasse $hausnummer $plz $ort $gemeindeName") : '';

            // Termin-Daten
            $organizer = get_field('organisator', $post->ID);
            $startdatum = get_field('startdatum', $post->ID);
            $enddatum = get_field('enddatum', $post->ID);
            $time = trim(get_field('uhrzeit', $post->ID));
            $more_days = get_field('more_days', $post->ID);
            $date_icon = get_field('date_icon', 'option') ?: 'dashicons-calendar-alt';
            $time_icon = get_field('time_icon', 'option') ?: 'dashicons-clock';

            $startdatum_formatted = $startdatum ? date_i18n("d. F Y", strtotime($startdatum)) : '';
            $enddatum_formatted = $enddatum ? date_i18n("d. F Y", strtotime($enddatum)) : '';

            $post_thumbnail = get_post_thumbnail_id();

            $content = get_the_content();

            $start_month = date_i18n('F', strtotime($startdatum));

            if ($start_month != $current_month) {
                $current_month = $start_month;
                echo '<h2 class="mt-3">' . $current_month . '</h2>';
                echo '<hr class="mb-1">';
            }
            ?>
            <!-- === POST ITEM === -->
            <div id="child-<?php the_ID(); ?>" class="child-page-list-item post-item-startseite post-listing-item termin-item"
                data-search-term="<?php echo esc_attr(implode(' ', wp_list_pluck(get_the_category(), 'slug'))); ?>"
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
                    <?php if ($content): ?>
                        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"
                        aria-label="Mehr über <?php the_title(); ?> lesen">
                        <?php endif; ?>
                        <?php the_title(); ?>
                        <?php if ($content): ?>
                        </a>
                        <?php endif; ?>
                    </h3>

                    <!-- Adresse -->
                    <?php if ($adresse || $organizer): ?>
                        <span class="post-listing-item-adresse adresse">
                            <?php if ($organizer): ?>
                                <?= $organizer ?>
                            <?php endif; ?>
                            <?php if ($adresse && $organizer): ?>
                                <span class="separator">|</span>
                            <?php endif; ?>
                            <?php if ($adresse): ?>
                                <a href="<?php echo esc_url($adresslink); ?>" target="_blank"
                                    aria-label="Adresse von <?php the_title(); ?> in Google Maps anzeigen">
                                    <?php echo esc_html($adresse); ?>
                                </a>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>

                    <!-- Termin-Infos -->
                    <div class="termin-info-wrap">
                        <span class="termin-info <?php echo $more_days ? 'no-margin-right' : ''; ?>">
                            <span class="termin-icon dashicons <?php echo esc_attr($date_icon); ?>" aria-hidden="true"></span>
                            <?php echo $more_days ? 'Vom ' : ''; ?>
                            <?php echo esc_html($startdatum_formatted); ?>
                        </span>
                        <?php if (!$more_days): ?>
                            <?php if ($time): ?>
                                <span class="post-listing-item-info-time termin-info">
                                    <span class="termin-icon dashicons <?php echo esc_attr($time_icon); ?>" aria-hidden="true"></span>
                                    <?php echo esc_html($time); ?> Uhr
                                </span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="post-listing-item-info-date-end no-padding-left">
                                bis zum <?php echo esc_html($enddatum_formatted); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Excerpt -->
                    <?php if (function_exists('custom_field_excerpt') && !empty(custom_field_excerpt())): ?>
                        <span class="post-excerpt" aria-label="Zusammenfassung von <?php the_title(); ?>">
                            <?php echo custom_field_excerpt(); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($content): ?>
                        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"
                        aria-label="Mehr über <?php the_title(); ?> lesen" class="block mt-1"> Weitere Infos</a>
                    <?php endif; ?>
                        

                </div>
                <?php if ($post_thumbnail): ?>
                    <?php if ($content): ?>
                        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                        <?php endif; ?>
                        <div class="post-listing-item-image termin-post-thumbnail">
                            <?php imageOutput($post_thumbnail, 'img-480', '200px'); ?>
                        </div>
                        <?php if ($content): ?>
                        </a>
                    <?php endif; ?>
                    <!-- Bild -->
                <?php elseif ($placeholder_image): ?>
                    <div class="post-listing-item-image <?php echo $post_image ? '' : 'is-empty-post-image'; ?>">
                        <?php if ($content): ?>
                            <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                            <?php endif; ?>
                            <?php
                            if ($post_image) {
                                echo wp_get_attachment_image($post_image, 'post-listing', false, [
                                    'width' => $post_image_width,
                                    'height' => $post_image_height,
                                    'alt' => esc_attr(get_post_meta($post_image, '_wp_attachment_image_alt', true) ?: get_the_title($post_image)),
                                    'loading' => 'lazy',
                                ]);
                            } else {
                                echo wp_get_attachment_image($placeholder_image, 'post-listing', false, [
                                    'width' => $post_image_width,
                                    'height' => $post_image_height,
                                    'alt' => esc_attr(get_post_meta($placeholder_image, '_wp_attachment_image_alt', true) ?: get_the_title($placeholder_image)),
                                    'loading' => 'lazy',
                                ]);
                            }
                            ?>
                            <?php if ($content): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        endwhile; ?>
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
        <h3 class="hsmall" aria-live="polite">Aktuell gibt es leider keine geplanten Termine</h3>
        <?php
    endif;
    wp_reset_postdata();
    ?>
</div>