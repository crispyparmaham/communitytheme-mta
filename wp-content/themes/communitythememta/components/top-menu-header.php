<?php 
    $privacyPolicyPage = get_privacy_policy_url();
?>

<nav class="head-menu" aria-label="Sidenavigation">
    <ul class="menu-items">
        <!-- <li class="menu-item">
            <a href="<?php echo esc_url(home_url('/kontakt/')); ?>" class="menu-link">Kontakt</a>
        </li> -->
        <li class="menu-item">
            <a href="<?php echo esc_url(home_url('/impressum/')); ?>" class="menu-link">Impressum</a>
        </li>
        <li class="menu-item">
            <a href="<?php echo esc_url($privacyPolicyPage); ?>" class="menu-link">Datenschutz</a>
        </li>
    </ul>
</nav>