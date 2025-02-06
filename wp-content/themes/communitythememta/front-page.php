<?php get_header(); ?>
<?php
$main_slider = get_field('main_slider', 'option');
$page_slider = get_field('page_slider');
$slider = $page_slider ? $page_slider : $main_slider;

?>

<main class="main-container">
	<section id="start-header" class="header-img-wrap" role="banner">
		<?php if ($slider && !empty($slider)): ?>
			<?php get_template_part('components/top-slider'); ?>
		<?php endif; ?>
		<div class="header-img-heading">
			<?php
			$gemeindeName = get_field('gemeindename', 'option');
			?>
			<h1><?php the_title(); ?> <span><?php echo $gemeindeName; ?></span></h1>
		</div>
	</section>

	<div id="front-page">
		<section id="grusswort" class="content-container">
			<?php include get_template_directory() . '/blocks/front-page/grusswort.php'; ?>
		</section>
		<section id="daten-fakten" class="content-container">
			<h2 class="section-heading inner-max-width">Daten & Fakten</h2>
			<?php include get_template_directory() . '/blocks/front-page/daten.php'; ?>
		</section>
		<section id="galerie " class="content-container">
			<?php

			$linkes_bild = get_field('linkes_bild', 'option');
			$rechtes_bild = get_field('rechtes_bild', 'option');

			?>
			<div class="gallery-img-wrap">
				<img src="<?php echo esc_url($linkes_bild['url']); ?>" alt="Bild 1">
				<img src="<?php echo esc_url($rechtes_bild['url']); ?>" alt="Bild 1">
			</div>
		</section>
		<section id="vereine" class="content-container">
			<h2 class="section-heading inner-max-width">Vereine</h2>
			<?php include get_template_directory() . '/blocks/vereine/verein.php'; ?>
		</section>
		<section id="geschichte" class="content-container">
			<h2 class="section-heading inner-max-width">Geschichte</h2>
			<?php include get_template_directory() . '/blocks/front-page/geschichte.php'; ?>
		</section>
	</div>
</main>

<?php get_footer(); ?>