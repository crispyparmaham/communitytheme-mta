<?php
$termine_query = new WP_Query([
    'post_type' => 'termin',
    'posts_per_page' => 1
]);

if ($termine_query->have_posts()): ?>
    <div id="termine" class="sidebar-block" role="list">
        <h4 class="sidebar-heading">Termine</h4>
        <?php get_template_part('components/termin-listing-simple'); ?>
    </div>
<?php 
endif;
wp_reset_postdata();
?>