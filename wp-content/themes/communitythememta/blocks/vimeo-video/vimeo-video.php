<?php 
$blockName = 'vimeo-video-embed';
$video_id = get_field('yt_video_id');

// Support custom "anchor" values.
$anchor = '';
if (!empty($block['anchor'])) {
  $anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

$class_name = $blockName . ' acf-custom-block';
if (!empty($block['className'])) {
  $class_name .= ' ' . $block['className'];
}


?>


<div <?php echo esc_attr($anchor); ?>class="<?php echo esc_attr($class_name); ?>">
  <?php if($video_id) : ?>
  <?php
    echo do_shortcode('[ma-gdpr-vimeo video="' . $video_id . '"]');
  ?>
  <?php else: ?>
    <p>Bitte geben Sie eine Vimeo-Video-ID ein.</p>
  <?php endif;?>
</div>
