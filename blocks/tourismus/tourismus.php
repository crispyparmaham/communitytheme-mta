<?php
// WP Query für den CPT "tourismus"
$args = array(
    'post_type' => 'tourismus',
    'posts_per_page' => -1,
    'post_status' => 'publish',
);

$query = new WP_Query( $args );

if ( $query->have_posts() ) :
    // Globalen Post setzen
    global $post;
    ?>
    <div class="accordion inner-max-width" id="tourismusAccordion" role="region" aria-labelledby="tourismusHeading">
        <?php
        while ( $query->have_posts() ) :
            $query->the_post();

            // ACF-Felder abrufen mit globalem $post
            $association_img = get_field( 'association_img', $post->ID );
            $desc = get_field('desc', $post->ID);
            $contact = get_field( 'contact', $post->ID );
            $website = get_field( 'website', $post->ID );
            $email = get_field( 'e-mail', $post->ID );

            // Einzigartige IDs für Akkordeon
            $post_id = get_the_ID();
            $heading_id = 'heading-' . $post_id;
            $collapse_id = 'collapse-' . $post_id;
            ?>
            <div class="accordion-item"  id="tourismus-<?= $post_id ?>">
                <div class="accordion-header" id="<?php echo esc_attr( $heading_id ); ?>">
                    <button class="accordion-button" type="button" aria-expanded="false"
                        aria-controls="<?php echo esc_attr( $collapse_id ); ?>" tabindex="0">
                        <h4><?php the_title(); ?></h4>
                        <span class="accordion-icon">+</span>
                    </button>
                </div>
                <div id="<?php echo esc_attr( $collapse_id ); ?>" class="accordion-collapse">
                    <div class="accordion-body">
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

                        <?php if ( $contact ) : ?>
							<div class="contact-wrap">
								<div class="inner"><?php echo wp_kses_post( $contact ); ?></div>
								<div class="social-link-wrap">
									<?php if ( $website ) : ?>
										<a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer"
											class="social-link link-icon-before cticon-web link-icon icon-accent">
											Webseite
										</a>
									<?php endif; ?>

									<?php if ( $email ) : ?>
										<a href="mailto:<?php echo esc_attr( antispambot($email) ); ?>" class="social-link link-icon-before cticon-email link-icon icon-accent">
											E-Mail
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
    echo '<p>Keine Tourismus-Beiträge gefunden.</p>';
endif;

wp_reset_postdata();
?>
