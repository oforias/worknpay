<?php
require_once '../settings/core.php';
require_once '../classes/payout_account_class.php';
require_login('login.php');

if (!is_worker()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$worker_id = get_user_id();
$worker_name = get_user_name();

// Get worker's payout accounts
$payout_account = new payout_account_class();
$accounts = $payout_account->get_worker_accounts($worker_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Accounts - WorkNPay</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0A0E1A;
            min-height: 100vh;
            padding-bottom: 40px;
            color: white;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255, 215, 0, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(255, 165, 0, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .header {
            background: linear-gradient(135deg, rgba(10, 14, 26, 0.95) 0%, rgba(20, 24, 36, 0.95) 100%);
            backdrop-filter: blur(20px);
            padding: 24px 20px;
            color: white;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .header p {
            opacity: 0.8;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .container {
            max-width: 800px;
            margin: 20px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .account-item {
            padding: 20px;
            border: 2px solid rgba(255, 215, 0, 0.2);
            border-radius: 16px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.02);
        }
        
        .account-item:hover {
            border-color: rgba(255, 215, 0, 0.4);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.15);
        }
        
        .account-item.default {
            border-color: #FFD700;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 165, 0, 0.1) 100%);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.2);
        }
        
        .account-info {
            flex: 1;
        }
        
        .account-type {
            font-size: 12px;
            color: rgba(255, 215, 0, 0.8);
            text-transform: uppercase;
            margin-bottom: 6px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .account-details {
            font-size: 18px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 6px;
        }
        
        .account-network {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .default-badge {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            margin-right: 12px;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.5);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #FFD700;
            border: 2px solid rgba(255, 215, 0, 0.3);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 215, 0, 0.1);
            border-color: rgba(255, 215, 0, 0.5);
            transform: translateY(-2px);
        }
        
        .btn-small {
            padding: 10px 20px;
            font-size: 13px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .hint {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 6px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: rgba(10, 14, 26, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .modal-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .close-modal {
            font-size: 32px;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            line-height: 1;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            color: #FFD700;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .empty-state div:first-child {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 8px;
            color: #FFD700;
            text-decoration: none;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: rgba(255, 215, 0, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="worker_dashboard_new.php" class="back-link">← Back to Dashboard</a>
        <h1>Payout Accounts</h1>
        <p>Manage your withdrawal accounts</p>
    </div>
    
    <div class="container">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div class="card-title">Your Payout Accounts</div>
                <button class="btn btn-primary btn-small" onclick="openAddAccountModal()">+ Add Account</button>
            </div>
            
            <?php if (empty($accounts)): ?>
                <div class="empty-state">
                    <div style="font-size: 48px; margin-bottom: 16px;">💳</div>
                    <div style="font-size: 16px; margin-bottom: 8px;">No payout accounts yet</div>
                    <div style="font-size: 14px;">Add a mobile money or bank account to receive withdrawals</div>
                </div>
            <?php else: ?>
                <?php foreach ($accounts as $account): ?>
                    <div class="account-item <?php echo $account['is_default'] ? 'default' : ''; ?>">
                        <div class="account-info">
                            <div class="account-type"><?php echo str_replace('_', ' ', $account['account_type']); ?></div>
                            <?php if ($account['account_type'] === 'mobile_money'): ?>
                                <div class="account-details"><?php echo htmlspecialchars($account['mobile_number']); ?></div>
                                <div class="account-network"><?php echo htmlspecialchars($account['mobile_network']); ?></div>
                            <?php else: ?>
                                <div class="account-details"><?php echo htmlspecialchars($account['account_number']); ?></div>
                                <div class="account-network"><?php echo htmlspecialchars($account['bank_name']); ?> - <?php echo htmlspecialchars($account['account_holder_name']); ?></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if ($account['is_default']): ?>
                                <span class="default-badge">DEFAULT</span>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-small" onclick="setDefault(<?php echo $account['account_id']; ?>)">Set Default</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Account Modal -->
    <div id="addAccountModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Payout Account</h2>
                <span class="close-modal" onclick="closeAddAccountModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="accountType">Account Type</label>
                    <select id="accountType" onchange="toggleAccountFields()">
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                
                <!-- Mobile Money Fields -->
                <div id="mobileMoneyFields">
                    <div class="form-group">
                        <label for="mobileNumber">Mobile Number</label>
                        <input type="text" id="mobileNumber" placeholder="0244123456" maxlength="10">
                        <div class="hint">10 digits starting with 0</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobileNetwork">Network</label>
                        <select id="mobileNetwork">
                            <option value="MTN">MTN Mobile Money</option>
                            <option value="Vodafone">Vodafone Cash</option>
                            <option value="Telecel">Telecel Cash</option>
                        </select>
                    </div>
                </div>
                
                <!-- Bank Transfer Fields -->
                <div id="bankFields" style="display: none;">
                    <div class="form-group">
                        <label for="bankName">Bank Name</label>
                        <input type="text" id="bankName" placeholder="e.g., GCB Bank">
                    </div>
                    
                    <div class="form-group">
                        <label for="accountNumber">Account Number</label>
                        <input type="text" id="accountNumber" placeholder="Enter account number">
                    </div>
                    
                    <div class="form-group">
                        <label for="accountHolderName">Account Holder Name</label>
                        <input type="text" id="accountHolderName" placeholder="Full name as on account">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="setAsDefault"> Set as default account
                    </label>
                </div>
                
                <button class="btn btn-primary" style="width: 100%;" onclick="submitAccount()">Add Account</button>
            </div>
        </div>
    </div>
    
    <script>
        function openAddAccountModal() {
            document.getElementById('addAccountModal').classList.add('show');
        }
        
        function closeAddAccountModal() {
            document.getElementById('addAccountModal').classList.remove('show');
            // Reset form
            document.getElementById('accountType').value = 'mobile_money';
            toggleAccountFields();
            document.getElementById('mobileNumber').value = '';
            document.getElementById('setAsDefault').checked = false;
        }
        
        function toggleAccountFields() {
            const accountType = document.getElementById('accountType').value;
            const mobileFields = document.getElementById('mobileMoneyFields');
            const bankFields = document.getElementById('bankFields');
            
            if (accountType === 'mobile_money') {
                mobileFields.style.display = 'block';
                bankFields.style.display = 'none';
            } else {
                mobileFields.style.display = 'none';
                bankFields.style.display = 'block';
            }
        }
        
        async function submitAccount() {
            const accountType = document.getElementById('accountType').value;
            const isDefault = document.getElementById('setAsDefault').checked;
            
            let data = {
                account_type: accountType,
                is_default: isDefault
            };
            
            if (accountType === 'mobile_money') {
                const mobileNumber = document.getElementById('mobileNumber').value.trim();
                const mobileNetwork = document.getElementById('mobileNetwork').value;
                
                if (!mobileNumber || !/^0\d{9}$/.test(mobileNumber)) {
                    alert('Please enter a valid 10-digit mobile number starting with 0');
                    return;
                }
                
                data.mobile_number = mobileNumber;
                data.mobile_network = mobileNetwork;
            } else {
                const bankName = document.getElementById('bankName').value.trim();
                const accountNumber = document.getElementById('accountNumber').value.trim();
                const accountHolderName = document.getElementById('accountHolderName').value.trim();
                
                if (!bankName || !accountNumber || !accountHolderName) {
                    alert('Please fill in all bank account fields');
                    return;
                }
                
                data.bank_name = bankName;
                data.account_number = accountNumber;
                data.account_holder_name = accountHolderName;
            }
            
            try {
                const response = await fetch('../actions/add_payout_account.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to add account. Please try again.');
            }
        }
        
        async function setDefault(accountId) {
            if (!confirm('Set this as your default payout account?')) return;
            
            try {
                const response = await fetch('../actions/set_default_account.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ account_id: accountId })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to update default account. Please try again.');
            }
        }
    </script>
</body>
</html>
