<?php




function add_custom_price_by_value( ){
    global $product;
    global  $woocommerce;
    $active_price = (float) $product->get_price();
        ?>
        <div class="wrap">
            <label for="pricebyvalue">Engrave Your Name <span style="color:red;font-size:12px">Payment Charge As Word Count</span></label>
            </br>
            <input type="text" id="pricebyvalue" name="pricebyvalue"  class="form-control" >
            <input type="hidden" id="active_price_id" name="new_active_price" >
            <input type="hidden"  name="active_price" value="<?php echo $product->get_price(); ?>" >
            <p id="pricechangehere"></p>
            </br>
	    </div>

        <script type="text/javascript">
            jQuery(function($) {
                var cb = 1.15;
                var pp = 'p.price';
                $('#pricebyvalue').keyup(function(){
                    var words = $("#pricebyvalue").val();
                    var total_word_count = words.length;
                    var sub_total = total_word_count * cb;
                    var total = sub_total + <?php echo $active_price; ?>;
                    var print = total ;
                    
                    $(pp).html( "<?php echo get_woocommerce_currency_symbol() ?>" + print);
                    $("#active_price_id").val(total);
                });
            });
    </script>
    <?php
}
add_action('woocommerce_before_add_to_cart_button','add_custom_price_by_value',10);



function engrave_name_cart_session($cart_item_data, $product_id, $variation_id){
    $pricebyvalue = filter_input( INPUT_POST, 'pricebyvalue' );
    if ( empty( $pricebyvalue ) ) {
		return $cart_item_data;
	}
	$cart_item_data['pricebyvalue'] = $pricebyvalue;
     $cart_item_data['newprice'] = (float) $_POST['new_active_price'] ;
     
    

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'engrave_name_cart_session',10,3 );



function engrave_text_cart( $item_data, $cart_item ) {
	if ( empty( $cart_item['pricebyvalue'] ) ) {
		return $item_data;
	}

	$item_data[] = array(
		'key'     => __( 'Engraving Name', 'field' ),
		'value'   => wc_clean( $cart_item['pricebyvalue'] ),
		'display' => '',   
    );
    $item_data[] = array(
        'key'     => __( 'New Price', 'field' ),
		'value'   => wc_clean( $cart_item['newprice'] ),
		'display' => '',
    );

	return $item_data;
}

add_filter( 'woocommerce_get_item_data', 'engrave_text_cart', 10, 2 );


// Front: Set the new calculated cart item price
add_action('woocommerce_before_calculate_totals', 'new_price_add_custom_price', 20, 1);

function new_price_add_custom_price($cart) {
    if (is_admin() && !defined('DOING_AJAX'))
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    foreach($cart->get_cart() as $cart_item) {
        if (isset($cart_item['newprice']))
            $cart_item['data']->set_price((float) $cart_item['newprice']);
    }
}



function engrave_order_items( $item, $cart_item_key, $values, $order ) {
	if ( empty( $values['pricebyvalue'] ) ) {
		return;
	}

	$item->add_meta_data( __( 'Engrave Name ', 'field' ), $values['pricebyvalue'] );
}

add_action( 'woocommerce_checkout_create_order_line_item', 'engrave_order_items', 10, 4 );