<div class="accessibility-tools-wrapper">
    <button data-id="open-accessibility-tools" class="accessibility-button" aria-label="Barrierefreiheits Einstellungen öffnen">
        <img width="32" height="32" src="<?= get_template_directory_uri() ?>/assets/images/icons/accessibility-icon.svg"
    alt="Icon: Ein Piktogramm eines menschen mit ausgestreckten Armen und Beinen.">
        <span class="sr-only">Barrierefreiheits Einstellungen öffnen</span>
    </button>

    <div class="accessibility-tools-dialog" id="accessibility-tools-dialog" aria-hidden="true" aria-label="Barrierefreiheits-Einstellungen" role="dialog">
        <button class="acc-button close-acc-dialog">
            <img width="32" height="32" src="<?= get_template_directory_uri() ?>/assets/images/icons/close-icon-acc.svg"
            alt="Icon: Ein Kreis mit einem X in der Mitte.">
            <span class="sr-only">Schließen</span>
        </button>
    <?php 
        get_template_part('components/accessibility-select');
    ?>
    </div>
</div>

