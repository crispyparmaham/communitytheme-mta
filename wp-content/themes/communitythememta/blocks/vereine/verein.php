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
	<div class="accordion inner-max-width" id="vereineAccordion" role="region" aria-labelledby="vereineHeading">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			
			$post_id = get_the_ID();

			// ACF-Felder abrufen
			$association_img = get_field( 'association_img', $post_id );
			$desc = get_field( 'desc', $post_id );
			$contact = get_field( 'contact',$post_id );
			$website = get_field( 'website' , $post_id);
			$email = get_field( 'e-mail' , $post_id);

			// Einzigartige IDs für Akkordeon
			$heading_id = 'heading-' . $post_id;
			$collapse_id = 'collapse-' . $post_id;
			?>
			<div class="accordion-item">
				<div class="accordion-header" id="<?php echo esc_attr( $heading_id ); ?>">
					<button class="accordion-button" type="button" aria-expanded="false"
						aria-controls="<?php echo esc_attr( $collapse_id ); ?>" tabindex="0">
						<h4><?php the_title(); ?></h4>
						<span class="accordion-icon">+</span>
					</button>
				</div>
				<div id="<?php echo esc_attr( $collapse_id ); ?>" class="accordion-collapse">
					<div class="accordion-body">
						<?php if ( $association_img || $desc ) : ?>
						<div class="image-description-container">
							<?php if ( $association_img ) : ?>
								<div class="image-wrap">
									<img src="<?php echo esc_url( $association_img['url'] ); ?>"
										alt="<?php echo esc_attr( $association_img['alt'] ); ?>" class="img-fluid" />
								</div>
							<?php endif; ?>

							<?php if ( $desc ) : ?>
								<div class="description">
									<p><?php echo wp_kses_post( $desc ); ?></p>
								</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>

						<?php if ( $contact ) : ?>
							<div class="contact-wrap">
								<div class="inner"><?php echo wp_kses_post( $contact ); ?></div>
								<div class="social-link-wrap">
									<?php if ( $website ) : ?>
										<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer"
											class="social-link">
											<img src="/wp-content/themes/communitythememta/assets/images/icons/web-icon.svg"
												alt="Web-Icon">
											<p>Webseite</p>
										</a>
									<?php endif; ?>

									<?php if ( $email ) : ?>
										<a href="mailto:<?php echo esc_attr( $email ); ?>" class="social-link">
											<img src="/wp-content/themes/communitythememta/assets/images/icons/mail-icon.svg"
												alt="E-Mail-Icon">
											<p>E-Mail</p>
										</a>
									<?php endif; ?>
								</div>
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