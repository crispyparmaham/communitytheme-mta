<aside>
    <?php if (is_active_sidebar('main-sidebar') && wp_get_sidebars_widgets()['main-sidebar']): ?>
        <div class="sidebar-block">
            <?php dynamic_sidebar('main-sidebar'); ?>
        </div>
        <?php
    endif;
    ?>
    <div id="termine" class="sidebar-block" role="list">
        <h4 class="sidebar-heading">Termine</h4>
        <?php get_template_part('components/termin-listing-simple'); ?>
    </div>
</aside>