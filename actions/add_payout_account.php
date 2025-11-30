<?php
/**
 * Add Payout Account Action
 * Allows workers to add mobile money or bank account for withdrawals
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../classes/payout_account_class.php';

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

if (!$input || !isset($input['account_type'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Account type is required'
    ]);
    exit();
}

$worker_id = get_user_id();
$account_type = trim($input['account_type']);

// Validate account type
if (!in_array($account_type, ['mobile_money', 'bank_transfer'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid account type'
    ]);
    exit();
}

$payout_account = new payout_account_class();

// Validate based on account type
if ($account_type === 'mobile_money') {
    if (!isset($input['mobile_number']) || !isset($input['mobile_network'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Mobile number and network are required'
        ]);
        exit();
    }
    
    $mobile_number = trim($input['mobile_number']);
    $mobile_network = trim($input['mobile_network']);
    
    // Validate mobile number format
    if (!$payout_account->validate_mobile_number($mobile_number)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid mobile number format. Must be 10 digits starting with 0'
        ]);
        exit();
    }
    
    // Validate network
    if (!in_array($mobile_network, ['MTN', 'Vodafone', 'Telecel'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid mobile network'
        ]);
        exit();
    }
    
    $data = [
        'mobile_number' => $mobile_number,
        'mobile_network' => $mobile_network,
        'is_default' => isset($input['is_default']) ? (bool)$input['is_default'] : false
    ];
    
} else {
    // Bank transfer
    if (!isset($input['bank_name']) || !isset($input['account_number']) || !isset($input['account_holder_name'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Bank name, account number, and account holder name are required'
        ]);
        exit();
    }
    
    $bank_name = trim($input['bank_name']);
    $account_number = trim($input['account_number']);
    $account_holder_name = trim($input['account_holder_name']);
    
    if (empty($bank_name) || empty($account_number) || empty($account_holder_name)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All bank account fields are required'
        ]);
        exit();
    }
    
    $data = [
        'bank_name' => $bank_name,
        'account_number' => $account_number,
        'account_holder_name' => $account_holder_name,
        'is_default' => isset($input['is_default']) ? (bool)$input['is_default'] : false
    ];
}

try {
    // Add payout account
    if ($payout_account->add_payout_account($worker_id, $account_type, $data)) {
        error_log("Worker #$worker_id added payout account: $account_type");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Payout account added successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add payout account'
        ]);
    }
} catch (Exception $e) {
    error_log("Error adding payout account: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while adding payout account'
    ]);
}
?>
