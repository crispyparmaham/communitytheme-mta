<?php if (is_active_sidebar('main-sidebar') && wp_get_sidebars_widgets()['main-sidebar']): ?>
    <aside class="sidebar-block">
        <?php dynamic_sidebar('main-sidebar'); ?>
    </aside>
    <?php
endif;
?>
<div id="termine" class="sidebar-block" role="list">
    <h4 class="sidebar-heading">Termine</h4>
    <?php get_template_part('components/termin-listing-simple'); ?>
</div>