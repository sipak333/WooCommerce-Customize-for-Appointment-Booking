<?php
// Reorder the elements on the product page
function reorder_product_page_elements() {

	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 50);
}
add_action('init', 'reorder_product_page_elements');


// Remove quantity input and default Add to Cart button from single product page
add_filter( 'woocommerce_product_single_add_to_cart_text',
'woocommerce_custom_single_add_to_cart_text', 9999);

function woocommerce_custom_single_add_to_cart_text() {
    return __( 'Book an Appointment', 'woocommerce' ); 
}
function wc_remove_all_quantity_fields( $return, $product ) {
    return true;
}
add_filter( 'woocommerce_is_sold_individually', 'wc_remove_all_quantity_fields', 10, 2 );


// Add custom time slots field to the product page
function add_custom_product_time_slots() {
    global $post;

    $days_of_week = array(
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
        'sunday' => 'Sunday',
    );

    foreach ($days_of_week as $key => $day) {
        woocommerce_wp_checkbox(
            array(
                'id' => "_custom_time_slot_{$key}_available",
                'label' => __("Available on $day", 'woocommerce'),
                'description' => __('Check this box to indicate the product is available on this day.'),
                'value' => get_post_meta($post->ID, "_custom_time_slot_{$key}_available", true) ? 'yes' : 'no',
            )
        );

        woocommerce_wp_textarea_input(
            array(
                'id' => "_custom_time_slot_{$key}_times",
                'label' => __("Enter Time Slots for $day (comma separated)", 'woocommerce'),
                'placeholder' => '08:30 AM, 09:30 AM, 11:30 AM, 02:00 PM',
                'value' => get_post_meta($post->ID, "_custom_time_slot_{$key}_times", true),
            )
        );
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

// Enqueue necessary scripts and styles for time slot functionality
function enqueue_custom_time_slot_script() {

    // Enqueue custom time slot CSS and JS for the child theme
    wp_enqueue_style('custom-time-slot-style', get_stylesheet_directory_uri() . '/assets/css/time-slot.css', array(), null, 'all');
    wp_enqueue_script('custom-time-slot-script', get_stylesheet_directory_uri() . '/assets/js/custom-time-slot.js', array('jquery'), null, true);

    // Enqueue Flatpickr (date picker) CSS and JS from CDN
    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), null, true);

    // Enqueue custom Flatpickr initialization JS from child theme
    wp_enqueue_script('custom-flatpickr-init', get_stylesheet_directory_uri() . '/assets/js/custom-flatpickr-init.js', array('flatpickr-js'), null, true);



    
    $days_of_week = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    $available_slots = [];

    foreach ($days_of_week as $key) {
        $is_available = get_post_meta(get_the_ID(), "_custom_time_slot_{$key}_available", true);
        $time_slots = get_post_meta(get_the_ID(), "_custom_time_slot_{$key}_times", true);
        
        if ($is_available === 'yes' && !empty($time_slots)) {
            $available_slots[$key] = explode(',', $time_slots); // Store time slots by day
        }
    }

    wp_localize_script('custom-time-slot-script', 'available_time_slots', $available_slots);
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

//redirect
add_filter ('add_to_cart_redirect', 'redirect_to_checkout');
function redirect_to_checkout() {
    global $woocommerce;
    $checkout_url = $woocommerce->cart->get_checkout_url();
    return $checkout_url;
}

//add-to-cart
function add_custom_time_slot_to_cart($cart_item_data, $product_id) {
    if (isset($_POST['product_date']) && isset($_POST['product_time_slot']) && isset($_POST['product_location'])) {
        $cart_item_data['product_date'] = sanitize_text_field($_POST['product_date']);
        $cart_item_data['product_time_slot'] = sanitize_text_field($_POST['product_time_slot']);
        $cart_item_data['product_location'] = sanitize_text_field($_POST['product_location']);
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_custom_time_slot_to_cart', 10, 2);

//order summary
function display_custom_time_slot_on_checkout_order_review($order) {
    $items = $order->get_items();
    
    foreach ($items as $item_id => $item) {
        // Get custom fields data from order items
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



function custom_js_remove_fields() {
    ?>
     <style>
        /* Hide City and PIN Code fields */
        .wc-block-components-address-form__city,
        .wc-block-components-address-form__postcode,
        .wc-block-components-address-form__country{
            display: none !important;
        }

 
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Run the JS code after 2 seconds
            setTimeout(function() {
                // Remove City field and its label
                const cityField = document.querySelector('#billing-city');
                const cityLabel = document.querySelector('label[for="billing-city"]');
                if (cityField && cityLabel) {
                    cityField.remove();
                    cityLabel.remove();
                }
                
               
                
                // Remove PIN Code field and its label
                const postcodeField = document.querySelector('#billing-postcode');
                const postcodeLabel = document.querySelector('label[for="billing-postcode"]');
                if (postcodeField && postcodeLabel) {
                    postcodeField.remove();
                    postcodeLabel.remove();
                }

                // Remove Apartment/Suite field and its label
                const apartmentField = document.querySelector('#billing-address_2');
                const apartmentLabel = document.querySelector('label[for="billing-address_2"]');
                if (apartmentField && apartmentLabel) {
                    apartmentField.remove();
                    apartmentLabel.remove();
                }

                // Remove "+ Add apartment, suite, etc." span
                const apartmentToggle = document.querySelector('.wc-block-components-address-form__address_2-toggle');
                if (apartmentToggle) {
                    apartmentToggle.remove();
                }
                
                const billingPhone = document.querySelector('#billing-phone');
                billingPhone.setAttribute('required', 'true');
                const label = document.querySelector('label[for="billing-phone"]');
                label.textContent = 'Enter your phone number *';

                const buttonText = document.querySelector('.wc-block-components-checkout-place-order-button__text');
                buttonText.textContent = 'Make Payment and Book';
                
                const linkText = document.querySelector('.wc-block-components-checkout-return-to-cart-button');
                linkText.textContent = 'Manage Your Appointment';


            }, 1100); // 2000ms = 2 seconds
        });
    </script>
    <?php
}
add_action('wp_footer', 'custom_js_remove_fields');

function change_cart_btn_name() {
        ?>
        <script type="text/javascript">
            setTimeout(function() {
                const submitButton = document.querySelector('.wc-block-cart__submit-container a');
                const buttonText = submitButton.querySelector('.wc-block-components-button__text');
                buttonText.textContent = 'Proceed To Book an Appointment';
            }, 800); // Delay of 2000 milliseconds (2 seconds)
        </script>
        <?php
    
}
add_action('wp_footer', 'change_cart_btn_name');



// //Hrushikes Code














?>