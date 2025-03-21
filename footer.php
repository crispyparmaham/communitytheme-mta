<?php 
    $privacyPolicyPage = get_privacy_policy_url();
?>


<?php 
	get_template_part('components/accessibility-popup');
?>

<footer class="main-footer" role="contentinfo">
	<div class="footer-inner-wrap">
		<div class="footer-top">
			<?php
			// Logo von der Optionsseite abrufen
			$logo = get_field( 'logo_footer', 'option' );
			$logo_size = get_field( 'logo_size_footer', 'option' );

			if ( $logo ) :
				$logo_class = '';
				switch ( $logo_size ) {
					case 'small':
						$logo_class = 'logo-small';
						break;
					case 'medium':
						$logo_class = 'logo-medium';
						break;
					case 'large':
						$logo_class = 'logo-large';
						break;
				}
				?>
				<div class="main-logo <?php echo esc_attr( $logo_class ); ?>">
					<a href="/" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<img src="<?php echo esc_url( $logo['url'] ); ?>"
							alt="<?php echo esc_attr( $logo['alt'] ?: __( 'Footer-Logo von', 'communitytheme' ) . ' ' . get_bloginfo( 'name' ) ); ?>"
							loading="lazy">
					</a>
				</div>
			<?php else : ?>
				<h1>
					<a href="<?php echo esc_url( home_url() ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<?php bloginfo( 'name' ); ?>
					</a>
				</h1>
			<?php endif; ?>

			<div class="footer-top-inner">
				<div class="footer-contact-info">
					<?php $contact_info = get_field( 'contact', 'option' ); ?>
					<?php if ( $contact_info ) : ?>
					<h4><?php _e( 'Kontakt', 'communitytheme' ); ?></h4>
						<address><?php echo wp_kses_post( $contact_info ); ?></address>
					<?php endif; ?>
				</div>

				<div class="footer-navigation">
					<?php
					$selected_menu_id = get_field( 'selected_menu', 'option' );
					if ( $selected_menu_id ) :
						wp_nav_menu( array(
							'menu' => $selected_menu_id,
							'container' => 'nav',
							'container_class' => 'main-navigation',
							'aria-label' => __( 'Footer-Navigation', 'communitytheme' ),
							'role' => 'navigation', // Role hinzugefügt
							'fallback_cb' => false // Verhindert die Anzeige von Standardmenüs
						) );
					endif;
					?>
				</div>
			</div>
		</div>

		<div class="footer-bottom">
			<div class="footer-copyright">
				<span>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?></span> | <span style="display: inline-block !important; opacity: 1 !important; visibility: visible !important;">made by <a style="display: inline-block !important; opacity: 1 !important; visibility: visible !important;" href="https://morethanads.de" target="_blank">more than ads</a></span>
			</div>
			<nav class="footer-policy" aria-label="Rechtliche Hinweise">
				<ul>
					<li><a href="/impressum" rel="nofollow" aria-label="<?php _e( 'Impressum', 'communitytheme' ); ?>"><?php _e( 'Impressum', 'communitytheme' ); ?></a></li>
					<li><a href="<?= esc_url($privacyPolicyPage) ?>" rel="nofollow" aria-label="<?php _e( 'Datenschutz', 'communitytheme' ); ?>"><?php _e( 'Datenschutz', 'communitytheme' ); ?></a></li>
				</ul>
			</nav>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
