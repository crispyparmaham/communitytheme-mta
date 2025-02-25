<?php
// WP Query für den CPT "gewerbe"
$args = array(
    'post_type' => 'infrastruktur',
    'posts_per_page' => -1,
    'post_status' => 'publish',
);

$query = new WP_Query( $args );

if ( $query->have_posts() ) :
    // Globalen Post setzen
    global $post;
    ?>
    <div class="infrastructure-listing">
        <?php
        while ( $query->have_posts() ) :
            $query->the_post();
            $post_id = get_the_ID();

            $title = get_the_title();
            // ACF-Felder abrufen mit globalem $post
            $image = get_field('image', $post->ID);
            $desc = get_field('description', $post->ID);
            ?>
            <div class="infrastructure-item">
                <?php if($image): ?>
                <div class="infrastructure-image-container">
                    <?php imageOutput($image['ID'], 'img-640', '640px'); ?>
                </div>
                <?php endif ?>
                <div class="infrastructure-content-container">
                    <h3><?= $title ?></h3>
                    <?= $desc ?>
                </div>
            </div>            
        <?php endwhile; ?>
    </div>
    <?php
else :

endif;
wp_reset_postdata();
?>
