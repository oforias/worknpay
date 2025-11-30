<?php
require_once '../settings/core.php';

// Require login
require_login('login.php');

// Check if user is customer
if (!is_customer()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$user_name = get_user_name();
$user_email = get_user_email();
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #F5F5F5;
            padding-bottom: 80px;
        }
        
        .header {
            background: linear-gradient(135deg, #0052CC 0%, #2684FF 100%);
            padding: 24px 20px;
            color: white;
        }
        
        .welcome-text {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 4px;
        }
        
        .user-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .profile-icon {
            position: absolute;
            top: 24px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: #FFD700;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
        }
        
        .search-box {
            position: relative;
            margin-top: 16px;
        }
        
        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #757575;
            font-size: 18px;
        }
        
        .search-input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            background: white;
        }
        
        .search-input::placeholder {
            color: #BDBDBD;
        }
        
        .search-input:focus {
            outline: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .container {
            padding: 20px;
        }
        
        .special-offer {
            background: linear-gradient(135deg, #FFD700 0%, #FFC700 100%);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 24px;
            position: relative;
        }
        
        .offer-label {
            font-size: 12px;
            font-weight: 600;
            color: #1A1A1A;
            margin-bottom: 4px;
        }
        
        .offer-title {
            font-size: 20px;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 4px;
        }
        
        .offer-code {
            font-size: 12px;
            color: #666;
        }
        
        .claim-btn {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: #0052CC;
            color: white;
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .claim-btn:hover {
            background: #0747A6;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight:x;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .card h3 {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .card p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">WorkNPay</div>
            <div class="nav-links">
                <a href="customer_dashboard.php">Dashboard</a>
                <a href="browse_services.php">Browse Services</a>
                <a href="my_bookings.php">My Bookings</a>
                <a href="profile.php">Profile</a>
                <a href="../actions/logout_action.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
            <p>Find skilled workers and get your tasks done with ease</p>
        </div>

        <div class="grid">
            <div class="card">
                <div class="card-icon">🔍</div>
                <h3>Browse Services</h3>
                <p>Discover skilled workers in your area offering various services from gadget repair to tutoring.</p>
                <a href="browse_services.php" class="btn">Explore Services</a>
            </div>

            <div class="card">
                <div class="card-icon">📅</div>
                <h3>My Bookings</h3>
                <p>View and manage your service bookings, track progress, and communicate with workers.</p>
                <a href="my_bookings.php" class="btn">View Bookings</a>
            </div>

            <div class="card">
                <div class="card-icon">⭐</div>
                <h3>Leave Reviews</h3>
                <p>Share your experience and help others find the best workers on the platform.</p>
                <a href="my_bookings.php" class="btn">Rate Services</a>
            </div>

            <div class="card">
                <div class="card-icon">👤</div>
                <h3>My Profile</h3>
                <p>Update your personal information, manage payment methods, and view your activity history.</p>
                <a href="profile.php" class="btn">Edit Profile</a>
            </div>
        </div>
    </div>
</body>
</html>
