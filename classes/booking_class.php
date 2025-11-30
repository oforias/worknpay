<?php
/**
 * Booking Class
 * Handles all booking-related database operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Booking extends db_connection
{
    /**
     * Create a new booking
     */
    public function create_booking($customer_id, $worker_id, $service_id, $booking_date, $booking_time, 
                                   $service_address, $estimated_price, $customer_notes = null)
    {
        // Generate unique booking reference
        $booking_reference = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        $customer_id = (int) $customer_id;
        $worker_id = (int) $worker_id;
        // Handle NULL service_id properly - don't cast NULL to 0
        $service_id = ($service_id !== null) ? (int) $service_id : 'NULL';
        $booking_date = $this->db_escape($booking_date);
        $booking_time = $this->db_escape($booking_time);
        $service_address = $this->db_escape($service_address);
        $estimated_price = (float) $estimated_price;
        $customer_notes = $customer_notes ? "'" . $this->db_escape($customer_notes) . "'" : 'NULL';
        
        $sql = "INSERT INTO bookings (customer_id, worker_id, service_id, booking_reference, 
                booking_date, booking_time, service_address, customer_notes, estimated_price, 
                booking_status, payment_status) 
                VALUES ($customer_id, $worker_id, $service_id, '$booking_reference', 
                '$booking_date', '$booking_time', '$service_address', $customer_notes, 
                $estimated_price, 'pending', 'paid')";
        
        if ($this->db_write_query($sql)) {
            return [
                'booking_id' => $this->last_insert_id(),
                'booking_reference' => $booking_reference
            ];
        }
        
        return false;
    }
    
    /**
     * Get booking by ID
     */
    public function get_booking_by_id($booking_id)
    {
        $booking_id = (int) $booking_id;
        $sql = "SELECT b.*, u1.user_name as customer_name, u2.user_name as worker_name,
                s.service_title, s.base_price
                FROM bookings b
                JOIN users u1 ON b.customer_id = u1.user_id
                JOIN users u2 ON b.worker_id = u2.user_id
                LEFT JOIN services s ON b.service_id = s.service_id
                WHERE b.booking_id = $booking_id";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Get bookings by customer
     */
    public function get_customer_bookings($customer_id, $status = null)
    {
        $customer_id = (int) $customer_id;
        $sql = "SELECT b.*, u.user_name as worker_name, s.service_title
                FROM bookings b
                JOIN users u ON b.worker_id = u.user_id
                LEFT JOIN services s ON b.service_id = s.service_id
                WHERE b.customer_id = $customer_id";
        
        if ($status) {
            $status = $this->db_escape($status);
            $sql .= " AND b.booking_status = '$status'";
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Get bookings by worker
     */
    public function get_worker_bookings($worker_id, $status = null)
    {
        $worker_id = (int) $worker_id;
        $sql = "SELECT b.*, u.user_name as customer_name, u.user_phone as customer_phone,
                s.service_title
                FROM bookings b
                JOIN users u ON b.customer_id = u.user_id
                LEFT JOIN services s ON b.service_id = s.service_id
                WHERE b.worker_id = $worker_id";
        
        if ($status) {
            $status = $this->db_escape($status);
            $sql .= " AND b.booking_status = '$status'";
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Update booking status
     */
    public function update_booking_status($booking_id, $status)
    {
        $booking_id = (int) $booking_id;
        $status = $this->db_escape($status);
        
        $sql = "UPDATE bookings SET booking_status = '$status', updated_at = CURRENT_TIMESTAMP 
                WHERE booking_id = $booking_id";
        
        return $this->db_write_query($sql);
    }
    
    /**
     * Update payment status
     */
    public function update_payment_status($booking_id, $payment_status)
    {
        $booking_id = (int) $booking_id;
        $payment_status = $this->db_escape($payment_status);
        
        $sql = "UPDATE bookings SET payment_status = '$payment_status' 
                WHERE booking_id = $booking_id";
        
        return $this->db_write_query($sql);
    }
    
    /**
     * Set completion date
     */
    public function complete_booking($booking_id, $final_price = null)
    {
        $booking_id = (int) $booking_id;
        $final_price_sql = $final_price ? ", final_price = " . (float)$final_price : "";
        
        $sql = "UPDATE bookings 
                SET booking_status = 'completed', 
                    completion_date = CURRENT_TIMESTAMP
                    $final_price_sql
                WHERE booking_id = $booking_id";
        
        return $this->db_write_query($sql);
    }
    
    /**
     * Cancel booking
     */
    public function cancel_booking($booking_id)
    {
        return $this->update_booking_status($booking_id, 'cancelled');
    }
    
    /**
     * Validate status transition
     */
    public function validate_status_transition($current_status, $new_status)
    {
        $valid_transitions = [
            'pending' => ['accepted', 'rejected', 'cancelled'],
            'accepted' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => [],
            'rejected' => [],
            'cancelled' => []
        ];
        
        if (!isset($valid_transitions[$current_status])) {
            return false;
        }
        
        return in_array($new_status, $valid_transitions[$current_status]);
    }
    
    /**
     * Update booking status with validation
     */
    public function update_booking_status_validated($booking_id, $new_status)
    {
        // Get current status
        $booking = $this->get_booking_by_id($booking_id);
        if (!$booking) {
            return false;
        }
        
        // Validate transition
        if (!$this->validate_status_transition($booking['booking_status'], $new_status)) {
            error_log("Invalid status transition: {$booking['booking_status']} -> $new_status for booking $booking_id");
            return false;
        }
        
        // Update status
        return $this->update_booking_status($booking_id, $new_status);
    }
    
    /**
     * Add completion photo
     */
    public function add_completion_photo($booking_id, $photo_url)
    {
        $booking_id = (int) $booking_id;
        $photo_url = $this->db_escape($photo_url);
        
        // Get existing photos
        $booking = $this->get_booking_by_id($booking_id);
        $existing_photos = $booking['completion_photos'] ? json_decode($booking['completion_photos'], true) : [];
        
        // Add new photo
        $existing_photos[] = $photo_url;
        $photos_json = json_encode($existing_photos);
        $photos_json = $this->db_escape($photos_json);
        
        $sql = "UPDATE bookings 
                SET completion_photos = '$photos_json'
                WHERE booking_id = $booking_id";
        
        return $this->db_write_query($sql);
    }
    
    /**
     * Verify booking belongs to worker
     */
    public function verify_worker_ownership($booking_id, $worker_id)
    {
        $booking_id = (int) $booking_id;
        $worker_id = (int) $worker_id;
        
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE booking_id = $booking_id AND worker_id = $worker_id";
        
        $result = $this->db_fetch_one($sql);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Verify booking belongs to customer
     */
    public function verify_customer_ownership($booking_id, $customer_id)
    {
        $booking_id = (int) $booking_id;
        $customer_id = (int) $customer_id;
        
        $sql = "SELECT COUNT(*) as count FROM bookings 
                WHERE booking_id = $booking_id AND customer_id = $customer_id";
        
        $result = $this->db_fetch_one($sql);
        return $result && $result['count'] > 0;
    }
}
?>
