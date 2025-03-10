<?php 

function dashboard_analytics() {
    ?>
    <div class="dashboard-box">
    <?php 
    global $wpdb;

    // Handle reset button click
    if (isset($_POST['reset_analytics']) && check_admin_referer('reset_analytics_nonce')) {
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_page_views'");
        echo '<div class="updated" style="margin: 0; margin-bottom: 30px;"><p>Statistiken wurden zurückgesetzt.</p></div>';
    }

    // Fetch pages ordered by view count
    $query = "
        SELECT post_id, meta_value AS views 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = '_page_views' 
        AND meta_value != '' 
        ORDER BY CAST(meta_value AS UNSIGNED) DESC 
        LIMIT 10
    ";
    $results = $wpdb->get_results($query);

    ?>
    
        <h2>Analytics</h2>
        <table class="wp-list-table widefat fixed striped">
            <tr>
                <th>Seite</th>
                <th>Besucher:innen bisher</th>
            </tr>
            <?php 
            if ($results) {
                foreach ($results as $row) {
                    $post_title = get_the_title($row->post_id);
                    $post_url = get_permalink($row->post_id);
                    $views = intval($row->views);
                    ?>
                    <tr>
                        <td><a href="<?php echo esc_url($post_url); ?>" target="_blank"><?php echo esc_html($post_title); ?></a></td>
                        <td><?php echo number_format($views); ?></td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="2">Keine Daten verfügbar.</td></tr>';
            }
            ?>
        </table>
        <small style="display: block; margin-top: 20px">
            Diese Statistiken tracken die Nutzer komplett anonym. Es werden keine personenbezogenen Daten gespeichert.
            Jeder Besuch auf jeder Seite wird jedes Mal gezählt. 
        </small>

        <!-- Reset Analytics Button -->
        <form method="post" style="margin-top: 20px;">
            <?php wp_nonce_field('reset_analytics_nonce'); ?>
            <input type="submit" name="reset_analytics" class="button button-danger" value="Analytics zurücksetzen">
        </form>
        <small>
            Dieser Vorgang kann <strong>NICHT</strong> rückgängig gemacht werden.
        </small>
    </div>
    <?php 
}
?>
