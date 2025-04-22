<?php

// Alter WooCommerce View Cart Text
add_filter( 'gettext', function( $translated_text ) {
    if ( 'View cart' === $translated_text ) {
        $translated_text = 'Please Confirm the Doctor';
    }
    return $translated_text;
} );


// Alter WooCommerce Checkout Text
add_filter( 'gettext', function( $translated_text ) {
    if ( 'Checkout' === $translated_text ) {
        $translated_text = 'Book Now';
    }
    return $translated_text;
} );

add_filter('gettext', 'change_add_to_cart_button_text', 20, 3);
function change_add_to_cart_button_text($translated_text, $text, $domain) {
    if ($text === 'Add to cart') {
        $translated_text = 'Book Appointment';
    }
    return $translated_text;
}

function change_order_received_text( $text ) {
    if ( $text == 'Thank you. Your order has been received.' ) {
        return 'Thank You! Your Appointment is Confirmed.';
    }
    return $text;
}
add_filter( 'woocommerce_thankyou_order_received_text', 'change_order_received_text' );

















// function send_order_data_to_api($order_id) {
//     if (!$order_id) return;

//     // Get order details
//     $order = wc_get_order($order_id);

//     // Prepare data for API
//     $data = [
//         'order_id'       => $order->get_id(),
//         'customer_name'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
//         'email'          => $order->get_billing_email(),
//         'phone'          => $order->get_billing_phone(),
//         'total'          => $order->get_total(),
//         'currency'       => $order->get_currency(),
//         'payment_method' => $order->get_payment_method_title(),
//         'products'       => []
//     ];

//     // Loop through products
//     foreach ($order->get_items() as $item) {
//         $product = $item->get_product();
//         if ($product) {
//             $data['products'][] = [
//                 'product_id'   => $product->get_id(),
//                 'product_name' => $product->get_name(),
//                 'quantity'     => $item->get_quantity(),
//                 'price'        => $item->get_total(),
//             ];
//         }
//     }

//     // API endpoint and key
//     $api_url = 'https://cep.prodoc.ai/docs_api'; // Ensure this endpoint is correct
//     $api_key = '08088c1a09e8f8fcae36bb770974a2c875b69a9a7a806d3a926096a6b332af36';

//     // Send data to API
//     $response = wp_remote_post($api_url, [
//         'method'    => 'POST',
//         'body'      => json_encode($data),
//         'headers'   => [
//             'Content-Type'  => 'application/json',
//             'Authorization' => 'Bearer ' . $api_key
//         ]
//     ]);

//     // Log API response for debugging
//     if (is_wp_error($response)) {
//         error_log('API Error: ' . $response->get_error_message());
//     } else {
//         error_log('API Response (HTTP ' . wp_remote_retrieve_response_code($response) . '): ' . wp_remote_retrieve_body($response));
//     }
// }

// // Hook into WooCommerce order completion
// add_action('woocommerce_thankyou', 'send_order_data_to_api', 10, 1);
//?>
