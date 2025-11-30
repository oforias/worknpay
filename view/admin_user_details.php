<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';

require_login('login.php');

if (!is_admin()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    header('Location: admin_users.php');
    exit();
}

$db = new db_connection();

// Get user details
$user = $db->db_fetch_one("SELECT u.*, 
                           CASE 
                               WHEN u.user_role = 1 THEN 'Customer'
                               WHEN u.user_role = 2 THEN 'Worker'
                               WHEN u.user_role = 3 THEN 'Admin'
                           END as role_name
                           FROM users u
                           WHERE u.user_id = $user_id");

if (!$user) {
    header('Location: admin_users.php?error=user_not_found');
    exit();
}

// Get worker profile if user is a worker
$worker_profile = null;
if ($user['user_role'] == 2) {
    $worker_profile = $db->db_fetch_one("SELECT * FROM worker_profiles WHERE user_id = $user_id");
}

// Get bookings
$bookings_as_customer = $db->db_fetch_all("SELECT b.*, u.user_name as worker_name 
                                           FROM bookings b
                                           LEFT JOIN users u ON b.worker_id = u.user_id
                                           WHERE b.customer_id = $user_id
                                           ORDER BY b.created_at DESC LIMIT 10");

$bookings_as_worker = $db->db_fetch_all("SELECT b.*, u.user_name as customer_name 
                                         FROM bookings b
                                         LEFT JOIN users u ON b.customer_id = u.user_id
                                         WHERE b.worker_id = $user_id
                                         ORDER BY b.created_at DESC LIMIT 10");

// Get disputes
$disputes = $db->db_fetch_all("SELECT d.*, b.booking_reference 
                               FROM disputes d
                               JOIN bookings b ON d.booking_id = b.booking_id
                               WHERE d.customer_id = $user_id OR d.worker_id = $user_id
                               ORDER BY d.created_at DESC LIMIT 5");

// Get statistics
$total_bookings_customer = $db->db_fetch_one("SELECT COUNT(*) as count FROM bookings WHERE customer_id = $user_id")['count'];
$total_bookings_worker = $db->db_fetch_one("SELECT COUNT(*) as count FROM bookings WHERE worker_id = $user_id")['count'];
$total_spent = $db->db_fetch_one("SELECT SUM(estimated_price) as total FROM bookings WHERE customer_id = $user_id AND booking_status = 'completed'")['total'] ?? 0;
$total_earned = $db->db_fetch_one("SELECT SUM(estimated_price) as total FROM bookings WHERE worker_id = $user_id AND booking_status = 'completed'")['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - <?php echo htmlspecialchars($user['user_name']); ?></title>
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
            padding: 20px;
        }
        .back-link {
            display: inline-block;
            color: #FFD700;
            text-decoration: none;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
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
        .role-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-top: 8px;
        }
        .role-badge.customer {
            background: rgba(59, 130, 246, 0.2);
            color: #3B82F6;
        }
        .role-badge.worker {
            background: rgba(16, 185, 129, 0.2);
            color: #10B981;
        }
        .role-badge.admin {
            background: rgba(168, 85, 247, 0.2);
            color: #A855F7;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }
        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-secondary);
        }
        .info-value {
            color: var(--text-primary);
        }
        .stat-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            text-align: center;
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
        .booking-item {
            padding: 12px;
            background: var(--bg-tertiary);
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.completed {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
        }
        .status-badge.pending {
            background: rgba(255, 193, 7, 0.1);
            color: #FFA000;
        }
        .status-badge.cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: #EF4444;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body class="dark-mode">
    <a href="admin_users.php" class="back-link">← Back to Users</a>
    
    <div class="header">
        <h1><?php echo htmlspecialchars($user['user_name']); ?></h1>
        <p><?php echo htmlspecialchars($user['user_email']); ?></p>
        <span class="role-badge <?php echo strtolower($user['role_name']); ?>">
            <?php echo $user['role_name']; ?>
        </span>
    </div>
    
    <!-- Statistics -->
    <div class="grid">
        <?php if ($user['user_role'] == 1): ?>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_bookings_customer; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">GH₵<?php echo number_format($total_spent, 2); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        <?php elseif ($user['user_role'] == 2): ?>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_bookings_worker; ?></div>
                <div class="stat-label">Total Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">GH₵<?php echo number_format($total_earned, 2); ?></div>
                <div class="stat-label">Total Earned</div>
            </div>
            <?php if ($worker_profile): ?>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($worker_profile['average_rating'], 1); ?>⭐</div>
                <div class="stat-label">Average Rating</div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="stat-card">
            <div class="stat-value"><?php echo count($disputes ?? []); ?></div>
            <div class="stat-label">Disputes</div>
        </div>
    </div>
    
    <!-- User Information -->
    <div class="grid">
        <div class="card">
            <div class="card-title">User Information</div>
            <div class="info-row">
                <span class="info-label">User ID:</span>
                <span class="info-value"><?php echo $user['user_id']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['user_email']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span class="info-value"><?php echo htmlspecialchars($user['user_phone'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Joined:</span>
                <span class="info-value"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
            </div>
        </div>
        
        <?php if ($worker_profile): ?>
        <div class="card">
            <div class="card-title">Worker Profile</div>
            <div class="info-row">
                <span class="info-label">Service Title:</span>
                <span class="info-value"><?php echo htmlspecialchars($worker_profile['service_title'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Experience:</span>
                <span class="info-value"><?php echo $worker_profile['experience_years'] ?? 0; ?> years</span>
            </div>
            <div class="info-row">
                <span class="info-label">Total Jobs:</span>
                <span class="info-value"><?php echo $worker_profile['total_jobs_completed'] ?? 0; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Available Balance:</span>
                <span class="info-value">GH₵<?php echo number_format($worker_profile['available_balance'] ?? 0, 2); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Bookings -->
    <?php if ($user['user_role'] == 1 && !empty($bookings_as_customer)): ?>
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">Recent Bookings (as Customer)</div>
        <?php foreach ($bookings_as_customer as $booking): ?>
            <div class="booking-item">
                <div>
                    <div style="font-weight: 600;"><?php echo $booking['booking_reference']; ?></div>
                    <div style="font-size: 14px; color: var(--text-secondary);">
                        Worker: <?php echo htmlspecialchars($booking['worker_name'] ?? 'N/A'); ?> | 
                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                    </div>
                </div>
                <div>
                    <span class="status-badge <?php echo $booking['booking_status']; ?>">
                        <?php echo ucfirst($booking['booking_status']); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if ($user['user_role'] == 2 && !empty($bookings_as_worker)): ?>
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">Recent Jobs (as Worker)</div>
        <?php foreach ($bookings_as_worker as $booking): ?>
            <div class="booking-item">
                <div>
                    <div style="font-weight: 600;"><?php echo $booking['booking_reference']; ?></div>
                    <div style="font-size: 14px; color: var(--text-secondary);">
                        Customer: <?php echo htmlspecialchars($booking['customer_name'] ?? 'N/A'); ?> | 
                        <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?>
                    </div>
                </div>
                <div>
                    <span class="status-badge <?php echo $booking['booking_status']; ?>">
                        <?php echo ucfirst($booking['booking_status']); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Disputes -->
    <?php if (!empty($disputes)): ?>
    <div class="card">
        <div class="card-title">Recent Disputes</div>
        <?php foreach ($disputes as $dispute): ?>
            <div class="booking-item">
                <div>
                    <div style="font-weight: 600;">Dispute #<?php echo $dispute['dispute_id']; ?></div>
                    <div style="font-size: 14px; color: var(--text-secondary);">
                        Booking: <?php echo $dispute['booking_reference']; ?> | 
                        <?php echo ucfirst(str_replace('_', ' ', $dispute['dispute_reason'])); ?>
                    </div>
                </div>
                <div>
                    <span class="status-badge <?php echo $dispute['dispute_status'] === 'open' ? 'pending' : 'completed'; ?>">
                        <?php echo ucfirst($dispute['dispute_status']); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</body>
</html>
