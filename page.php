<?php get_header(); ?>

<?php
  $main_slider = get_field('main_slider', 'option');
  $page_slider = get_field('page_slider');
  $slider = $page_slider ? $page_slider : $main_slider;

?>


<main class="main-container" aria-label="Haupt Inhaltsbereich">
	<section class="header-img-wrap">
		<?php if ( $slider && !empty($slider) ) : ?>
			<?php get_template_part( 'components/top-slider' ); ?>
		<?php endif; ?>
	</section>
	<section class="inner-max-width">
		<?php custom_breadcrumbs(); ?>
		<div class="page-start-text">
			<h1><?php the_title(); ?></h1>
			<?php
			$startText = get_field( 'einleitungstext' );
			?>
			<?php if ( $startText ) : ?>
				<?php echo wp_kses_post($startText); ?>
			<?php endif; ?>
		</div>
	</section>

	<div class="main-content">
		<article class="left-content-column">
			<div class="left-content-column-content-wrapper">

			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) :
						the_post(); ?>
						<?php the_content(); ?>
				<?php endwhile; ?>
			<?php else : ?>
						<p>Keine Inhalte gefunden. Bitte versuchen Sie es später erneut.</p>
			<?php endif; ?>
			</div>
		</article>

		<aside class="right-content-column">
			<div class="scroll-container">
				<?php get_sidebar(); ?>
			</div>
		</aside>
	</div>
</main>

<?php get_footer(); ?>