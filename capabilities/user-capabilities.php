<?php

// Hide unwanted roles from dropdown
function filter_editable_roles($roles) {
    $allowed_roles = ['community_admin', 'community_editor']; // Only allow these roles

    foreach ($roles as $role => $details) {
        if (!in_array($role, $allowed_roles)) {
            unset($roles[$role]); // Remove unwanted roles from dropdown
        }
    }
    
    return $roles;
}
add_filter('editable_roles', 'filter_editable_roles');

// Create custom roles
function create_community_roles() {
    $editor_caps = get_role('editor')->capabilities;

    // Remove delete capabilities
    unset($editor_caps['delete_pages']);
    unset($editor_caps['delete_published_pages']);
    unset($editor_caps['delete_others_pages']);
    unset($editor_caps['create_pages']);
    

    // Define extra capabilities for Community Admin
    $community_admin_caps = array_merge($editor_caps, [
        'list_users'   => true,  // View user list
        'create_users' => true,  // Create new users (but NOT admins)
        'edit_users'   => true,  // Edit users (but NOT admins)
        'delete_users' => true,  // Delete users (but NOT admins)
        'promote_users' => true, // Promote users (but NOT to admin)
        'edit_theme_options' => true,
    ]);

    $community_editor_caps = array_merge($editor_caps, [
        'edit_theme_options' => true, // ✅ Allow access to Widgets menu
    ]);

    // Remove old roles to ensure changes apply
    remove_role('community_admin');
    remove_role('community_editor');

    // Create Community Admin role
    add_role('community_admin', 'Gemeinde Admin', $community_admin_caps);

    // Create Community Editor role (inherits Editor capabilities, no user management)
    add_role('community_editor', 'Gemeinde Redakteur', $community_editor_caps);
}
add_action('init', 'create_community_roles');


function remove_unwanted_appearance_menus() {
    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    if (array_intersect($restricted_roles, $current_user->roles)) {
        // Remove everything under "Appearance" except "Widgets"
        remove_submenu_page('themes.php', 'themes.php');         // Remove Themes
        remove_submenu_page('themes.php', 'customize.php');     
        remove_submenu_page('themes.php', 'nav-menus.php');      // Remove Menus
        remove_submenu_page('themes.php', 'theme-editor.php');   // Remove Theme Editor
        remove_submenu_page('themes.php', 'site-editor.php');   

        remove_menu_page('customize.php'); // Customizer
        remove_menu_page('site-editor.php'); // Full Site Editor (FSE)
    }
}
add_action('admin_menu', 'remove_unwanted_appearance_menus', 999);


function block_customizer_and_site_editor() {
    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    if (array_intersect($restricted_roles, $current_user->roles)) {
        global $pagenow;

        if ($pagenow === 'customize.php' || $pagenow === 'site-editor.php') {
            wp_redirect(admin_url('admin.php?page=theme-settings-colors'));
            exit;
        }
    }
}
add_action('admin_init', 'block_customizer_and_site_editor');

/**
 * Hide Customizer & Site Editor from Admin Bar
 */
function hide_customizer_from_admin_bar($wp_admin_bar) {
    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    if (array_intersect($restricted_roles, $current_user->roles)) {
        $wp_admin_bar->remove_node('customize');
        $wp_admin_bar->remove_node('site-editor');
    }
}
add_action('admin_bar_menu', 'hide_customizer_from_admin_bar', 999);


// Restrict Community Admin from managing Admin users
function hide_admins_from_community_admin($query) {
    $current_user = wp_get_current_user();
    
    if (in_array('community_admin', $current_user->roles)) {
        global $wpdb;
        $query->query_where .= " AND {$wpdb->users}.ID NOT IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$wpdb->prefix}capabilities' AND meta_value LIKE '%administrator%')";
    }
}
add_action('pre_user_query', 'hide_admins_from_community_admin');


function remove_menus_for_community_roles() {
    if (!is_admin()) {
        return;
    }

    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    // Check if the user is in one of the restricted roles
    if (array_intersect($restricted_roles, $current_user->roles)) {
        remove_menu_page('edit-comments.php'); // Remove "Comments"
        remove_menu_page('tools.php'); // Remove "Tools"
        remove_menu_page('wpseo_workouts'); // Remove Yoast SEO Workouts
    }
}
add_action('admin_menu', 'remove_menus_for_community_roles', 999);


function remove_yoast_admin_bar_menu($wp_admin_bar) {
    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    if (array_intersect($restricted_roles, $current_user->roles)) {
        $wp_admin_bar->remove_node('wpseo-menu'); // Remove Yoast SEO from the admin bar
        $wp_admin_bar->remove_node('comments'); // Remove Yoast SEO from the admin bar
    }
}
add_action('admin_bar_menu', 'remove_yoast_admin_bar_menu', 999);



/* GUTENBERG EDITOR  */
function hide_yoast_seo_gutenberg_sidebar_css() {
    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    if (array_intersect($restricted_roles, $current_user->roles)) {
        echo '<style>
            button[aria-controls="yoast-seo:seo-sidebar"],
            .yoast-seo-sidebar-panel {
                display: none !important;
            }
        </style>';
    }
}
add_action('admin_head', 'hide_yoast_seo_gutenberg_sidebar_css');


function remove_add_new_page_button() {
    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    if (array_intersect($restricted_roles, $current_user->roles)) {
        echo '<style>
            #wp-admin-bar-new-page,  /* Remove from admin bar */
            .page-title-action,      /* Remove from Pages list */
            .menu-top [href="post-new.php?post_type=page"] { /* Remove from menu */
                display: none !important;
            }
        </style>';
    }
}
add_action('admin_head', 'remove_add_new_page_button');

function remove_dashboard_widgets() {
    global $wp_meta_boxes;

    unset($wp_meta_boxes['dashboard']['normal']['core']); // Remove normal widgets
    unset($wp_meta_boxes['dashboard']['side']['core']);   // Remove side widgets
    unset($wp_meta_boxes['dashboard']['wpseo']);   // Remove side widgets
}
add_action('wp_dashboard_setup', 'remove_dashboard_widgets');

function remove_yoast_columns($columns) {
    unset($columns['wpseo-score']);         // SEO Score
    unset($columns['wpseo-score-readability']);         // SEO Score
    unset($columns['wpseo-links']);
    unset($columns['wpseo-linked']);

    return $columns;
}
add_filter('manage_edit-page_columns', 'remove_yoast_columns');


function redirect_dashboard() {
    if (is_admin() && !defined('DOING_AJAX')) {
        $current_user = wp_get_current_user();
        $restricted_roles = ['community_admin', 'community_editor'];

        // Ensure the user has one of the restricted roles
        if (array_intersect($restricted_roles, $current_user->roles)) {
            global $pagenow;

            // Redirect if the user is on the default WordPress dashboard
            if ($pagenow === 'index.php') {
                wp_redirect(admin_url('admin.php?page=custom-dashboard'));
                exit;
            }
            if ($pagenow === 'tools.php' || $pagenow === 'edit-comments.php') {
                wp_redirect(admin_url('admin.php?page=custom-dashboard'));
                exit;
            }
        }
    }
}
add_action('admin_init', 'redirect_dashboard');


function redirect_frontpage_edit() {
    if (isset($_GET['post'], $_GET['action']) && $_GET['action'] === 'edit') {
        $post_id = intval($_GET['post']);
        $front_page_id = get_option('page_on_front');
        $current_user = wp_get_current_user();
        $restricted_roles = ['community_admin', 'community_editor'];
        if (array_intersect($restricted_roles, $current_user->roles)) {
            if ($post_id === (int) $front_page_id) {
                wp_redirect(admin_url('admin.php?page=theme-settings-frontpage'));
                exit;
            } 
        }
    }
}
add_action('load-post.php', 'redirect_frontpage_edit');



/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
// ===  REMOVE GUTENBERG BLOCKS FOR USER ROLES === //
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */
/* -------------------------------------------------------------- */

function restrict_gutenberg_blocks($allowed_blocks, $editor_context) {
    $current_user = wp_get_current_user();
    $restricted_roles = ['community_admin', 'community_editor'];

    // If the user has one of the restricted roles, modify allowed blocks
    if (array_intersect($restricted_roles, $current_user->roles)) {
        $allowed_blocks = [
            'core/paragraph',
            'core/heading',
            'core/list',
            'core/image',
            'core/gallery',
            'core/button',
            'core/table',
            'core/columns',
            'core/group',
            'core/buttons',
            // CUSTOM BLOCKS
            'acf/tourismus',
            'acf/vereinsliste',
            'acf/gewerbeliste',
            'acf/tourismus',
            'acf/impressum',
            'acf/privacy',
            'acf/infrastructure',
            'acf/beitragsliste',
            'acf/beitragsliste-short',
            'acf/terminliste',
            'acf/yt-video',
            'acf/vimeo-video',
            'acf/external-resources'
        ];
    }

    return $allowed_blocks;
}
add_filter('allowed_block_types_all', 'restrict_gutenberg_blocks', 10, 2);
