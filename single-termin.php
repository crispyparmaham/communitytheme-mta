<?php
get_header();
?>

<?php $headerImage = get_the_post_thumbnail_url(null, 'full'); ?>
<main class="main-container" aria-label="Haupt Inhaltsbereich">
	<!-- Termin-Header: Bild und Titel -->
	<section class="header-img-wrap header-img-wrap-termin">
		<?php if ($headerImage): ?>
			<img src="<?php echo esc_url($headerImage); ?>" alt="<?php the_title_attribute(); ?>" class="termin-header-image">
			<?php else: ?>
				<img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/mta-communitytheme-bg-thumbnail.jpg"
				alt="Standard-Hintergrundbild der MTA-Community">
				<?php endif; ?>
				
		<?php if ($headerImage): ?>
			<div class="overlay-image">
				<img src="<?php echo esc_url($headerImage); ?>" alt="<?php the_title_attribute(); ?>">
			</div>
		<?php endif; ?>


	</section>
	<section class="inner-max-width">
		<?php custom_breadcrumbs(); ?>
		<div class="page-start-text">
			<h1><?php the_title(); ?></h1>
			<?php
			$startText = get_field('einleitungstext');
			?>
			<?php if ($startText): ?>
				<?php echo $startText; ?>
			<?php endif; ?>
		</div>
	</section>
	<!-- Main Content: Weitere Informationen und Gutenberg-Content -->
	<div class="main-content">
		<article class="left-content-column termin-single-container">
			<?php
			if (have_posts()):
				while (have_posts()):
					the_post();

					// Termin-Daten aus den Custom Fields abrufen
					$strasse = get_field('strasse');
					$hausnummer = get_field('hausnummer');
					$plz = get_field('plz');
					$ort = get_field('ort');
					$adresseParts = array_filter([$strasse . ' ' . $hausnummer, $plz . ' ' . $ort], function ($value) {
						return trim($value) !== '';
					});
					$adresse = !empty($adresseParts) ? implode(', ', $adresseParts) : '';

					$startdatum = get_field('startdatum');
					$enddatum = get_field('enddatum');
					$time = trim(get_field('uhrzeit'));
					$more_days = get_field('more_days');
					$startdatum_formatted = $startdatum ? date_i18n("d. F Y", strtotime($startdatum)) : '';
					$enddatum_formatted = $enddatum ? date_i18n("d. F Y", strtotime($enddatum)) : '';

					$content = get_the_content();
					?>

					<!-- Termin-Inhalt -->

					<article id="post-<?php the_ID(); ?>" <?php post_class('termin-detail'); ?>>
						<div class="termin-info-wrap">

							<!-- Adresse -->
							<?php if ($adresse): ?>
								<p class="termin-adresse">
									<strong>Ort:</strong> <?php echo esc_html($adresse); ?>
								</p>
							<?php endif; ?>

							<!-- Datum und Zeit -->
							<p class="termin-datum">
								<strong>Datum:</strong>
								<?php echo $more_days ? "Vom $startdatum_formatted bis $enddatum_formatted" : $startdatum_formatted; ?>
							</p>
							<?php if (!$more_days): ?>
								<p class="termin-zeit">
									<strong>Uhrzeit:</strong> <?php echo esc_html($time); ?> Uhr
								</p>
							<?php endif; ?>
						</div>

						<!-- Termin-Beschreibung (Gutenberg Content) -->
						<?php if ($content): ?>
							<div class="termin-content">
								<?= $content; ?>
							</div>
						<?php endif; ?>
					</article>

				<?php endwhile;
			else:
				echo '<p>Leider konnte dieser Termin nicht gefunden werden.</p>';
			endif;
			?>
		</article>

		<aside class="right-content-column">
			<div class="scroll-container ">
				<?php
				// Termin-Daten aus den Custom Fields abrufen
				$name = get_field('vor-_nachname');
				$phone = get_field('telefonnummer');
				$mail = get_field('mail');

				?>

				<!-- Termin-Kontakt -->
				<?php if ($phone || $mail || $name): ?>
						<article class="termin-cta-wrap">
							<h3>Ansprechpartner</h3>
							<?php if ($name): ?>
								<span><?php echo $name; ?></span>
							<?php endif; ?>
							<?php if ($phone): ?>
								<span class="termin-cta-link">Tel.: <a
										href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></span>
							<?php endif; ?>
							<?php if ($mail): ?>
								<span class="termin-cta-link">E-Mail: <a
										href="mailto:<?php echo antispambot($mail); ?>"><?php echo antispambot($mail); ?></a></span>
							<?php endif; ?>
						</article>
					<?php endif; ?>
			</div>
		</aside>
	</div>
</main>

<?php
get_footer();
?>