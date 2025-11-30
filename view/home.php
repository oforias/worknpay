<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

// Redirect workers to their dashboard
if (is_worker()) {
    header('Location: worker_dashboard_new.php');
    exit();
}

// Only customers can access home page
if (!is_customer()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$user_name = get_user_name();

// Fetch real workers from database
$db = new db_connection();
$workers_query = "SELECT u.user_id, u.user_name, u.user_city, 
                  COALESCE(wp.bio, '') as bio, 
                  COALESCE(wp.skills, 'Service Provider') as skills, 
                  COALESCE(wp.hourly_rate, 50.00) as hourly_rate, 
                  COALESCE(wp.average_rating, 0) as average_rating, 
                  COALESCE(wp.total_jobs_completed, 0) as total_jobs_completed, 
                  COALESCE(wp.verification_badge, 0) as verification_badge
                  FROM users u
                  LEFT JOIN worker_profiles wp ON u.user_id = wp.user_id
                  WHERE u.user_role = 2 AND u.is_active = 1
                  ORDER BY wp.average_rating DESC, wp.total_jobs_completed DESC
                  LIMIT 10";
$workers = $db->db_fetch_all($workers_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - WorkNPay</title>
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
            max-width: 1200px;
            margin: 0 auto;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            body {
                max-width: 100%;
            }
        }
        
        /* Premium Dark Header */
        .header {
            background: var(--header-bg);
            backdrop-filter: blur(20px);
            color: white;
            padding: 24px 20px 32px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .welcome-text {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 4px;
        }
        
        .user-name {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .notification-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 215, 0, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .notification-btn:hover {
            background: rgba(255, 215, 0, 0.25);
            transform: scale(1.05);
        }
        
        .logout-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 215, 0, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            margin-left: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255, 215, 0, 0.25);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }
        
        /* Premium Search Bar */
        .search-container {
            position: relative;
            z-index: 1;
        }
        
        .search-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.2);
            background: white;
        }
        
        .search-input::placeholder {
            color: #BDBDBD;
        }
        
        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #FFD700;
            font-size: 18px;
        }
        
        /* Luxury Promo Banner */
        .promo-banner {
            margin: 20px;
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
            padding: 24px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid rgba(255, 215, 0, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .promo-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .promo-content {
            position: relative;
            z-index: 1;
        }
        
        .promo-content h3 {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 4px;
        }
        
        .promo-content p {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 4px;
        }
        
        .promo-code {
            font-size: 12px;
            color: #FFD700;
            font-weight: 600;
        }
        
        .claim-btn {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #1a1f36;
            padding: 12px 28px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(255, 215, 0, 0.4);
            position: relative;
            z-index: 1;
        }
        
        .claim-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(255, 215, 0, 0.6);
        }
        
        .claim-btn:active {
            transform: translateY(0);
        }
        
        /* Luxury Sections */
        .section {
            padding: 0 20px;
            margin-bottom: 32px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            position: relative;
            padding-left: 16px;
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
        
        .see-all {
            color: #FFD700;
            font-size: 14px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .see-all:hover {
            color: #FFA500;
            transform: translateX(4px);
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
        }
        
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
            }
        }
        
        .category-card {
            background: var(--bg-secondary);
            padding: 24px 16px;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--card-shadow);
            text-decoration: none;
            display: block;
            border: 2px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 165, 0, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(255, 215, 0, 0.3);
            border-color: rgba(255, 215, 0, 0.5);
        }
        
        .category-card:hover::before {
            opacity: 1;
        }
        
        .category-card:active {
            transform: translateY(-4px);
        }
        
        .category-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .category-icon.electrical { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
        .category-icon.plumbing { background: linear-gradient(135deg, #4FC3F7 0%, #0288D1 100%); }
        .category-icon.cleaning { background: linear-gradient(135deg, #BA68C8 0%, #7B1FA2 100%); }
        .category-icon.tutoring { background: linear-gradient(135deg, #81C784 0%, #388E3C 100%); }
        .category-icon.tailoring { background: linear-gradient(135deg, #F06292 0%, #C2185B 100%); }
        .category-icon.repair { background: linear-gradient(135deg, #FF8A65 0%, #D84315 100%); }
        
        .category-name {
            font-size: 14px;
            color: var(--text-primary);
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        /* Premium Worker Cards */
        .worker-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 16px;
            display: flex;
            gap: 16px;
            box-shadow: var(--card-shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }
        
        .worker-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.05) 0%, rgba(255, 165, 0, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .worker-card:hover {
            box-shadow: 0 12px 40px rgba(255, 215, 0, 0.2);
            transform: translateY(-4px);
            border-color: rgba(255, 215, 0, 0.3);
        }
        
        .worker-card:hover::before {
            opacity: 1;
        }
        
        .worker-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: #E0E0E0;
            position: relative;
        }
        
        .verified-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 20px;
            height: 20px;
            background: #FFD700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border: 2px solid white;
        }
        
        .worker-info {
            flex: 1;
        }
        
        .worker-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }
        
        .worker-role {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        
        .worker-meta {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .rating {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .location {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .worker-price {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .book-btn {
            background: #0052CC;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 82, 204, 0.3);
        }
        
        .book-btn:hover {
            background: #0747A6;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 82, 204, 0.4);
        }
        
        .book-btn:active {
            transform: scale(0.98);
        }
        
        .top-rated-badge {
            background: #FFF9E6;
            color: #F57C00;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 8px;
        }
        
        /* Bottom Navigation */
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
        }
        
        .nav-item.active {
            color: #0052CC;
        }
        
        .nav-icon {
            font-size: 24px;
        }
    </style>
</head>
<body>
    <!-- Blue Header -->
    <div class="header">
        <div class="header-top">
            <div>
                <div class="welcome-text">Welcome back,</div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div class="notification-btn">🔔</div>
                <a href="../actions/logout_action.php" class="logout-btn" title="Logout">🚪</a>
            </div>
        </div>
        
        <div class="search-container">
            <form action="browse_workers.php" method="GET" style="position: relative;">
                <span class="search-icon">🔍</span>
                <input type="text" name="search" class="search-input" placeholder="Find a Service...">
            </form>
        </div>
    </div>
    
    <!-- Promo Banner -->
    <div class="promo-banner">
        <div class="promo-content">
            <h3>Special Offer!</h3>
            <p>20% Off First Booking</p>
            <div class="promo-code">Use code: FIRST20</div>
        </div>
        <button class="claim-btn">Claim</button>
    </div>
    
    <!-- Categories -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Categories</h2>
            <a href="#" class="see-all">See All</a>
        </div>
        <div class="categories-grid">
            <a href="browse_workers.php?category=Electrical" class="category-card">
                <div class="category-icon electrical">⚡</div>
                <div class="category-name">Electrical</div>
            </a>
            <a href="browse_workers.php?category=Plumbing" class="category-card">
                <div class="category-icon plumbing">🔧</div>
                <div class="category-name">Plumbing</div>
            </a>
            <a href="browse_workers.php?category=Cleaning" class="category-card">
                <div class="category-icon cleaning">✨</div>
                <div class="category-name">Cleaning</div>
            </a>
            <a href="browse_workers.php?category=Tutoring" class="category-card">
                <div class="category-icon tutoring">🎓</div>
                <div class="category-name">Tutoring</div>
            </a>
            <a href="browse_workers.php?category=Tailoring" class="category-card">
                <div class="category-icon tailoring">👔</div>
                <div class="category-name">Tailoring</div>
            </a>
            <a href="browse_workers.php?category=Repair" class="category-card">
                <div class="category-icon repair">🔨</div>
                <div class="category-name">Repair</div>
            </a>
        </div>
    </div>
    
    <!-- Featured Workers -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Featured Workers</h2>
            <a href="browse_workers.php" class="see-all">See All</a>
        </div>
        
        <?php if (!empty($workers)): ?>
            <?php foreach ($workers as $worker): ?>
                <div class="worker-card" data-worker-id="<?php echo $worker['user_id']; ?>">
                    <div class="worker-avatar">
                        <?php if ($worker['verification_badge']): ?>
                            <div class="verified-badge">✓</div>
                        <?php endif; ?>
                    </div>
                    <div class="worker-info">
                        <?php if ($worker['average_rating'] >= 4.5): ?>
                            <div class="top-rated-badge">Top Rated</div>
                        <?php endif; ?>
                        <div class="worker-name"><?php echo htmlspecialchars($worker['user_name']); ?></div>
                        <div class="worker-role"><?php echo htmlspecialchars($worker['skills'] ?? 'Service Provider'); ?></div>
                        <div class="worker-meta">
                            <div class="rating">⭐ <?php echo number_format($worker['average_rating'], 1); ?> (<?php echo $worker['total_jobs_completed']; ?>)</div>
                            <div class="location">📍 <?php echo htmlspecialchars($worker['user_city'] ?? 'Ghana'); ?></div>
                        </div>
                        <div class="worker-price">GH₵<?php echo number_format($worker['hourly_rate'], 2); ?>/hr</div>
                    </div>
                    <button class="book-btn" onclick="bookWorker(<?php echo $worker['user_id']; ?>, event)">Book Now</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 40px 20px; color: #757575;">
                <div style="font-size: 48px; margin-bottom: 16px;">👷</div>
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">No Workers Available</div>
                <div style="font-size: 14px;">Check back soon for available service providers!</div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <a href="home.php" class="nav-item active">
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
        <a href="profile.php" class="nav-item">
            <div class="nav-icon">👤</div>
            <div>Profile</div>
        </a>
    </div>
    
    <script>
        // Claim promo code
        document.querySelector('.claim-btn').addEventListener('click', function() {
            alert('Promo code FIRST20 copied! Use it at checkout for 20% off your first booking.');
            // Copy to clipboard
            navigator.clipboard.writeText('FIRST20').then(() => {
                this.textContent = 'Claimed!';
                this.style.background = '#00C853';
                setTimeout(() => {
                    this.textContent = 'Claim';
                    this.style.background = '#0052CC';
                }, 2000);
            });
        });
        
        // Book Now function
        function bookWorker(workerId, event) {
            event.stopPropagation();
            window.location.href = `booking.php?worker_id=${workerId}`;
        }
        
        // Notification bell
        document.querySelector('.notification-btn').addEventListener('click', function() {
            alert('You have no new notifications.');
            // In production: window.location.href = 'notifications.php';
        });
        
        // Worker card click (view profile)
        document.querySelectorAll('.worker-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Don't trigger if clicking the book button
                if (e.target.classList.contains('book-btn')) return;
                
                // Get worker ID from data attribute
                const workerId = this.dataset.workerId;
                
                // Redirect to worker profile
                window.location.href = `worker_profile.php?id=${workerId}`;
            });
            
            // Add cursor pointer
            card.style.cursor = 'pointer';
        });
    </script>
</body>
</html>
