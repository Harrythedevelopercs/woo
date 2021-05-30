<?php

defined('ABSPATH') or die();

//CUSTOM FIELD ADD KRNI HAI SINGLE PRODUCT ME 

function harry_bookings_custom_feild(){
	?>
	<div class="wrap">
		<label for="harry-feild">Select Date</label>
		</br>
		<input type="date" id="harry-feild" name="harry-feild" class="form-control" >
		</br>
		
	</div>
	<?php
}

add_action('woocommerce_before_add_to_cart_button','harry_bookings_custom_feild',10);

//CART K SESSION ME DATA STORE KR RAHA HO


function harry_bookings_custom_cart_item( $cart_item_data, $product_id, $variation_id){
	$harry_field = filter_input( INPUT_POST, 'harry-feild' );
	if ( empty( $harry_field ) ) {
		return $cart_item_data;
	}
	$cart_item_data['harry-field'] = $harry_field;

	return $cart_item_data;

}
add_filter( 'woocommerce_add_cart_item_data', 'harry_bookings_custom_cart_item',10,3 );


// CART ME SHOW KR RAHA HO 

function harry_text_cart( $item_data, $cart_item ) {
	if ( empty( $cart_item['harry-field'] ) ) {
		return $item_data;
	}

	$item_data[] = array(
		'key'     => __( 'Booking Dates', 'field' ),
		'value'   => wc_clean( $cart_item['harry-field'] ),
		'display' => '',
	);

	return $item_data;
}

add_filter( 'woocommerce_get_item_data', 'harry_text_cart', 10, 2 );


//ORDER FORM ME SHOW KRNE K LIYE


function harry_order_items( $item, $cart_item_key, $values, $order ) {
	if ( empty( $values['harry-field'] ) ) {
		return;
	}

	$item->add_meta_data( __( 'Select Dates', 'field' ), $values['harry-field'] );
}

add_action( 'woocommerce_checkout_create_order_line_item', 'harry_order_items', 10, 4 );


//include('inc/extraprice_checkboc/index.php');
include('inc/extraprice_checkboc/pricebyvalue/index.php');