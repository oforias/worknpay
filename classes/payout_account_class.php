<?php
/**
 * Payout Account Class
 * Handles worker payout account management
 */

require_once(__DIR__ . '/../settings/db_class.php');

class payout_account_class extends db_connection {
    
    /**
     * Add a new payout account
     */
    public function add_payout_account($worker_id, $account_type, $data) {
        $worker_id = intval($worker_id);
        $account_type = mysqli_real_escape_string($this->db_conn(), $account_type);
        
        // If this is set as default, unset other defaults first
        if (isset($data['is_default']) && $data['is_default']) {
            $this->unset_default_accounts($worker_id);
        }
        
        if ($account_type === 'mobile_money') {
            $mobile_number = mysqli_real_escape_string($this->db_conn(), $data['mobile_number']);
            $mobile_network = mysqli_real_escape_string($this->db_conn(), $data['mobile_network']);
            $is_default = isset($data['is_default']) ? intval($data['is_default']) : 0;
            
            $query = "INSERT INTO worker_payout_accounts 
                      (worker_id, account_type, mobile_number, mobile_network, is_default) 
                      VALUES ($worker_id, '$account_type', '$mobile_number', '$mobile_network', $is_default)";
        } else {
            $bank_name = mysqli_real_escape_string($this->db_conn(), $data['bank_name']);
            $account_number = mysqli_real_escape_string($this->db_conn(), $data['account_number']);
            $account_holder_name = mysqli_real_escape_string($this->db_conn(), $data['account_holder_name']);
            $is_default = isset($data['is_default']) ? intval($data['is_default']) : 0;
            
            $query = "INSERT INTO worker_payout_accounts 
                      (worker_id, account_type, bank_name, account_number, account_holder_name, is_default) 
                      VALUES ($worker_id, '$account_type', '$bank_name', '$account_number', '$account_holder_name', $is_default)";
        }
        
        return $this->db_query($query);
    }
    
    /**
     * Get all payout accounts for a worker
     */
    public function get_worker_accounts($worker_id) {
        $worker_id = intval($worker_id);
        
        $query = "SELECT * FROM worker_payout_accounts 
                  WHERE worker_id = $worker_id 
                  ORDER BY is_default DESC, created_at DESC";
        
        return $this->db_fetch_all($query);
    }
    
    /**
     * Get default payout account
     */
    public function get_default_account($worker_id) {
        $worker_id = intval($worker_id);
        
        $query = "SELECT * FROM worker_payout_accounts 
                  WHERE worker_id = $worker_id AND is_default = 1 
                  LIMIT 1";
        
        return $this->db_fetch_one($query);
    }
    
    /**
     * Set an account as default
     */
    public function set_default_account($account_id, $worker_id) {
        $account_id = intval($account_id);
        $worker_id = intval($worker_id);
        
        // First, unset all defaults for this worker
        $this->unset_default_accounts($worker_id);
        
        // Then set the new default
        $query = "UPDATE worker_payout_accounts 
                  SET is_default = 1 
                  WHERE account_id = $account_id AND worker_id = $worker_id";
        
        return $this->db_query($query);
    }
    
    /**
     * Unset all default accounts for a worker
     */
    private function unset_default_accounts($worker_id) {
        $worker_id = intval($worker_id);
        
        $query = "UPDATE worker_payout_accounts 
                  SET is_default = 0 
                  WHERE worker_id = $worker_id";
        
        return $this->db_query($query);
    }
    
    /**
     * Validate mobile number format
     */
    public function validate_mobile_number($mobile_number) {
        // Ghana mobile numbers: 10 digits starting with 0
        return preg_match('/^0\d{9}$/', $mobile_number);
    }
    
    /**
     * Delete payout account
     */
    public function delete_account($account_id, $worker_id) {
        $account_id = intval($account_id);
        $worker_id = intval($worker_id);
        
        $query = "DELETE FROM worker_payout_accounts 
                  WHERE account_id = $account_id AND worker_id = $worker_id";
        
        return $this->db_query($query);
    }
    
    /**
     * Get account by ID
     */
    public function get_account_by_id($account_id) {
        $account_id = intval($account_id);
        
        $query = "SELECT * FROM worker_payout_accounts 
                  WHERE account_id = $account_id";
        
        return $this->db_fetch_one($query);
    }
}
?>
