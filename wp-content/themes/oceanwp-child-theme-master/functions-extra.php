<?php

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



// Remove field from checkout page
add_filter('woocommerce_get_country_locale', function( $locale ) {
    foreach ( $locale as $key => $value ) {
        $locale[$key]['address_1'] = [
            'required' => false,
            'hidden'   => false,
        ];

        $locale[$key]['postcode'] = [
            'required' => false,
            'hidden'   => true,
        ];

        $locale[$key]['city'] = [
            'required' => false,
            'hidden'   => true,
        ];

        $locale[$key]['state'] = [
            'required' => false,
            'hidden'   => true,
        ];

        $locale[$key]['company'] = [
            'required' => false,
            'hidden'   => true,
        ];

        $locale[$key]['phone'] = [
            'required' => true,
            'hidden'   => false,
            
        ];

    }
    return $locale;
});

// Reorder product page elements
function reorder_product_page_elements() {
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 50);
}
add_action('init', 'reorder_product_page_elements');

// Remove quantity input and default Add to Cart button
add_filter('woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_single_add_to_cart_text', 9999);
function woocommerce_custom_single_add_to_cart_text() {
    return __('Book an Appointment', 'woocommerce');
}

function wc_remove_all_quantity_fields($return, $product) {
    return true;
}
add_filter('woocommerce_is_sold_individually', 'wc_remove_all_quantity_fields', 10, 2);

// Add custom time slots field to the product page
function add_custom_product_time_slots() {
    global $post;

    $days_of_week = array(
        'monday'    => 'Monday',
        'tuesday'   => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday'  => 'Thursday',
        'friday'    => 'Friday',
        'saturday'  => 'Saturday',
        'sunday'    => 'Sunday',
    );

    foreach ($days_of_week as $key => $day) {
        woocommerce_wp_checkbox([
            'id'          => "_custom_time_slot_{$key}_available",
            'label'       => __("Available on $day", 'woocommerce'),
            'description' => __('Check this box to indicate the product is available on this day.'),
            'value'       => get_post_meta($post->ID, "_custom_time_slot_{$key}_available", true) ? 'yes' : 'no',
        ]);

        woocommerce_wp_textarea_input([
            'id'          => "_custom_time_slot_{$key}_times",
            'label'       => __("Enter Time Slots for $day (comma separated)", 'woocommerce'),
            'placeholder' => '08:30 AM, 09:30 AM, 11:30 AM, 02:00 PM',
            'value'       => get_post_meta($post->ID, "_custom_time_slot_{$key}_times", true),
        ]);
    }
}

add_action('woocommerce_product_options_general_product_data', 'add_custom_product_time_slots');

// Save custom time slot fields
function save_custom_product_time_slots($post_id) {
    $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    
    foreach ($days_of_week as $key) {
        $time_slot_available = isset($_POST["_custom_time_slot_{$key}_available"]) ? 'yes' : 'no';
        update_post_meta($post_id, "_custom_time_slot_{$key}_available", $time_slot_available);

        $time_slots = isset($_POST["_custom_time_slot_{$key}_times"]) ? sanitize_textarea_field($_POST["_custom_time_slot_{$key}_times"]) : '';
        update_post_meta($post_id, "_custom_time_slot_{$key}_times", $time_slots);
    }
}

add_action('woocommerce_process_product_meta', 'save_custom_product_time_slots');

// Display custom time slots on single product page
function display_custom_time_slots_on_single_product() {
    if (is_product()) {
        $current_date = date('Y-m-d');
        echo '<label for="product_date">Select Appointment Date:</label>';
        echo '<input type="date" id="product_date" name="product_date" onchange="showTimeSlots()" min="' . $current_date . '">';
        echo '<div id="time_slots_container"></div>';
    }
}
add_action('woocommerce_single_product_summary', 'display_custom_time_slots_on_single_product', 15);

// Add custom date and time slot to order review
function add_custom_time_slot_to_order_review($order_id) {
    if (!empty($_POST['product_date']) && !empty($_POST['product_time_slot'])) {
        $order = wc_get_order($order_id);
        $order->add_meta_data(__('Appointment Date', 'woocommerce'), sanitize_text_field($_POST['product_date']));
        $order->add_meta_data(__('Time Slot', 'woocommerce'), sanitize_text_field($_POST['product_time_slot']));
        $order->save();
    }
}
add_action('woocommerce_checkout_update_order_meta', 'add_custom_time_slot_to_order_review');

// Display custom fields on the order details page
function display_custom_time_slot_on_order($order) {
    $appointment_date = $order->get_meta(__('Appointment Date', 'woocommerce'));
    $time_slot = $order->get_meta(__('Time Slot', 'woocommerce'));

    if ($appointment_date && $time_slot) {
        echo '<p><strong>' . __('Appointment Date:', 'woocommerce') . '</strong> ' . esc_html($appointment_date) . '</p>';
        echo '<p><strong>' . __('Time Slot:', 'woocommerce') . '</strong> ' . esc_html($time_slot) . '</p>';
    }
}
add_action('woocommerce_order_details_after_order_table', 'display_custom_time_slot_on_order');

// Display custom fields in the email
function display_custom_time_slot_in_email($order, $sent_to_admin, $plain_text, $email) {
    $appointment_date = $order->get_meta(__('Appointment Date', 'woocommerce'));
    $time_slot = $order->get_meta(__('Time Slot', 'woocommerce'));

    if ($appointment_date && $time_slot) {
        echo '<p><strong>' . __('Appointment Date:', 'woocommerce') . '</strong> ' . esc_html($appointment_date) . '</p>';
        echo '<p><strong>' . __('Time Slot:', 'woocommerce') . '</strong> ' . esc_html($time_slot) . '</p>';
    }
}
add_action('woocommerce_email_customer_details', 'display_custom_time_slot_in_email', 20, 4);


add_action('woocommerce_product_options_general_product_data', 'add_custom_product_id_field');

function add_custom_product_id_field() {
    $product_id = get_the_ID();
    $custom_product_id = get_post_meta($product_id, '_custom_product_id', true);
    error_log("Custom Product ID: " . print_r($custom_product_id, true)); // This will log the custom product ID in debug.log
    
    woocommerce_wp_text_input( array(
        'id' => '_custom_product_id',
        'label' => __('Custom Product ID', 'woocommerce'),
        'desc_tip' => 'true',
        'description' => __('Enter the custom product ID for this product.', 'woocommerce')
    ));
}

add_action('woocommerce_process_product_meta', 'save_custom_product_id_field');

function save_custom_product_id_field($post_id) {
    if (isset($_POST['_custom_product_id'])) {
        // Save the custom product ID as a product meta
        update_post_meta($post_id, '_custom_product_id', sanitize_text_field($_POST['_custom_product_id']));
    }
}

function enqueue_custom_time_slot_script() {
    wp_enqueue_script('custom-time-slot-script', get_stylesheet_directory_uri() . '/assets/js/custom-time-slot.js', array('jquery'), null, true);
    wp_enqueue_style('custom-time-slot-style', get_stylesheet_directory_uri() . '/assets/css/time-slot.css', array(), null, 'all');
    
    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), null, true);
    wp_enqueue_script('custom-flatpickr-init', get_stylesheet_directory_uri() . '/assets/js/custom-flatpickr-init.js', array('flatpickr-js'), null, true);

    $available_slots = [];
    $booked_slots = [];
    $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    
    foreach ($days_of_week as $key) {
        $is_available = get_post_meta(get_the_ID(), "_custom_time_slot_{$key}_available", true);
        $time_slots = get_post_meta(get_the_ID(), "_custom_time_slot_{$key}_times", true);

        if ($is_available === 'yes' && !empty($time_slots)) {
            $available_slots[$key] = array_map('trim', explode(',', $time_slots));
        }

        // Fetch the booked slots
        $args = array(
            'status' => array('completed', 'processing'),
            'limit' => -1,
            'return' => 'ids',
        );
        $orders = wc_get_orders($args);

        foreach ($orders as $order_id) {
            $order = wc_get_order($order_id);
            foreach ($order->get_items() as $item_id => $item) {
                if ($item->get_product_id() == get_the_ID()) {
                    $appointment_date = wc_get_order_item_meta($item_id, 'Appointment Date', true);
                    $time_slot = wc_get_order_item_meta($item_id, 'Time Slot', true);

                    if ($appointment_date && $time_slot) {
                        // Store booked slots by date
                        $booked_slots[$appointment_date][] = $time_slot;
                    }
                }
            }
        }
    }

    // Pass the available and booked slots to JS
    wp_localize_script('custom-time-slot-script', 'available_time_slots', $available_slots);
    wp_localize_script('custom-time-slot-script', 'booked_time_slots', $booked_slots);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_time_slot_script');



// Register custom "Location" taxonomy for products
function create_locations_taxonomy() {
    $labels = array(
        'name'              => 'Locations',
        'singular_name'     => 'Location',
        'search_items'      => 'Search Locations',
        'all_items'         => 'All Locations',
        'parent_item'       => 'Parent Location',
        'parent_item_colon' => 'Parent Location:',
        'edit_item'         => 'Edit Location',
        'update_item'       => 'Update Location',
        'add_new_item'      => 'Add New Location',
        'new_item_name'     => 'New Location Name',
        'menu_name'         => 'Locations',
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'location'),
    );

    register_taxonomy('location', 'product', $args);
}
add_action('init', 'create_locations_taxonomy', 0);

// Display location dropdown on the single product page
function display_location_dropdown_on_product_page() {
    global $post;
    
    $locations = get_the_terms($post->ID, 'location');
    
    if ($locations && !is_wp_error($locations)) {
        echo '<label for="product_location">' . __('Select - Hospital Location *', 'woocommerce') . '</label>';
        echo '<select id="product_location" name="product_location">';
        echo '<option value="">' . __('Select a location', 'woocommerce') . '</option>';
        
        foreach ($locations as $location) {
            echo '<option value="' . esc_attr($location->term_id) . '">' . esc_html($location->name) . '</option>';
        }

        echo '</select>';
    }
}
add_action('woocommerce_single_product_summary', 'display_location_dropdown_on_product_page', 17);

// Redirect to checkout after adding to cart
add_filter('add_to_cart_redirect', 'redirect_to_checkout');
function redirect_to_checkout() {
    global $woocommerce;
    $checkout_url = $woocommerce->cart->get_checkout_url();
    return $checkout_url;
}

// Add custom time slot to cart
function add_custom_time_slot_to_cart($cart_item_data, $product_id) {
    if (isset($_POST['product_date']) && isset($_POST['product_time_slot']) && isset($_POST['product_location'])) {
        $cart_item_data['product_date'] = sanitize_text_field($_POST['product_date']);
        $cart_item_data['product_time_slot'] = sanitize_text_field($_POST['product_time_slot']);
        $cart_item_data['product_location'] = sanitize_text_field($_POST['product_location']);
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_custom_time_slot_to_cart', 10, 2);

// Display custom time slot on checkout order review
function display_custom_time_slot_on_checkout_order_review($order) {
    $items = $order->get_items();
    
    foreach ($items as $item_id => $item) {
        $product_date = wc_get_order_item_meta($item_id, 'product_date', true);
        $product_location = wc_get_order_item_meta($item_id, 'product_location', true);
        $time_slot = wc_get_order_item_meta($item_id, 'product_time_slot', true);
        
        if ($product_date && $product_location && $time_slot) {
            echo '<p><strong>' . __('Appointment Date:', 'woocommerce') . '</strong> ' . esc_html($product_date) . '</p>';
            echo '<p><strong>' . __('Location:', 'woocommerce') . '</strong> ' . esc_html($product_location) . '</p>';
            echo '<p><strong>' . __('Time Slot:', 'woocommerce') . '</strong> ' . esc_html($time_slot) . '</p>';
        }
    }
}
add_action('woocommerce_checkout_order_review', 'display_custom_time_slot_on_checkout_order_review', 10, 2);

// Custom JS to modify button names
function custom_js_remove_fields() {
    ?>
    <style>
        .wc-block-components-address-form__country, .wc-block-components-address-form__address_2-toggle {
            display: none !important;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ensure the elements exist before manipulating them
            const checkButtonText = setInterval(function () {
                const buttonText = document.querySelector('.wc-block-components-checkout-place-order-button__text');
                const linkText = document.querySelector('.wc-block-components-checkout-return-to-cart-button');
                const phonePeLabel = document.querySelector('#radio-control-wc-payment-method-options-phonepe__label');
                
                if (buttonText && linkText && phonePeLabel) {
                    // Update text content
                    buttonText.textContent = 'Confirm your Appointment';
                    linkText.textContent = 'Manage Your Appointment';
                    phonePeLabel.textContent = 'Pay Online';
                    
                    // Clear interval once the elements are updated
                    clearInterval(checkButtonText);
                }
            }, 1200);  // Check every 100ms for the elements

        });
    </script>
    <?php
}
add_action('wp_footer', 'custom_js_remove_fields');


// Change cart button text
function change_cart_btn_name() {
    ?>
    <script type="text/javascript">
        setTimeout(function() {
            const submitButton = document.querySelector('.wc-block-cart__submit-container a');
            const buttonText = submitButton.querySelector('.wc-block-components-button__text');
            buttonText.textContent = 'Proceed To Book an Appointment';
        }, 800); 
    </script>
    <?php
}
add_action('wp_footer', 'change_cart_btn_name');


// Add phone validation script
function add_phone_validation_script() {
    if (is_checkout()) :
    ?>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const phoneField = document.getElementById('billing-phone');
                const placeOrderButton = document.querySelector('.wc-block-components-button.wp-element-button.wc-block-components-checkout-place-order-button');
                const checkoutForm = document.querySelector('form');

                phoneField.setAttribute('placeholder', 'Enter a 10-digit phone number (e.g., 9876543210)');
                phoneField.setAttribute('pattern', '\\d{10}');
                
                placeOrderButton.classList.add('disabled-button');
                placeOrderButton.disabled = true;

                phoneField.addEventListener('input', function() {
                    if (phoneField.value.match(/^\d{10}$/)) { // Corrected the regular expression
                        placeOrderButton.classList.remove('disabled-button');
                        placeOrderButton.disabled = false;
                    } else {
                        placeOrderButton.classList.add('disabled-button');
                        placeOrderButton.disabled = true;
                    }
                });
            }, 1000);
        });
    </script>
    <?php
    endif;
}
add_action('wp_footer', 'add_phone_validation_script');

?>
