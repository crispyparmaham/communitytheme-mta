<?php
$blockName = 'external-resources-embed';
$html = get_field('html');
$headline = get_field('headline');
$text = get_field('text') ?: 'Bei Klick wird dieser Inhalt von externen Servern geladen.';
$privacyPolicyPage = get_privacy_policy_url();
$text .= ' Details siehe <a href="' . $privacyPolicyPage . '" target="_blank">Datenschutzerklärung</a>.';
$button_text = get_field('button') ?: 'Externen Inhalt laden';

// Support custom "anchor" values.
$anchor = '';
$block_id = md5($html);
if (!empty($block['anchor'])) {
  $anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

$class_name = $blockName . ' acf-custom-block';
if (!empty($block['className'])) {
  $class_name .= ' ' . $block['className'];
}
?>


<div <?php echo esc_attr($anchor); ?>class="<?php echo esc_attr($class_name); ?>" data-unlock-id="<?= $block_id ?>">
  <?php if ($html): ?>
    <div class="external-resource">

      <?php
      $shortcode = '[ma-content-consent alt="' . $headline . '" title="' . $headline . '" text="' . esc_attr($text) . '" button-text="' . esc_attr($button_text) . '" ]' . $html . '[/ma-content-consent]';
      echo htmlspecialchars_decode(do_shortcode($shortcode));
      ?>
      <label class="unlock-external-resource-label">
        <input type="checkbox" class="unlock-external-resources" data-id="<?= $block_id ?>">
        Diesen Inhalt immer entsperren
      </label>
    </div>
  <?php else: ?>
    <p>Bitte tragen Sie das gewünschte HTML ein.</p>
  <?php endif; ?>
</div>