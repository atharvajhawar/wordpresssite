<?php
defined( 'ABSPATH' ) || exit;
get_header( 'shop' ); ?>

<div class="product-page-layout">
  <div class="product-image-area">
    <?php wc_get_template( 'single-product/product-image.php' ); ?>
  </div>
  <div class="product-info-area">
    <?php wc_get_template( 'single-product/add-to-cart/variable.php' ); ?>
  </div>
</div>

<?php get_footer( 'shop' ); ?> 