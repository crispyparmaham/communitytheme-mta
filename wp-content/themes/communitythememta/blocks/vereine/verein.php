<?php
// WP Query für den CPT "vereine"
$args = array(
	'post_type' => 'verein',
	'posts_per_page' => -1,
	'post_status' => 'publish',
);

$query = new WP_Query( $args );

if ( $query->have_posts() ) :
	?>
	<div class="accordion" id="vereineAccordion" role="region" aria-labelledby="vereineHeading">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();

			// ACF-Felder abrufen
			$association_img = get_field( 'association_img' );
			$contact = get_field( 'contact' );
			$website = get_field( 'website' );
			$email = get_field( 'e-mail' );

			// Einzigartige IDs für Akkordeon
			$post_id = get_the_ID();
			$heading_id = 'heading-' . $post_id;
			$collapse_id = 'collapse-' . $post_id;
			?>
			<div class="accordion-item">
				<h2 class="accordion-header" id="<?php echo esc_attr( $heading_id ); ?>">
					<button class="accordion-button" type="button" aria-expanded="false"
						aria-controls="<?php echo esc_attr( $collapse_id ); ?>">
						<?php the_title(); ?>
						<span class="accordion-icon">+</span> <!-- Das Plus-Symbol -->
					</button>
				</h2>
				<div id="<?php echo esc_attr( $collapse_id ); ?>" class="accordion-collapse">
					<div class="accordion-body">
						<?php if ( $association_img ) : ?>
							<img src="<?php echo esc_url( $association_img['url'] ); ?>"
								alt="<?php echo esc_attr( $association_img['alt'] ); ?>" class="img-fluid" />
						<?php endif; ?>

						<?php if ( $contact ) : ?>
							<div class="contact-wrap">
								<div class="inner"><?php echo wp_kses_post( $contact ); ?></div>
								<?php if ( $website ) : ?>
									<p>
										<strong>Webseite:</strong>
										<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo esc_html( $website ); ?>
										</a>
									</p>
								<?php endif; ?>

								<?php if ( $email ) : ?>
									<p>
										<strong>E-Mail:</strong>
										<a href="mailto:<?php echo esc_attr( $email ); ?>">
											<?php echo esc_html( $email ); ?>
										</a>
									</p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>

		<?php endwhile; ?>
	</div>
	<?php
else :
	echo '<p>Keine Vereine gefunden.</p>';
endif;

wp_reset_postdata();
?>