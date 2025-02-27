<?php get_header(); ?>

<main class="main-container">
    <!-- Termin-Header: Bild und Titel -->
    <section class="header-img-wrap">
        <?php if (has_post_thumbnail()): ?>
            <?php $headerImage = get_the_post_thumbnail_url(null, 'full'); ?>
            <img src="<?php echo esc_url($headerImage); ?>" alt="<?php the_title_attribute(); ?>">
        <?php else: ?>
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/mta-communitytheme-bg-thumbnail.jpg"
                alt="Standard-Hintergrundbild der MTA-Community">
        <?php endif; ?>
        <div class="header-img-heading">
            <h1><?php printf(__('Suchergebnisse für: %s', 'textdomain'), '<span>' . get_search_query() . '</span>'); ?>
            </h1>
        </div>
    </section>
    <div class="main-content">
        <article class="left-content-column">

            <div class="search-container">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <label for="search"
                        class="screen-reader-text"><?php echo _x('Suche nach:', 'label', 'textdomain'); ?></label>
                    <input type="search" id="search" class="search-field" placeholder="Suchbegriff eingeben..."
                        value="<?= get_search_query() ?>" name="s" />
                    <button type="submit" class="search-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path
                                d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z" />
                        </svg>
                        <span
                            class="screen-reader-text"><?php echo _x('Suchen', 'submit button', 'textdomain'); ?></span>
                    </button>
                </form>
            </div>

            <?php if (have_posts()): ?>
                <div class="search-results">
                    <?php while (have_posts()):
                        the_post();
                        $post_title = get_the_title();
                        $post_link = get_permalink();
                        $post_thumbnail = get_post_thumbnail_id();
                        // Beitragsauszug
                        $post_type_title = get_post_type_object(get_post_type())->labels->singular_name;
                        $post_type = get_post_type();
                        $excerpt = wp_trim_words(get_the_excerpt(), 30, '...');
                        $termin_data = [];
                        if ($post_type === 'termin') {
                            $termin_data = get_termin_data($post->ID);
                        }

                        $button_text = "Weiterlesen";
                        switch ($post_type) {
                            case 'termin':
                                $button_text = "Zum Termin";
                                break;
                            case 'post':
                                $button_text = "Zum Beitrag";
                                break;
                            case 'page':
                                $button_text = "Zur Seite";
                                break;
                        }

                        ?>
                        <article class="search-result-item post-item <?php if ($post_thumbnail) {
                            echo 'has-thumbnail';
                        } ?>"
                            role="listitem">
                            <div class="post-content">
                                <span><?= $post_type_title ?></span>
                                <h2 class="search-result-title">
                                    <a href="<?php echo $permalink; ?>"><?php echo $post_title; ?></a>
                                </h2>
                                <?php if ($excerpt): ?>
                                    <div class="search-result-excerpt">
                                        <?php echo $excerpt; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($termin_data)): ?>
                                    <div class="termin-listing-simple">

                                        <?php
                                        echo get_termin_data_formatted_simple($termin_data);
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <a class="post-link" href="<?php echo esc_url($post_link); ?>"
                                    title="<?php echo esc_attr($post_title); ?>"><?= $button_text ?></a>
                            </div>
                            <?php if ($post_thumbnail): ?>
                                <div class="post-thumbnail">
                                    <?php imageOutput($post_thumbnail, 'img-640', '600px'); ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination (Seitenzahlen) -->
                <div class="pagination">
                    <?php
                    the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => __('Zurück', 'textdomain'),
                        'next_text' => __('Weiter', 'textdomain'),
                    ));
                    ?>
                </div>

            <?php else: ?>
                <p><?php _e('Keine Ergebnisse gefunden.', 'textdomain'); ?></p>
            <?php endif; ?>
        </article>

        <aside class="right-content-column">
            <div class="scroll-container">
                <?php get_sidebar(); ?>
            </div>
        </aside>
    </div>
</main>
<?php get_footer(); ?>