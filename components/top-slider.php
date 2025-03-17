<?php
$main_slider = get_field('main_slider', 'option');
$page_slider = get_field('page_slider');
$slider = $page_slider ? $page_slider : $main_slider;
?>

<div class="swiper-container swiper top-image-swiper">
    <div class="swiper-wrapper">
        <?php foreach ($slider as $slide): ?>
            <div class="swiper-slide">
                <div class="top-image-swiper__image-wrapper">
                    <?php
                    if (function_exists('imageOutput')) {
                        imageOutput($slide['ID'], 'img-1920', '1920px');
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="swiper-pagination"></div>
</div>