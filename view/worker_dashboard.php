<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_once '../controllers/booking_controller.php';

// Require login
require_login('login.php');

// Check if user is worker
if (!is_worker()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$user_name = get_user_name();
$worker_id = get_user_id();

// Fetch worker stats
$db = new db_connection();

// Get worker profile data
$profile_query = "SELECT total_earnings, total_jobs_completed, average_rating, available_balance
                  FROM worker_profiles WHERE user_id = $worker_id";
$profile = $db->db_fetch_one($profile_query);

$total_earnings = $profile['total_earnings'] ?? 0;
$jobs_completed = $profile['total_jobs_completed'] ?? 0;
$rating = $profile['average_rating'] ?? 0;
$available_balance = $profile['available_balance'] ?? 0;

// Get active and completed bookings
$all_bookings = get_worker_bookings_ctr($worker_id);
$active_jobs = [];
$completed_jobs = [];

if ($all_bookings) {
    foreach ($all_bookings as $booking) {
        $job = [
            'id' => $booking['booking_id'],
            'title' => 'Service Request',
            'customer' => $booking['customer_name'],
            'phone' => $booking['customer_phone'],
            'date' => date('m/d/Y', strtotime($booking['booking_date'])),
            'time' => date('H:i', strtotime($booking['booking_time'])),
            'price' => $booking['estimated_price'],
            'status' => $booking['booking_status'],
            'address' => $booking['service_address']
        ];
        
        if (in_array($booking['booking_status'], ['pending', 'accepted', 'in_progress'])) {
            $active_jobs[] = $job;
        } elseif ($booking['booking_status'] == 'completed') {
            $completed_jobs[] = $job;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - WorkNPay</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #F5F5F5;
            padding-bottom: 20px;
        }
        
        .navbar {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .nav-links a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #10b981;
        }
        
        .btn-logout {
            background: #ef4444;
            color: white;
            padding: 8px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: #dc2626;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 40px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        
        .welcome-card h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .welcome-card p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .card {
            background: white;
            padding: 30px;
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
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">WorkNPay</div>
            <div class="nav-links">
                <a href="worker_dashboard.php">Dashboard</a>
                <a href="my_jobs.php">My Jobs</a>
                <a href="earnings.php">Earnings</a>
                <a href="worker_profile.php">My Profile</a>
                <a href="../actions/logout_action.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>! 💼</h1>
            <p>Manage your services, bookings, and earnings all in one place</p>
        </div>

        <div class="grid">
            <div class="card">
                <div class="card-icon">📋</div>
                <h3>Active Jobs</h3>
                <p>View and manage your current service bookings. Accept new requests and update job status.</p>
                <a href="my_jobs.php" class="btn">View Jobs</a>
            </div>

            <div class="card">
                <div class="card-icon">💰</div>
                <h3>Earnings</h3>
                <p>Track your income, view payment history, and request payouts to your account.</p>
                <a href="earnings.php" class="btn">View Earnings</a>
            </div>

            <div class="card">
                <div class="card-icon">⚙️</div>
                <h3>My Services</h3>
                <p>Add, edit, or remove the services you offer. Set your rates and availability.</p>
                <a href="my_services.php" class="btn">Manage Services</a>
            </div>

            <div class="card">
                <div class="card-icon">⭐</div>
                <h3>Reviews & Ratings</h3>
                <p>See what customers are saying about your work and build your reputation.</p>
                <a href="my_reviews.php" class="btn">View Reviews</a>
            </div>
        </div>
    </div>
</body>
</html>
