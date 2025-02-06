<aside>
    <?php if (is_active_sidebar('main-sidebar') && wp_get_sidebars_widgets()['main-sidebar']): ?>
        <div class="sidebar-block">
            <?php dynamic_sidebar('main-sidebar'); ?>
        </div>
        <?php
    endif;
    ?>

    <?php get_template_part('components/termin-lsiting-simple-in-sidebar'); ?>
</aside>