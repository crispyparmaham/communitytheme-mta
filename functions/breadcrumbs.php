<?php
function custom_breadcrumbs() {
    if ( function_exists('yoast_breadcrumb') ) {
        yoast_breadcrumb( '</p><p id="breadcrumbs" class="breadcrumbs">','</p><p>' );
      }
}
?>
