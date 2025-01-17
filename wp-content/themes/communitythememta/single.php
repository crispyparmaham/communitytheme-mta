<?php get_header(); ?>
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
<?php get_footer(); ?>