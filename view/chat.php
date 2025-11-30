<?php
require_once '../settings/core.php';
require_login('login.php');

$user_name = get_user_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - WorkNPay</title>
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
            padding: 24px 20px;
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
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 24px 20px;
        }
        
        .coming-soon {
            text-align: center;
            padding: 80px 20px;
        }
        
        .coming-soon-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 32px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            box-shadow: 0 8px 32px rgba(255, 215, 0, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .coming-soon h2 {
            font-size: 32px;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 16px;
        }
        
        .coming-soon p {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 32px;
            line-height: 1.6;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .feature-list {
            background: var(--bg-secondary);
            padding: 32px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            max-width: 500px;
            margin: 0 auto 32px;
            text-align: left;
        }
        
        .feature-list h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
            padding: 12px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        
        .feature-icon {
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .feature-text {
            flex: 1;
        }
        
        .feature-text h4 {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .feature-text p {
            font-size: 13px;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .btn-notify {
            padding: 14px 32px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.4);
        }
        
        .btn-notify:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(255, 215, 0, 0.6);
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
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Messages</h1>
            <p>Chat with workers and customers</p>
        </div>
    </div>
    
    <div class="container">
        <div class="coming-soon">
            <div class="coming-soon-icon">💬</div>
            <h2>Coming Soon!</h2>
            <p>We're building an amazing in-app messaging system to help you communicate seamlessly with workers and customers.</p>
            
            <div class="feature-list">
                <h3>What to Expect</h3>
                <div class="feature-item">
                    <div class="feature-icon">⚡</div>
                    <div class="feature-text">
                        <h4>Real-time Messaging</h4>
                        <p>Instant message delivery and notifications</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📸</div>
                    <div class="feature-text">
                        <h4>Photo Sharing</h4>
                        <p>Share images and documents easily</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔔</div>
                    <div class="feature-text">
                        <h4>Push Notifications</h4>
                        <p>Never miss an important message</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔒</div>
                    <div class="feature-text">
                        <h4>Secure & Private</h4>
                        <p>End-to-end encrypted conversations</p>
                    </div>
                </div>
            </div>
            
            <button class="btn-notify" onclick="notifyMe()">Notify Me When Ready</button>
        </div>
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
        <a href="chat.php" class="nav-item active">
            <div class="nav-icon">💬</div>
            <div>Chat</div>
        </a>
        <a href="profile.php" class="nav-item">
            <div class="nav-icon">👤</div>
            <div>Profile</div>
        </a>
    </div>
    
    <script>
        function notifyMe() {
            alert('Great! We\'ll notify you when the messaging feature is ready. 🎉');
            // In production: Save user preference to database
        }
    </script>
    <script src="../js/theme-toggle.js"></script>
</body>
</html>
