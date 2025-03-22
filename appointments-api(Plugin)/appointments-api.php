<?php
/**
 * Plugin Name: Appointments API
 * Description: Custom API for retrieving appointment details.
 * Version: 1.0
 * Author: Sipak
 */

// https://unitedhealthcheckups.com/doctors/appointments-details/

add_action('init', 'custom_rewrite_rules');
add_action('template_redirect', 'handle_custom_endpoint');

function custom_rewrite_rules() {
    add_rewrite_rule(
        '^doctors/appointments-details/?$', 
        'index.php?appointments_details=1', 
        'top'
    );
}

function custom_query_vars($vars) {
    $vars[] = 'appointments_details';
    return $vars;
}
add_filter('query_vars', 'custom_query_vars');

function handle_custom_endpoint() {
    if (get_query_var('appointments_details')) {
        $appointments = get_appointments_details();
        header('Content-Type: application/json');
        echo json_encode($appointments);
        exit;
    }
}

function get_appointments_details() {
    $appointments = [];
    $orders = wc_get_orders([
        'limit' => -1,
    ]);

    foreach ($orders as $order) {
        $order_data = [
            'order_id' => $order->get_id(),
            'status' => ucfirst($order->get_status()),
            'total' => $order->get_total(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'appointment_details' => [
                'doctor_name' => '',
                'appointment_date' => '',
                'time_slot' => '',
                'location' => '',
            ],
            'payment_method' => $order->get_payment_method_title(),
            'billing_address' => $order->get_billing_address_1(),
            'phone_number' => $order->get_billing_phone(),
            'date_created' => $order->get_date_created()->date('Y-m-d H:i:s'),
        ];

        foreach ($order->get_items() as $item_id => $item) {
            $doctor_name = $item->get_name();
            $meta_data = $item->get_meta_data();
            foreach ($meta_data as $meta) {
                if ($meta->key == 'Appointment Date') {
                    $order_data['appointment_details']['appointment_date'] = $meta->value;
                }
                if ($meta->key == 'Time Slot' || $meta->key == 'Appointment Time') {
                    $order_data['appointment_details']['time_slot'] = $meta->value;
                }
                if ($meta->key == 'Location' || $meta->key == 'Select - Hospital Location') {
                    $order_data['appointment_details']['location'] = $meta->value;
                }
            }
            $order_data['appointment_details']['doctor_name'] = $doctor_name;
        }

        $appointments[] = $order_data;
    }

    return $appointments;
}
