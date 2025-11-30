<?php
/**
 * Payout Class
 * Handles worker payout operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class payout_class extends db_connection {
    
    /**
     * Create a new payout request
     */
    public function create_payout($worker_id, $amount, $payout_type, $payout_account_id, $fee, $net_amount) {
        $worker_id = intval($worker_id);
        $amount = floatval($amount);
        $fee = floatval($fee);
        $net_amount = floatval($net_amount);
        $payout_account_id = intval($payout_account_id);
        $payout_type = mysqli_real_escape_string($this->db_conn(), $payout_type);
        
        $query = "INSERT INTO payouts (worker_id, amount, payout_type, payout_fee, net_amount, payout_status, requested_at) 
                  VALUES ($worker_id, $amount, '$payout_type', $fee, $net_amount, 'pending', NOW())";
        
        return $this->db_query($query);
    }
    
    /**
     * Get all payouts for a worker
     */
    public function get_worker_payouts($worker_id) {
        $worker_id = intval($worker_id);
        
        $query = "SELECT * FROM payouts 
                  WHERE worker_id = $worker_id 
                  ORDER BY requested_at DESC";
        
        return $this->db_fetch_all($query);
    }
    
    /**
     * Get pending payouts for admin
     */
    public function get_pending_payouts() {
        $query = "SELECT p.*, u.user_name, u.user_email, u.user_phone 
                  FROM payouts p
                  JOIN users u ON p.worker_id = u.user_id
                  WHERE p.payout_status = 'pending'
                  ORDER BY 
                    CASE WHEN p.payout_type = 'instant' THEN 0 ELSE 1 END,
                    p.requested_at ASC";
        
        return $this->db_fetch_all($query);
    }
    
    /**
     * Update payout status
     */
    public function update_payout_status($payout_id, $status, $reference = null) {
        $payout_id = intval($payout_id);
        $status = mysqli_real_escape_string($this->db_conn(), $status);
        
        $timestamp_field = $status === 'completed' ? 'completed_at' : 'processed_at';
        
        if ($reference) {
            $reference = mysqli_real_escape_string($this->db_conn(), $reference);
            $query = "UPDATE payouts 
                      SET payout_status = '$status', 
                          payout_reference = '$reference',
                          $timestamp_field = NOW()
                      WHERE payout_id = $payout_id";
        } else {
            $query = "UPDATE payouts 
                      SET payout_status = '$status',
                          $timestamp_field = NOW()
                      WHERE payout_id = $payout_id";
        }
        
        return $this->db_query($query);
    }
    
    /**
     * Get payout by ID
     */
    public function get_payout_by_id($payout_id) {
        $payout_id = intval($payout_id);
        
        $query = "SELECT p.*, u.user_name, u.user_email, u.user_phone 
                  FROM payouts p
                  JOIN users u ON p.worker_id = u.user_id
                  WHERE p.payout_id = $payout_id";
        
        return $this->db_fetch_one($query);
    }
}
?>
