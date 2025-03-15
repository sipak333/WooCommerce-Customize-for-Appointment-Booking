<?php
/**
 * Display appointment custom fields on cart and checkout pages
 */

// Display custom fields in cart
function display_appointment_fields_in_cart($item_data, $cart_item) {
    if (isset($cart_item['product_date'])) {
        $item_data[] = array(
            'key'   => __('Appointment Date', 'woocommerce'),
            'value' => wc_clean($cart_item['product_date']),
            'display' => '',
        );
    }
    
    if (isset($cart_item['product_time_slot'])) {
        $item_data[] = array(
            'key'   => __('Time Slot', 'woocommerce'),
            'value' => wc_clean($cart_item['product_time_slot']),
            'display' => '',
        );
    }
    
    if (isset($cart_item['product_location'])) {
        // Get the location term name instead of ID
        $location_term = get_term($cart_item['product_location'], 'location');
        $location_name = is_wp_error($location_term) ? '' : $location_term->name;
        
        $item_data[] = array(
            'key'   => __('Location', 'woocommerce'),
            'value' => $location_name,
            'display' => '',
        );
    }
    
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_appointment_fields_in_cart', 10, 2);

// Save custom fields to order items during checkout
function add_appointment_fields_to_order_items($item, $cart_item_key, $values, $order) {
    if (isset($values['product_date'])) {
        $item->add_meta_data(__('Appointment Date', 'woocommerce'), $values['product_date']);
    }
    
    if (isset($values['product_time_slot'])) {
        $item->add_meta_data(__('Time Slot', 'woocommerce'), $values['product_time_slot']);
    }
    
    if (isset($values['product_location'])) {
        // Get the location term name instead of ID
        $location_term = get_term($values['product_location'], 'location');
        $location_name = is_wp_error($location_term) ? '' : $location_term->name;
        
        $item->add_meta_data(__('Location', 'woocommerce'), $location_name);
        // Also save the location ID for reference
        // $item->add_meta_data('_location_id', $values['product_location'], true);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'add_appointment_fields_to_order_items', 10, 4);

// Fix the form submission to include the selected time slot
function add_time_slot_to_add_to_cart_form() {
    if (is_product()) {
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // When the add to cart button is clicked
            $('.single_add_to_cart_button').on('click', function(e) {
                // Check if date and time slot are selected
                var selectedDate = $('#product_date').val();
                var selectedTimeSlot = $('input[name="time_slot"]:checked').val();
                var selectedLocation = $('#product_location').val();
                
                if (!selectedDate) {
                    e.preventDefault();
                    alert('Please select an appointment date.');
                    return false;
                }
                
                if (!selectedTimeSlot) {
                    e.preventDefault();
                    alert('Please select a time slot.');
                    return false;
                }
                
                if ($('#product_location').length && !selectedLocation) {
                    e.preventDefault();
                    alert('Please select a location.');
                    return false;
                }
                
                // Add hidden fields to the form
                var form = $(this).closest('form');
                
                // Remove any existing hidden fields to avoid duplicates
                form.find('input[name="product_date"]').remove();
                form.find('input[name="product_time_slot"]').remove();
                form.find('input[name="product_location"]').remove();
                
                // Add the hidden fields with the selected values
                form.append('<input type="hidden" name="product_date" value="' + selectedDate + '" />');
                form.append('<input type="hidden" name="product_time_slot" value="' + selectedTimeSlot + '" />');
                
                if (selectedLocation) {
                    form.append('<input type="hidden" name="product_location" value="' + selectedLocation + '" />');
                }
            });
            // Select the <a> element inside the container
const submitButton = document.querySelector('.wc-block-cart__submit-container a');

// Select the text inside the <span> element
const buttonText = submitButton.querySelector('.wc-block-components-button__text');

// Change the text content
buttonText.textContent = 'New Checkout Text';

        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'add_time_slot_to_add_to_cart_form');

// Display appointment details in order emails
function display_appointment_details_in_emails($order, $sent_to_admin, $plain_text, $email) {
    if ($plain_text) {
        return;
    }
    
    $items = $order->get_items();
    
    if (count($items) > 0) {
        echo '<h2>' . __('Appointment Details', 'woocommerce') . '</h2>';
        echo '<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; margin-bottom: 20px;">';
        echo '<thead><tr>';
        echo '<th class="td" scope="col" style="text-align:left;">' . __('Product', 'woocommerce') . '</th>';
        echo '<th class="td" scope="col" style="text-align:left;">' . __('Appointment Date', 'woocommerce') . '</th>';
        echo '<th class="td" scope="col" style="text-align:left;">' . __('Time Slot', 'woocommerce') . '</th>';
        echo '<th class="td" scope="col" style="text-align:left;">' . __('Location', 'woocommerce') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($items as $item_id => $item) {
            $product = $item->get_product();
            $product_name = $item->get_name();
            $appointment_date = wc_get_order_item_meta($item_id, 'Appointment Date', true);
            $time_slot = wc_get_order_item_meta($item_id, 'Time Slot', true);
            $location = wc_get_order_item_meta($item_id, 'Location', true);
            
            echo '<tr>';
            echo '<td class="td" style="text-align:left; vertical-align:middle;">' . esc_html($product_name) . '</td>';
            echo '<td class="td" style="text-align:left; vertical-align:middle;">' . esc_html($appointment_date) . '</td>';
            echo '<td class="td" style="text-align:left; vertical-align:middle;">' . esc_html($time_slot) . '</td>';
            echo '<td class="td" style="text-align:left; vertical-align:middle;">' . esc_html($location) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
}
add_action('woocommerce_email_after_order_table', 'display_appointment_details_in_emails', 10, 4);

// Add custom CSS for the time slot buttons
function add_time_slot_styles() {
    ?>
    <style>
        /* Time slot styling */
        .time-slot-radio {
            display: none;
        }
        
        .time-slot-btn {
            display: inline-block;
            padding: 8px 12px;
            margin: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .time-slot-radio:checked + .time-slot-btn {
            background-color: #1E2D5D;
            color: white;
            border-color: #1E2D5D;
        }
        
        /* Form field styling */
        #product_date, #product_location {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        #time_slots_container {
            margin: 15px 0;
        }
        
        /* Validation message */
        .validation-message {
            color: #f44336;
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
    <?php
}
add_action('wp_head', 'add_time_slot_styles');

