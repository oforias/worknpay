<?php
require_once '../settings/core.php';
require_once '../controllers/payout_controller.php';
require_login('login.php');

if (!is_admin()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$admin_name = get_user_name();

// Get pending payouts
$pending_payouts = get_pending_payouts_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Management - WorkNPay Admin</title>
    <style>
        :root {
            /* Light Mode Colors */
            --bg-primary: #F5F7FA;
            --bg-secondary: #FFFFFF;
            --bg-tertiary: #F5F7FA;
            --text-primary: #1a1f36;
            --text-secondary: #6B7280;
            --border-color: #E5E7EB;
            --header-bg: linear-gradient(135deg, #7C3AED 0%, #A78BFA 100%);
            --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --hover-shadow: 0 4px 12px rgba(124, 58, 237, 0.1);
            --modal-bg: #FFFFFF;
        }
        
        body.dark-mode {
            /* Dark Mode Colors */
            --bg-primary: #0A0E1A;
            --bg-secondary: rgba(255, 255, 255, 0.03);
            --bg-tertiary: rgba(255, 255, 255, 0.05);
            --text-primary: rgba(255, 255, 255, 0.95);
            --text-secondary: rgba(255, 255, 255, 0.6);
            --border-color: rgba(255, 215, 0, 0.2);
            --header-bg: linear-gradient(135deg, rgba(10, 14, 26, 0.95) 0%, rgba(20, 24, 36, 0.95) 100%);
            --card-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            --hover-shadow: 0 8px 24px rgba(255, 215, 0, 0.15);
            --modal-bg: rgba(10, 14, 26, 0.98);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            padding-bottom: 20px;
            color: var(--text-primary);
            transition: background 0.3s ease, color 0.3s ease;
            position: relative;
        }
        
        body.dark-mode::before {
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
            background: var(--header-bg);
            backdrop-filter: blur(20px);
            padding: 24px 20px;
            color: white;
            position: relative;
            z-index: 1;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .theme-toggle {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 8px 16px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .logout-btn {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 20px;
            padding: 8px 16px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
            transform: translateY(-2px);
        }
        
        .container {
            max-width: 1200px;
            margin: -20px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--hover-shadow);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .card {
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-primary);
        }
        
        .payout-item {
            padding: 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            background: var(--bg-tertiary);
        }
        
        .payout-item:hover {
            border-color: #FFD700;
            box-shadow: var(--hover-shadow);
            transform: translateY(-2px);
        }
        
        .payout-item.urgent {
            border-color: #FFA500;
            background: rgba(255, 165, 0, 0.1);
        }
        
        .payout-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }
        
        .worker-info h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .worker-info p {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .payout-amount {
            text-align: right;
        }
        
        .amount-value {
            font-size: 24px;
            font-weight: 700;
            color: #7C3AED;
            margin-bottom: 4px;
        }
        
        .amount-label {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .payout-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .detail-item {
            font-size: 14px;
        }
        
        .detail-label {
            color: var(--text-secondary);
            margin-bottom: 4px;
        }
        
        .detail-value {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .payout-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
        }
        
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }
        
        .btn-danger {
            background: white;
            color: #EF4444;
            border: 2px solid #EF4444;
        }
        
        .btn-danger:hover {
            background: #FEE2E2;
        }
        
        .urgent-badge {
            background: #FFA500;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
            background: var(--modal-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        
        .modal-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-primary);
        }
        
        .close-modal {
            font-size: 32px;
            color: var(--text-secondary);
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
            <div class="header-actions">
                <button class="logout-btn" onclick="logout()">
                    <span>🚪</span>
                    <span>Logout</span>
                </button>
            </div>
        </div>
        <h1>Payout Management</h1>
        <p>Process worker withdrawal requests - Welcome, <?php echo htmlspecialchars($admin_name); ?>!</p>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($pending_payouts); ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php 
                    $urgent_count = 0;
                    foreach ($pending_payouts as $payout) {
                        if ($payout['payout_type'] === 'instant') $urgent_count++;
                    }
                    echo $urgent_count;
                    ?>
                </div>
                <div class="stat-label">Urgent (Instant)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    GH₵<?php 
                    $total_amount = 0;
                    foreach ($pending_payouts as $payout) {
                        $total_amount += $payout['net_amount'];
                    }
                    echo number_format($total_amount, 2);
                    ?>
                </div>
                <div class="stat-label">Total Amount</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-title">Pending Payout Requests</div>
            
            <?php if (empty($pending_payouts)): ?>
                <div class="empty-state">
                    <div style="font-size: 48px; margin-bottom: 16px;">✅</div>
                    <div style="font-size: 18px; margin-bottom: 8px;">All caught up!</div>
                    <div style="font-size: 14px;">No pending payout requests at the moment</div>
                </div>
            <?php else: ?>
                <?php foreach ($pending_payouts as $payout): ?>
                    <div class="payout-item <?php echo $payout['payout_type'] === 'instant' ? 'urgent' : ''; ?>">
                        <?php if ($payout['payout_type'] === 'instant'): ?>
                            <div class="urgent-badge">🔥 URGENT - Instant Payout</div>
                        <?php endif; ?>
                        
                        <div class="payout-header">
                            <div class="worker-info">
                                <h3><?php echo htmlspecialchars($payout['user_name']); ?></h3>
                                <p><?php echo htmlspecialchars($payout['user_email']); ?> • <?php echo htmlspecialchars($payout['user_phone']); ?></p>
                            </div>
                            <div class="payout-amount">
                                <div class="amount-value">GH₵<?php echo number_format($payout['net_amount'], 2); ?></div>
                                <div class="amount-label">Net Amount</div>
                            </div>
                        </div>
                        
                        <div class="payout-details">
                            <div class="detail-item">
                                <div class="detail-label">Requested Amount</div>
                                <div class="detail-value">GH₵<?php echo number_format($payout['amount'], 2); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Processing Fee</div>
                                <div class="detail-value">GH₵<?php echo number_format($payout['payout_fee'], 2); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Payout Type</div>
                                <div class="detail-value"><?php echo $payout['payout_type'] === 'instant' ? 'Instant (2%)' : 'Next-Day (FREE)'; ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Requested</div>
                                <div class="detail-value"><?php echo date('M d, Y H:i', strtotime($payout['requested_at'])); ?></div>
                            </div>
                        </div>
                        
                        <div class="payout-actions">
                            <button class="btn btn-danger" onclick="failPayout(<?php echo $payout['payout_id']; ?>, '<?php echo htmlspecialchars($payout['user_name']); ?>')">Reject</button>
                            <button class="btn btn-success" onclick="completePayout(<?php echo $payout['payout_id']; ?>, '<?php echo htmlspecialchars($payout['user_name']); ?>', <?php echo $payout['net_amount']; ?>)">Process Payment</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Complete Payout Modal -->
    <div id="completeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Process Payment</h2>
                <span class="close-modal" onclick="closeCompleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 20px; color: #6B7280;">
                    Confirm that you have transferred <strong id="confirmAmount"></strong> to <strong id="confirmWorker"></strong>
                </p>
                
                <div class="form-group">
                    <label for="transactionRef">Transaction Reference</label>
                    <input type="text" id="transactionRef" placeholder="e.g., MTN-123456789" required>
                </div>
                
                <button class="btn btn-success" style="width: 100%;" onclick="submitComplete()">Confirm Payment Sent</button>
            </div>
        </div>
    </div>
    
    <!-- Fail Payout Modal -->
    <div id="failModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reject Payout</h2>
                <span class="close-modal" onclick="closeFailModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 20px; color: #6B7280;">
                    Rejecting payout for <strong id="failWorker"></strong>. The amount will be returned to their balance.
                </p>
                
                <div class="form-group">
                    <label for="failureReason">Reason for Rejection</label>
                    <textarea id="failureReason" rows="4" placeholder="e.g., Invalid account details, suspicious activity..." required></textarea>
                </div>
                
                <button class="btn btn-danger" style="width: 100%;" onclick="submitFail()">Reject Payout</button>
            </div>
        </div>
    </div>
    
    <script>
        // Theme Toggle
        function toggleTheme() {
            const body = document.body;
            const themeIcon = document.getElementById('themeIcon');
            const themeText = document.getElementById('themeText');
            
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                themeIcon.textContent = '☀️';
                themeText.textContent = 'Light Mode';
                localStorage.setItem('adminTheme', 'dark');
            } else {
                themeIcon.textContent = '🌙';
                themeText.textContent = 'Dark Mode';
                localStorage.setItem('adminTheme', 'light');
            }
        }
        
        // Load saved theme preference
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('adminTheme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
                document.getElementById('themeIcon').textContent = '☀️';
                document.getElementById('themeText').textContent = 'Light Mode';
            }
        });
        
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../actions/logout_action.php';
            }
        }
        
        let currentPayoutId = null;
        
        function completePayout(payoutId, workerName, amount) {
            currentPayoutId = payoutId;
            document.getElementById('confirmWorker').textContent = workerName;
            document.getElementById('confirmAmount').textContent = 'GH₵' + amount.toFixed(2);
            document.getElementById('completeModal').classList.add('show');
        }
        
        function closeCompleteModal() {
            document.getElementById('completeModal').classList.remove('show');
            document.getElementById('transactionRef').value = '';
            currentPayoutId = null;
        }
        
        async function submitComplete() {
            const transactionRef = document.getElementById('transactionRef').value.trim();
            
            if (!transactionRef) {
                alert('Please enter a transaction reference');
                return;
            }
            
            if (!confirm('Confirm that payment has been sent?')) return;
            
            try {
                const response = await fetch('../actions/process_payout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        payout_id: currentPayoutId,
                        action: 'complete',
                        transaction_reference: transactionRef
                    })
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
                alert('Failed to process payout. Please try again.');
            }
        }
        
        function failPayout(payoutId, workerName) {
            currentPayoutId = payoutId;
            document.getElementById('failWorker').textContent = workerName;
            document.getElementById('failModal').classList.add('show');
        }
        
        function closeFailModal() {
            document.getElementById('failModal').classList.remove('show');
            document.getElementById('failureReason').value = '';
            currentPayoutId = null;
        }
        
        async function submitFail() {
            const failureReason = document.getElementById('failureReason').value.trim();
            
            if (!failureReason) {
                alert('Please enter a reason for rejection');
                return;
            }
            
            if (!confirm('Reject this payout? The amount will be returned to the worker\'s balance.')) return;
            
            try {
                const response = await fetch('../actions/process_payout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        payout_id: currentPayoutId,
                        action: 'fail',
                        failure_reason: failureReason
                    })
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
                alert('Failed to reject payout. Please try again.');
            }
        }
    </script>
</body>
</html>
