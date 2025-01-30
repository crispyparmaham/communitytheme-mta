<?php get_header(); ?>

<main class="main-container">
	<!-- Termin-Header: Bild und Titel -->
	<section class="header-img-wrap">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php $headerImage = get_the_post_thumbnail_url( null, 'full' ); ?>
			<img src="<?php echo esc_url( $headerImage ); ?>" alt="<?php the_title_attribute(); ?>">
		<?php else : ?>
			<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/mta-communitytheme-bg-thumbnail.jpg"
				alt="Standard-Hintergrundbild der MTA-Community">
		<?php endif; ?>
		<div class="header-img-heading">
            <h1><?php printf( __( 'Suchergebnisse für: %s', 'textdomain' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
		</div>
	</section>
	<div class="main-content">
		<article class="left-content-column">
        <?php if ( have_posts() ) : ?>
        <div class="search-results">
            <?php while ( have_posts() ) : the_post(); ?>
                <article class="search-result-item">
                    <h2 class="search-result-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                    <div class="search-result-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- Pagination (Seitenzahlen) -->
        <div class="pagination">
            <?php
            the_posts_pagination( array(
                'mid_size' => 2,
                'prev_text' => __( 'Zurück', 'textdomain' ),
                'next_text' => __( 'Weiter', 'textdomain' ),
            ) );
            ?>
        </div>

    <?php else : ?>
        <p><?php _e( 'Keine Ergebnisse gefunden.', 'textdomain' ); ?></p>
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