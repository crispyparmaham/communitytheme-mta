<?php 

function custom_dashboard_page() {
    add_menu_page(
        'Mein Dashboard',       // Page title
        'Dashboard',            // Menu title
        'edit_posts',                 // Capability required
        'custom-dashboard',     // Slug
        'custom_dashboard_content', // Callback function to display content
        'dashicons-dashboard',  // Icon
        2                       // Position (after Dashboard)
    );
}
add_action('admin_menu', 'custom_dashboard_page');

function custom_dashboard_content() {
    echo '<div class="wrap">';
    echo '<h1>MTA Community Dashboard</h1>';
    echo '<p>Hier werden wir Einstellungsmöglichkeiten hinzufügen.</p>';
    echo '</div>';
}


function remove_default_dashboard_menu() {
    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    if (array_intersect($restricted_roles, $current_user->roles)) {
        remove_menu_page('index.php'); // Removes the default Dashboard menu
    }
}
add_action('admin_menu', 'remove_default_dashboard_menu');
