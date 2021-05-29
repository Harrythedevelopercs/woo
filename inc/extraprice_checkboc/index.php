<?php
// Backend: Additional pricing option custom field
add_action( 'woocommerce_product_options_pricing', 'wc_cost_product_field' );
function wc_cost_product_field() {
    woocommerce_wp_text_input( array(
        'id'        => '_warrenty_price',
        'class'     => 'wc_input_price short',
        'label'     => __( 'Warrenty', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')'
    ));
}

// Backend: Saving product pricing option custom field value
add_action( 'woocommerce_admin_process_product_object', 'save_product_custom_meta_data', 100, 1 );
function save_product_custom_meta_data( $product ){
    if ( isset( $_POST['_warrenty_price'] ) )
        $product->update_meta_data( '_warrenty_price', sanitize_text_field($_POST['_warrenty_price']) );
}

// Front: Add a text input field inside the add to cart form on single product page
add_action('woocommerce_single_product_summary','add_warrenty_price_option_to_single_product', 2 );
function add_warrenty_price_option_to_single_product(){
    global $product;

    if( $product->is_type('variable') || ! $product->get_meta( '_warrenty_price' ) ) return;

    add_action('woocommerce_before_add_to_cart_button', 'product_option_custom_field', 30 );
}

function product_option_custom_field(){
    global $product;

    $active_price = (float) $product->get_price();
    $warrenty_price = (float) $product->get_meta( '_warrenty_price' );
    $warrenty_price_html   = strip_tags( wc_price( wc_get_price_to_display( $product, array('price' => $warrenty_price ) ) ) );
    $active_price_html   = wc_price( wc_get_price_to_display( $product ) );
    $disp_price_sum_html = wc_price( wc_get_price_to_display( $product, array('price' => $active_price + $warrenty_price ) ) );

    echo '<div class="hidden-field">
    <p class="form-row form-row-wide" id="warrenty_option_field" data-priority="">
    <span class="woocommerce-input-wrapper"><span class="war-title"> ' . __("Warrenty price:", "Woocommerce") .
    '</span><label class="checkbox"><input type="checkbox" class="input-checkbox " name="warrenty_option" id="warrenty_option" value="1"> Add Warrenty for ' . $warrenty_price_html .
    '</label></span></p>
    <input type="hidden" name="warrenty_price" value="' . $warrenty_price . '">
    <input type="hidden" name="active_price" value="' . $active_price . '"></div>';

    // Jquery: Update displayed price
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        var cb = 'input[name="warrenty_option"]'
            pp = 'p.price';

        // On change / select a variation
        $('form.cart').on( 'change', cb, function(){
            if( $(cb).prop('checked') === true )
                $(pp).html('<?php echo $disp_price_sum_html; ?>');
            else
                $(pp).html('<?php echo $active_price_html; ?>');
        })

    });
    </script>
    <?php
}

// Front: Calculate new item price and add it as custom cart item data
add_filter('woocommerce_add_cart_item_data', 'add_custom_product_data', 10, 3);
function add_custom_product_data( $cart_item_data, $product_id, $variation_id ) {
    if (isset($_POST['warrenty_option']) && !empty($_POST['warrenty_option'])) {
        $cart_item_data['new_price'] = (float) ($_POST['active_price'] + $_POST['warrenty_price']);
        $cart_item_data['warrenty_price'] = (float) $_POST['warrenty_price'];
        $cart_item_data['active_price'] = (float) $_POST['active_price'];
        $cart_item_data['unique_key'] = md5(microtime().rand());
    }

    return $cart_item_data;
}

// Front: Set the new calculated cart item price
add_action('woocommerce_before_calculate_totals', 'extra_price_add_custom_price', 20, 1);

function extra_price_add_custom_price($cart) {
    if (is_admin() && !defined('DOING_AJAX'))
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    foreach($cart->get_cart() as $cart_item) {
        if (isset($cart_item['new_price']))
            $cart_item['data']->set_price((float) $cart_item['new_price']);
    }
}

// Front: Display option in cart item
add_filter('woocommerce_get_item_data', 'display_custom_item_data', 10, 2);

function display_custom_item_data($cart_item_data, $cart_item) {
    if (isset($cart_item['warrenty_price'])) {
        $cart_item_data[] = array(
            'name' => __("Extra Warrenty", "woocommerce"),
            'value' => strip_tags( '+ ' . wc_price( wc_get_price_to_display( $cart_item['data'], array('price' => $cart_item['warrenty_price'] ) ) ) )
        );
    }

    return $cart_item_data;
}