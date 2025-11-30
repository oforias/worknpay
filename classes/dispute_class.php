<?php
/**
 * Dispute Class
 * Handles dispute management operations
 */

require_once(__DIR__ . '/../settings/db_class.php');

class Dispute extends db_connection
{
    /**
     * Create a new dispute
     */
    public function create_dispute($booking_id, $customer_id, $worker_id, $reason, $description, $evidence_photos = null)
    {
        $booking_id = (int)$booking_id;
        $customer_id = (int)$customer_id;
        $worker_id = (int)$worker_id;
        $reason = $this->db_escape($reason);
        $description = $this->db_escape($description);
        $evidence_photos = $evidence_photos ? $this->db_escape($evidence_photos) : 'NULL';
        
        $sql = "INSERT INTO disputes 
                (booking_id, customer_id, worker_id, dispute_reason, dispute_description, evidence_photos, dispute_status, created_at)
                VALUES 
                ($booking_id, $customer_id, $worker_id, '$reason', '$description', '$evidence_photos', 'open', NOW())";
        
        return $this->db_query($sql);
    }
    
    /**
     * Get dispute by ID
     */
    public function get_dispute_by_id($dispute_id)
    {
        $dispute_id = (int)$dispute_id;
        
        $sql = "SELECT d.*, 
                b.booking_reference, b.estimated_price, b.booking_date,
                c.user_name as customer_name, c.user_email as customer_email,
                w.user_name as worker_name, w.user_email as worker_email,
                p.amount as payment_amount, p.escrow_status
                FROM disputes d
                JOIN bookings b ON d.booking_id = b.booking_id
                JOIN users c ON d.customer_id = c.user_id
                JOIN users w ON d.worker_id = w.user_id
                LEFT JOIN payments p ON b.booking_id = p.booking_id
                WHERE d.dispute_id = $dispute_id";
        
        return $this->db_fetch_one($sql);
    }
    
    /**
     * Get disputes for a booking
     */
    public function get_booking_disputes($booking_id)
    {
        $booking_id = (int)$booking_id;
        
        $sql = "SELECT * FROM disputes 
                WHERE booking_id = $booking_id 
                ORDER BY created_at DESC";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Check if dispute window is valid (48 hours)
     */
    public function validate_dispute_window($booking_id)
    {
        $booking_id = (int)$booking_id;
        
        $sql = "SELECT completion_date 
                FROM bookings 
                WHERE booking_id = $booking_id 
                AND booking_status = 'completed'
                AND completion_date >= DATE_SUB(NOW(), INTERVAL 48 HOUR)";
        
        $result = $this->db_fetch_one($sql);
        return $result !== false;
    }
    
    /**
     * Check if booking already has open dispute
     */
    public function has_open_dispute($booking_id)
    {
        $booking_id = (int)$booking_id;
        
        $sql = "SELECT dispute_id FROM disputes 
                WHERE booking_id = $booking_id 
                AND dispute_status IN ('open', 'under_review')";
        
        $result = $this->db_fetch_one($sql);
        return $result !== false;
    }
    
    /**
     * Get all disputes (for admin)
     */
    public function get_all_disputes($status = null)
    {
        $where = $status ? "WHERE d.dispute_status = '" . $this->db_escape($status) . "'" : "";
        
        $sql = "SELECT d.*, 
                b.booking_reference, b.estimated_price,
                c.user_name as customer_name,
                w.user_name as worker_name,
                p.amount as payment_amount, p.escrow_status
                FROM disputes d
                JOIN bookings b ON d.booking_id = b.booking_id
                JOIN users c ON d.customer_id = c.user_id
                JOIN users w ON d.worker_id = w.user_id
                LEFT JOIN payments p ON b.booking_id = p.booking_id
                $where
                ORDER BY d.created_at DESC";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Add worker response to dispute
     */
    public function add_worker_response($dispute_id, $response)
    {
        $dispute_id = (int)$dispute_id;
        $response = $this->db_escape($response);
        
        $sql = "UPDATE disputes 
                SET worker_response = '$response',
                    worker_response_date = NOW()
                WHERE dispute_id = $dispute_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Resolve dispute
     */
    public function resolve_dispute($dispute_id, $resolution, $outcome, $refund_amount, $resolved_by)
    {
        $dispute_id = (int)$dispute_id;
        $resolution = $this->db_escape($resolution);
        $outcome = $this->db_escape($outcome);
        $refund_amount = ($refund_amount !== null && $refund_amount !== '') ? (float)$refund_amount : 0;
        $resolved_by = (int)$resolved_by;
        
        $sql = "UPDATE disputes 
                SET dispute_status = 'resolved',
                    resolution = '$resolution',
                    resolution_outcome = '$outcome',
                    refund_amount = $refund_amount,
                    resolved_by = $resolved_by,
                    resolved_at = NOW()
                WHERE dispute_id = $dispute_id";
        
        return $this->db_query($sql);
    }
    
    /**
     * Get worker disputes
     */
    public function get_worker_disputes($worker_id)
    {
        $worker_id = (int)$worker_id;
        
        $sql = "SELECT d.*, 
                b.booking_reference, b.estimated_price,
                c.user_name as customer_name
                FROM disputes d
                JOIN bookings b ON d.booking_id = b.booking_id
                JOIN users c ON d.customer_id = c.user_id
                WHERE d.worker_id = $worker_id
                ORDER BY d.created_at DESC";
        
        return $this->db_fetch_all($sql);
    }
    
    /**
     * Delete/Cancel a dispute (only if not resolved)
     */
    public function delete_dispute($dispute_id)
    {
        $dispute_id = (int)$dispute_id;
        
        // Check if dispute is still open
        $dispute = $this->get_dispute_by_id($dispute_id);
        if (!$dispute) {
            return false;
        }
        
        if ($dispute['dispute_status'] === 'resolved') {
            return false; // Cannot delete resolved disputes
        }
        
        $sql = "DELETE FROM disputes WHERE dispute_id = $dispute_id";
        
        return $this->db_query($sql);
    }
}
?>
