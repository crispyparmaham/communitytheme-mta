<aside>
    <?php get_template_part('components/termin-listing-simple-in-sidebar'); ?>

    
    <?php if (is_active_sidebar('main-sidebar') && wp_get_sidebars_widgets()['main-sidebar']): ?>
        <div class="sidebar-block">
            <?php dynamic_sidebar('main-sidebar'); ?>
        </div>
        <?php
    endif;
    ?>

</aside>