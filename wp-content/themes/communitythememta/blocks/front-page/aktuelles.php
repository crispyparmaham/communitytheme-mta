<div class="inner-max-width">

    <div class="aktuelles-termine-wrapper">

        <div class="aktuelles">
            <?php get_template_part('blocks/posts-recent/posts-recent'); ?>
            <?php if(wp_count_posts('post')->publish > 3) : ?>
            <a href="/aktuelles/" title="Zu allen Aktuelles Beiträgen und Terminen">Alle Beiträge ansehen</a>
            <?php endif; ?>
        </div>
        <div class="termine">
            <?php get_template_part('components/termin-listing-simple-in-sidebar'); ?>
        </div>

    </div>
</div>