<?php 
    $communityName = get_field('gemeindename', 'option');
    $zip = get_field('zip', 'option');
    $buergermeister = get_field('buergermeister', 'option');
    $phone = get_field('phone_number', 'option');
    $mail = get_field('mail_address', 'option');
?>

<div class="impressum">
    <h2>Kontakt-Informationen</h2>
    <p>
       <strong> Gemeinde <?= $communityName; ?></strong><br>
        Bürgermeister: <?= $buergermeister; ?><br>
        <?= $zip; ?> <?= $communityName; ?><br>
        Tel.: <?= $phone; ?><br>
        E-Mail: <?= antispambot($mail); ?>
    </p>
    <p>
        Die Gemeinde <?= $communityName; ?> ist eine Körperschaft des Öffentlichen Rechts. Sie wird vertreten durch den Bürgermeister <?= $buergermeister; ?>.
    </p>
    <h2>Urheberrecht</h2>    
    <p>
    Das Layout der Homepage, die verwendeten Grafiken sowie die Sammlung der Beiträge sind urheberrechtlich geschützt. Die Seiten dürfen nur zum privaten Gebrauch vervielfältigt, Änderungen nicht vorgenommen und Vervielfältigungsstücke ohne Genehmigung nicht verbreitet werden. Die einzelnen Beiträge sind ebenfalls urheberrechtlich geschützt; weitere Hinweise können ggf. dort nachgelesen werden.
    </p>
    <h2>Haftungshinweis</h2>
    <p>
    Für den Inhalt extern verlinkter Webseiten kann keine Gewähr übernommen werden. Wir distanzieren uns ausdrücklich von deren Inhalten und Aufbereitung und betonen, keinerlei Einfluss auf Gestaltung und Inhalt dieser Links sowie sämtlichen weiterführenden Angeboten zu haben
    </p>
</div>