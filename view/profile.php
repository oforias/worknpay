<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

$user_id = get_user_id();
$db = new db_connection();

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = $user_id";
$user = $db->db_fetch_one($user_query);

// Get booking stats for customers
$stats_query = "SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
                SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings
                FROM bookings 
                WHERE customer_id = $user_id";
$stats = $db->db_fetch_one($stats_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - WorkNPay</title>
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
            padding: 24px 20px 80px;
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
            text-align: center;
        }
        
        .profile-avatar-large {
            width: 100px;
            height: 100px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            box-shadow: 0 8px 32px rgba(255, 215, 0, 0.4);
            border: 4px solid rgba(255, 255, 255, 0.2);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .container {
            max-width: 800px;
            margin: -40px auto 0;
            padding: 0 20px 24px;
            position: relative;
            z-index: 10;
        }
        
        .stats-card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        
        .stat-item {
            text-align: center;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .section {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 20px;
            margin-bottom: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
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
            height: 20px;
            background: linear-gradient(180deg, #FFD700 0%, #FFA500 100%);
            border-radius: 2px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            margin-bottom: 12px;
            border: 1px solid var(--border-color);
        }
        
        .info-label {
            font-size: 14px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            text-decoration: none;
            color: inherit;
        }
        
        .menu-item:hover {
            border-color: rgba(255, 215, 0, 0.3);
            background: rgba(255, 215, 0, 0.05);
        }
        
        .menu-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .menu-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
        }
        
        .menu-text h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }
        
        .menu-text p {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .menu-arrow {
            font-size: 20px;
            color: var(--text-secondary);
        }
        
        .logout-btn {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);
            margin-top: 20px;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(239, 68, 68, 0.5);
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
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="profile-avatar-large">👤</div>
            <h1><?php echo htmlspecialchars($user['user_name']); ?></h1>
            <p><?php echo htmlspecialchars($user['user_email']); ?></p>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-card">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['total_bookings'] ?? 0; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['completed_bookings'] ?? 0; ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $stats['pending_bookings'] ?? 0; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Personal Information</h2>
            <div class="info-row">
                <div class="info-label">
                    <span>👤</span>
                    <span>Full Name</span>
                </div>
                <div class="info-value"><?php echo htmlspecialchars($user['user_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">
                    <span>📧</span>
                    <span>Email</span>
                </div>
                <div class="info-value"><?php echo htmlspecialchars($user['user_email']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">
                    <span>📱</span>
                    <span>Phone</span>
                </div>
                <div class="info-value"><?php echo htmlspecialchars($user['user_phone'] ?? 'Not set'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">
                    <span>📍</span>
                    <span>Location</span>
                </div>
                <div class="info-value"><?php echo htmlspecialchars($user['user_city'] ?? 'Not set'); ?></div>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Settings</h2>
            
            <a href="edit_profile.php" class="menu-item">
                <div class="menu-item-left">
                    <div class="menu-icon">✏️</div>
                    <div class="menu-text">
                        <h4>Edit Profile</h4>
                        <p>Update your personal information</p>
                    </div>
                </div>
                <div class="menu-arrow">›</div>
            </a>
            
            <a href="change_password.php" class="menu-item">
                <div class="menu-item-left">
                    <div class="menu-icon">🔒</div>
                    <div class="menu-text">
                        <h4>Change Password</h4>
                        <p>Update your account password</p>
                    </div>
                </div>
                <div class="menu-arrow">›</div>
            </a>
            
            <a href="#" class="menu-item" onclick="alert('Notification settings coming soon!'); return false;">
                <div class="menu-item-left">
                    <div class="menu-icon">🔔</div>
                    <div class="menu-text">
                        <h4>Notifications</h4>
                        <p>Manage your notification preferences</p>
                    </div>
                </div>
                <div class="menu-arrow">›</div>
            </a>
            
            <a href="#" class="menu-item" onclick="alert('Privacy settings coming soon!'); return false;">
                <div class="menu-item-left">
                    <div class="menu-icon">🛡️</div>
                    <div class="menu-text">
                        <h4>Privacy & Security</h4>
                        <p>Control your privacy settings</p>
                    </div>
                </div>
                <div class="menu-arrow">›</div>
            </a>
        </div>
        
        <div class="section">
            <h2 class="section-title">Support</h2>
            
            <a href="#" class="menu-item" onclick="alert('Help center coming soon!'); return false;">
                <div class="menu-item-left">
                    <div class="menu-icon">❓</div>
                    <div class="menu-text">
                        <h4>Help Center</h4>
                        <p>Get answers to common questions</p>
                    </div>
                </div>
                <div class="menu-arrow">›</div>
            </a>
            
            <a href="#" class="menu-item" onclick="alert('Contact support: support@worknpay.com'); return false;">
                <div class="menu-item-left">
                    <div class="menu-icon">💬</div>
                    <div class="menu-text">
                        <h4>Contact Support</h4>
                        <p>Reach out to our support team</p>
                    </div>
                </div>
                <div class="menu-arrow">›</div>
            </a>
            
            <a href="#" class="menu-item" onclick="alert('Terms & Privacy coming soon!'); return false;">
                <div class="menu-item-left">
                    <div class="menu-icon">📄</div>
                    <div class="menu-text">
                        <h4>Terms & Privacy</h4>
                        <p>Read our terms and privacy policy</p>
                    </div>
                </div>
                <div class="menu-arrow">›</div>
            </a>
        </div>
        
        <button class="logout-btn" onclick="if(confirm('Are you sure you want to logout?')) window.location.href='../actions/logout_action.php'">
            🚪 Logout
        </button>
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
        <a href="wallet.php" class="nav-item">
            <div class="nav-icon">💳</div>
            <div>Wallet</div>
        </a>
        <a href="chat.php" class="nav-item">
            <div class="nav-icon">💬</div>
            <div>Chat</div>
        </a>
        <a href="profile.php" class="nav-item active">
            <div class="nav-icon">👤</div>
            <div>Profile</div>
        </a>
    </div>
    
    <script src="../js/theme-toggle.js"></script>
</body>
</html>
