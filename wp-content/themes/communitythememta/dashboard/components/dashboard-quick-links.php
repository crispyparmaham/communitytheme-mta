<?php 

function dashboard_quick_link($link, $title, $icon, $description = "") {

    ?>
    <a class="mta-dashboard-quick-link" href="<?php echo $link; ?>">
            <div class="mta-dashboard-quick-link-icon">
                <i class="dashicons <?php echo $icon; ?>"></i>
            </div>
            <h2><?php echo $title; ?></h2>
            <?php if($description) : ?>
                <p><?php echo $description; ?></p>
            <?php endif; ?>
    </a>
    <?php 

}

function dashboard_quick_links() {
    $quick_links = array(
        array(
            'link' => admin_url('admin.php?page=theme-settings-frontpage'),
            'title' => 'Startseite',
            'icon' => 'dashicons-admin-home',
            'description' => 'Startseite bearbeiten'
        ),
        array(
            'link' => admin_url('edit.php?post_type=page'),
            'title' => 'Seiten',
            'icon' => 'dashicons-admin-page',
            'description' => 'Seiten bearbeiten'
        ),
        array(
            'link' => admin_url('admin.php?page=theme-settings-colors'),
            'title' => 'Farben & Einstellungen',
            'icon' => 'dashicons-color-picker',
            'description' => 'Theme Farben, Menü, Schriften, Logo etc.'
        ),
        array(
            'link' => admin_url('admin.php?page=theme-settings-content'),
            'title' => 'Allgemeine Daten',
            'icon' => 'dashicons-edit-page',
            'description' => 'Seitenübergreifende Elemente anpassen – Slider, Events ...'
        ),
        array(
            'link' => admin_url('widgets.php'),
            'title' => 'Widgets',
            'icon' => 'dashicons-welcome-widgets-menus',
            'description' => 'Sidebar bearbeiten'
        ),
    );

    ?>
    <div class="quick-link-wrapper">

        <?php 
    foreach($quick_links as $link) {
        dashboard_quick_link($link['link'], $link['title'], $link['icon'], $link['description']);
    }
    ?>
    </div>
    <?php 
}