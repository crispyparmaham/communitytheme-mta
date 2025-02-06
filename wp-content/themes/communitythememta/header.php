<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php bloginfo('name'); ?><?php wp_title('|', true, 'left'); ?></title>
	<meta name="description" content="<?php bloginfo('description'); ?>">
	<meta property="og:title" content="<?php wp_title(); ?>">
	<meta property="og:description" content="<?php bloginfo('description'); ?>">
	<meta property="og:url" content="<?php echo esc_url(home_url()); ?>">
	<meta property="og:type" content="website">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<header class="main-header">
		<div class="header-inner-wrap">
			<?php
			$logo = get_field('logo', 'option');
			$logo_size = get_field('logo_size', 'option');
			$home_url = esc_url(home_url('/'));
			$gemeindeName = get_field('gemeindename', 'option');
			?>
			<!-- Hamburger Button für mobile Ansicht -->
			<button class="hamburger-menu" aria-label="Menü anzeigen" aria-expanded="false">
				<span class="hamburger-icon"></span>
				<span class="hamburger-icon"></span>
				<span class="hamburger-icon"></span>
			</button>

			<div class="menu-wrap col-sm-12 col-xs-12">
				<?php
				$selected_menu_id = get_field('selected_menu', 'option');

				if ($selected_menu_id):
					?>
					<nav class="head-menu" aria-label="Sidenavigation">
						<ul class="menu-items">
							<li class="menu-item">
								<a href="<?php echo esc_url(home_url('/kontakt/')); ?>" class="menu-link">Kontakt</a>
							</li>
							<li class="menu-item">
								<a href="<?php echo esc_url(home_url('/impressum/')); ?>"
									class="menu-link">Impressum</a>
							</li>
							<li class="menu-item">
								<a href="<?php echo esc_url(home_url('/datenschutz/')); ?>"
									class="menu-link">Datenschutz</a>
							</li>
						</ul>
					</nav>


					<nav class="main-menu main-navigation" aria-label="Hauptnavigation">
						<div class="main-menu-inner-wrap">
							<span class="header-greeting">Willkommen in der Gemeinde <span>
									<?php echo $gemeindeName; ?></span></span>
							<?php
							wp_nav_menu(array(
								'menu' => $selected_menu_id,
								'container' => false,
								'menu_class' => 'menu-items',
								'aria-label' => __('Hauptnavigation', 'communitytheme'),
							));
							?>
						</div>
						<!-- Suche hinzufügen -->
						<div class="search-container">
							<form role="search" method="get" class="search-form"
								action="<?php echo esc_url(home_url('/')); ?>">
								<label for="search"
									class="screen-reader-text"><?php echo _x('Suche nach:', 'label', 'textdomain'); ?></label>
								<input type="search" id="search" class="search-field" placeholder="Suchbegriff eingeben..."
									value="" name="s" />
								<button type="submit" class="search-submit">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
										<path
											d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z" />
									</svg>
									<span
										class="screen-reader-text"><?php echo _x('Suchen', 'submit button', 'textdomain'); ?></span>
								</button>
							</form>
						</div>
					</nav>

				<?php else: ?>
					<nav class="fallback-navigation" aria-label="Navigation fehlt">
						<p><?php echo __('Kein Menü ausgewählt.', 'communitytheme'); ?></p>
					</nav>
				<?php endif; ?>
			</div>
			<?php
			if ($logo):
				$logo_class = '';

				switch ($logo_size) {
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
				<div class="logo-wrap col-xs-4 col-sm-2 <?php echo esc_attr($logo_class); ?>">


					<a href="<?php echo $home_url; ?>">
						<img src="<?php echo esc_url($logo['url']); ?>"
							alt="<?php echo esc_attr($logo['alt'] ?: __('Logo von', 'communitytheme') . ' ' . get_bloginfo('name')); ?>"
							loading="lazy">
					</a>

				<?php else: ?>
					<div style="display: none;">
						<?php if (is_front_page() && is_home()): ?>
							<h1><a href="<?php echo $home_url; ?>">
									<?php bloginfo('name'); ?>
								</a></h1>
						<?php else: ?>
							<p><a href="<?php echo $home_url; ?>">
									<?php bloginfo('name'); ?>
								</a></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>


		<div class="shape-divider">
			<div class="inner-shape-divider">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none">
					<path fill="currentColor" fill-opacity="1"
						d="M0,128L80,122.7C160,117,320,107,480,122.7C640,139,800,181,960,170.7C1120,160,1280,96,1360,64L1440,32L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z">
					</path>
				</svg>
			</div>
		</div>

	</header>

	<?php wp_footer(); ?>
</body>

</html>