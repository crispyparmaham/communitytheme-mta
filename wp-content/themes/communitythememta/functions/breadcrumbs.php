<?php
function custom_breadcrumbs() {
    // Home-Link hinzufügen
    echo '<div class="breadcrumbs">';
    echo '<a href="' . home_url() . '">Home</a> / ';
    
    if (is_single()) {
        // Wenn wir uns auf einem Einzelbeitrag befinden
        $post = get_post();
        
        // Eltern-Seite des Beitrags finden (falls vorhanden)
        $parent = get_post($post->post_parent);
        
        if ($parent) {
            // Link zur übergeordneten Seite
            echo '<a href="' . get_permalink($parent->ID) . '">' . $parent->post_title . '</a> / ';
        }
        
        // Titel des Beitrags hinzufügen
        echo the_title();
    } elseif (is_category()) {
        // Wenn wir uns auf einer Kategorieseite befinden
        echo single_cat_title('', false);
    } elseif (is_page()) {
        // Wenn es eine normale Seite ist
        echo the_title();
    }
    
    echo '</div>';
}
?>
