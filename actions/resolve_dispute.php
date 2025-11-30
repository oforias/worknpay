<?php
/**
 * Resolve Dispute Action
 * Allows admin to resolve disputes
 */

// Prevent any output before JSON
ob_start();

// Suppress all errors and warnings from being displayed
error_reporting(0);
ini_set('display_errors', 0);

require_once '../settings/core.php';
require_once '../controllers/dispute_controller.php';
require_once '../classes/payment_class.php';

// Clear any output that might have been generated
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Check authentication and admin role
if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit();
}

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
    // Log the incoming request for debugging
    error_log("Resolve dispute request: " . json_encode($input));
    
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
    
    $payment = new payment_class();
    $refund_amount = 0;
    
    require_once '../settings/db_class.php';
    $db = new db_connection();
    
    // Get payment details
    $payment_record = $db->db_fetch_one("SELECT * FROM payments WHERE booking_id = {$dispute['booking_id']}");
    if (!$payment_record) {
        throw new Exception('Payment record not found for this booking. Cannot process refund or payment release.');
    }
    
    $payment_amount = floatval($payment_record['amount']);
    
    // Check if payment is in a state that can be resolved
    if ($payment_record['payment_status'] === 'refunded') {
        throw new Exception('Payment has already been refunded');
    }
    
    // Process based on outcome
    switch ($outcome) {
        case 'refund_customer':
            // Full refund to customer
            $refund_amount = $payment_amount;
            $db->db_query("UPDATE payments 
                          SET payment_status = 'refunded', 
                              escrow_status = 'refunded' 
                          WHERE booking_id = {$dispute['booking_id']}");
            error_log("Dispute #{$dispute_id}: Full refund of GH₵{$refund_amount} to customer");
            break;
            
        case 'pay_worker':
            // Release full amount to worker
            $payment->release_escrow($payment_record['payment_id'], $dispute['worker_id']);
            error_log("Dispute #{$dispute_id}: Full payment released to worker");
            break;
            
        case 'partial_refund':
            // Split payment
            if (!isset($input['refund_amount']) || $input['refund_amount'] === '' || $input['refund_amount'] === null) {
                throw new Exception('Refund amount required for partial refund');
            }
            $refund_amount = (float)$input['refund_amount'];
            
            if ($refund_amount <= 0) {
                throw new Exception('Refund amount must be greater than 0');
            }
            
            if ($refund_amount > $payment_amount) {
                throw new Exception('Refund amount cannot exceed payment amount');
            }
            
            $worker_amount = $payment_amount - $refund_amount;
            
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
            $payment->release_escrow($payment_record['payment_id'], $dispute['worker_id']);
            error_log("Dispute #{$dispute_id}: No action - Payment released to worker");
            break;
    }
    
    // Resolve dispute
    if (resolve_dispute_ctr($dispute_id, $outcome, $resolution_notes, $refund_amount, $admin_id)) {
        // Clear any output buffer and send clean JSON
        while (ob_get_level()) {
            ob_end_clean();
        }
        echo json_encode([
            'status' => 'success',
            'message' => 'Dispute resolved successfully'
        ]);
    } else {
        throw new Exception('Failed to resolve dispute');
    }
    
} catch (Exception $e) {
    error_log("Dispute resolution error: " . $e->getMessage());
    // Clear any output buffer and send clean JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
