<?php
/**
 * Resolve Dispute Action
 * Allows admin to resolve disputes
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../controllers/dispute_controller.php';
require_once '../classes/payment_class.php';

// Check authentication and admin role
require_login();

if (!is_admin()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Admin access required'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

try {
    if (!isset($input['dispute_id']) || !isset($input['outcome']) || !isset($input['resolution_notes'])) {
        throw new Exception('Dispute ID, outcome, and resolution notes are required');
    }
    
    $dispute_id = (int)$input['dispute_id'];
    $outcome = $input['outcome'];
    $resolution_notes = trim($input['resolution_notes']);
    $admin_id = get_user_id();
    
    // Validate outcome
    $valid_outcomes = ['refund_customer', 'pay_worker', 'partial_refund', 'no_action'];
    if (!in_array($outcome, $valid_outcomes)) {
        throw new Exception('Invalid outcome');
    }
    
    // Get dispute details
    $dispute = get_dispute_details_ctr($dispute_id);
    
    if (!$dispute) {
        throw new Exception('Dispute not found');
    }
    
    if ($dispute['dispute_status'] !== 'open') {
        throw new Exception('Dispute is already resolved');
    }
    
    $payment = new Payment();
    $refund_amount = 0;
    
    require_once '../settings/db_class.php';
    $db = new db_connection();
    
    // Process based on outcome
    switch ($outcome) {
        case 'refund_customer':
            // Full refund to customer
            $refund_amount = $dispute['payment_amount'];
            $db->db_query("UPDATE payments 
                          SET payment_status = 'refunded', 
                              escrow_status = 'refunded' 
                          WHERE booking_id = {$dispute['booking_id']}");
            error_log("Dispute #{$dispute_id}: Full refund of GH₵{$refund_amount} to customer");
            break;
            
        case 'pay_worker':
            // Release full amount to worker
            $payment->release_escrow($dispute['booking_id']);
            error_log("Dispute #{$dispute_id}: Full payment released to worker");
            break;
            
        case 'partial_refund':
            // Split payment
            if (!isset($input['refund_amount'])) {
                throw new Exception('Refund amount required for partial refund');
            }
            $refund_amount = (float)$input['refund_amount'];
            $worker_amount = $dispute['payment_amount'] - $refund_amount;
            
            // Update payment status
            $db->db_query("UPDATE payments 
                          SET payment_status = 'refunded', 
                              escrow_status = 'released' 
                          WHERE booking_id = {$dispute['booking_id']}");
            
            // Add worker portion to balance
            $db->db_query("UPDATE worker_profiles 
                          SET available_balance = available_balance + $worker_amount 
                          WHERE user_id = {$dispute['worker_id']}");
            
            error_log("Dispute #{$dispute_id}: Partial refund - Customer: GH₵{$refund_amount}, Worker: GH₵{$worker_amount}");
            break;
            
        case 'no_action':
            // Release to worker (no refund)
            $payment->release_escrow($dispute['booking_id']);
            error_log("Dispute #{$dispute_id}: No action - Payment released to worker");
            break;
    }
    
    // Resolve dispute
    if (resolve_dispute_ctr($dispute_id, $resolution_notes, $outcome, $refund_amount, $admin_id)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Dispute resolved successfully'
        ]);
    } else {
        throw new Exception('Failed to resolve dispute');
    }
    
} catch (Exception $e) {
    error_log("Dispute resolution error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
