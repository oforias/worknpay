<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

if (!is_admin()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$db = new db_connection();
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$where = "";
if ($filter === 'pending') $where = "WHERE b.booking_status = 'pending'";
elseif ($filter === 'completed') $where = "WHERE b.booking_status = 'completed'";
elseif ($filter === 'cancelled') $where = "WHERE b.booking_status IN ('cancelled', 'rejected')";

$bookings = $db->db_fetch_all("SELECT b.*, 
                                c.user_name as customer_name, c.user_email as customer_email,
                                w.user_name as worker_name, w.user_email as worker_email
                                FROM bookings b
                                JOIN users c ON b.customer_id = c.user_id
                                LEFT JOIN users w ON b.worker_id = w.user_id
                                $where
                                ORDER BY b.created_at DESC
                                LIMIT 100");

$total = $db->db_fetch_one("SELECT COUNT(*) as count FROM bookings")['count'];
$pending = $db->db_fetch_one("SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'pending'")['count'];
$completed = $db->db_fetch_one("SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'completed'")['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - Admin</title>
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
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        .filters {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }
        .filter-btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            color: var(--text-primary);
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }
        .filter-btn.active {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
        }
        .bookings-table {
            background: var(--bg-secondary);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: var(--bg-tertiary);
        }
        th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 14px;
        }
        td {
            padding: 16px;
            border-top: 1px solid var(--border-color);
        }
        tbody tr:hover {
            background: var(--bg-tertiary);
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.pending {
            background: rgba(255, 193, 7, 0.1);
            color: #FFA000;
        }
        .status-badge.completed {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
        }
        .status-badge.cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
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
            <li class="nav-item"><a href="admin_bookings.php" class="nav-link active"><span class="nav-icon">📅</span><span>Bookings</span></a></li>
            <li class="nav-item"><a href="admin_disputes.php" class="nav-link"><span class="nav-icon">⚖️</span><span>Disputes</span></a></li>
            <li class="nav-item"><a href="admin_payouts.php" class="nav-link"><span class="nav-icon">💰</span><span>Payouts</span></a></li>
            <li class="nav-item"><a href="admin_reports.php" class="nav-link"><span class="nav-icon">📈</span><span>Reports</span></a></li>
            <li class="nav-item"><a href="admin_settings.php" class="nav-link"><span class="nav-icon">⚙️</span><span>Settings</span></a></li>
            <li class="nav-item" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);"><a href="../actions/logout.php" class="nav-link"><span class="nav-icon">🚪</span><span>Logout</span></a></li>
        </ul>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>Bookings Management</h1>
            <p>View and manage all platform bookings</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $pending; ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $completed; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        
        <div class="filters">
            <a href="admin_bookings.php?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="admin_bookings.php?filter=pending" class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="admin_bookings.php?filter=completed" class="filter-btn <?php echo $filter === 'completed' ? 'active' : ''; ?>">Completed</a>
            <a href="admin_bookings.php?filter=cancelled" class="filter-btn <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
        </div>
        
        <div class="bookings-table">
            <table>
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Worker</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 40px;">No bookings found</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td style="font-weight: 600;"><?php echo $booking['booking_reference']; ?></td>
                                <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['worker_name'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td>GH₵<?php echo number_format($booking['estimated_price'], 2); ?></td>
                                <td><span class="status-badge <?php echo $booking['booking_status']; ?>"><?php echo ucfirst($booking['booking_status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
