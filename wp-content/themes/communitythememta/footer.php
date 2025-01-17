<?php wp_footer(); ?>

<footer class="main-footer">
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
					<a href="/">
						<img src="<?php echo esc_url( $logo['url'] ); ?>"
							alt="<?php echo esc_attr( $logo['alt'] ?: __( 'Footer-Logo von', 'communitytheme' ) . ' ' . get_bloginfo( 'name' ) ); ?>">
					</a>
				</div>
			<?php else : ?>
				<h1>
					<a href="<?php echo esc_url( home_url() ); ?>">
						<?php bloginfo( 'name' ); ?>
					</a>
				</h1>
			<?php endif; ?>

			<div class="footer-top-inner">
				<div class="footer-contact-info">
					<?php $contact_info = get_field( 'contact', 'option' ); ?>
					<h4><?php _e( 'Kontakt', 'communitytheme' ); ?></h4>
					<?php if ( $contact_info ) : ?>
						<address><?php echo wp_kses_post( $contact_info ); ?></address>
					<?php else : ?>
						<p><?php _e( 'Keine Kontaktinformationen verfügbar.', 'communitytheme' ); ?></p>
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
						) );
					else :
						echo '<p>' . __( 'Kein Menü ausgewählt.', 'communitytheme' ) . '</p>';
					endif;
					?>
				</div>
			</div>
		</div>

		<div class="footer-bottom">
			<div class="footer-copyright">
				<span>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?></span>
			</div>
			<nav class="footer-policy">
				<ul>
					<li><a href="/impressum" rel="nofollow"><?php _e( 'Impressum', 'communitytheme' ); ?></a></li>
					<li><a href="/datenschutz" rel="nofollow"><?php _e( 'Datenschutz', 'communitytheme' ); ?></a></li>
				</ul>
			</nav>
		</div>
	</div>
</footer>
</body>

</html>