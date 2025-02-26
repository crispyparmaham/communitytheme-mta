<?php
$today = date('Ymd');
$termine_query = new WP_Query([
    'post_type' => 'termin',
    'posts_per_page' => 1,
    'meta_query' => [
        [
            'key' => 'startdatum',
            'compare' => '>=',
            'value' => $today,
            'type' => 'DATE'
        ],
    ],
]);

if ($termine_query->have_posts()): ?>
    <div id="termine" class="sidebar-block" role="list">
        <h4 class="sidebar-heading">Anstehende Termine</h4>
        <?php get_template_part('components/termin-listing-simple'); ?>
    </div>
<?php 
endif;
wp_reset_postdata();
?>