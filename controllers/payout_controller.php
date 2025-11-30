<?php
/**
 * Payout Controller
 * Business logic for payout operations
 */

require_once(__DIR__ . '/../classes/payout_class.php');

/**
 * Request a payout
 */
function request_payout_ctr($worker_id, $amount, $payout_type, $payout_account_id) {
    $payout = new payout_class();
    
    // Calculate fee
    $fee = ($payout_type === 'instant') ? ($amount * 0.02) : 0;
    $net_amount = $amount - $fee;
    
    return $payout->create_payout($worker_id, $amount, $payout_type, $payout_account_id, $fee, $net_amount);
}

/**
 * Get worker payout history
 */
function get_payout_history_ctr($worker_id) {
    $payout = new payout_class();
    return $payout->get_worker_payouts($worker_id);
}

/**
 * Get pending payouts (admin)
 */
function get_pending_payouts_ctr() {
    $payout = new payout_class();
    return $payout->get_pending_payouts();
}

/**
 * Process payout (admin)
 */
function process_payout_ctr($payout_id, $status, $reference = null) {
    $payout = new payout_class();
    return $payout->update_payout_status($payout_id, $status, $reference);
}

/**
 * Get payout details
 */
function get_payout_details_ctr($payout_id) {
    $payout = new payout_class();
    return $payout->get_payout_by_id($payout_id);
}
?>
