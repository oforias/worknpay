<?php
/**
 * Instant Payout Action
 * Releases escrow immediately with 2% fee
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../settings/db_class.php';

// Check if user is logged in and is a worker
require_login();
if (!is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Workers only.'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['booking_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking ID is required'
    ]);
    exit();
}

$booking_id = (int)$input['booking_id'];
$worker_id = get_user_id();

$db = new db_connection();

try {
    // Verify booking belongs to this worker
    $booking_query = "SELECT b.booking_id, b.worker_id, b.booking_status, b.estimated_price
                      FROM bookings b
                      WHERE b.booking_id = $booking_id 
                      AND b.worker_id = $worker_id
                      AND b.booking_status = 'completed'";
    
    $booking = $db->db_fetch_one($booking_query);
    
    if (!$booking) {
        throw new Exception('Booking not found or not completed');
    }
    
    // Get payment details
    $payment_query = "SELECT payment_id, worker_payout, escrow_status
                      FROM payments
                      WHERE booking_id = $booking_id";
    
    $payment = $db->db_fetch_one($payment_query);
    
    if (!$payment) {
        throw new Exception('Payment not found');
    }
    
    if ($payment['escrow_status'] !== 'held') {
        throw new Exception('Payment already released');
    }
    
    $worker_payout = (float)$payment['worker_payout'];
    $instant_fee = $worker_payout * 0.02; // 2% instant fee
    $net_amount = $worker_payout - $instant_fee;
    
    // Start transaction
    $db->db_query("START TRANSACTION");
    
    // Update payment escrow status
    $update_payment = "UPDATE payments 
                      SET escrow_status = 'released',
                          escrow_release_date = NOW()
                      WHERE payment_id = {$payment['payment_id']}";
    
    if (!$db->db_query($update_payment)) {
        throw new Exception('Failed to update payment status');
    }
    
    // Add net amount to worker balance (after instant fee)
    $update_balance = "UPDATE worker_profiles 
                      SET available_balance = available_balance + $net_amount
                      WHERE user_id = $worker_id";
    
    if (!$db->db_query($update_balance)) {
        throw new Exception('Failed to update worker balance');
    }
    
    // Log transaction
    $log_query = "INSERT INTO transaction_logs 
                 (user_id, transaction_type, amount, description, created_at)
                 VALUES ($worker_id, 'escrow_release', $net_amount,
                        'Instant payout for booking #$booking_id (2% fee: GH₵" . number_format($instant_fee, 2) . ")',
                        NOW())";
    $db->db_query($log_query);
    
    // Commit transaction
    $db->db_query("COMMIT");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Payment released instantly!',
        'amount_received' => $net_amount,
        'instant_fee' => $instant_fee
    ]);
    
} catch (Exception $e) {
    $db->db_query("ROLLBACK");
    error_log("Instant payout error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
