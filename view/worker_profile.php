<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

$worker_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($worker_id <= 0) {
    header('Location: home.php');
    exit();
}

$db = new db_connection();

// Fetch worker details
$worker_query = "SELECT u.user_id, u.user_name, u.user_email, u.user_city, u.user_phone,
                 wp.bio, wp.skills, wp.hourly_rate, wp.average_rating, wp.total_jobs_completed, 
                 wp.verification_badge, wp.experience_years
                 FROM users u
                 LEFT JOIN worker_profiles wp ON u.user_id = wp.user_id
                 WHERE u.user_id = $worker_id AND u.user_role = 2 AND u.is_active = 1";
$worker = $db->db_fetch_one($worker_query);

if (!$worker) {
    header('Location: home.php?error=worker_not_found');
    exit();
}

// Fetch worker reviews
$reviews_query = "SELECT r.rating, r.review_text, r.created_at, u.user_name as customer_name
                  FROM reviews r
                  JOIN users u ON r.customer_id = u.user_id
                  WHERE r.worker_id = $worker_id
                  ORDER BY r.created_at DESC
                  LIMIT 10";
$reviews = $db->db_fetch_all($reviews_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($worker['user_name']); ?> - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            padding-bottom: 100px;
            transition: all 0.3s ease;
        }
        
        .header {
            background: var(--header-bg);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            color: white;
        }
        
        .back-btn {
            font-size: 28px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateX(-4px);
        }
        
        .header-title {
            font-size: 20px;
            font-weight: 700;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px 20px;
        }
        
        .profile-card {
            background: var(--bg-secondary);
            padding: 32px;
            border-radius: 24px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .profile-header {
            display: flex;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 20px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            position: relative;
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.3);
        }
        
        .verified-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 32px;
            height: 32px;
            background: #10B981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            border: 3px solid var(--bg-secondary);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        .profile-info h1 {
            font-size: 28px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .profile-info .skills {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }
        
        .profile-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .rating-large {
            font-size: 18px;
            font-weight: 700;
            color: #FFD700;
        }
        
        .section {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 16px;
        }
        
        .bio-text {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
        }
        
        .stat-box {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .review-card {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-color);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .reviewer-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 15px;
        }
        
        .review-rating {
            color: #FFD700;
            font-size: 16px;
        }
        
        .review-text {
            color: var(--text-secondary);
            line-height: 1.6;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .review-date {
            font-size: 12px;
            color: var(--text-secondary);
            opacity: 0.7;
        }
        
        .empty-reviews {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }
        
        .bottom-action {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            padding: 20px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            border-top: 1px solid var(--border-color);
            z-index: 1000;
        }
        
        .action-container {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .price-display {
            flex: 1;
        }
        
        .price-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 2px;
        }
        
        .price-value {
            font-size: 24px;
            font-weight: 700;
            color: #FFD700;
        }
        
        .btn-book {
            padding: 16px 40px;
            border-radius: 16px;
            border: none;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.4);
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(255, 215, 0, 0.6);
        }
        
        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-avatar {
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="back-btn" onclick="window.history.back()">←</span>
        <h1 class="header-title">Worker Profile</h1>
    </div>
    
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    👨‍🔧
                    <?php if ($worker['verification_badge']): ?>
                        <div class="verified-badge">✓</div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($worker['user_name']); ?></h1>
                    <div class="skills"><?php echo htmlspecialchars($worker['skills'] ?? 'Service Provider'); ?></div>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <span>⭐</span>
                            <span class="rating-large"><?php echo number_format($worker['average_rating'], 1); ?></span>
                            <span>(<?php echo $worker['total_jobs_completed']; ?> jobs)</span>
                        </div>
                        <div class="meta-item">
                            <span>📍</span>
                            <span><?php echo htmlspecialchars($worker['user_city'] ?? 'Ghana'); ?></span>
                        </div>
                        <?php if ($worker['experience_years']): ?>
                        <div class="meta-item">
                            <span>💼</span>
                            <span><?php echo $worker['experience_years']; ?> years experience</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($worker['bio']): ?>
        <div class="section">
            <h2 class="section-title">About</h2>
            <p class="bio-text"><?php echo nl2br(htmlspecialchars($worker['bio'])); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <h2 class="section-title">Stats</h2>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value"><?php echo $worker['total_jobs_completed']; ?></div>
                    <div class="stat-label">Jobs Completed</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?php echo number_format($worker['average_rating'], 1); ?>★</div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">GH₵<?php echo number_format($worker['hourly_rate'], 0); ?></div>
                    <div class="stat-label">Per Hour</div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title">Reviews (<?php echo count($reviews ?? []); ?>)</h2>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="reviewer-name"><?php echo htmlspecialchars($review['customer_name']); ?></div>
                        <div class="review-rating">
                            <?php for ($i = 0; $i < $review['rating']; $i++): ?>★<?php endfor; ?>
                        </div>
                    </div>
                    <?php if ($review['review_text']): ?>
                    <p class="review-text"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    <?php endif; ?>
                    <div class="review-date"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-reviews">
                    <p>No reviews yet. Be the first to review this worker!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="bottom-action">
        <div class="action-container">
            <div class="price-display">
                <div class="price-label">Starting from</div>
                <div class="price-value">GH₵<?php echo number_format($worker['hourly_rate'], 2); ?>/hr</div>
            </div>
            <a href="booking.php?worker_id=<?php echo $worker_id; ?>" class="btn-book">Book Now</a>
        </div>
    </div>
</body>
</html>
