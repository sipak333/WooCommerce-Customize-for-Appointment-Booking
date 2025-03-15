<?php
/**
 * Template Name: All Woo Orders
 */
get_header();

// WooCommerce API credentials
$base_url = "https://unitedhealthcheckups.com/doctors/wp-json/wc/v3/orders";
$consumer_key = 'ck_e692287810ae35833ba8e14b17b1664f634e39d9';
$consumer_secret = 'cs_4c086640656603d9ea248e4ad95472fda197cff2';

$per_page = 100;

// Initialize an empty array to store all orders
$all_orders = array();

$page = 1;
$more_orders = true;

// Loop through pages until we've retrieved all orders
while ($more_orders) {
    // Build the URL with pagination parameters
    $store_url = $base_url . "?per_page={$per_page}&page={$page}";
    
    // Initialize cURL session
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $store_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, "$consumer_key:$consumer_secret");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    
    // Execute cURL and fetch the response
    $response = curl_exec($ch);
    
    if ($response === false) {
        echo "Error fetching order data: " . curl_error($ch);
        exit;
    }
    
    // Decode the JSON response
    $orders_page = json_decode($response, true);
    
    // If we got orders, add them to our array
    if (!empty($orders_page) && is_array($orders_page)) {
        $all_orders = array_merge($all_orders, $orders_page);
        
        // If we got fewer orders than the per_page limit, we've reached the end
        if (count($orders_page) < $per_page) {
            $more_orders = false;
        } else {
            // Move to the next page
            $page++;
        }
    } else {
        // No more orders or error
        $more_orders = false;
    }
}

// Prepare the data to send to the external API
$all_orders_data = [];
foreach ($all_orders as $order) {
    $appointment_details = '';
    $doctor_name = '';
    $appointment_date = '';
    $time_slot = '';
    $location = '';
    
    if (!empty($order['line_items'])) {
        $line_item = $order['line_items'][0]; // Get the first line item
        $doctor_name = $line_item['name'];

        // Extract appointment details from meta data
        if (!empty($line_item['meta_data'])) {
            foreach ($line_item['meta_data'] as $meta) {
                if ($meta['key'] == 'Appointment Date') {
                    $appointment_date = $meta['value'];
                }
                if ($meta['key'] == 'Time Slot' || $meta['key'] == 'Appointment Time') {
                    $time_slot = $meta['value'];
                }
                if ($meta['key'] == 'Location' || $meta['key'] == 'Select - Hospital Location') {
                    $location = $meta['value'];
                }
            }
        }
    }

    // Structure the data to send
    $all_orders_data[] = [
        'order_id' => $order['id'],
        'status' => ucfirst($order['status']),
        'total' => $order['total'] . ' ' . $order['currency'],
        'customer_name' => $order['billing']['first_name'] . ' ' . $order['billing']['last_name'],
        'appointment_details' => [
            'doctor_name' => $doctor_name,
            'appointment_date' => $appointment_date,
            'time_slot' => $time_slot,
            'location' => $location
        ],
        'payment_method' => $order['payment_method_title'],
        'billing_address' => $order['billing']['address_1'] . ', ' . $order['billing']['city'] . ', ' . $order['billing']['state'] . ', ' . $order['billing']['country'],
        'phone_number' => $order['billing']['phone'],
        'date_created' => date('Y-m-d H:i:s', strtotime($order['date_created']))
    ];
}

// External API URL to send the data
$external_api_url = 'https://your-external-api.com/endpoint';

// Send the all orders data to the external API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $external_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($all_orders_data)); // Send data as JSON
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
));

$response = curl_exec($ch);
curl_close($ch);

// Check for success
if ($response) {
    echo 'All orders data sent successfully!';
} else {
    echo 'Error sending data to external API.';
}

// Count total orders retrieved
$total_orders = count($all_orders);

if (!empty($all_orders)) {
    ?>
    <div class="container mx-auto py-4">
        <h1 class="text-2xl font-bold mb-4">All Appointments (<?php echo $total_orders; ?> total)</h1>

        <table border="1" cellpadding="10" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Customer Name</th>
                    <th>Appointment Details</th>
                    <th>Payment Method</th>
                    <th>Billing Address</th>
                    <th>Phone Number</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($all_orders as $order) {
                    $order_id = $order['id'];
                    $status = ucfirst($order['status']);
                    $total = $order['total'] . ' ' . $order['currency'];
                    $date_created = date('Y-m-d H:i:s', strtotime($order['date_created']));
                    $customer_name = $order['billing']['first_name'] . ' ' . $order['billing']['last_name'];
                    $payment_method = $order['payment_method_title'];
                    $billing_address = $order['billing']['address_1'] . ', ' . $order['billing']['city'] . ', ' . $order['billing']['state'] . ', ' . $order['billing']['country'];
                    $customer_number = $order['billing']['phone'];
                    // Get doctor name and appointment details from line items
                    $doctor_name = '';
                    $appointment_date = '';
                    $time_slot = '';
                    $location = '';
                    
                    if (!empty($order['line_items'])) {
                        $line_item = $order['line_items'][0]; // Get the first line item
                        $doctor_name = $line_item['name'];
                        
                        // Extract appointment details from meta data
                        if (!empty($line_item['meta_data'])) {
                            foreach ($line_item['meta_data'] as $meta) {
                                if ($meta['key'] == 'Appointment Date') {
                                    $appointment_date = $meta['value'];
                                }
                                if ($meta['key'] == 'Time Slot' || $meta['key'] == 'Appointment Time') {
                                    $time_slot = $meta['value'];
                                }
                                if ($meta['key'] == 'Location' || $meta['key'] == 'Select - Hospital Location') {
                                    $location = $meta['value'];
                                }
                            }
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo $order_id; ?></td>
                        <td><?php echo $status; ?></td>
                        <td><?php echo $total; ?></td>
                        <td><?php echo $customer_name; ?></td>
                        <td>
                            <strong><?php echo $doctor_name; ?></strong><br>
                            <?php if (!empty($appointment_date)): ?>
                                <strong>Appointment Date:</strong> <?php echo $appointment_date; ?><br>
                            <?php endif; ?>
                            <?php if (!empty($time_slot)): ?>
                                <strong>Time Slot:</strong> <?php echo $time_slot; ?><br>
                            <?php endif; ?>
                            <?php if (!empty($location)): ?>
                                <strong>Location:</strong> <?php echo $location; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $payment_method; ?></td>
                        <td><?php echo $billing_address; ?></td>
                        <td><?php echo $customer_number; ?></td>
                        <td><?php echo $date_created; ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
} else {
    echo "No orders found.";
}

get_footer(); // Get the WordPress footer
?>