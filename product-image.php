<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.7.0
 */

use Automattic\WooCommerce\Enums\ProductType;

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
	return;
}

global $product;

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters(
	'woocommerce_single_product_image_gallery_classes',
	array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
		'woocommerce-product-gallery--columns-' . absint( $columns ),
		'images',
	)
);
?>
<div class="product-image-card">
  <div class="custom-product-gallery-carousel">
      <button class="carousel-arrow left" id="carousel-arrow-left" aria-label="Previous image">&#8592;</button>
      <div class="main-image-container" id="main-image-container">
          <?php
          if ( $post_thumbnail_id ) {
              $html = wc_get_gallery_image_html( $post_thumbnail_id, true );
          } else {
              $wrapper_classname = $product->is_type( ProductType::VARIABLE ) && ! empty( $product->get_available_variations( 'image' ) ) ?
                  'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder' :
                  'woocommerce-product-gallery__image--placeholder';
              $html              = sprintf( '<div class="%s">', esc_attr( $wrapper_classname ) );
              $html             .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
              $html             .= '</div>';
          }
          echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id );
          ?>
      </div>
      <button class="carousel-arrow right" id="carousel-arrow-right" aria-label="Next image">&#8594;</button>
  </div>
  <div class="custom-thumbnails-wrapper" id="custom-thumbnails-wrapper">
      <?php do_action( 'woocommerce_product_thumbnails' ); ?>
  </div>
</div>
