<?php
require_once '../settings/core.php';
require_login('login.php');

if (!is_admin()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$admin_name = get_user_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
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
        .settings-card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        .settings-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .setting-item {
            padding: 16px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .setting-item:last-child {
            border-bottom: none;
        }
        .setting-label {
            font-weight: 600;
            margin-bottom: 8px;
        }
        .setting-value {
            color: var(--text-secondary);
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
            <li class="nav-item"><a href="admin_reports.php" class="nav-link"><span class="nav-icon">📈</span><span>Reports</span></a></li>
            <li class="nav-item"><a href="admin_settings.php" class="nav-link active"><span class="nav-icon">⚙️</span><span>Settings</span></a></li>
            <li class="nav-item" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);"><a href="../actions/logout.php" class="nav-link"><span class="nav-icon">🚪</span><span>Logout</span></a></li>
        </ul>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>Platform Settings</h1>
            <p>Configure platform settings and preferences</p>
        </div>
        
        <div class="settings-card">
            <div class="settings-title">👤 Admin Account</div>
            <div class="setting-item">
                <div class="setting-label">Admin Name</div>
                <div class="setting-value"><?php echo htmlspecialchars($admin_name); ?></div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Role</div>
                <div class="setting-value">Administrator</div>
            </div>
        </div>
        
        <div class="settings-card">
            <div class="settings-title">💳 Payment Settings</div>
            <div class="setting-item">
                <div class="setting-label">Payment Gateway</div>
                <div class="setting-value">Paystack</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Platform Commission</div>
                <div class="setting-value">12% (7% customer + 5% worker)</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Instant Payout Fee</div>
                <div class="setting-value">2%</div>
            </div>
        </div>
        
        <div class="settings-card">
            <div class="settings-title">⚖️ Dispute Settings</div>
            <div class="setting-item">
                <div class="setting-label">Dispute Window</div>
                <div class="setting-value">48 hours after job completion</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Auto-Release Escrow</div>
                <div class="setting-value">24 hours after completion (if no dispute)</div>
            </div>
        </div>
        
        <div class="settings-card">
            <div class="settings-title">🔒 Security</div>
            <div class="setting-item">
                <div class="setting-label">Session Timeout</div>
                <div class="setting-value">30 minutes of inactivity</div>
            </div>
            <div class="setting-item">
                <div class="setting-label">Password Requirements</div>
                <div class="setting-value">Minimum 8 characters</div>
            </div>
        </div>
    </div>
</body>
</html>
