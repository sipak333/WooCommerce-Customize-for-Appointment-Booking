<?php
/**
 * Plugin Name: Appointments API
 * Description: Custom Endpoint for retrieving WooCommerce Orders .
 * Version: 1.0
 * Author: Sipak
 */
//https://unitedhealthcheckups.com/doctors/wp-json/custom/v1/all-orders/
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/all-orders/', [
        'methods' => 'GET',
        'callback' => 'get_all_woo_orders_details',
        'permission_callback' => '__return_true',
    ]);
});

function get_all_woo_orders_details(WP_REST_Request $request) {
    $args = [
        'status' => 'any',    // Retrieve orders in any status
        'limit' => -1,        // No limit on the number of orders
        'return' => 'ids',    // Only fetch order IDs to improve performance
    ];

    $order_ids = wc_get_orders($args);

    if (empty($order_ids)) {
        return new WP_REST_Response('No orders found.', 404);
    }

    $all_orders_data = [];

    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);

        // Prepare the order data in the required format
        $order_data = [
            'appointment_with' => '',   // Custom product ID
            'appointment_date' => '',   // Appointment date
            'appointment_slot' => '',   // Appointment time slot
            'comments' => 'NA',         // Default 'NA'
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),  // Customer name
            'phone' => $order->get_billing_phone(),  // Customer phone number
            'email' => 'NA',            // Email (currently 'NA')
            'status' => $order->get_status(),    // Order status
            'total_amount' => $order->get_total(),  // Total amount
            'booked_date' => $order->get_date_created()->date('Y-m-d H:i:s'),  // Date the order was created
            'payment_method' => $order->get_payment_method_title(),  // Payment method
        ];

        // Extract appointment details from the order items
        foreach ($order->get_items() as $item_id => $item) {
            $meta_data = $item->get_meta_data();

            foreach ($meta_data as $meta) {
                if ($meta->key == 'Appointment Date') {
                    $order_data['appointment_date'] = date('d-m-Y', strtotime($meta->value)); // Format the date as required
                }
                if ($meta->key == 'Time Slot' || $meta->key == 'Appointment Time') {
                    $order_data['appointment_slot'] = $meta->value;  // Appointment time
                }
            }

            // Get the custom product ID for appointment_with
            $product = $item->get_product();
            if ($product) {
                $custom_product_id = $product->get_meta('_custom_product_id');
                if ($custom_product_id) {
                    $order_data['appointment_with'] = $custom_product_id;  // Set custom product ID
                }
            }
        }

        // Check if appointment_with has a valid value (non-empty)
        if (!empty($order_data['appointment_with'])) {
            // Send to the external API
            $response = send_to_external_api($order_data);
            if (is_wp_error($response)) {
                // Handle any errors that occur when sending data
                error_log('Error sending data to external API: ' . $response->get_error_message());
            }
            // Add the valid order data to the response array
            $all_orders_data[] = $order_data;
        }
    }

    // Return the JSON response with the valid orders (just for inspection)
    return new WP_REST_Response($all_orders_data, 200);
}

function send_to_external_api($order_data) {
    $url = 'https://cep.prodoc.ai/api/v1/appointment/book-appointment';
    $headers = [
        'Authorization' => '08088c1a09e8f8fcae36bb770974a2c875b69a9a7a806d3a926096a6b332af36',
        'Content-Type' => 'application/json',
    ];

    // Prepare the request body
    $body = json_encode([
        'appointment_with' => $order_data['appointment_with'],
        'appointment_date' => $order_data['appointment_date'],
        'appointment_slot' => $order_data['appointment_slot'],
        'comments' => $order_data['comments'],
        'name' => $order_data['name'],
        'phone' => $order_data['phone'],
        'email' => $order_data['email'],
        'status' => $order_data['status'],
        'total_amount' => $order_data['total_amount'],
        'booked_date' => $order_data['booked_date'],
        'payment_method' => $order_data['payment_method'],
    ]);

    // Send the POST request to the external API
    $response = wp_remote_post($url, [
        'method'    => 'POST',
        'headers'   => $headers,
        'body'      => $body,
    ]);

    // Check if there is an error in the response
    if (is_wp_error($response)) {
        // Log the error message
        error_log('Error sending data to external API: ' . $response->get_error_message());
        return false;
    }

    // Log the response status and body
    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);
    
    error_log("Response Code: " . $response_code); // Log the response code
    error_log("Response Body: " . $response_body); // Log the response body for debugging

    // Check if the API response is successful
    if ($response_code == 200) {
        // Log success message if successful
        error_log('Data successfully sent to external API.');
        return true;
    } else {
        // Log failure if response code is not 200
        error_log('Failed to send data to external API. Response: ' . $response_body);
        return false;
    }
    // return $response;
}
