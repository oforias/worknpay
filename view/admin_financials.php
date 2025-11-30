<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

if (!is_admin()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$admin_name = get_user_name();
$db = new db_connection();

// Get financial statistics
$financials = [];

// Total commission revenue from completed bookings
$commission_query = "SELECT 
                        COALESCE(SUM(customer_commission), 0) as customer_commission,
                        COALESCE(SUM(worker_commission), 0) as worker_commission,
                        COALESCE(SUM(customer_commission + worker_commission), 0) as total_commission,
                        COUNT(*) as completed_bookings
                     FROM payments
                     WHERE payment_status = 'successful'";
$result = $db->db_fetch_one($commission_query);
$financials['customer_commission'] = $result ? floatval($result['customer_commission']) : 0;
$financials['worker_commission'] = $result ? floatval($result['worker_commission']) : 0;
$financials['total_commission'] = $result ? floatval($result['total_commission']) : 0;
$financials['completed_bookings'] = $result ? intval($result['completed_bookings']) : 0;

// Instant payout fees (2% of instant payouts)
$payout_fees_query = "SELECT 
                        COALESCE(SUM(payout_fee), 0) as total_fees,
                        COUNT(*) as instant_payouts
                      FROM payouts
                      WHERE payout_type = 'instant' AND payout_status IN ('completed', 'pending')";
$result = $db->db_fetch_one($payout_fees_query);
$financials['payout_fees'] = $result ? floatval($result['total_fees']) : 0;
$financials['instant_payouts'] = $result ? intval($result['instant_payouts']) : 0;

// Total revenue
$financials['total_revenue'] = $financials['total_commission'] + $financials['payout_fees'];

// Total amount processed through platform
$processed_query = "SELECT COALESCE(SUM(amount), 0) as total_processed FROM payments WHERE payment_status = 'successful'";
$result = $db->db_fetch_one($processed_query);
$financials['total_processed'] = $result ? floatval($result['total_processed']) : 0;

// Pending payouts (money owed to workers)
$pending_payouts_query = "SELECT COALESCE(SUM(net_amount), 0) as pending_amount FROM payouts WHERE payout_status = 'pending'";
$result = $db->db_fetch_one($pending_payouts_query);
$financials['pending_payouts'] = $result ? floatval($result['pending_amount']) : 0;

// Escrow balance (money held for completed jobs)
$escrow_query = "SELECT COALESCE(SUM(worker_payout), 0) as escrow_balance FROM payments WHERE escrow_status = 'held'";
$result = $db->db_fetch_one($escrow_query);
$financials['escrow_balance'] = $result ? floatval($result['escrow_balance']) : 0;

// Recent transactions
$recent_transactions = $db->db_fetch_all("
    SELECT 
        p.payment_id,
        p.amount,
        p.customer_commission,
        p.worker_commission,
        p.payment_date,
        b.booking_reference,
        u.user_name as customer_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.booking_id
    JOIN users u ON b.customer_id = u.user_id
    WHERE p.payment_status = 'successful'
    ORDER BY p.payment_date DESC
    LIMIT 10
");
if (!$recent_transactions) {
    $recent_transactions = [];
}

// Monthly revenue breakdown
$monthly_revenue = $db->db_fetch_all("
    SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month,
        COALESCE(SUM(customer_commission + worker_commission), 0) as commission,
        COUNT(*) as transactions
    FROM payments
    WHERE payment_status = 'successful'
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
if (!$monthly_revenue) {
    $monthly_revenue = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Dashboard - WorkNPay Admin</title>
    <style>
        :root {
            --bg-primary: #F5F7FA;
            --bg-secondary: #FFFFFF;
            --text-primary: #1a1f36;
            --text-secondary: #6B7280;
            --border-color: #E5E7EB;
            --header-bg: linear-gradient(135deg, #7C3AED 0%, #A78BFA 100%);
            --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --info-color: #3B82F6;
        }
        
        body.dark-mode {
            --bg-primary: #0A0E1A;
            --bg-secondary: rgba(255, 255, 255, 0.03);
            --text-primary: rgba(255, 255, 255, 0.95);
            --text-secondary: rgba(255, 255, 255, 0.6);
            --border-color: rgba(255, 215, 0, 0.2);
            --header-bg: linear-gradient(135deg, rgba(10, 14, 26, 0.95) 0%, rgba(20, 24, 36, 0.95) 100%);
            --card-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            padding-bottom: 20px;
            color: var(--text-primary);
            transition: background 0.3s ease, color 0.3s ease;
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
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
            margin-bottom: 24px;
            position: relative;
            z-index: 10;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            align-items: center;
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
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .stat-value.success {
            color: var(--success-color);
        }
        
        .stat-value.warning {
            color: var(--warning-color);
        }
        
        .stat-value.info {
            color: var(--info-color);
        }
        
        .stat-subtitle {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .card {
            background: var(--bg-secondary);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-primary);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            border-bottom: 2px solid var(--border-color);
        }
        
        td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        
        tr:hover {
            background: rgba(124, 58, 237, 0.05);
        }
        
        .amount {
            font-weight: 600;
            color: var(--success-color);
        }
        
        .commission {
            font-size: 12px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-actions">
            <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
            <button class="theme-toggle" onclick="toggleTheme()">
                <span id="themeIcon">🌙</span>
                <span id="themeText">Dark Mode</span>
            </button>
        </div>
        <h1>💰 Financial Dashboard</h1>
        <p>Platform revenue and transaction overview</p>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value success">GH₵<?php echo number_format($financials['total_revenue'], 2); ?></div>
                <div class="stat-subtitle">Commission + Fees</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Commission Revenue</div>
                <div class="stat-value">GH₵<?php echo number_format($financials['total_commission'], 2); ?></div>
                <div class="stat-subtitle">12% from bookings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Payout Fees</div>
                <div class="stat-value">GH₵<?php echo number_format($financials['payout_fees'], 2); ?></div>
                <div class="stat-subtitle">2% instant payout fees</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Total Processed</div>
                <div class="stat-value info">GH₵<?php echo number_format($financials['total_processed'], 2); ?></div>
                <div class="stat-subtitle"><?php echo $financials['completed_bookings']; ?> transactions</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Escrow Balance</div>
                <div class="stat-value warning">GH₵<?php echo number_format($financials['escrow_balance'], 2); ?></div>
                <div class="stat-subtitle">Held for completed jobs</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-label">Pending Payouts</div>
                <div class="stat-value warning">GH₵<?php echo number_format($financials['pending_payouts'], 2); ?></div>
                <div class="stat-subtitle">Awaiting processing</div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-title">Revenue Breakdown</div>
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div>
                    <div class="stat-label">Customer Commission (7%)</div>
                    <div class="stat-value" style="font-size: 24px;">GH₵<?php echo number_format($financials['customer_commission'], 2); ?></div>
                </div>
                <div>
                    <div class="stat-label">Worker Commission (5%)</div>
                    <div class="stat-value" style="font-size: 24px;">GH₵<?php echo number_format($financials['worker_commission'], 2); ?></div>
                </div>
                <div>
                    <div class="stat-label">Instant Payout Fees</div>
                    <div class="stat-value" style="font-size: 24px;">GH₵<?php echo number_format($financials['payout_fees'], 2); ?></div>
                    <div class="stat-subtitle"><?php echo $financials['instant_payouts']; ?> instant payouts</div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($monthly_revenue)): ?>
        <div class="card">
            <div class="card-title">Monthly Revenue</div>
            <table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Commission</th>
                        <th>Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_revenue as $month): ?>
                    <tr>
                        <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                        <td class="amount">GH₵<?php echo number_format($month['commission'], 2); ?></td>
                        <td><?php echo $month['transactions']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($recent_transactions)): ?>
        <div class="card">
            <div class="card-title">Recent Transactions</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Commission</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $txn): ?>
                    <tr>
                        <td><?php echo date('M d, Y H:i', strtotime($txn['payment_date'])); ?></td>
                        <td><?php echo htmlspecialchars($txn['booking_reference']); ?></td>
                        <td><?php echo htmlspecialchars($txn['customer_name']); ?></td>
                        <td class="amount">GH₵<?php echo number_format($txn['amount'], 2); ?></td>
                        <td>
                            <div class="amount">GH₵<?php echo number_format($txn['customer_commission'] + $txn['worker_commission'], 2); ?></div>
                            <div class="commission">
                                Customer: GH₵<?php echo number_format($txn['customer_commission'], 2); ?> | 
                                Worker: GH₵<?php echo number_format($txn['worker_commission'], 2); ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
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
    </script>
</body>
</html>
