<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_once '../controllers/worker_controller.php';
require_login('login.php');

// Get filter parameters
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch workers based on filters
if (!empty($search)) {
    $workers = search_workers_ctr($search);
    $page_title = "Search Results: " . htmlspecialchars($search);
} elseif (!empty($category)) {
    $workers = get_workers_by_category_ctr($category);
    $page_title = htmlspecialchars($category) . " Workers";
} else {
    $workers = get_workers_by_category_ctr(null, 50);
    $page_title = "All Workers";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            padding-bottom: 80px;
            transition: all 0.3s ease;
        }
        
        .header {
            background: var(--header-bg);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
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
            flex: 1;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px 20px;
        }
        
        .search-bar {
            background: var(--bg-secondary);
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .search-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            background: var(--input-bg);
            color: var(--text-primary);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .results-count {
            font-size: 16px;
            color: var(--text-secondary);
        }
        
        .filter-btn {
            padding: 10px 20px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            border-color: #FFD700;
            background: rgba(255, 215, 0, 0.1);
        }
        
        .workers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .worker-card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid var(--border-color);
            cursor: pointer;
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
        
        .worker-header {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .worker-avatar {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            position: relative;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }
        
        .verified-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 20px;
            height: 20px;
            background: #10B981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border: 2px solid var(--bg-secondary);
        }
        
        .worker-info {
            flex: 1;
        }
        
        .worker-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .worker-skills {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 8px;
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
        
        .worker-price {
            font-size: 20px;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 16px;
        }
        
        .book-btn {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }
        
        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.5);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            font-size: 24px;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        
        .empty-state p {
            font-size: 16px;
            margin-bottom: 24px;
        }
        
        .btn-secondary {
            padding: 12px 32px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-secondary:hover {
            border-color: #FFD700;
            background: rgba(255, 215, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .workers-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="back-btn" onclick="window.history.back()">←</span>
        <h1 class="header-title"><?php echo $page_title; ?></h1>
    </div>
    
    <div class="container">
        <div class="search-bar">
            <form action="browse_workers.php" method="GET">
                <input 
                    type="text" 
                    name="search" 
                    class="search-input" 
                    placeholder="Search by name, skill, or location..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >
            </form>
        </div>
        
        <div class="results-header">
            <div class="results-count">
                <?php echo count($workers); ?> worker<?php echo count($workers) != 1 ? 's' : ''; ?> found
            </div>
        </div>
        
        <?php if (!empty($workers)): ?>
            <div class="workers-grid">
                <?php foreach ($workers as $worker): ?>
                    <div class="worker-card" onclick="window.location.href='worker_profile.php?id=<?php echo $worker['user_id']; ?>'">
                        <div class="worker-header">
                            <div class="worker-avatar">
                                👨‍🔧
                                <?php if ($worker['verification_badge']): ?>
                                    <div class="verified-badge">✓</div>
                                <?php endif; ?>
                            </div>
                            <div class="worker-info">
                                <div class="worker-name"><?php echo htmlspecialchars($worker['user_name']); ?></div>
                                <div class="worker-skills"><?php echo htmlspecialchars($worker['skills'] ?? 'Service Provider'); ?></div>
                                <div class="worker-meta">
                                    <div class="rating">⭐ <?php echo number_format($worker['average_rating'], 1); ?></div>
                                    <div>📍 <?php echo htmlspecialchars($worker['user_city'] ?? 'Ghana'); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="worker-price">GH₵<?php echo number_format($worker['hourly_rate'], 2); ?>/hr</div>
                        <button class="book-btn" onclick="event.stopPropagation(); window.location.href='booking.php?worker_id=<?php echo $worker['user_id']; ?>'">
                            Book Now
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🔍</div>
                <h3>No Workers Found</h3>
                <p>Try adjusting your search or browse all available workers</p>
                <a href="browse_workers.php" class="btn-secondary">View All Workers</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../js/theme-toggle.js"></script>
</body>
</html>
