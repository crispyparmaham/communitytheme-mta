<?php
get_header();
?>

<main class="content-container">
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
			<h1><?php the_title(); ?></h1>
		</div>
	</section>


	<!-- Main Content: Weitere Informationen und Gutenberg-Content -->
	<div class="main-content">
		<article class="left-content-column termin-single-container">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();

					// Termin-Daten aus den Custom Fields abrufen
					$strasse = get_field( 'strasse' );
					$hausnummer = get_field( 'hausnummer' );
					$plz = get_field( 'plz' );
					$ort = get_field( 'ort' );
					$adresse = trim( "$strasse $hausnummer, $plz $ort" ) ?: false;

					$startdatum = get_field( 'startdatum' );
					$enddatum = get_field( 'enddatum' );
					$time = trim( get_field( 'uhrzeit' ) );
					$more_days = get_field( 'more_days' );
					$startdatum_formatted = $startdatum ? date_i18n( "d. F Y", strtotime( $startdatum ) ) : '';
					$enddatum_formatted = $enddatum ? date_i18n( "d. F Y", strtotime( $enddatum ) ) : '';
					?>

					<!-- Termin-Inhalt -->
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'termin-detail' ); ?>>
						<div class="termin-info-wrap">

							<!-- Adresse -->
							<?php if ( $adresse ) : ?>
								<p class="termin-adresse">
									<strong>Adresse:</strong> <?php echo esc_html( $adresse ); ?>
								</p>
							<?php endif; ?>

							<!-- Datum und Zeit -->
							<p class="termin-datum">
								<strong>Datum:</strong>
								<?php echo $more_days ? "Vom $startdatum_formatted bis $enddatum_formatted" : $startdatum_formatted; ?>
							</p>
							<?php if ( ! $more_days ) : ?>
								<p class="termin-zeit">
									<strong>Uhrzeit:</strong> <?php echo esc_html( $time ); ?> Uhr
								</p>
							<?php endif; ?>
						</div>

						<!-- Termin-Beschreibung (Gutenberg Content) -->
						<div class="termin-content">
							<?php the_content(); ?>
						</div>
					</article>

				<?php endwhile;
			else :
				echo '<p>Leider konnte dieser Termin nicht gefunden werden.</p>';
			endif;
			?>
		</article>

		<aside class="right-content-column">
			<div class="scroll-container">
				<?php
				if ( have_posts() ) :
					while ( have_posts() ) :
						the_post();

						// Termin-Daten aus den Custom Fields abrufen
						$name = get_field( 'vor-_nachname' );
						$phone = get_field( 'telefonnummer' );
						$mail = get_field( 'mail' );

						?>

						<!-- Termin-Kontakt -->
						<article class="termin-cta-wrap">
                            <h3>Ansprechpartner</h3>
                            <span><?php echo $name; ?></span>
                            <span class="termin-cta-link">Tel.: <a href="tel:<?php echo $phone; ?>"><?php echo $phone; ?></a></span>
                            <span class="termin-cta-link">E-Mail: <a href="mailto:<?php echo $mail; ?>"><?php echo $mail; ?></a></span>
						</article>

					<?php endwhile;
				else :
					echo '<p>Leider konnte dieser Termin nicht gefunden werden.</p>';
				endif;
				?>
			</div>
		</aside>
	</div>
</main>

<?php
get_footer();
?>