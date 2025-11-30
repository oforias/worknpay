<?php
/**
 * Get Worker Payout Accounts
 * Returns all payout accounts for the logged-in worker
 */

require_once '../settings/core.php';
require_once '../classes/payout_account_class.php';

header('Content-Type: application/json');

// Check if user is logged in and is a worker
if (!is_logged_in() || !is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit();
}

$worker_id = get_user_id();
$payout_account = new payout_account_class();

try {
    $accounts = $payout_account->get_worker_accounts($worker_id);
    
    if (!$accounts) {
        echo json_encode([
            'status' => 'success',
            'accounts' => [],
            'message' => 'No payout accounts found'
        ]);
        exit();
    }
    
    // Format accounts for display
    $formatted_accounts = [];
    foreach ($accounts as $account) {
        $display_text = '';
        $masked_number = '';
        
        if ($account['account_type'] === 'mobile_money') {
            $masked_number = substr($account['mobile_number'], 0, 4) . '****' . substr($account['mobile_number'], -2);
            $display_text = "{$account['mobile_network']} - {$masked_number}";
        } else {
            $masked_number = '****' . substr($account['account_number'], -4);
            $display_text = "{$account['bank_name']} - {$masked_number}";
        }
        
        $formatted_accounts[] = [
            'account_id' => $account['account_id'],
            'account_type' => $account['account_type'],
            'display_text' => $display_text,
            'is_default' => $account['is_default'],
            'full_number' => $account['account_type'] === 'mobile_money' ? $account['mobile_number'] : $account['account_number']
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'accounts' => $formatted_accounts
    ]);
    
} catch (Exception $e) {
    error_log("Get payout accounts error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load payout accounts'
    ]);
}
?>
