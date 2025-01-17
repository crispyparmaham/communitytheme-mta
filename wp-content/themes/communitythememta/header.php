<!DOCTYPE html>
<html lang="de">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php bloginfo('name'); ?></title>
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

        if ($logo) :
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
            <div class="main-logo <?php echo esc_attr($logo_class); ?>">
                <a href="/">
                    <img src="<?php echo esc_url($logo['url']); ?>" 
                         alt="<?php echo esc_attr($logo['alt'] ?: __('Logo von', 'communitytheme') . ' ' . get_bloginfo('name')); ?>">
                </a>
            </div>
        <?php else : ?>
            <?php if (is_front_page() && is_home()) : ?>
                <h1><a href="<?php echo esc_url(home_url()); ?>">
                    <?php bloginfo('name'); ?>
                </a></h1>
            <?php else : ?>
                <p><a href="<?php echo esc_url(home_url()); ?>">
                    <?php bloginfo('name'); ?>
                </a></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php
        $selected_menu_id = get_field('selected_menu', 'option'); 

        if ($selected_menu_id) :
            wp_nav_menu(array(
                'menu' => $selected_menu_id,
                'container' => 'nav',
                'container_class' => 'main-navigation',
                'container_id' => 'main-navigation',
                'aria-label' => __('Hauptnavigation', 'communitytheme'),
            ));
        else :
            echo '<nav aria-label="' . __('Navigation fehlt', 'communitytheme') . '">';
            echo '<p>' . __('Kein Menü ausgewählt.', 'communitytheme') . '</p>';
            echo '</nav>';
        endif;
        ?>
    </div>
</header>
<?php wp_footer(); ?>
</body>
</html>
