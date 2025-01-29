<?php get_header(); ?>

<main class="main-container">
    <section class="header-img-wrap" role="banner">
        <?php if ( has_post_thumbnail() ) : ?>
            <?php $headerImage = get_the_post_thumbnail_url( null, 'full' ); ?>
            <img src="<?php echo esc_url( $headerImage ); ?>" 
                alt="<?php echo esc_attr( get_the_title() ); ?>" 
                loading="lazy">
        <?php else : ?>
            <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/mta-communitytheme-bg-thumbnail.jpg" 
                alt="Standard-Hintergrundbild der MTA-Community" 
                loading="lazy">
        <?php endif; ?>
        <div class="header-img-heading">
            <h1><?php the_title(); ?></h1>
        </div>
    </section>

    <div class="main-content">
        <section id="vereine" class="content-container">
            <h2 class="section-heading">Vereine</h2>
            <?php include get_template_directory() . '/blocks/vereine/verein.php'; ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>
