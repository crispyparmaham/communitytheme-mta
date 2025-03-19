<?php
// ACF Felder von der Theme-Settings-Seite abrufen
$koordinaten = get_field('koordinaten', 'option');
$einwohner = get_field('einwohner', 'option');
$plz = get_field('plz', 'option');
$hohe = get_field('hohe', 'option');
$flache = get_field('flache', 'option');
$einwohner_ort = get_field('einwohner_ort', 'option'); // Repeater
$personen = get_field('personen', 'option'); // WYSIWYG Inhalt

if ($einwohner || $plz || $hohe || $flache || $einwohner_ort || $personen) :
    ?>

    <div class="daten-fakten">
        <div class="daten">
            <span class="coordinates"><?php echo wp_kses_post($koordinaten); ?></span>
            <div class="inner-wrap inner-max-width">
                <div class="data-item einwohner">
                    <span class="label">Einwohner</span>
                    <span class="value link-icon-before cticon-person icon-accent"><?php echo esc_html($einwohner); ?></span>
                </div>
                <div class="data-item plz">
                    <span class="label">Postleitzahl</span>
                    <span class="value link-icon-before cticon-map icon-accent"><?php echo esc_html($plz); ?></span>
                </div>
                <div class="data-item hoehe">
                    <span class="label">Höhe</span>
                    <span class="value link-icon-before cticon-height icon-accent"><?php echo esc_html($hohe); ?> m</span>
                </div>
                <div class="data-item flaeche">
                    <span class="label">Fläche</span>
                    <span class="value link-icon-before cticon-area icon-accent"><?php echo esc_html($flache); ?> km²</span>
                </div>
            </div>
        </div>

        <div class="einwohner-gemeinderat inner-max-width">
            <div class="einwohner-grid">
                <h3 id="einwohnerOrtsteil">Einwohner pro Ortsteil</h3>
                <div class="ortsteil-tabelle" aria-labelledby="einwohnerOrtsteil">
                    <table>
                        <tbody>
                            <?php if (is_array($einwohner_ort)) : ?>
                                <?php foreach ($einwohner_ort as $ort) :
                                    $ortsteil = $ort['ortsteil'];
                                    $einwohner_ortsteil = $ort['einwohner'];
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($ortsteil); ?></td>
                                        <td><?php echo esc_html($einwohner_ortsteil); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="2">Keine Daten verfügbar.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="gemeinderat-grid">
                <h4 id="gemeinderatHeader">Gemeinderat</h4>
                <div class="personen" aria-labelledby="gemeinderatHeader">
                    <?php echo wp_kses_post($personen); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
else :
    ?>
    <p>Keine Daten & Fakten verfügbar.</p>
    <?php
endif;
?>
