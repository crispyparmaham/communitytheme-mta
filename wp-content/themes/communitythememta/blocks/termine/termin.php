<div class="termin-list-start">
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
        'post_type'      => 'termin',
        'posts_per_page' => $count,
        'paged'          => $paged,
        'offset'         => $offset,
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_key'       => 'startdatum',
        'meta_query'     => [
            [
                'key'     => 'startdatum',
                'compare' => '>=',
                'value'   => $today,
            ],
        ],
    ];

    $custom_query = new WP_Query($args);

    // === POSTS LOOP === //
    if ($custom_query->have_posts()) :
        while ($custom_query->have_posts()) :
            $custom_query->the_post();

            // Post-Daten
            $post_image = get_post_thumbnail_id();
            $post_image_array = wp_get_attachment_image_src($post_image, 'post-listing');
            $post_image_width = $post_image_array ? $post_image_array[1] : 310;
            $post_image_height = $post_image_array ? $post_image_array[2] : 200;
            $placeholder_image = get_field('platzhalter_bild_termine', 'option')['id'];

            // Adresse
            $strasse = get_field('strasse', $post->ID);
            $hausnummer = get_field('hausnummer', $post->ID);
            $plz = get_field('plz', $post->ID);
            $ort = get_field('ort', $post->ID);
            $adresse = trim("$strasse $hausnummer, $plz $ort") ?: false;
            $adresslink = $adresse ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode("$strasse $hausnummer $plz $ort") : '';

            // Termin-Daten
            $startdatum = get_field('startdatum', $post->ID);
            $enddatum = get_field('enddatum', $post->ID);
            $time = trim(get_field('uhrzeit', $post->ID));
            $more_days = get_field('more_days', $post->ID);
            $date_icon = get_field('date_icon', 'option') ?: 'dashicons-calendar-alt';
            $time_icon = get_field('time_icon', 'option') ?: 'dashicons-clock';

            $startdatum_formatted = $startdatum ? date_i18n("d. F Y", strtotime($startdatum)) : '';
            $enddatum_formatted = $enddatum ? date_i18n("d. F Y", strtotime($enddatum)) : '';
    ?>
            <!-- === POST ITEM === -->
            <div id="child-<?php the_ID(); ?>" class="child-page-list-item post-item-startseite post-listing-item termin-item"
                data-search-term="<?php echo esc_attr(implode(' ', wp_list_pluck(get_the_category(), 'slug'))); ?>">
                
                <!-- Kategorien -->
                <?php 
                $categories = get_the_category();
                if ($categories) :
                    foreach ($categories as $category) : ?>
                        <span class="post-category"><?php echo esc_html($category->name); ?></span>
                    <?php endforeach; 
                endif; ?>

                <!-- Bild -->
                <div class="post-listing-item-image <?php echo $post_image ? '' : 'is-empty-post-image'; ?>">
                    <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                        <img <?php responsive_image($post_image ?: $placeholder_image, 'post-listing', '310px'); ?>
                            height="<?php echo esc_attr($post_image_height); ?>" 
                            width="<?php echo esc_attr($post_image_width); ?>" 
                            alt="<?php echo esc_attr(get_post_meta($post_image ?: $placeholder_image, '_wp_attachment_image_alt', true) ?: get_the_title($post_image ?: $placeholder_image)); ?>" 
                            loading="lazy">
                    </a>
                </div>

                <!-- Text -->
                <div class="listing-text-wrap post-listing-item-text">
                    <h3 class="post-listings-item-heading">
                        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                    </h3>

                    <!-- Adresse -->
                    <?php if ($adresse) : ?>
                        <span class="post-listing-item-adresse adresse">
                            <a href="<?php echo esc_url($adresslink); ?>" target="_blank"><?php echo esc_html($adresse); ?></a>
                        </span>
                    <?php endif; ?>

                    <!-- Termin-Infos -->
                    <div class="termin-info-wrap">
                        <span class="termin-info <?php echo $more_days ? 'no-margin-right' : ''; ?>">
                            <span class="termin-icon dashicons <?php echo esc_attr($date_icon); ?>"></span>
                            <?php echo $more_days ? 'Vom ' : ''; ?>
                            <?php echo esc_html($startdatum_formatted); ?>
                        </span>
                        <?php if (!$more_days) : ?>
                            <span class="post-listing-item-info-time termin-info">
                                <span class="termin-icon dashicons <?php echo esc_attr($time_icon); ?>"></span>
                                <?php echo esc_html($time); ?> Uhr
                            </span>
                        <?php else : ?>
                            <span class="post-listing-item-info-date-end no-padding-left">
                                bis zum <?php echo esc_html($enddatum_formatted); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Excerpt -->
                    <?php if (function_exists('custom_field_excerpt') && !empty(custom_field_excerpt())) : ?>
                        <span class="post-excerpt"><?php echo custom_field_excerpt(); ?></span>
                    <?php endif; ?>
                </div>
            </div>
    <?php 
        endwhile;
    else : 
    ?>
        <h3 class="hsmall">Aktuell gibt es leider keine geplanten Termine</h3>
    <?php 
    endif;
    wp_reset_postdata(); 
    ?>
</div>