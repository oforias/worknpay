<?php
/**
 * Payment Class
 * Handles payment records and escrow management
 */

require_once(__DIR__ . '/../settings/db_class.php');

class payment_class extends db_connection {
    
    /**
     * Record a payment for a booking
     * 
     * @param int $booking_id Booking ID
     * @param float $amount Total payment amount
     * @param string $payment_method Payment method (paystack, mobile_money, etc.)
     * @param string $transaction_ref Payment gateway reference
     * @param string $payment_status Payment status (completed, pending, failed)
     * @param float $customer_commission Commission from customer (7%)
     * @param float $worker_commission Commission from worker (5%)
     * @param float $worker_payout Amount to be paid to worker
     * @return int|false Payment ID on success, false on failure
     */
    public function record_payment($booking_id, $amount, $payment_method, $transaction_ref, $payment_status = 'completed', $customer_commission = 0, $worker_commission = 0, $worker_payout = 0) {
        $booking_id = (int)$booking_id;
        $amount = (float)$amount;
        $payment_method = mysqli_real_escape_string($this->db_conn(), $payment_method);
        $transaction_ref = mysqli_real_escape_string($this->db_conn(), $transaction_ref);
        $payment_status = mysqli_real_escape_string($this->db_conn(), $payment_status);
        $customer_commission = (float)$customer_commission;
        $worker_commission = (float)$worker_commission;
        $worker_payout = (float)$worker_payout;
        
        $sql = "INSERT INTO payments (
                    booking_id, 
                    amount, 
                    payment_method, 
                    transaction_reference, 
                    payment_status,
                    escrow_status,
                    customer_commission,
                    worker_commission,
                    worker_payout,
                    payment_date
                ) VALUES (
                    $booking_id, 
                    $amount, 
                    '$payment_method', 
                    '$transaction_ref', 
                    '$payment_status',
                    'held',
                    $customer_commission,
                    $worker_commission,
                    $worker_payout,
                    NOW()
                )";
        
        if ($this->db_query($sql)) {
            return mysqli_insert_id($this->db_conn());
        }
        
        return false;
    }
    
    /**
     * Get payment by booking ID
     * 
     * @param int $booking_id Booking ID
     * @return array|false Payment data or false
     */
    public function get_payment_by_booking($booking_id) {
        $booking_id = (int)$booking_id;
        
        $sql = "SELECT * FROM payments WHERE booking_id = $booking_id LIMIT 1";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Get payment by transaction reference
     * 
     * @param string $transaction_ref Transaction reference
     * @return array|false Payment data or false
     */
    public function get_payment_by_reference($transaction_ref) {
        $transaction_ref = mysqli_real_escape_string($this->db_conn(), $transaction_ref);
        
        $sql = "SELECT * FROM payments WHERE transaction_reference = '$transaction_ref' LIMIT 1";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Update payment status
     * 
     * @param int $payment_id Payment ID
     * @param string $status New status
     * @return bool Success status
     */
    public function update_payment_status($payment_id, $status) {
        $payment_id = (int)$payment_id;
        $status = mysqli_real_escape_string($this->db_conn(), $status);
        
        $sql = "UPDATE payments SET payment_status = '$status' WHERE payment_id = $payment_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Update escrow status
     * 
     * @param int $payment_id Payment ID
     * @param string $status New escrow status (held, released, refunded)
     * @return bool Success status
     */
    public function update_escrow_status($payment_id, $status) {
        $payment_id = (int)$payment_id;
        $status = mysqli_real_escape_string($this->db_conn(), $status);
        
        $sql = "UPDATE payments SET escrow_status = '$status' WHERE payment_id = $payment_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Set auto-release date for escrow (24 hours after completion)
     * 
     * @param int $payment_id Payment ID
     * @return bool Success status
     */
    public function set_auto_release_date($payment_id) {
        $payment_id = (int)$payment_id;
        
        $sql = "UPDATE payments 
                SET auto_release_date = DATE_ADD(NOW(), INTERVAL 24 HOUR) 
                WHERE payment_id = $payment_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Get payments ready for auto-release
     * 
     * @return array Payments ready for release
     */
    public function get_payments_for_auto_release() {
        $sql = "SELECT p.*, b.worker_id 
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                WHERE p.escrow_status = 'held'
                AND p.auto_release_date IS NOT NULL
                AND p.auto_release_date <= NOW()
                AND NOT EXISTS (
                    SELECT 1 FROM disputes d 
                    WHERE d.booking_id = b.booking_id 
                    AND d.dispute_status = 'open'
                )";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Release escrow to worker
     * 
     * @param int $payment_id Payment ID
     * @param int $worker_id Worker ID
     * @return bool Success status
     */
    public function release_escrow($payment_id, $worker_id) {
        $payment_id = (int)$payment_id;
        $worker_id = (int)$worker_id;
        
        // Get payment details
        $payment = $this->db_fetch_one("SELECT worker_payout FROM payments WHERE payment_id = $payment_id");
        
        if (!$payment) {
            return false;
        }
        
        $worker_payout = (float)$payment['worker_payout'];
        
        // Start transaction
        $this->db_query("START TRANSACTION");
        
        try {
            // Update escrow status
            $sql1 = "UPDATE payments SET escrow_status = 'released', released_date = NOW() WHERE payment_id = $payment_id";
            if (!$this->db_query($sql1)) {
                throw new Exception("Failed to update payment status");
            }
            
            // Add to worker balance
            $sql2 = "UPDATE worker_profiles SET available_balance = available_balance + $worker_payout WHERE user_id = $worker_id";
            if (!$this->db_query($sql2)) {
                throw new Exception("Failed to update worker balance");
            }
            
            // Commit transaction
            $this->db_query("COMMIT");
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            $this->db_query("ROLLBACK");
            error_log("Escrow release error: " . $e->getMessage());
            return false;
        }
    }
}
?>
