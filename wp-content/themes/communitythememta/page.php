<?php get_header(); ?>

<main class="content-container">
	<section class="header-img-wrap">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php $headerImage = get_the_post_thumbnail_url( null, 'full' ); ?>
			<img src="<?php echo esc_url( $headerImage ); ?>" alt="<?php the_title_attribute(); ?>">
		<?php else : ?>
			<img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/mta-communitytheme-bg-thumbnail.jpg"
				alt="Standard-Hintergrundbild der MTA-Community">
		<?php endif; ?>
		<div class="header-img-heading">
			<h1><?php the_title(); ?></h1>
		</div>
	</section>

	<div class="main-content">
		<article class="left-content-column">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php the_content(); ?>
				<?php endwhile; ?>
			<?php else : ?>
				<p>Keine Inhalte gefunden. Bitte versuchen Sie es später erneut.</p>
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
