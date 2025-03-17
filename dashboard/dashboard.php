<?php 
require_once(get_template_directory() . '/dashboard/components/dashboard-quick-links.php');
require_once(get_template_directory() . '/dashboard/components/dashboard-management.php');
require_once(get_template_directory() . '/dashboard/components/dashboard-analytics.php');


add_action('admin_enqueue_scripts', 'enqueue_dashboard_styles');
function enqueue_dashboard_styles() {
    wp_enqueue_style('mta-dashboard', get_template_directory_uri() . '/dashboard/assets/dashboard.css', [], THEME_VERSION);
    wp_enqueue_style('mta-dashboard-fonts', get_template_directory_uri() . '/dashboard/assets/dashboard-fonts.css', [], THEME_VERSION);
}

function dashboard_page() {
    ?>
    <div class="mta-dashboard">
        <?php dashboard_header(); ?>
        <?php dashboard_content(); ?>
        <?php dashboard_footer(); ?>
    </div>
    <?php 
}

function dashboard_header() {
    $logged_in_user = wp_get_current_user();
    $logged_in_user_name = $logged_in_user->display_name;
    $logged_in_user_firstname = $logged_in_user->first_name;
    $showname = $logged_in_user_firstname ?: $logged_in_user_name;
    $theme_version = wp_get_theme()->get('Version');
    ?>
    <div class="mta-dashboard-header">
        <div>
            <h1>Willkommen zurück, <?= $showname  ?>!</h1>
            <p>Theme-Version: <?= $theme_version ?></p>
        </div>
    </div>
    <?php 
}

function dashboard_footer() {
    ?>
    <div class="mta-dashboard-footer">
        <p><a href="https://morethanads.de" target="_blank">more than ads</a> – Community Theme</p>
    </div>
    <?php 
}

function dashboard_content() {
    ?>
    <div class="mta-dashboard-content">
        <?php dashboard_quick_links(); ?>
        <div class="dashboard-box-wrapper dashboard-box-wrapper-50-50">
            <?php dashboard_management(); ?>
            <?php dashboard_analytics(); ?>
        </div>
    </div>
    <?php 
}