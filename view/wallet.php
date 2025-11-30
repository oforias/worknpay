<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

// Only customers can access wallet
if (!is_customer()) {
    header('Location: home.php');
    exit();
}

$customer_id = get_user_id();
$db = new db_connection();

// Get customer's payment history
$payments_query = "SELECT p.*, b.booking_reference, b.booking_date, u.user_name as worker_name
                   FROM payments p
                   JOIN bookings b ON p.booking_id = b.booking_id
                   JOIN users u ON b.worker_id = u.user_id
                   WHERE b.customer_id = $customer_id
                   ORDER BY p.payment_date DESC
                   LIMIT 20";
$payments = $db->db_fetch_all($payments_query);

// Calculate totals
$total_spent = 0;
$total_refunded = 0;
if ($payments) {
    foreach ($payments as $payment) {
        // Count successful and pending payments as spent
        if ($payment['payment_status'] === 'successful' || $payment['payment_status'] === 'pending') {
            $total_spent += (float)$payment['amount'];
        }
        if ($payment['payment_status'] === 'refunded') {
            $total_refunded += (float)$payment['amount'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet - WorkNPay</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            padding-bottom: 80px;
            transition: all 0.3s ease;
        }
        
        .header {
            background: var(--header-bg);
            color: white;
            padding: 24px 20px;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255, 215, 0, 0.15) 0%, transparent 70%);
        }
        
        .header-content {
            position: relative;
            z-index: 1;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px 20px;
        }
        
        .balance-card {
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
            padding: 32px;
            border-radius: 24px;
            margin-bottom: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 215, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .balance-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
        }
        
        .balance-content {
            position: relative;
            z-index: 1;
        }
        
        .balance-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
        }
        
        .balance-amount {
            font-size: 48px;
            font-weight: 800;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
        }
        
        .balance-note {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .balance-card {
                padding: 24px;
            }
            
            .balance-amount {
                font-size: 36px;
            }
            
            .transaction-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .transaction-amount {
                font-size: 18px;
            }
            
            .transaction-meta {
                flex-wrap: wrap;
            }
        }
        
        .stat-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 16px;
            padding-left: 16px;
            position: relative;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 24px;
            background: linear-gradient(180deg, #FFD700 0%, #FFA500 100%);
            border-radius: 2px;
        }
        
        .transaction-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .transaction-card:hover {
            border-color: rgba(255, 215, 0, 0.3);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.1);
        }
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        
        .transaction-info h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .transaction-info p {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .transaction-amount {
            font-size: 20px;
            font-weight: 700;
            color: #EF4444;
        }
        
        .transaction-amount.refund {
            color: #10B981;
        }
        
        .transaction-meta {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .status-badge.completed,
        .status-badge.successful {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .status-badge.refunded {
            background: rgba(59, 130, 246, 0.1);
            color: #3B82F6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .status-badge.pending {
            background: rgba(255, 193, 7, 0.1);
            color: #FFA000;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-badge.failed {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        .empty-state h3 {
            font-size: 20px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            padding: 12px 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-around;
            z-index: 100;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #BDBDBD;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .nav-item.active {
            color: #FFD700;
        }
        
        .nav-icon {
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>My Wallet</h1>
            <p>Track your spending and transactions</p>
        </div>
    </div>
    
    <div class="container">
        <div class="balance-card">
            <div class="balance-content">
                <div class="balance-label">Total Spent</div>
                <div class="balance-amount">GH₵<?php echo number_format($total_spent, 2); ?></div>
                <div class="balance-note">💳 All payments are secure and protected</div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($payments); ?></div>
                <div class="stat-label">Total Transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">GH₵<?php echo number_format($total_refunded, 2); ?></div>
                <div class="stat-label">Total Refunded</div>
            </div>
        </div>
        
        <h2 class="section-title">Transaction History</h2>
        
        <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $payment): ?>
                <div class="transaction-card">
                    <div class="transaction-header">
                        <div class="transaction-info">
                            <h3><?php echo htmlspecialchars($payment['worker_name'] ?? 'Worker Not Found'); ?></h3>
                            <p>Booking: <?php echo htmlspecialchars($payment['booking_reference']); ?></p>
                        </div>
                        <div class="transaction-amount <?php echo $payment['payment_status'] === 'refunded' ? 'refund' : ''; ?>">
                            <?php echo $payment['payment_status'] === 'refunded' ? '+' : '-'; ?>GH₵<?php echo number_format($payment['amount'], 2); ?>
                        </div>
                    </div>
                    <div class="transaction-meta">
                        <span>📅 <?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></span>
                        <span>💳 <?php echo ucfirst($payment['payment_method'] ?? 'N/A'); ?></span>
                        <span class="status-badge <?php echo $payment['payment_status']; ?>">
                            <?php echo ucfirst($payment['payment_status']); ?>
                        </span>
                        <?php if (!empty($payment['escrow_status'])): ?>
                            <span>🔒 Escrow: <?php echo ucfirst($payment['escrow_status']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">💳</div>
                <h3>No Transactions Yet</h3>
                <p>Your payment history will appear here</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="home.php" class="nav-item">
            <div class="nav-icon">🏠</div>
            <div>Home</div>
        </a>
        <a href="my_bookings.php" class="nav-item">
            <div class="nav-icon">📅</div>
            <div>Bookings</div>
        </a>
        <a href="wallet.php" class="nav-item active">
            <div class="nav-icon">💳</div>
            <div>Wallet</div>
        </a>
        <a href="chat.php" class="nav-item">
            <div class="nav-icon">💬</div>
            <div>Chat</div>
        </a>
        <a href="profile.php" class="nav-item">
            <div class="nav-icon">👤</div>
            <div>Profile</div>
        </a>
    </div>
    
    <script src="../js/theme-toggle.js"></script>
</body>
</html>
