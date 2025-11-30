<?php
/**
 * Cart Controller (Stub)
 * This is a service marketplace application, not an e-commerce cart system.
 * This file exists to prevent errors from legacy code references.
 * 
 * Note: WorkNPay uses a booking system, not a cart system.
 * For booking payments, use: actions/booking_payment_init.php and actions/process_booking_payment.php
 */

/**
 * Get user cart items (stub - returns empty array)
 * @param int $customer_id Customer ID
 * @return array Empty array
 */
function get_user_cart_ctr($customer_id) {
    // This application uses bookings, not carts
    // Return empty array to prevent errors
    return [];
}

/**
 * Add item to cart (stub - not used in booking system)
 * @param int $customer_id Customer ID
 * @param int $product_id Product ID
 * @param int $qty Quantity
 * @return bool Always returns false
 */
function add_to_cart_ctr($customer_id, $product_id, $qty) {
    // Not applicable to booking system
    return false;
}

/**
 * Remove item from cart (stub - not used in booking system)
 * @param int $customer_id Customer ID
 * @param int $product_id Product ID
 * @return bool Always returns false
 */
function remove_from_cart_ctr($customer_id, $product_id) {
    // Not applicable to booking system
    return false;
}

/**
 * Clear cart (stub - not used in booking system)
 * @param int $customer_id Customer ID
 * @return bool Always returns false
 */
function clear_cart_ctr($customer_id) {
    // Not applicable to booking system
    return false;
}

?>

