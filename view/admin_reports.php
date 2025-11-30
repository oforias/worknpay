<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

if (!is_admin()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$db = new db_connection();

// Get statistics - Platform revenue (12% commission + instant payout fees)
// Calculate from payments table: customer_commission (7%) + worker_commission (5%)
$revenue_query = "SELECT 
                    COALESCE(SUM(customer_commission + worker_commission), 0) as commission_revenue
                  FROM payments
                  WHERE payment_status = 'successful'";
$result = $db->db_fetch_one($revenue_query);
$commission_revenue = ($result && $result['commission_revenue']) ? floatval($result['commission_revenue']) : 0;

// Calculate instant payout fees (2% of instant payouts)
$payout_fees_query = "SELECT 
                        COALESCE(SUM(payout_fee), 0) as payout_fees
                      FROM payouts
                      WHERE payout_type = 'instant' AND payout_status IN ('completed', 'pending')";
$result = $db->db_fetch_one($payout_fees_query);
$payout_fees = ($result && $result['payout_fees']) ? floatval($result['payout_fees']) : 0;

$total_revenue = $commission_revenue + $payout_fees;
$total_bookings = $db->db_fetch_one("SELECT COUNT(*) as count FROM bookings")['count'];
$total_users = $db->db_fetch_one("SELECT COUNT(*) as count FROM users")['count'];
$total_workers = $db->db_fetch_one("SELECT COUNT(*) as count FROM users WHERE user_role = 2")['count'];
$total_disputes = $db->db_fetch_one("SELECT COUNT(*) as count FROM disputes")['count'];
$open_disputes = $db->db_fetch_one("SELECT COUNT(*) as count FROM disputes WHERE dispute_status = 'open'")['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            padding: 24px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .logo {
            padding: 0 24px 24px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 24px;
        }
        .logo h1 {
            font-size: 24px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-menu {
            list-style: none;
            padding: 0 12px;
        }
        .nav-item {
            margin-bottom: 4px;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }
        .nav-link.active {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 165, 0, 0.1) 100%);
            color: #FFD700;
            font-weight: 600;
        }
        .nav-icon {
            font-size: 20px;
        }
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 24px;
        }
        .header {
            background: var(--header-bg);
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            color: white;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 8px;
        }
        .stat-label {
            font-size: 16px;
            color: var(--text-secondary);
        }
        .report-card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 16px;
        }
    </style>
</head>
<body class="dark-mode">
    <aside class="sidebar">
        <div class="logo">
            <h1>WorkNPay</h1>
            <p style="font-size: 12px; color: var(--text-secondary); margin-top: 4px;">Admin Panel</p>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link"><span class="nav-icon">📊</span><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="admin_users.php" class="nav-link"><span class="nav-icon">👥</span><span>Users</span></a></li>
            <li class="nav-item"><a href="admin_bookings.php" class="nav-link"><span class="nav-icon">📅</span><span>Bookings</span></a></li>
            <li class="nav-item"><a href="admin_disputes.php" class="nav-link"><span class="nav-icon">⚖️</span><span>Disputes</span></a></li>
            <li class="nav-item"><a href="admin_payouts.php" class="nav-link"><span class="nav-icon">💰</span><span>Payouts</span></a></li>
            <li class="nav-item"><a href="admin_reports.php" class="nav-link active"><span class="nav-icon">📈</span><span>Reports</span></a></li>
            <li class="nav-item"><a href="admin_settings.php" class="nav-link"><span class="nav-icon">⚙️</span><span>Settings</span></a></li>
            <li class="nav-item" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);"><a href="../actions/logout.php" class="nav-link"><span class="nav-icon">🚪</span><span>Logout</span></a></li>
        </ul>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>Platform Reports</h1>
            <p>Overview of platform performance and statistics</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">GH₵<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_bookings; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_workers; ?></div>
                <div class="stat-label">Active Workers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_disputes; ?></div>
                <div class="stat-label">Total Disputes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $open_disputes; ?></div>
                <div class="stat-label">Open Disputes</div>
            </div>
        </div>
        
        <div class="report-card">
            <div class="report-title">📊 Platform Summary</div>
            <p style="color: var(--text-secondary); line-height: 1.6;">
                The platform currently has <?php echo $total_users; ?> registered users, with <?php echo $total_workers; ?> active workers providing services. 
                A total of <?php echo $total_bookings; ?> bookings have been made, generating GH₵<?php echo number_format($total_revenue, 2); ?> in revenue.
                <?php if ($open_disputes > 0): ?>
                There are currently <?php echo $open_disputes; ?> open disputes that require attention.
                <?php endif; ?>
            </p>
        </div>
    </div>
</body>
</html>
