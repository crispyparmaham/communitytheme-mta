<?php 


function dashboard_management() {

    ?>
    <div class="dashboard-box">
        <h2>Verwaltung</h2>
        <nav>
            <ul>
                <?php
                    $current_user = wp_get_current_user();
                    $restricted_roles = ['community_admin'];
                    if (array_intersect($restricted_roles, $current_user->roles)) :
                ?>
                <li>
                    <a href="<?php echo admin_url('users.php'); ?>">
                        <i class="dashicons dashicons-admin-users"></i>
                        <span>Benutzer verwalten</span>
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="<?php echo admin_url('edit.php?post_type=page'); ?>">
                        <i class="dashicons dashicons-admin-page"></i>
                        <span>Seiten verwalten</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo admin_url('edit.php?post_type=termin'); ?>">
                        <i class="dashicons dashicons-calendar"></i>
                        <span>Termine verwalten</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo admin_url('edit.php?post_type=post'); ?>">
                        <i class="dashicons dashicons-admin-post"></i>
                        <span>Aktuelles Beiträge verwalten</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo admin_url('edit.php?post_type=verein'); ?>">
                        <i class="dashicons dashicons-groups"></i>
                        <span>Vereine verwalten</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo admin_url('edit.php?post_type=infrastruktur'); ?>">
                        <i class="dashicons dashicons-admin-home"></i>
                        <span>Infrastruktur verwalten</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo admin_url('edit.php?post_type=tourismus'); ?>">
                        <i class="dashicons dashicons-location-alt"></i>
                        <span>Gastronomie, Ferienwohnungen etc. verwalten</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo admin_url('edit.php?post_type=gewerbe'); ?>">
                        <i class="dashicons dashicons-businessperson"></i>
                        <span>Gewerbe & Fimen verwalten</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php 

}