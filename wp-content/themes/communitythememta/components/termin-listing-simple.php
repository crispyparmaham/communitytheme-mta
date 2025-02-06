<div class="termin-listing-simple">
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
        'posts_per_page' => $count,
        'paged' => $paged,
        'offset' => $offset,
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

            // Adresse
            $strasse = get_field('strasse', $post->ID);
            $hausnummer = get_field('hausnummer', $post->ID);
            $plz = get_field('plz', $post->ID);
            $ort = get_field('ort', $post->ID);
            $adresseParts = array_filter([$strasse . ' ' . $hausnummer, $plz . ' ' . $ort], function ($value) {
                return trim($value) !== '';
            });
            $adresse = !empty($adresseParts) ? implode(', ', $adresseParts) : '';
            $adresslink = $adresse ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode("$strasse $hausnummer $plz $ort") : '';

            // Termin-Daten
            $startdatum = get_field('startdatum', $post->ID);
            $enddatum = get_field('enddatum', $post->ID);
            $time = trim(get_field('uhrzeit', $post->ID));
            $more_days = get_field('more_days', $post->ID);

            $startdatum_formatted = $startdatum ? date_i18n("d. F Y", strtotime($startdatum)) : '';
            $enddatum_formatted = $enddatum ? date_i18n("d. F Y", strtotime($enddatum)) : '';
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

                    <!-- Adresse -->
                    <?php if ($adresse): ?>
                        <span class="post-listing-item-adresse adresse">
                            <a href="<?php echo esc_url($adresslink); ?>" target="_blank"
                                aria-label="Adresse von <?php the_title(); ?> in Google Maps anzeigen">
                                <?php echo esc_html($adresse); ?>
                            </a>
                        </span>
                    <?php endif; ?>

                    <!-- Termin-Infos -->
                    <div class="termin-info-wrap">
                        <span class="termin-info <?php echo $more_days ? 'no-margin-right' : ''; ?>">
                            <?php echo $more_days ? 'Vom ' : ''; ?>
                            <?php echo esc_html($startdatum_formatted); ?>
                        </span>
                        <?php if (!$more_days): ?>
                            <span class="post-listing-item-info-time termin-info">
                                <?php echo esc_html($time); ?> Uhr
                            </span>
                        <?php else: ?>
                            <span class="post-listing-item-info-date-end no-padding-left">
                                bis zum <?php echo esc_html($enddatum_formatted); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <a class="termin-link" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"
                        aria-label="Mehr über <?php the_title(); ?> lesen" class="post-link">zum Termin</a>
                </div>
            </div>
        <?php
        endwhile;
    else:
        ?>
        <h3 class="hsmall" aria-live="polite">Aktuell gibt es leider keine geplanten Termine</h3>
    <?php
    endif;
    wp_reset_postdata();
    ?>

</div>