<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

?>
<div class="product-header">
  <h1 class="product-title"><?php echo esc_html( $product->get_name() ); ?></h1>
  <div class="product-subheading">Your perfect daily hydration companion</div>
  <div class="product-rating">
    <?php echo wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() ); ?>
    <span class="review-count">
      (<?php echo number_format_i18n( $product->get_rating_count() ); ?>+ reviews)
    </span>
  </div>
  <div class="product-short-description">
    <?php echo apply_filters( 'woocommerce_short_description', $product->get_short_description() ); ?>
  </div>
</div>

<?php
do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<div class="recommended-bar">
  Recommended
</div>

<!-- Purchase Options Radio Group -->
<div id="purchase-options" style="margin-bottom: 1.5em;">
  <div class="purchase-option-row">
    <span class="purchase-option-label">
      <input type="radio" name="purchase_mode" value="single" checked>
      Single Drink Subscription
    </span>
    <span class="purchase-option-price">
      <?php
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        if ($sale_price && $sale_price < $regular_price) {
          echo wc_price($sale_price) . ' <del>' . wc_price($regular_price) . '</del>';
        } else {
          echo wc_price($regular_price);
        }
      ?>
    </span>
  </div>
  <div class="purchase-option-card" data-mode="double">
    <input type="radio" name="purchase_mode" value="double"> <span>Double Drink Subscription</span>
  </div>
  <div id="purchase-benefits" style="margin-top: 0.5em;">
    <!-- Dynamic: Included items, discounts, and benefits will appear here -->
  </div>
</div>

<!-- Flavor Selector as Images -->
<div class="flavor-selector-group">
  <label class="flavor-selector selected">
    <input type="radio" name="flavor" value="chocolate" data-img="https://www.shutterstock.com/image-vector/black-shampoo-bottle-mockup-realistic-260nw-1663150600.jpg" checked>
    <img src="https://www.shutterstock.com/image-vector/black-shampoo-bottle-mockup-realistic-260nw-1663150600.jpg" alt="Chocolate" />
    <span>Chocolate</span>
    <span class="flavor-badge">Best Seller</span>
  </label>
  <label class="flavor-selector">
    <input type="radio" name="flavor" value="vanilla" data-img="https://statics.promofarma.com/static/promofarma/prod/product_images/mr/GXW0FL41_es_ES_0.jpeg">
    <img src="https://statics.promofarma.com/static/promofarma/prod/product_images/mr/GXW0FL41_es_ES_0.jpeg" alt="Vanilla" />
    <span>Vanilla</span>
  </label>
  <label class="flavor-selector">
    <input type="radio" name="flavor" value="orange" data-img="https://sc04.alicdn.com/kf/H71e3f7d78d0c408b8913cf5d77f567803.jpg_350x350.jpg">
    <img src="https://sc04.alicdn.com/kf/H71e3f7d78d0c408b8913cf5d77f567803.jpg_350x350.jpg" alt="Orange" />
    <span>Orange</span>
  </label>
</div>

<!-- What’s Included Box -->
<?php
// Get product meta fields for what's included (if any)
$included_single = get_post_meta($product->get_id(), '_included_single', true);
$included_double = get_post_meta($product->get_id(), '_included_double', true);
$short_desc = apply_filters('woocommerce_short_description', $product->get_short_description());
?>
<div id="whats-included-box" style="margin-bottom: 1.5em; border: 1px solid #eee; padding: 1em; background: #fafafa;"
     data-included-single="<?php echo esc_attr($included_single ? $included_single : strip_tags($short_desc)); ?>"
     data-included-double="<?php echo esc_attr($included_double ? $included_double : strip_tags($short_desc)); ?>">
  <strong>What’s Included</strong>
  <div id="whats-included-content">
    <div class="included-plan-row">
      <div class="included-plan-label">Every 30 Days</div>
      <div class="included-plan-images" id="included-every30-images">
        <!-- JS will insert the selected flavor image here -->
      </div>
    </div>
    <div class="included-plan-row">
      <div class="included-plan-label">One Time <span class="included-plan-free">(Free)</span></div>
      <div class="included-plan-images" id="included-onetime-images">
        <!-- JS will insert all flavor images here -->
      </div>
    </div>
  </div>
</div>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		<table class="variations" cellspacing="0" role="presentation">
			<tbody>
				<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<tr>
						<th class="label"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></th>
						<td class="value">
							<?php
								wc_dropdown_variation_attribute_options(
									array(
										'options'   => $options,
										'attribute' => $attribute_name,
										'product'   => $product,
									)
								);
								/**
								 * Filters the reset variation button.
								 *
								 * @since 2.5.0
								 *
								 * @param string  $button The reset variation button HTML.
								 */
								echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Clear options', 'woocommerce' ) . '">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ) : '';
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
