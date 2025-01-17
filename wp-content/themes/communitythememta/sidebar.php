<aside class="sidebar">
    <?php
        if ( is_active_sidebar( 'main-sidebar' ) ) :
            dynamic_sidebar( 'main-sidebar' );
        else :
            echo '<p>Keine Widgets hinzugefügt!</p>';
        endif;
    ?>
</aside>
