<?php

function get_termin_data($postId)
{
    $gemeindeName = get_field('gemeindename', 'option');
    $strasse = get_field('strasse', $postId);
    $hausnummer = get_field('hausnummer', $postId);
    $plz = get_field('plz', $postId);
    $ort = get_field('ort', $postId);
    $adresseParts = array_filter([$strasse . ' ' . $hausnummer, $plz . ' ' . $ort], function ($value) {
        return trim($value) !== '';
    });
    $adresse = !empty($adresseParts) ? implode(', ', $adresseParts) : '';
    $adresslink = $adresse ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode("$strasse $hausnummer $plz $ort $gemeindeName") : '';

    // Termin-Daten
    $organizer = get_field('organisator', $postId);
    $startdatum = get_field('startdatum', $postId);
    $enddatum = get_field('enddatum', $postId);
    $time = get_field('uhrzeit', $postId) ? trim(get_field('uhrzeit', $postId)) : '';
    $more_days = get_field('more_days', $postId);
    $date_icon = get_field('date_icon', 'option') ?: 'dashicons-calendar-alt';
    $time_icon = get_field('time_icon', 'option') ?: 'dashicons-clock';

    $startdatum_formatted = $startdatum ? date_i18n("d. F Y", strtotime($startdatum)) : '';
    $enddatum_formatted = $enddatum ? date_i18n("d. F Y", strtotime($enddatum)) : '';

    return array(
        'adresse' => $adresse,
        'adresslink' => $adresslink,
        'organizer' => $organizer,
        'startdatum' => $startdatum,
        'enddatum' => $enddatum,
        'time' => $time,
        'more_days' => $more_days,
        'date_icon' => $date_icon,
        'time_icon' => $time_icon,
        'startdatum_formatted' => $startdatum_formatted,
        'enddatum_formatted' => $enddatum_formatted,
    );
}

function get_termin_data_formatted_with_icons($termin_data)
{
    $adresse = $termin_data['adresse'];
    $adresslink = $termin_data['adresslink'];
    $organizer = $termin_data['organizer'];
    $startdatum = $termin_data['startdatum'];
    $enddatum = $termin_data['enddatum'];
    $time = $termin_data['time'];
    $more_days = $termin_data['more_days'];
    $date_icon = $termin_data['date_icon'];
    $time_icon = $termin_data['time_icon'];
    $startdatum_formatted = $termin_data['startdatum_formatted'];
    $enddatum_formatted = $termin_data['enddatum_formatted'];

    ?>
    <?php if ($adresse || $organizer): ?>
        <span class="post-listing-item-adresse adresse">
            <?php if ($organizer): ?>
                <?= $organizer ?>
            <?php endif; ?>
            <?php if ($adresse && $organizer): ?>
                <span class="separator">|</span>
            <?php endif; ?>
            <?php if ($adresse): ?>
                <a href="<?php echo esc_url($adresslink); ?>" target="_blank"
                    aria-label="Adresse von <?php the_title(); ?> in Google Maps anzeigen">
                    <?php echo esc_html($adresse); ?>
                </a>
            <?php endif; ?>
        </span>
    <?php endif; ?>

    <!-- Termin-Infos -->
    <div class="termin-info-wrap">
        <span class="termin-info <?php echo $more_days ? 'mr-05' : ''; ?> link-icon-before cticon-calendar link-icon icon-accent">
            <?php echo $more_days ? 'Vom ' : ''; ?>
            <?php echo esc_html($startdatum_formatted); ?>
        </span>
        <?php if (!$more_days): ?>
            <?php if ($time): ?>
                <span class="post-listing-item-info-time termin-info link-icon-before cticon-time link-icon icon-accent">
                    <?php echo esc_html($time); ?> Uhr
                </span>
            <?php endif; ?>
        <?php else: ?>
            <span class="post-listing-item-info-date-end no-padding-left">
                bis zum <?php echo esc_html($enddatum_formatted); ?>
            </span>
        <?php endif; ?>
    </div>
    <?php
}


function get_termin_data_formatted_simple($termin_data)
{
    $adresse = $termin_data['adresse'];
    $adresslink = $termin_data['adresslink'];
    $startdatum_formatted = $termin_data['startdatum_formatted'];
    $enddatum_formatted = $termin_data['enddatum_formatted'];
    $time = $termin_data['time'];
    $more_days = $termin_data['more_days'];
    ?>
    <!-- Adresse -->
    <?php if ($adresse): ?>
        <span class="post-listing-item-adresse adresse">
            <a href="<?php echo esc_url($adresslink); ?>" target="_blank"
                aria-label="Adresse von <?php the_title(); ?> in Google Maps anzeigen">
                <?php echo esc_html($adresse); ?>
            </a>
        </span>
    <?php endif; ?>

    <!-- Termin-Infos -->
    <div class="termin-info-wrap">
        <span class="termin-info <?php echo $more_days ? 'mr-05' : ''; ?>">
            <?php echo $more_days ? 'Vom ' : ''; ?>
            <?php echo esc_html($startdatum_formatted); ?>
            <?php if ($more_days): ?>
                bis zum <?php echo esc_html($enddatum_formatted); ?>
            <?php endif; ?>
        </span>
        <?php if (!$more_days): ?>
            <span class="separator">|</span>
            <span class="post-listing-item-info-time termin-info">
                <?php echo esc_html($time); ?> Uhr
            </span>
        <?php endif; ?>
    </div>

    <?php

}