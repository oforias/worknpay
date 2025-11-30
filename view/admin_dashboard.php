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

// Get statistics
$stats = [];

// Total users
$users_query = "SELECT COUNT(*) as total FROM users";
$result = $db->db_fetch_one($users_query);
$stats['total_users'] = $result ? $result['total'] : 0;

// Total workers
$workers_query = "SELECT COUNT(*) as total FROM users WHERE user_role = 2";
$result = $db->db_fetch_one($workers_query);
$stats['total_workers'] = $result ? $result['total'] : 0;

// Total customers
$customers_query = "SELECT COUNT(*) as total FROM users WHERE user_role = 1";
$result = $db->db_fetch_one($customers_query);
$stats['total_customers'] = $result ? $result['total'] : 0;

// Total bookings
$bookings_query = "SELECT COUNT(*) as total FROM bookings";
$result = $db->db_fetch_one($bookings_query);
$stats['total_bookings'] = $result ? $result['total'] : 0;

// Pending bookings
$pending_bookings_query = "SELECT COUNT(*) as total FROM bookings WHERE booking_status = 'pending'";
$result = $db->db_fetch_one($pending_bookings_query);
$stats['pending_bookings'] = $result ? $result['total'] : 0;

// Completed bookings
$completed_bookings_query = "SELECT COUNT(*) as total FROM bookings WHERE booking_status = 'completed'";
$result = $db->db_fetch_one($completed_bookings_query);
$stats['completed_bookings'] = $result ? $result['total'] : 0;

// Total revenue
$revenue_query = "SELECT SUM(estimated_price) as total FROM bookings WHERE booking_status = 'completed'";
$result = $db->db_fetch_one($revenue_query);
$stats['total_revenue'] = ($result && $result['total']) ? $result['total'] : 0;

// Pending payouts
$payouts_query = "SELECT COUNT(*) as total FROM payouts WHERE payout_status = 'pending'";
$result = $db->db_fetch_one($payouts_query);
$stats['pending_payouts'] = $result ? $result['total'] : 0;

// Recent activities
$recent_bookings = $db->db_fetch_all("SELECT b.*, u.user_name as customer_name, w.user_name as worker_name 
                                       FROM bookings b
                                       JOIN users u ON b.customer_id = u.user_id
                                       LEFT JOIN users w ON b.worker_id = w.user_id
                                       ORDER BY b.created_at DESC LIMIT 5");
if (!$recent_bookings) {
    $recent_bookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WorkNPay</title>
    <style>
        :root {
            /* Light Mode Colors */
            --bg-primary: #F5F7FA;
            --bg-secondary: #FFFFFF;
            --bg-tertiary: #F5F7FA;
            --text-primary: #111827;
            --text-secondary: #374151;
            --border-color: #E5E7EB;
            --header-bg: linear-gradient(135deg, #7C3AED 0%, #A78BFA 100%);
            --card-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --hover-shadow: 0 4px 12px rgba(124, 58, 237, 0.1);
            --modal-bg: #FFFFFF;
            --accent-color: #7C3AED;
            --accent-hover: #6D28D9;
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
            --accent-color: #FFD700;
            --accent-hover: #FFA500;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            color: var(--text-primary);
            transition: all 0.3s ease;
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
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-color);
            padding: 24px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        
        .logo {
            padding: 0 24px 24px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 24px;
        }
        
        .logo h1 {
            font-size: 24px;
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .logo p {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 4px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 24px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: rgba(124, 58, 237, 0.1);
            color: var(--accent-color);
        }
        
        body.dark-mode .nav-link:hover {
            background: rgba(255, 215, 0, 0.1);
            color: #FFD700;
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.15) 0%, rgba(167, 139, 250, 0.15) 100%);
            color: var(--accent-color);
            border-right: 3px solid var(--accent-color);
        }
        
        body.dark-mode .nav-link.active {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 165, 0, 0.15) 100%);
            color: #FFD700;
            border-right: 3px solid #FFD700;
        }
        
        .nav-icon {
            font-size: 20px;
            width: 24px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 24px;
            position: relative;
            z-index: 1;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .header h2 {
            font-size: 28px;
            color: var(--text-primary);
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .theme-toggle {
            background: rgba(124, 58, 237, 0.1);
            border: 1px solid rgba(124, 58, 237, 0.3);
            border-radius: 20px;
            padding: 8px 16px;
            color: var(--accent-color);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .theme-toggle:hover {
            background: rgba(124, 58, 237, 0.2);
            transform: translateY(-2px);
        }
        
        body.dark-mode .theme-toggle {
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            color: #FFD700;
        }
        
        body.dark-mode .theme-toggle:hover {
            background: rgba(255, 215, 0, 0.2);
        }
        
        .logout-btn {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 20px;
            padding: 8px 16px;
            color: #EF4444;
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
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        body.dark-mode .stat-card::before {
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--hover-shadow);
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.users { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); }
        .stat-icon.workers { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
        .stat-icon.bookings { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); }
        .stat-icon.revenue { background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%); }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        
        .action-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
        }
        
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--hover-shadow);
            border-color: #FFD700;
        }
        
        .action-icon {
            font-size: 32px;
            margin-bottom: 12px;
        }
        
        .action-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        /* Recent Activity */
        .activity-card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: var(--card-shadow);
        }
        
        .activity-header {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .activity-item {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-item:hover {
            background: rgba(255, 215, 0, 0.05);
            border-radius: 8px;
        }
        
        .activity-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .activity-meta {
            font-size: 12px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body class="dark-mode">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <h1>WorkNPay</h1>
                <p>Admin Panel</p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link active">
                        <span class="nav-icon">📊</span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_users.php" class="nav-link">
                        <span class="nav-icon">👥</span>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_bookings.php" class="nav-link">
                        <span class="nav-icon">📅</span>
                        <span>Bookings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_disputes.php" class="nav-link">
                        <span class="nav-icon">⚖️</span>
                        <span>Disputes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_payouts.php" class="nav-link">
                        <span class="nav-icon">💰</span>
                        <span>Payouts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_reports.php" class="nav-link">
                        <span class="nav-icon">📈</span>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="admin_settings.php" class="nav-link">
                        <span class="nav-icon">⚙️</span>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <div>
                    <h2>Dashboard Overview</h2>
                    <p style="color: var(--text-secondary); margin-top: 4px;">Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</p>
                </div>
                <div class="header-actions">
                    <button class="theme-toggle" onclick="toggleTheme()">
                        <span id="themeIcon">🌙</span>
                        <span id="themeText">Dark Mode</span>
                    </button>
                    <button class="logout-btn" onclick="logout()">
                        <span>🚪</span>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['total_users']); ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                        <div class="stat-icon users">👥</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['total_workers']); ?></div>
                            <div class="stat-label">Active Workers</div>
                        </div>
                        <div class="stat-icon workers">🔧</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo number_format($stats['total_bookings']); ?></div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                        <div class="stat-icon bookings">📅</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value">GH₵<?php echo number_format($stats['total_revenue'], 0); ?></div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                        <div class="stat-icon revenue">💰</div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <h3 style="font-size: 20px; margin-bottom: 16px; color: var(--text-primary);">Quick Actions</h3>
            <div class="quick-actions">
                <a href="admin_users.php" class="action-card">
                    <div class="action-icon">👥</div>
                    <div class="action-title">Manage Users</div>
                </a>
                <a href="admin_bookings.php" class="action-card">
                    <div class="action-icon">📅</div>
                    <div class="action-title">View Bookings</div>
                </a>
                <a href="admin_disputes.php" class="action-card">
                    <div class="action-icon">⚖️</div>
                    <div class="action-title">Manage Disputes</div>
                    <?php 
                    $open_disputes_count = $db->db_fetch_one("SELECT COUNT(*) as count FROM disputes WHERE dispute_status = 'open'");
                    if ($open_disputes_count && $open_disputes_count['count'] > 0): 
                    ?>
                        <div style="background: #FFA000; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; margin-top: 8px; display: inline-block;">
                            <?php echo $open_disputes_count['count']; ?> Open
                        </div>
                    <?php endif; ?>
                </a>
                <a href="admin_payouts.php" class="action-card">
                    <div class="action-icon">💰</div>
                    <div class="action-title">Process Payouts</div>
                    <?php if ($stats['pending_payouts'] > 0): ?>
                        <div style="background: #EF4444; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; margin-top: 8px; display: inline-block;">
                            <?php echo $stats['pending_payouts']; ?> Pending
                        </div>
                    <?php endif; ?>
                </a>
                <a href="admin_reports.php" class="action-card">
                    <div class="action-icon">📈</div>
                    <div class="action-title">View Reports</div>
                </a>
            </div>
            
            <!-- Recent Activity -->
            <div class="activity-card">
                <div class="activity-header">Recent Bookings</div>
                <?php if (!empty($recent_bookings)): ?>
                    <?php foreach ($recent_bookings as $booking): ?>
                        <div class="activity-item">
                            <div class="activity-title">
                                <?php echo htmlspecialchars($booking['customer_name']); ?> 
                                <?php if ($booking['worker_name']): ?>
                                    → <?php echo htmlspecialchars($booking['worker_name']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="activity-meta">
                                <?php echo ucfirst($booking['booking_status']); ?> • 
                                GH₵<?php echo number_format($booking['estimated_price'], 2); ?> • 
                                <?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                        No recent bookings
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="../js/theme-toggle.js"></script>
    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../actions/logout_action.php';
            }
        }
    </script>
</body>
</html>
