<?php 
$blockName = 'yt-video-embed';
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
    echo do_shortcode('[ma-gdpr-youtube video="' . $video_id . '"]');
  ?>
  <?php else: ?>
    <p>Bitte geben Sie eine YouTube-Video-ID ein.</p>
    <img src="<?php echo get_template_directory_uri() . '/blocks/youtube-video/g-dpr-youtube-01.webp'; ?>" alt="YouTube Video Placeholder">
  <?php endif;?>
</div>
