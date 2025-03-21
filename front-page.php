<?php get_header(); ?>
<?php
$main_slider = get_field('main_slider', 'option');
$page_slider = get_field('page_slider');
$slider = $page_slider ? $page_slider : $main_slider;

?>

<main class="main-container" aria-label="Haupt Inhaltsbereich"> 
	<?php if ($slider && !empty($slider)): ?>
	<section id="start-header" class="header-img-wrap" role="banner">
			<?php get_template_part('components/top-slider'); ?>
			<div class="header-img-heading">
				<?php

					$slider_row1 = get_field('slider_row1', 'option');
					$slider_row2 = get_field('slider_row2', 'option');

			?>
			<?php if($slider_row1 || $slider_row2 ) : ?>
				<h1><?php echo $slider_row1; ?> <span><?php echo $slider_row2; ?></span></h1>
			<?php endif; ?>
		</div>
	</section>
	<?php else : ?>
		<section id="start" class="pt-2" role="banner">
			<div class="inner-max-width ptb-5">
				<?= get_the_title(); ?>
				<h1>
					<?= get_bloginfo('name')?>
				</h1>
			</div>
		</section>
	<?php endif; ?>

	<div id="front-page">
		<section id="grusswort" class="content-container">
			<?php include get_template_directory() . '/blocks/front-page/grusswort.php'; ?>
		</section>
		<?php
		$einwohner = get_field('einwohner', 'option');
		$plz = get_field('plz', 'option');
		$hohe = get_field('hohe', 'option');
		$flache = get_field('flache', 'option');
		if ($einwohner || $plz || $hohe || $flache) : ?>
		<section id="daten-fakten" class="content-container">
			<h2 class="section-heading inner-max-width">Daten & Fakten</h2>
			<?php include get_template_directory() . '/blocks/front-page/daten.php'; ?>
		</section>
		<?php endif; ?>

		<?php 
		$communityName = get_field('gemeindename', 'option');
		$today = date('Ymd');
		$termine_query = new WP_Query([
			'post_type' => 'termin',
			'posts_per_page' => 1,
			'meta_query' => [
				[
					'key' => 'startdatum',
					'compare' => '>=',
					'value' => $today,
					'type' => 'DATE'
				],
			],
		]);
		if(wp_count_posts('post')->publish > 0 || $termine_query->have_posts()) :
		?>
		<section>
			<h2 class="section-heading inner-max-width">Aktuelles & Termine in <?= $communityName ?></h2>
			<?php include get_template_directory() . '/blocks/front-page/aktuelles.php'; ?>
		</section>
		<?php
			endif;
			wp_reset_postdata();
		?>

		<?php
		$linkes_bild = get_field('linkes_bild', 'option');
		$rechtes_bild = get_field('rechtes_bild', 'option');
		
		if($linkes_bild && $rechtes_bild): ?>
		<section id="galerie " class="content-container">
			<div class="gallery-img-wrap">
				<img src="<?php echo esc_url($linkes_bild['url']); ?>" alt="Bild 1">
				<img src="<?php echo esc_url($rechtes_bild['url']); ?>" alt="Bild 1">
			</div>
		</section>
		<?php endif; ?>
		<?php if (wp_count_posts('verein')->publish > 0) : ?>
		<section id="vereine" class="content-container">
			<h2 class="section-heading inner-max-width">Vereine & Institutionen</h2>
			<?php include get_template_directory() . '/blocks/vereine/verein.php'; ?>
		</section>
		<?php endif; ?>
		<?php if (wp_count_posts('infrastruktur')->publish > 0) : ?>
		<section id="infrastruktur" class="content-container">
			<h2 class="section-heading inner-max-width">Infrastruktur</h2>
			<div class="inner-max-width">
				<?php include get_template_directory() . '/blocks/infrastructure/infrastructure.php'; ?>
			</div>
		</section>
		<?php endif; ?>
		<?php 
			$history = get_field('geschichte_der_gemeinde', 'option');
			$sidebar_history = get_field('sidebar_geschichte', 'option');
		?>
		<?php if($history || $sidebar_history) :  ?>
		<section id="geschichte" class="content-container">
			<h2 class="section-heading inner-max-width">Geschichte</h2>
			<?php include get_template_directory() . '/blocks/front-page/geschichte.php'; ?>
		</section>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>