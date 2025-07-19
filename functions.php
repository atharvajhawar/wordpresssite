<?php
// Enable WooCommerce product gallery features
function my_tt5_child_woocommerce_gallery_support() {
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'my_tt5_child_woocommerce_gallery_support' );

// Enqueue parent and child theme styles
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(), array('parent-style') );
});
    

    function mytt5child_enqueue_scripts() {
        // Enqueue child theme stylesheet
        wp_enqueue_style('mytt5child-style', get_stylesheet_uri());
        // Enqueue custom JS (depends on jQuery, load in footer)
        wp_enqueue_script('mytt5child-custom-js', get_stylesheet_directory_uri() . '/custom.js', array('jquery'), null, true);
        // Enqueue custom CSS for modern styles
        wp_enqueue_style('mytt5child-custom-css', get_stylesheet_directory_uri() . '/custom.css', array('mytt5child-style'));
    }
    add_action('wp_enqueue_scripts', 'mytt5child_enqueue_scripts');

    add_filter('woocommerce_add_cart_item_data', function($cart_item_data, $product_id) {
        if (isset($_POST['flavour_2'])) {
            $cart_item_data['flavour_2'] = sanitize_text_field($_POST['flavour_2']);
        }
        if (isset($_POST['purchase_mode'])) {
            $cart_item_data['purchase_mode'] = sanitize_text_field($_POST['purchase_mode']);
        }
        return $cart_item_data;
    }, 10, 2);

    // Adjust cart item price based on purchase mode and discounts
    add_action('woocommerce_before_calculate_totals', function($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;
        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['purchase_mode'])) {
                $product = $cart_item['data'];
                $base_price = floatval($product->get_regular_price());
                $mode = $cart_item['purchase_mode'];
                if ($mode === 'single' || $mode === 'double') {
                    // Subscription price: 25% off
                    $sub_price = $base_price * 0.75;
                    // Sales price: 20% off
                    $final_price = $sub_price * 0.8;
                    $product->set_price($final_price);
                }
            }
        }
    });

    add_filter('woocommerce_get_item_data', function($item_data, $cart_item) {
        if (isset($cart_item['flavour_2']) && $cart_item['flavour_2']) {
            $item_data[] = [
                'name' => 'Second Flavor',
                'value' => $cart_item['flavour_2'],
            ];
        }
        if (isset($cart_item['purchase_mode'])) {
            $item_data[] = [
                'name' => 'Purchase Mode',
                'value' => ucfirst($cart_item['purchase_mode']) . ' Drink Subscription',
            ];
        }
        return $item_data;
    }, 10, 2);

    // Show original price (compare-at) in cart and checkout
    add_filter('woocommerce_cart_item_price', function($price, $cart_item, $cart_item_key) {
        if (isset($cart_item['purchase_mode'])) {
            $product = $cart_item['data'];
            $regular_price = floatval($product->get_regular_price());
            $current_price = floatval($product->get_price());
            if ($current_price < $regular_price) {
                $price = '<del>' . wc_price($regular_price) . '</del> <ins>' . wc_price($current_price) . '</ins>';
            } else {
                $price = wc_price($current_price);
            }
        }
        return $price;
    }, 10, 3);

    add_action('woocommerce_after_cart_table', function() {
        echo '<div class="cart-gifts" style="margin-top: 30px; border: 1px solid #eee; padding: 15px;">
            <h3>🎁 Your Free Gift!</h3>
            <p>Enjoy a free shaker bottle with your order.</p>
        </div>';
    });