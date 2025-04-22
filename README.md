# wc-booking-prodoc-sync

A custom WooCommerce plugin to sync appointment booking orders with the Prodoc dashboard using their API.

## Features

- Sync WooCommerce order data to the Prodoc appointment booking system via API.
- Custom booking setup based on doctor availability and selected location.
- Each product represents a doctor, matched via a custom `product_doctor_id` field.
- Custom time slots and booking dates managed on the product page.
- WooCommerce single product page is customized for day-based booking.
- Custom fields added to products:
  - Doctor ID
  - Time Slots (based on availability)
- Checkout page UI customized:
  - Default WooCommerce buttons renamed.
  - Custom booking details saved as order metadata.
- Booking details sent via:
  - WooCommerce order emails.
  - Prodoc API on order creation.

## Requirements Tested Till

- WordPress (5.0+)
- WooCommerce (4.0+)
- Access to the Prodoc API

## Installation

1. Download or clone this repository.
2. Upload the plugin to your WordPress `/wp-content/plugins/` directory.
3. Activate the plugin through the WordPress admin panel.
4. Configure your product settings:
   - Add `product_doctor_id` and available `time_slots` as custom fields.
5. Set up Prodoc API credentials in the plugin settings (if applicable).

## How It Works

1. Customer selects a doctor (product) and an available day/time.
2. On checkout, selected booking info is saved as custom order meta.
3. Once the order is placed:
   - Booking data is sent to the Prodoc dashboard via API.
   - Confirmation email includes booking details.

## Notes

- Make sure doctor time slots and IDs match with Prodoc system.
- API endpoint and authentication setup may vary depending on your Prodoc configuration.

