<?php
/**
 * Booking Controller
 * Business logic for booking operations
 */

require_once(__DIR__ . '/../classes/booking_class.php');

/**
 * Create a new booking
 */
function create_booking_ctr($customer_id, $worker_id, $service_id, $booking_date, $booking_time, 
                            $service_address, $estimated_price, $customer_notes = null)
{
    $booking = new Booking();
    return $booking->create_booking($customer_id, $worker_id, $service_id, $booking_date, 
                                   $booking_time, $service_address, $estimated_price, $customer_notes);
}

/**
 * Get booking by ID
 */
function get_booking_by_id_ctr($booking_id)
{
    $booking = new Booking();
    return $booking->get_booking_by_id($booking_id);
}

/**
 * Get customer bookings
 */
function get_customer_bookings_ctr($customer_id, $status = null)
{
    $booking = new Booking();
    return $booking->get_customer_bookings($customer_id, $status);
}

/**
 * Get worker bookings
 */
function get_worker_bookings_ctr($worker_id, $status = null)
{
    $booking = new Booking();
    return $booking->get_worker_bookings($worker_id, $status);
}

/**
 * Update booking status
 */
function update_booking_status_ctr($booking_id, $status)
{
    $booking = new Booking();
    return $booking->update_booking_status($booking_id, $status);
}

/**
 * Update payment status
 */
function update_payment_status_ctr($booking_id, $payment_status)
{
    $booking = new Booking();
    return $booking->update_payment_status($booking_id, $payment_status);
}

/**
 * Complete booking
 */
function complete_booking_ctr($booking_id, $final_price = null)
{
    $booking = new Booking();
    return $booking->complete_booking($booking_id, $final_price);
}

/**
 * Cancel booking
 */
function cancel_booking_ctr($booking_id)
{
    $booking = new Booking();
    return $booking->cancel_booking($booking_id);
}

/**
 * Update booking status with validation
 */
function update_booking_status_validated_ctr($booking_id, $new_status, $worker_id)
{
    $booking = new Booking();
    
    // Verify worker owns this booking
    if (!$booking->verify_worker_ownership($booking_id, $worker_id)) {
        return ['success' => false, 'message' => 'Unauthorized: Booking does not belong to you'];
    }
    
    // Update with validation
    if ($booking->update_booking_status_validated($booking_id, $new_status)) {
        return ['success' => true, 'message' => 'Booking status updated successfully'];
    }
    
    return ['success' => false, 'message' => 'Invalid status transition or update failed'];
}

/**
 * Add completion photo
 */
function add_completion_photo_ctr($booking_id, $photo_url)
{
    $booking = new Booking();
    return $booking->add_completion_photo($booking_id, $photo_url);
}

/**
 * Verify worker owns booking
 */
function verify_worker_booking_ctr($booking_id, $worker_id)
{
    $booking = new Booking();
    return $booking->verify_worker_ownership($booking_id, $worker_id);
}

/**
 * Verify customer owns booking
 */
function verify_customer_booking_ctr($booking_id, $customer_id)
{
    $booking = new Booking();
    return $booking->verify_customer_ownership($booking_id, $customer_id);
}
?>
