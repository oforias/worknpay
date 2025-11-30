<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

require_login('login.php');

if (!is_admin()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$db = new db_connection();

// Get filter from URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query based on filter
$where = "";
if ($filter === 'customers') {
    $where = "WHERE user_role = 1";
} elseif ($filter === 'workers') {
    $where = "WHERE user_role = 2";
} elseif ($filter === 'admins') {
    $where = "WHERE user_role = 3";
}

// Get users
$users_query = "SELECT u.*, 
                CASE 
                    WHEN u.user_role = 1 THEN 'Customer'
                    WHEN u.user_role = 2 THEN 'Worker'
                    WHEN u.user_role = 3 THEN 'Admin'
                END as role_name,
                (SELECT COUNT(*) FROM bookings WHERE customer_id = u.user_id) as bookings_as_customer,
                (SELECT COUNT(*) FROM bookings WHERE worker_id = u.user_id) as bookings_as_worker
                FROM users u
                $where
                ORDER BY u.created_at DESC";

$users = $db->db_fetch_all($users_query);
if (!$users) {
    $users = [];
}

// Get statistics
$total_users = $db->db_fetch_one("SELECT COUNT(*) as count FROM users")['count'];
$total_customers = $db->db_fetch_one("SELECT COUNT(*) as count FROM users WHERE user_role = 1")['count'];
$total_workers = $db->db_fetch_one("SELECT COUNT(*) as count FROM users WHERE user_role = 2")['count'];
$total_admins = $db->db_fetch_one("SELECT COUNT(*) as count FROM users WHERE user_role = 3")['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
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
        
        /* Sidebar */
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
        
        /* Main Content */
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
            flex-wrap: wrap;
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
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background: var(--bg-tertiary);
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            border-color: #FFD700;
        }
        
        .users-table {
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
            border-bottom: 1px solid var(--border-color);
        }
        
        td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tbody tr:hover {
            background: var(--bg-tertiary);
        }
        
        .role-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .role-badge.customer {
            background: rgba(59, 130, 246, 0.1);
            color: #3B82F6;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
        
        .role-badge.worker {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .role-badge.admin {
            background: rgba(168, 85, 247, 0.1);
            color: #A855F7;
            border: 1px solid rgba(168, 85, 247, 0.3);
        }
        
        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: var(--bg-tertiary);
            color: var(--text-primary);
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            background: var(--bg-primary);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 64px;
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
            <li class="nav-item">
                <a href="admin_dashboard.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin_users.php" class="nav-link active">
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
            <li class="nav-item" style="margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--border-color);">
                <a href="../actions/logout.php" class="nav-link">
                    <span class="nav-icon">🚪</span>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </aside>
    
    <div class="main-content">
        <div class="header">
            <h1>User Management</h1>
            <p>Manage all platform users</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_customers; ?></div>
                <div class="stat-label">Customers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_workers; ?></div>
                <div class="stat-label">Workers</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_admins; ?></div>
                <div class="stat-label">Admins</div>
            </div>
        </div>
        
        <div class="filters">
            <a href="admin_users.php?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All Users</a>
            <a href="admin_users.php?filter=customers" class="filter-btn <?php echo $filter === 'customers' ? 'active' : ''; ?>">Customers</a>
            <a href="admin_users.php?filter=workers" class="filter-btn <?php echo $filter === 'workers' ? 'active' : ''; ?>">Workers</a>
            <a href="admin_users.php?filter=admins" class="filter-btn <?php echo $filter === 'admins' ? 'active' : ''; ?>">Admins</a>
        </div>
        
        <div class="users-table">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <div class="empty-icon">👥</div>
                    <h3>No users found</h3>
                    <p style="color: var(--text-secondary); margin-top: 8px;">No users match the selected filter.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Bookings</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($user['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                                <td><?php echo htmlspecialchars($user['user_phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="role-badge <?php echo strtolower($user['role_name']); ?>">
                                        <?php echo $user['role_name']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['user_role'] == 1): ?>
                                        <?php echo $user['bookings_as_customer']; ?> as customer
                                    <?php elseif ($user['user_role'] == 2): ?>
                                        <?php echo $user['bookings_as_worker']; ?> as worker
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="admin_user_details.php?id=<?php echo $user['user_id']; ?>" class="action-btn">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
