jQuery(document).ready(function($) {
    // Helper to get selected variation data
    function getSelectedVariationData() {
        var $form = $('.variations_form');
        var variations = $form.data('product_variations');
        var attributes = {};
        $form.find('select[name^="attribute_"]').each(function() {
            attributes[$(this).attr('name')] = $(this).val();
        });
        // Find matching variation
        for (var i = 0; i < variations.length; i++) {
            var match = true;
            for (var key in variations[i].attributes) {
                if (variations[i].attributes[key] && variations[i].attributes[key] !== attributes[key]) {
                    match = false;
                    break;
                }
            }
            if (match) return variations[i];
        }
        return null;
    }

    function updatePurchaseUI() {
        var mode = $('input[name="purchase_mode"]:checked').val();
        var $benefits = $('#purchase-benefits');
        var variation = getSelectedVariationData();
        var mainPrice = 0, salePrice = 0;
        if (variation) {
            mainPrice = parseFloat(variation.display_regular_price);
            salePrice = parseFloat(variation.display_price);
        } else {
            // fallback to first visible price
            var $price = $('.single_variation .woocommerce-Price-amount');
            if ($price.length) {
                mainPrice = parseFloat($price.first().text().replace(/[^0-9.]/g, ''));
                salePrice = mainPrice;
            }
        }
        // Subscription price: 25% off
        var subPrice = salePrice * 0.75;
        // Final price after 20% sales discount
        var saleMain = salePrice * 0.8;
        var saleSub = subPrice * 0.8;
        var html = '';
        html += '<div><strong>' + (mode === 'single' ? 'Single' : 'Double') + ' Drink Subscription</strong></div>';
        html += '<div>Includes: ' + (mode === 'single' ? '1' : '2') + ' flavor selector' + (mode === 'double' ? 's' : '') + '</div>';
        html += '<div>Original Price: <del>' + mainPrice.toFixed(2) + '</del></div>';
        if (salePrice < mainPrice) {
            html += '<div>Sale Price: <ins>' + salePrice.toFixed(2) + '</ins></div>';
        }
        html += '<div>Subscription Price: <ins>' + subPrice.toFixed(2) + '</ins> <small>(25% off)</small></div>';
        html += '<div>Final Price after 20% off: <strong>' + saleMain.toFixed(2) + ' (main), ' + saleSub.toFixed(2) + ' (subscription)</strong></div>';
        html += '<div>Benefits: 25% off for subscription, 20% sales discount</div>';
        $benefits.html(html);
    }

    function updateWhatsIncluded() {
        var mode = $('input[name="purchase_mode"]:checked').val();
        var $box = $('#whats-included-box');
        var $content = $('#whats-included-content');
        var included = '';
        var frequency = '';
        if (mode === 'double') {
            included = $box.data('included-double');
            frequency = 'Every 30 Days';
        } else {
            included = $box.data('included-single');
            frequency = 'One Time';
        }
        var html = '';
        if (included) {
            html += '<div>' + included + '</div>';
        }
        html += '<div style="margin-top:0.5em;"><em>Frequency: ' + frequency + '</em></div>';
        $content.html(html);
    }

    // Add or remove second flavor selector
    function updateFlavorSelectors() {
        var mode = $('input[name="purchase_mode"]:checked').val();
        var $firstSelectorRow = $('.variations tr').first();
        var $secondSelector = $('#second-flavor-row');
        if (mode === 'double') {
            if ($secondSelector.length === 0) {
                // Clone the first selector row
                var $clone = $firstSelectorRow.clone();
                $clone.attr('id', 'second-flavor-row');
                // Update label and input names/ids
                $clone.find('label').text('Second Flavor');
                $clone.find('select').attr('name', 'flavour_2').attr('id', 'flavour_2');
                $clone.find('select').val(''); // Reset selection
                // Insert after the first selector
                $firstSelectorRow.after($clone);
            }
        } else {
            $secondSelector.remove();
        }
    }

    // On page load, pre-select 'Chocolate' flavor if available
    function preselectChocolateFlavor() {
        var $firstFlavor = $('.variations select').first();
        if ($firstFlavor.length) {
            var found = false;
            $firstFlavor.find('option').each(function() {
                if ($(this).text().toLowerCase().indexOf('chocolate') !== -1) {
                    $firstFlavor.val($(this).val()).trigger('change');
                    found = true;
                    return false;
                }
            });
            if (!found) {
                $firstFlavor.prop('selectedIndex', 1).trigger('change'); // fallback to first option
            }
        }
    }

    // Initial update
    updatePurchaseUI();
    updateFlavorSelectors();
    updateWhatsIncluded();
    preselectChocolateFlavor();

    // On radio change
    $(document).on('change', 'input[name="purchase_mode"]', function() {
        updatePurchaseUI();
        updateFlavorSelectors();
        updateWhatsIncluded();
    });

    // On variation change (update prices)
    $(document).on('show_variation', '.variations_form', function() {
        updatePurchaseUI();
    });
    $(document).on('change', '.variations select', function() {
        updatePurchaseUI();
    });

    // Add to Cart validation and feedback
    function showAddToCartError(msg) {
        var $error = $('#add-to-cart-error');
        if (!$error.length) {
            $error = $('<div id="add-to-cart-error" style="color:red; margin-bottom:1em;"></div>');
            $('.variations_form').before($error);
        }
        $error.text(msg).show();
    }
    function clearAddToCartError() {
        $('#add-to-cart-error').hide();
    }
    $(document).on('submit', '.variations_form', function(e) {
        clearAddToCartError();
        var mode = $('input[name="purchase_mode"]:checked').val();
        var $firstFlavor = $('.variations select').first();
        var $secondFlavor = $('#flavour_2');
        if (mode === 'single') {
            if (!$firstFlavor.val()) {
                showAddToCartError('Please select a flavor.');
                e.preventDefault();
                return false;
            }
        } else if (mode === 'double') {
            if (!$firstFlavor.val() || !$secondFlavor.val()) {
                showAddToCartError('Please select two flavors.');
                e.preventDefault();
                return false;
            }
            // Optional: ensure two different flavors
            if ($firstFlavor.val() === $secondFlavor.val()) {
                showAddToCartError('Please select two different flavors.');
                e.preventDefault();
                return false;
            }
        }
        clearAddToCartError();
    });

    // Card-style radio group for purchase options
    $(document).on('click', '.purchase-option-card', function() {
        $('.purchase-option-card').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true).trigger('change');
    });

    // Highlight selected flavor image card
    $(document).on('change', '.flavor-selector input[type="radio"]', function() {
        $('.flavor-selector').removeClass('selected');
        $(this).closest('.flavor-selector').addClass('selected');
    });

    // Product gallery thumbnail click logic
    $(document).on('click', '#custom-thumbnails-wrapper img', function() {
        var newSrc = $(this).attr('src');
        // Find the main image inside .main-image-container and update its src
        var $mainImg = $('#main-image-container img');
        if ($mainImg.length) {
            $mainImg.attr('src', newSrc);
        }
        // Highlight the selected thumbnail
        $('#custom-thumbnails-wrapper img').removeClass('selected');
        $(this).addClass('selected');
    });

    // Dynamic What's Included images based on selected flavor
    var flavorImages = {
      chocolate: "https://www.shutterstock.com/image-vector/black-shampoo-bottle-mockup-realistic-260nw-1663150600.jpg",
      vanilla: "https://statics.promofarma.com/static/promofarma/prod/product_images/mr/GXW0FL41_es_ES_0.jpeg",
      orange: "https://sc04.alicdn.com/kf/H71e3f7d78d0c408b8913cf5d77f567803.jpg_350x350.jpg"
    };
    function updateIncludedImages() {
      var selectedFlavor = $('.flavor-selector input[type="radio"]:checked').val();
      // Every 30 Days: show only the selected flavor
      $('#included-every30-images').html(
        '<img src="' + flavorImages[selectedFlavor] + '" alt="' + selectedFlavor + '" />'
      );
      // One Time (Free): show all flavors
      var allImgs = '';
      $.each(flavorImages, function(key, url) {
        allImgs += '<img src="' + url + '" alt="' + key + '" />';
      });
      $('#included-onetime-images').html(allImgs);
    }
    // Initial update
    updateIncludedImages();
    // On flavor change
    $(document).on('change', '.flavor-selector input[type="radio"]', function() {
      updateIncludedImages();
    });

    // Update main image when flavor is selected
    function updateMainImageFromFlavor() {
      var selectedImg = $('.flavor-selector input[type="radio"]:checked').data('img');
      if (selectedImg) {
        $('#main-image-container img').attr('src', selectedImg);
      }
    }
    // Initial update
    updateMainImageFromFlavor();
    // On flavor change
    $(document).on('change', '.flavor-selector input[type="radio"]', function() {
      updateMainImageFromFlavor();
    });
}); 