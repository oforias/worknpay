<?php
require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';
require_login('login.php');

if (!is_worker()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$worker_id = get_user_id();
$worker_name = get_user_name();

// Fetch worker stats
$db = new db_connection();

// Get worker profile stats
$stats_query = "SELECT total_jobs_completed, average_rating, available_balance 
                FROM worker_profiles WHERE user_id = $worker_id";
$stats = $db->db_fetch_one($stats_query);

// If no profile exists, redirect to onboarding
if (!$stats) {
    header('Location: worker_onboarding.php');
    exit();
}

// Get pending balance (money in escrow)
$pending_query = "SELECT SUM(p.amount) as pending_total 
                  FROM payments p
                  JOIN bookings b ON p.booking_id = b.booking_id
                  WHERE b.worker_id = $worker_id 
                  AND p.escrow_status = 'held'
                  AND b.booking_status = 'completed'";
$pending_result = $db->db_fetch_one($pending_query);
$pending_balance = $pending_result['pending_total'] ?? 0;

// Get active jobs count
$active_jobs_query = "SELECT COUNT(*) as count FROM bookings 
                      WHERE worker_id = $worker_id 
                      AND booking_status IN ('pending', 'accepted', 'in_progress')";
$active_jobs = $db->db_fetch_one($active_jobs_query);

// Get bookings for this worker
$bookings = get_worker_bookings_ctr($worker_id);

// Separate active and completed bookings
$active_bookings = [];
$completed_bookings = [];

if ($bookings) {
    foreach ($bookings as $booking) {
        if (in_array($booking['booking_status'], ['pending', 'accepted', 'in_progress'])) {
            $active_bookings[] = $booking;
        } elseif ($booking['booking_status'] === 'completed') {
            $completed_bookings[] = $booking;
        }
    }
}

// Calculate total earnings (sum of completed jobs)
$earnings_query = "SELECT SUM(estimated_price) as total FROM bookings 
                   WHERE worker_id = $worker_id AND booking_status = 'completed'";
$earnings_result = $db->db_fetch_one($earnings_query);
$total_earnings = $earnings_result['total'] ?? 0;

// Calculate weekly growth
$this_week_query = "SELECT SUM(estimated_price) as total FROM bookings 
                    WHERE worker_id = $worker_id 
                    AND booking_status = 'completed'
                    AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
$this_week_result = $db->db_fetch_one($this_week_query);
$this_week_earnings = $this_week_result['total'] ?? 0;

$last_week_query = "SELECT SUM(estimated_price) as total FROM bookings 
                    WHERE worker_id = $worker_id 
                    AND booking_status = 'completed'
                    AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) - 1";
$last_week_result = $db->db_fetch_one($last_week_query);
$last_week_earnings = $last_week_result['total'] ?? 0;

// Calculate growth percentage
$growth_percentage = 0;
$growth_direction = '';
if ($last_week_earnings > 0) {
    $growth_percentage = (($this_week_earnings - $last_week_earnings) / $last_week_earnings) * 100;
    $growth_direction = $growth_percentage >= 0 ? '📈' : '📉';
} elseif ($this_week_earnings > 0) {
    $growth_percentage = 100;
    $growth_direction = '📈';
}

// Get daily earnings for the current week (for chart)
$daily_earnings = [];
for ($i = 0; $i < 7; $i++) {
    $day_query = "SELECT COALESCE(SUM(estimated_price), 0) as total FROM bookings 
                  WHERE worker_id = $worker_id 
                  AND booking_status = 'completed'
                  AND DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL (6 - $i) DAY)";
    $day_result = $db->db_fetch_one($day_query);
    $daily_earnings[] = $day_result['total'] ?? 0;
}

// Find max for scaling
$max_daily = max($daily_earnings);
if ($max_daily == 0) $max_daily = 1; // Avoid division by zero
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - WorkNPay</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0A0E1A;
            min-height: 100vh;
            padding-bottom: 20px;
            position: relative;
            color: white;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(255, 215, 0, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(255, 165, 0, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .container-wrapper {
            position: relative;
            z-index: 1;
        }
        
        .container-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        @media (max-width: 768px) {
            .container-wrapper {
                max-width: 100%;
            }
        }
        
        /* Dark Header Section with Gold Accents */
        .header-section {
            background: linear-gradient(135deg, rgba(10, 14, 26, 0.95) 0%, rgba(20, 24, 36, 0.95) 100%);
            backdrop-filter: blur(20px);
            padding: 24px 20px 28px;
            color: white;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 100;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .welcome-text {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 4px;
        }
        
        .worker-name {
            font-size: 24px;
            font-weight: 600;
        }
        
        .profile-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .profile-icon:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .profile-dropdown {
            position: absolute;
            top: 50px;
            right: 0;
            background: rgba(20, 24, 36, 0.98);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
            min-width: 200px;
            display: none;
            z-index: 10000;
            overflow: hidden;
        }
        
        .profile-dropdown.show {
            display: block;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dropdown-item {
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: rgba(255, 215, 0, 0.1);
            color: #FFD700;
        }
        
        .dropdown-item.logout {
            color: #FF6B6B;
        }
        
        .dropdown-item.logout:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #FF4444;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 16px 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .stat-card:hover::before {
            opacity: 1;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card.green {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 165, 0, 0.1) 100%);
            border: 1px solid rgba(255, 215, 0, 0.2);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.2);
        }
        
        .stat-card.green:hover {
            box-shadow: 0 8px 32px rgba(255, 215, 0, 0.4);
            border-color: rgba(255, 215, 0, 0.4);
        }
        
        .stat-card.blue {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.08) 0%, rgba(255, 165, 0, 0.08) 100%);
            border: 1px solid rgba(255, 215, 0, 0.15);
            box-shadow: 0 4px 20px rgba(255, 165, 0, 0.15);
        }
        
        .stat-card.blue:hover {
            box-shadow: 0 8px 32px rgba(255, 165, 0, 0.3);
            border-color: rgba(255, 165, 0, 0.3);
        }
        
        .stat-card.yellow {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.12) 0%, rgba(255, 165, 0, 0.12) 100%);
            border: 1px solid rgba(255, 215, 0, 0.25);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.25);
        }
        
        .stat-card.yellow:hover {
            box-shadow: 0 8px 32px rgba(255, 215, 0, 0.5);
            border-color: rgba(255, 215, 0, 0.5);
        }
        
        .stat-card.purple {
            background: linear-gradient(135deg, rgba(255, 165, 0, 0.1) 0%, rgba(255, 140, 0, 0.1) 100%);
            border: 1px solid rgba(255, 165, 0, 0.2);
            box-shadow: 0 4px 20px rgba(255, 165, 0, 0.2);
        }
        
        .stat-card.purple:hover {
            box-shadow: 0 8px 32px rgba(255, 165, 0, 0.4);
            border-color: rgba(255, 165, 0, 0.4);
        }
        
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .stat-icon.green { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
        .stat-icon.blue { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); }
        .stat-icon.yellow { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
        .stat-icon.purple { background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%); }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }
        
        /* Weekly Earnings Section */
        .earnings-section {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            margin: 20px 20px;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.1);
            position: relative;
            z-index: 1;
        }
        
        @media (max-width: 768px) {
            .earnings-section {
                margin: 20px 16px;
            }
        }
        
        .earnings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .earnings-title {
            font-size: 18px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .earnings-growth {
            color: #FFD700;
            font-size: 14px;
            font-weight: 600;
        }
        
        .chart-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            height: 120px;
            gap: 8px;
        }
        
        .chart-bar {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .bar {
            width: 100%;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 8px 8px 0 0;
            transition: all 0.3s ease;
            cursor: pointer;
            min-height: 10px;
        }
        
        .bar:hover {
            background: rgba(255, 215, 0, 0.3);
        }
        
        .bar.active {
            background: linear-gradient(180deg, #FFD700 0%, #FFA500 100%);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
        }
        
        .bar.has-data {
            background: rgba(255, 215, 0, 0.4);
        }
        
        .bar.has-data.active {
            background: linear-gradient(180deg, #FFD700 0%, #FFA500 100%);
        }
        
        .day-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Jobs Section */
        .jobs-section {
            padding: 0 20px;
        }
        
        .tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            padding: 4px;
            margin: 0 20px 20px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
        }
        
        .job-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #FFD700 0%, #FFA500 100%);
            border-radius: 16px 16px 0 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .job-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 32px rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.3);
        }
        
        .job-card:hover::before {
            opacity: 1;
        }
        
        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }
        
        .job-title {
            font-size: 17px;
            font-weight: 700;
            background: linear-gradient(135deg, #FFFFFF 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }
        
        .customer-name {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-badge.pending {
            background: #FFF3E0;
            color: #E65100;
        }
        
        .status-badge.in-progress {
            background: #E3F2FD;
            color: #1565C0;
        }
        
        .job-details {
            display: flex;
            gap: 16px;
            margin-bottom: 12px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .job-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .job-price {
            font-size: 20px;
            font-weight: 700;
            color: #FFD700;
        }
        
        .job-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-chat {
            padding: 10px 18px;
            border-radius: 12px;
            border: 2px solid rgba(255, 215, 0, 0.3);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            color: rgba(255, 255, 255, 0.9);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-chat:hover {
            background: rgba(255, 215, 0, 0.1);
            border-color: rgba(255, 215, 0, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
        }
        
        .btn-action {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.5);
        }
        
        .btn-complete {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        }
        
        .btn-reject {
            padding: 10px 18px;
            border-radius: 12px;
            border: 2px solid rgba(239, 68, 68, 0.3);
            background: rgba(255, 255, 255, 0.8);
            color: #DC2626;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-reject::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-reject:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-reject:hover {
            border-color: rgba(239, 68, 68, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        

        
        /* Payout Card */
        .payout-card {
            background: linear-gradient(135deg, #FCD34D 0%, #F59E0B 100%);
            margin: 20px 20px;
            padding: 28px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4), 
                        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }
        
        .payout-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
            z-index: 0;
            pointer-events: none;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translate(0, 0); opacity: 0.5; }
            50% { transform: translate(-20%, -20%); opacity: 0.8; }
        }
        
        .payout-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 48px rgba(245, 158, 11, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.2) inset;
        }
        
        @media (max-width: 768px) {
            .payout-card {
                margin: 20px 16px;
            }
            
            .tabs {
                margin: 0 16px 20px;
            }
        }
        
        .payout-label {
            font-size: 14px;
            color: #78350F;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            opacity: 0.9;
        }
        
        .payout-amount {
            font-size: 32px;
            font-weight: 700;
            color: #78350F;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(120, 53, 15, 0.1);
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        
        .pending-balance-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.3);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .pending-icon {
            font-size: 24px;
            flex-shrink: 0;
        }
        
        .pending-text {
            flex: 1;
        }
        
        .pending-label {
            font-size: 12px;
            color: #78350F;
            opacity: 0.8;
            margin-bottom: 4px;
        }
        
        .pending-amount {
            font-size: 18px;
            font-weight: 700;
            color: #78350F;
            margin-bottom: 4px;
        }
        
        .pending-hint {
            font-size: 11px;
            color: #78350F;
            opacity: 0.7;
        }
        
        .payout-buttons {
            display: flex;
            gap: 12px;
            position: relative;
            z-index: 2;
        }
        
        .btn-payout {
            flex: 1;
            padding: 14px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        .btn-withdraw {
            background: rgba(255, 255, 255, 0.95);
            color: #78350F;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-withdraw:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }
        
        .btn-history {
            background: rgba(255, 255, 255, 0.2);
            color: #78350F;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn-history:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Completion Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payout-option {
            transition: all 0.3s ease;
        }
        
        .payout-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.3);
        }
        
        .payout-option:active {
            transform: translateY(0);
        }
        
        .modal-content {
            background: rgba(10, 14, 26, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 215, 0, 0.3);
            animation: slideUp 0.3s ease;
        }
        
        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            padding: 24px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            font-size: 22px;
            font-weight: 800;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .close-modal {
            font-size: 32px;
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            line-height: 1;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            color: #FFD700;
        }
        
        .modal-body {
            padding: 24px;
        }
        
        .modal-description {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 24px;
            font-size: 14px;
        }
        
        .photo-upload-area {
            border: 3px dashed rgba(255, 215, 0, 0.3);
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 215, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .photo-upload-area:hover {
            border-color: rgba(255, 215, 0, 0.6);
            background: rgba(255, 215, 0, 0.1);
        }
        
        .upload-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        
        .upload-text {
            font-size: 16px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 6px;
        }
        
        .upload-hint {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .photo-preview {
            position: relative;
            margin-bottom: 20px;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .photo-preview img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 16px;
        }
        
        .remove-photo {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        
        .balance-display {
            background: linear-gradient(135deg, rgba(0, 82, 204, 0.1) 0%, rgba(38, 132, 255, 0.1) 100%);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 24px;
        }
        
        .balance-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 8px;
        }
        
        .balance-amount {
            font-size: 32px;
            font-weight: 800;
            color: #FFD700;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .history-item {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .history-item:last-child {
            border-bottom: none;
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .history-title {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }
        
        .history-amount {
            font-weight: 700;
            color: #FFD700;
        }
        
        .history-date {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group .hint {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 6px;
        }
        
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.5);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
    <!-- Blue Header with Stats -->
    <div class="header-section">
        <div class="header-top">
            <div>
                <div class="welcome-text">Welcome back,</div>
                <div class="worker-name"><?php echo htmlspecialchars($worker_name); ?></div>
            </div>
            <div style="position: relative;">
                <div class="profile-icon" onclick="toggleProfileDropdown()">👤</div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="worker_profile.php" class="dropdown-item">
                        <span>👤</span>
                        <span>My Profile</span>
                    </a>
                    <a href="manage_services.php" class="dropdown-item">
                        <span>📋</span>
                        <span>Manage Services</span>
                    </a>
                    <a href="worker_disputes.php" class="dropdown-item">
                        <span>⚖️</span>
                        <span>My Disputes</span>
                    </a>
                    <a href="worker_payout_accounts.php" class="dropdown-item">
                        <span>💳</span>
                        <span>Payout Accounts</span>
                    </a>
                    <a href="worker_settings.php" class="dropdown-item">
                        <span>⚙️</span>
                        <span>Settings</span>
                    </a>
                    <div class="dropdown-item logout" onclick="logout()">
                        <span>🚪</span>
                        <span>Logout</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card green">
                <div class="stat-icon green">💰</div>
                <div class="stat-value">GH₵<?php echo number_format($total_earnings, 0); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
            
            <div class="stat-card blue">
                <div class="stat-icon blue">✓</div>
                <div class="stat-value"><?php echo $stats['total_jobs_completed'] ?? 0; ?></div>
                <div class="stat-label">Jobs Completed</div>
            </div>
            
            <div class="stat-card yellow">
                <div class="stat-icon yellow">⭐</div>
                <div class="stat-value"><?php echo number_format($stats['average_rating'] ?? 0, 1); ?></div>
                <div class="stat-label">Rating</div>
            </div>
            
            <div class="stat-card purple">
                <div class="stat-icon purple">📋</div>
                <div class="stat-value"><?php echo $active_jobs['count'] ?? 0; ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
        </div>
    </div>
    
    <!-- Weekly Earnings Chart -->
    <div class="earnings-section">
        <div class="earnings-header">
            <div class="earnings-title">Weekly Earnings</div>
            <?php if ($growth_percentage != 0): ?>
                <div class="earnings-growth" style="color: <?php echo $growth_percentage >= 0 ? '#10B981' : '#EF4444'; ?>">
                    <?php echo $growth_direction; ?> <?php echo $growth_percentage >= 0 ? '+' : ''; ?><?php echo number_format($growth_percentage, 1); ?>%
                </div>
            <?php endif; ?>
        </div>
        <div class="chart-container">
            <?php 
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $today_index = (date('N') - 1); // 0 = Monday, 6 = Sunday
            for ($i = 0; $i < 7; $i++): 
                $has_data = $daily_earnings[$i] > 0;
                $height = $max_daily > 0 ? ($daily_earnings[$i] / $max_daily) * 100 : 20;
                $is_today = ($i == $today_index);
                $bar_class = 'bar';
                if ($has_data) $bar_class .= ' has-data';
                if ($is_today) $bar_class .= ' active';
            ?>
            <div class="chart-bar">
                <div class="<?php echo $bar_class; ?>" style="height: <?php echo max($height, 10); ?>%;" title="GH₵<?php echo number_format($daily_earnings[$i], 2); ?>"></div>
                <div class="day-label"><?php echo $days[$i]; ?></div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
    
    <!-- Jobs Section -->
    <div class="jobs-section">
        <div class="tabs">
            <div class="tab active" data-tab="active">Active Jobs (<?php echo count($active_bookings); ?>)</div>
            <div class="tab" data-tab="completed">Completed</div>
        </div>
        
        <!-- Active Jobs Tab -->
        <div class="tab-content active" id="active-tab">
            <?php if (!empty($active_bookings)): ?>
                <?php foreach ($active_bookings as $booking): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <div class="job-title"><?php echo htmlspecialchars($booking['service_title'] ?? 'Service Request'); ?></div>
                                <div class="customer-name"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                            </div>
                            <span class="status-badge <?php echo strtolower(str_replace('_', '-', $booking['booking_status'])); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $booking['booking_status'])); ?>
                            </span>
                        </div>
                        
                        <div class="job-details">
                            <span>📅 <?php echo date('m/d/Y', strtotime($booking['booking_date'])); ?></span>
                            <span>🕐 <?php echo date('H:i', strtotime($booking['booking_time'])); ?></span>
                        </div>
                        
                        <div class="job-footer">
                            <div class="job-price">GH₵<?php echo number_format($booking['estimated_price'], 2); ?></div>
                            <div class="job-actions">
                                <?php if ($booking['booking_status'] === 'pending'): ?>
                                    <button class="btn-reject" onclick="rejectJob(<?php echo $booking['booking_id']; ?>)">Reject</button>
                                    <button class="btn-action" onclick="acceptJob(<?php echo $booking['booking_id']; ?>)">Accept</button>
                                <?php elseif ($booking['booking_status'] === 'in_progress'): ?>
                                    <button class="btn-chat" onclick="alert('Chat feature coming soon!')">💬 Chat</button>
                                    <button class="btn-action btn-complete" onclick="completeJob(<?php echo $booking['booking_id']; ?>)">Complete</button>
                                <?php else: ?>
                                    <button class="btn-chat" onclick="alert('Chat feature coming soon!')">💬 Chat</button>
                                    <button class="btn-action" onclick="startJob(<?php echo $booking['booking_id']; ?>)">Start Job</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
                    <div>No active jobs at the moment</div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Completed Jobs Tab -->
        <div class="tab-content" id="completed-tab">
            <?php if (!empty($completed_bookings)): ?>
                <?php foreach ($completed_bookings as $booking): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <div class="job-title"><?php echo htmlspecialchars($booking['service_title'] ?? 'Service Request'); ?></div>
                                <div class="customer-name"><?php echo htmlspecialchars($booking['customer_name']); ?></div>
                            </div>
                            <span class="status-badge" style="background: #D1FAE5; color: #065F46;">Completed</span>
                        </div>
                        
                        <div class="job-details">
                            <span>📅 <?php echo date('m/d/Y', strtotime($booking['booking_date'])); ?></span>
                            <span>🕐 <?php echo date('H:i', strtotime($booking['booking_time'])); ?></span>
                        </div>
                        
                        <div class="job-footer">
                            <div class="job-price">GH₵<?php echo number_format($booking['estimated_price'], 2); ?></div>
                            <div class="job-actions">
                                <button class="btn-chat" onclick="viewJobDetails(<?php echo $booking['booking_id']; ?>)">View Details</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 48px; margin-bottom: 16px;">✓</div>
                    <div>No completed jobs yet</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Payout Card -->
    <div class="payout-card">
        <div class="payout-label">Available for Payout</div>
        <div class="payout-amount">GH₵<?php echo number_format($stats['available_balance'] ?? 0, 2); ?></div>
        
        <?php if ($pending_balance > 0): ?>
        <div class="pending-balance-info">
            <div class="pending-icon">⏳</div>
            <div class="pending-text">
                <div class="pending-label">Pending Release</div>
                <div class="pending-amount">GH₵<?php echo number_format($pending_balance, 2); ?></div>
                <div class="pending-hint">Will be available after 24-hour escrow period</div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="payout-buttons">
            <button class="btn-payout btn-withdraw" onclick="openWithdrawModal()">Withdraw Now</button>
            <button class="btn-payout btn-history" onclick="openHistoryModal()">View History</button>
        </div>
    </div>
    
    <!-- Withdraw Modal -->
    <div id="withdrawModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Withdraw Funds</h2>
                <span class="close-modal" onclick="closeWithdrawModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="balance-display">
                    <div class="balance-label">Available Balance</div>
                    <div class="balance-amount">GH₵<?php echo number_format($stats['available_balance'] ?? 0, 2); ?></div>
                </div>
                
                <div class="form-group">
                    <label for="withdrawAmount">Amount to Withdraw</label>
                    <input type="number" id="withdrawAmount" min="50" step="10" placeholder="Minimum GH₵50" max="<?php echo $stats['available_balance'] ?? 0; ?>">
                    <div class="hint">Minimum withdrawal: GH₵50</div>
                </div>
                
                <div class="form-group">
                    <label for="payoutType">Payout Speed</label>
                    <select id="payoutType" onchange="updatePayoutFee()">
                        <option value="next_day">Next-Day Payout (FREE) - Within 24 hours</option>
                        <option value="instant">Instant Payout (2% fee) - Processed immediately</option>
                    </select>
                    <div class="hint" style="margin-top: 8px; padding: 12px; background: rgba(255, 215, 0, 0.1); border-radius: 8px; color: rgba(255, 215, 0, 0.9); border: 1px solid rgba(255, 215, 0, 0.3);">
                        <strong>💡 Why 24 hours?</strong><br>
                        Withdrawals are processed manually by our team via mobile money or bank transfer. The 24-hour window allows us to verify your request and process it securely during business hours. Choose instant payout (2% fee) if you need funds urgently.
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="payoutAccount">Payout Account</label>
                    <select id="payoutAccount">
                        <option value="">Loading accounts...</option>
                    </select>
                    <div class="hint">
                        <a href="worker_payout_accounts.php" style="color: #FFD700; text-decoration: none;">+ Add new payout account</a>
                    </div>
                </div>
                
                <div id="feeBreakdown" style="background: rgba(255, 215, 0, 0.05); padding: 16px; border-radius: 12px; margin-bottom: 20px; display: none; border: 1px solid rgba(255, 215, 0, 0.2);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: rgba(255, 255, 255, 0.9);">
                        <span>Withdrawal Amount:</span>
                        <span id="withdrawalAmount">GH₵0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: rgba(255, 255, 255, 0.7);" id="feeRow">
                        <span>Processing Fee (2%):</span>
                        <span id="processingFee">GH₵0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 8px; border-top: 2px solid rgba(255, 215, 0, 0.3); font-weight: 700; font-size: 16px; color: #FFD700;">
                        <span>You'll Receive:</span>
                        <span id="netAmount">GH₵0.00</span>
                    </div>
                </div>
                
                <button class="btn-primary" onclick="submitWithdrawal()">
                    Request Withdrawal
                </button>
            </div>
        </div>
    </div>
    
    <!-- History Modal -->
    <div id="historyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Transaction History</h2>
                <span class="close-modal" onclick="closeHistoryModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="historyContent">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Job Details Modal -->
    <div id="jobDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Job Details</h2>
                <span class="close-modal" onclick="closeJobDetailsModal()">&times;</span>
            </div>
            <div class="modal-body" id="jobDetailsContent">
                <div class="loading">Loading...</div>
            </div>
        </div>
    </div>
    
    <!-- Completion Modal -->
    <div id="completionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Complete Job</h2>
                <span class="close-modal" onclick="closeCompletionModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p class="modal-description">Upload a photo showing the completed work. This helps build trust with customers.</p>
                
                <div class="photo-upload-area" id="photoUploadArea">
                    <div class="upload-icon">📸</div>
                    <div class="upload-text">Click to upload photo</div>
                    <div class="upload-hint">JPG, PNG or WEBP (Max 5MB)</div>
                    <input type="file" id="completionPhoto" accept="image/*" style="display: none;">
                </div>
                
                <div id="photoPreview" class="photo-preview" style="display: none;">
                    <img id="previewImage" src="" alt="Preview">
                    <button class="remove-photo" onclick="removePhoto()">Remove</button>
                </div>
                
                <div class="form-group">
                    <label for="completionNotes">Additional Notes (Optional)</label>
                    <textarea id="completionNotes" placeholder="Any additional details about the completed work..."></textarea>
                </div>
                
                <button class="btn-primary" id="submitCompletionBtn" onclick="submitCompletion()">
                    Complete Job
                </button>
            </div>
        </div>
    </div>
    
    <!-- Payout Options Modal -->
    <div id="payoutOptionsModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>🎉 Job Completed!</h2>
                <span class="close-modal" onclick="closePayoutOptionsModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p class="modal-description" style="text-align: center; font-size: 16px; margin-bottom: 24px;">
                    Great work! Here's your payment breakdown:
                </p>
                
                <!-- Payment Breakdown -->
                <div style="background: rgba(255, 215, 0, 0.1); padding: 20px; border-radius: 16px; margin-bottom: 24px; border: 1px solid rgba(255, 215, 0, 0.3);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px;">
                        <span style="color: rgba(255, 255, 255, 0.8);">Job Charge:</span>
                        <span style="font-weight: 600; color: white;" id="payoutJobCharge">GH₵0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px;">
                        <span style="color: rgba(255, 255, 255, 0.8);">Platform Fee (5%):</span>
                        <span style="font-weight: 600; color: #EF4444;" id="payoutPlatformFee">-GH₵0.00</span>
                    </div>
                    <div style="height: 1px; background: rgba(255, 215, 0, 0.3); margin: 16px 0;"></div>
                    <div style="display: flex; justify-content: space-between; font-size: 20px;">
                        <span style="font-weight: 700; color: white;">You'll Receive:</span>
                        <span style="font-weight: 800; color: #FFD700;" id="payoutWorkerAmount">GH₵0.00</span>
                    </div>
                </div>
                
                <p style="text-align: center; font-size: 15px; font-weight: 600; margin-bottom: 16px; color: rgba(255, 255, 255, 0.9);">
                    Choose when to receive your payment:
                </p>
                
                <!-- Payout Options -->
                <div style="display: grid; gap: 12px; margin-bottom: 24px;">
                    <!-- Wait 24 Hours Option -->
                    <div class="payout-option" onclick="selectPayoutOption('wait')" id="waitOption" style="background: rgba(255, 255, 255, 0.05); border: 2px solid rgba(255, 215, 0, 0.3); border-radius: 16px; padding: 20px; cursor: pointer; transition: all 0.3s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="font-size: 32px;">⏰</div>
                                <div>
                                    <div style="font-size: 16px; font-weight: 700; color: white;">Wait 24 Hours</div>
                                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.7);">Free - No fees</div>
                                </div>
                            </div>
                            <div style="font-size: 18px; font-weight: 700; color: #10B981;" id="waitAmount">GH₵0.00</div>
                        </div>
                        <div style="font-size: 12px; color: rgba(255, 255, 255, 0.6);">
                            Payment will be automatically released to your available balance in 24 hours
                        </div>
                    </div>
                    
                    <!-- Instant Payout Option -->
                    <div class="payout-option" onclick="selectPayoutOption('instant')" id="instantOption" style="background: rgba(255, 215, 0, 0.08); border: 2px solid rgba(255, 215, 0, 0.3); border-radius: 16px; padding: 20px; cursor: pointer; transition: all 0.3s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="font-size: 32px;">⚡</div>
                                <div>
                                    <div style="font-size: 16px; font-weight: 700; color: white;">Get It Now</div>
                                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.7);">2% instant fee</div>
                                </div>
                            </div>
                            <div style="font-size: 18px; font-weight: 700; color: #FFD700;" id="instantAmount">GH₵0.00</div>
                        </div>
                        <div style="font-size: 12px; color: rgba(255, 255, 255, 0.6);">
                            Payment released immediately to your available balance (Fee: <span id="instantFeeText">GH₵0.00</span>)
                        </div>
                    </div>
                </div>
                
                <button class="btn-primary" id="confirmPayoutBtn" onclick="confirmPayoutChoice()" style="width: 100%; padding: 16px; font-size: 16px;">
                    Confirm Choice
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Profile dropdown toggle
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const profileIcon = document.querySelector('.profile-icon');
            
            if (!profileIcon.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
        
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../actions/logout_action.php';
            }
        }
        
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                const tabName = this.dataset.tab;
                document.getElementById(tabName + '-tab').classList.add('active');
            });
        });
        
        async function acceptJob(bookingId) {
            if (!confirm('Accept this job? You will be committed to completing it.')) return;
            
            await updateBookingStatus(bookingId, 'accepted', 'Job accepted successfully! Customer has been notified.');
        }
        
        async function rejectJob(bookingId) {
            if (!confirm('Reject this job? This action cannot be undone.')) return;
            
            await updateBookingStatus(bookingId, 'rejected', 'Job rejected. Customer will be notified.');
        }
        
        async function startJob(bookingId) {
            if (!confirm('Start this job?')) return;
            
            await updateBookingStatus(bookingId, 'in_progress', 'Job started successfully!');
        }
        
        async function viewJobDetails(bookingId) {
            document.getElementById('jobDetailsModal').classList.add('show');
            const content = document.getElementById('jobDetailsContent');
            content.innerHTML = '<div class="loading">Loading...</div>';
            
            try {
                const response = await fetch(`../actions/get_booking_details.php?booking_id=${bookingId}`);
                const result = await response.json();
                
                if (result.status === 'success') {
                    const booking = result.booking;
                    const statusColors = {
                        'pending': '#FFA500',
                        'accepted': '#3B82F6',
                        'in_progress': '#10B981',
                        'completed': '#059669',
                        'rejected': '#EF4444'
                    };
                    
                    const statusColor = statusColors[booking.booking_status] || '#6B7280';
                    
                    content.innerHTML = `
                        <div style="margin-bottom: 24px;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                                <div>
                                    <h3 style="font-size: 20px; font-weight: 700; color: rgba(255, 255, 255, 0.95); margin-bottom: 8px;">
                                        ${booking.service_title || 'Service Request'}
                                    </h3>
                                    <div style="color: rgba(255, 255, 255, 0.6);">Booking #${booking.booking_id}</div>
                                </div>
                                <span style="padding: 8px 16px; border-radius: 20px; background: ${statusColor}20; color: ${statusColor}; font-weight: 600; font-size: 13px;">
                                    ${booking.booking_status.replace('_', ' ').toUpperCase()}
                                </span>
                            </div>
                            
                            <div style="background: rgba(255, 215, 0, 0.05); padding: 20px; border-radius: 12px; border: 1px solid rgba(255, 215, 0, 0.2); margin-bottom: 20px;">
                                <div style="font-size: 14px; color: rgba(255, 255, 255, 0.7); margin-bottom: 8px;">Total Amount</div>
                                <div style="font-size: 32px; font-weight: 700; color: #FFD700;">GH₵${parseFloat(booking.estimated_price).toFixed(2)}</div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 24px;">
                                <div>
                                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.6); margin-bottom: 4px;">Customer</div>
                                    <div style="font-weight: 600; color: rgba(255, 255, 255, 0.9);">${booking.customer_name}</div>
                                </div>
                                <div>
                                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.6); margin-bottom: 4px;">Phone</div>
                                    <div style="font-weight: 600; color: rgba(255, 255, 255, 0.9);">${booking.customer_phone || 'N/A'}</div>
                                </div>
                                <div>
                                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.6); margin-bottom: 4px;">Date</div>
                                    <div style="font-weight: 600; color: rgba(255, 255, 255, 0.9);">${new Date(booking.booking_date).toLocaleDateString()}</div>
                                </div>
                                <div>
                                    <div style="font-size: 13px; color: rgba(255, 255, 255, 0.6); margin-bottom: 4px;">Time</div>
                                    <div style="font-weight: 600; color: rgba(255, 255, 255, 0.9);">${booking.booking_time}</div>
                                </div>
                            </div>
                            
                            ${booking.service_description ? `
                                <div style="margin-bottom: 20px;">
                                    <div style="font-size: 14px; font-weight: 600; color: rgba(255, 255, 255, 0.9); margin-bottom: 8px;">Description</div>
                                    <div style="color: rgba(255, 255, 255, 0.7); line-height: 1.6;">${booking.service_description}</div>
                                </div>
                            ` : ''}
                            
                            ${booking.location ? `
                                <div style="margin-bottom: 20px;">
                                    <div style="font-size: 14px; font-weight: 600; color: rgba(255, 255, 255, 0.9); margin-bottom: 8px;">Location</div>
                                    <div style="color: rgba(255, 255, 255, 0.7);">📍 ${booking.location}</div>
                                </div>
                            ` : ''}
                            
                            ${booking.completion_photo ? `
                                <div style="margin-bottom: 20px;">
                                    <div style="font-size: 14px; font-weight: 600; color: rgba(255, 255, 255, 0.9); margin-bottom: 8px;">Completion Photo</div>
                                    <img src="../${booking.completion_photo}" style="width: 100%; border-radius: 12px; max-height: 300px; object-fit: cover;" alt="Completion photo">
                                </div>
                            ` : ''}
                            
                            <div style="padding-top: 20px; border-top: 1px solid rgba(255, 215, 0, 0.1);">
                                <div style="font-size: 13px; color: rgba(255, 255, 255, 0.6);">
                                    Created: ${new Date(booking.created_at).toLocaleString()}
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<div class="loading">Failed to load job details</div>';
                }
            } catch (error) {
                console.error('Error loading job details:', error);
                content.innerHTML = '<div class="loading">Error loading job details</div>';
            }
        }
        
        function closeJobDetailsModal() {
            document.getElementById('jobDetailsModal').classList.remove('show');
        }
        
        let currentBookingId = null;
        let selectedPhoto = null;
        
        function completeJob(bookingId) {
            currentBookingId = bookingId;
            document.getElementById('completionModal').classList.add('show');
        }
        
        function closeCompletionModal() {
            document.getElementById('completionModal').classList.remove('show');
            currentBookingId = null;
            selectedPhoto = null;
            document.getElementById('photoPreview').style.display = 'none';
            document.getElementById('photoUploadArea').style.display = 'block';
            document.getElementById('completionNotes').value = '';
        }
        
        // Photo upload handling
        document.getElementById('photoUploadArea').addEventListener('click', function() {
            document.getElementById('completionPhoto').click();
        });
        
        document.getElementById('completionPhoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size
                if (file.size > 5 * 1024 * 1024) {
                    alert('File too large. Maximum size is 5MB.');
                    return;
                }
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file.');
                    return;
                }
                
                selectedPhoto = file;
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('photoPreview').style.display = 'block';
                    document.getElementById('photoUploadArea').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
        
        function removePhoto() {
            selectedPhoto = null;
            document.getElementById('photoPreview').style.display = 'none';
            document.getElementById('photoUploadArea').style.display = 'block';
            document.getElementById('completionPhoto').value = '';
        }
        
        let paymentBreakdown = null;
        let selectedPayoutOption = 'wait'; // default to wait 24 hours
        
        async function submitCompletion() {
            if (!selectedPhoto) {
                alert('Please upload a photo of the completed work.');
                return;
            }
            
            const submitBtn = document.getElementById('submitCompletionBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
            
            const formData = new FormData();
            formData.append('completion_photo', selectedPhoto);
            formData.append('booking_id', currentBookingId);
            formData.append('completion_notes', document.getElementById('completionNotes').value);
            
            try {
                const response = await fetch('../actions/upload_completion_photo.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Store booking ID from response (in case currentBookingId gets reset)
                    if (data.booking_id) {
                        currentBookingId = data.booking_id;
                    }
                    
                    closeCompletionModal();
                    
                    // Show payout options if available
                    if (data.show_payout_options && data.payment_breakdown) {
                        paymentBreakdown = data.payment_breakdown;
                        showPayoutOptionsModal();
                    } else {
                        alert('Job completed successfully! Payment will be processed.');
                        window.location.reload();
                    }
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Complete Job';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to complete job. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Job';
            }
        }
        
        function showPayoutOptionsModal() {
            // Populate payment breakdown
            document.getElementById('payoutJobCharge').textContent = 'GH₵' + paymentBreakdown.job_charge.toFixed(2);
            document.getElementById('payoutPlatformFee').textContent = '-GH₵' + paymentBreakdown.platform_fee.toFixed(2);
            document.getElementById('payoutWorkerAmount').textContent = 'GH₵' + paymentBreakdown.worker_payout.toFixed(2);
            
            // Set amounts for both options
            document.getElementById('waitAmount').textContent = 'GH₵' + paymentBreakdown.worker_payout.toFixed(2);
            document.getElementById('instantAmount').textContent = 'GH₵' + paymentBreakdown.instant_amount.toFixed(2);
            document.getElementById('instantFeeText').textContent = 'GH₵' + paymentBreakdown.instant_fee.toFixed(2);
            
            // Reset selection
            selectedPayoutOption = 'wait';
            selectPayoutOption('wait');
            
            // Show modal
            document.getElementById('payoutOptionsModal').classList.add('show');
        }
        
        function closePayoutOptionsModal() {
            document.getElementById('payoutOptionsModal').classList.remove('show');
            paymentBreakdown = null;
            selectedPayoutOption = 'wait';
        }
        
        function selectPayoutOption(option) {
            selectedPayoutOption = option;
            
            const waitOption = document.getElementById('waitOption');
            const instantOption = document.getElementById('instantOption');
            
            // Reset styles
            waitOption.style.borderColor = 'rgba(255, 215, 0, 0.3)';
            waitOption.style.background = 'rgba(255, 255, 255, 0.05)';
            instantOption.style.borderColor = 'rgba(255, 215, 0, 0.3)';
            instantOption.style.background = 'rgba(255, 215, 0, 0.08)';
            
            // Highlight selected
            if (option === 'wait') {
                waitOption.style.borderColor = '#10B981';
                waitOption.style.background = 'rgba(16, 185, 129, 0.1)';
                waitOption.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.2)';
                instantOption.style.boxShadow = 'none';
            } else {
                instantOption.style.borderColor = '#FFD700';
                instantOption.style.background = 'rgba(255, 215, 0, 0.15)';
                instantOption.style.boxShadow = '0 0 0 3px rgba(255, 215, 0, 0.2)';
                waitOption.style.boxShadow = 'none';
            }
        }
        
        async function confirmPayoutChoice() {
            const confirmBtn = document.getElementById('confirmPayoutBtn');
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Processing...';
            
            try {
                if (selectedPayoutOption === 'instant') {
                    // Request instant payout
                    const response = await fetch('../actions/instant_payout.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            booking_id: currentBookingId
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        alert(`🎉 Payment released instantly!\n\nYou received: GH₵${data.amount_received.toFixed(2)}\nInstant fee: GH₵${data.instant_fee.toFixed(2)}\n\nCheck your available balance!`);
                        closePayoutOptionsModal();
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        confirmBtn.disabled = false;
                        confirmBtn.textContent = 'Confirm Choice';
                    }
                } else {
                    // Wait 24 hours - just close and reload
                    alert(`✅ Job completed!\n\nYour payment of GH₵${paymentBreakdown.worker_payout.toFixed(2)} will be automatically released to your available balance in 24 hours.`);
                    closePayoutOptionsModal();
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to process payout choice. Please try again.');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Confirm Choice';
            }
        }
        
        async function updateBookingStatus(bookingId, newStatus, successMessage) {
            try {
                const response = await fetch('../actions/update_booking_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        booking_id: bookingId,
                        new_status: newStatus
                    })
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    alert(successMessage);
                    // Reload page to show updated status
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to update job status. Please try again.');
            }
        }
        
        // Withdraw Modal Functions
        async function openWithdrawModal() {
            document.getElementById('withdrawModal').classList.add('show');
            await loadPayoutAccounts();
        }
        
        async function loadPayoutAccounts() {
            const select = document.getElementById('payoutAccount');
            select.innerHTML = '<option value="">Loading...</option>';
            
            try {
                const response = await fetch('../actions/get_payout_accounts.php');
                const result = await response.json();
                
                if (result.status === 'success' && result.accounts.length > 0) {
                    select.innerHTML = '<option value="">Select payout account</option>';
                    result.accounts.forEach(account => {
                        const option = document.createElement('option');
                        option.value = account.account_id;
                        option.textContent = account.display_text;
                        if (account.is_default) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                } else {
                    select.innerHTML = '<option value="">No payout accounts found</option>';
                }
            } catch (error) {
                console.error('Error loading payout accounts:', error);
                select.innerHTML = '<option value="">Error loading accounts</option>';
            }
        }
        
        function closeWithdrawModal() {
            document.getElementById('withdrawModal').classList.remove('show');
            document.getElementById('withdrawAmount').value = '';
            document.getElementById('payoutAccount').value = '';
        }
        
        function updatePayoutFee() {
            const amount = parseFloat(document.getElementById('withdrawAmount').value) || 0;
            const payoutType = document.getElementById('payoutType').value;
            const feeBreakdown = document.getElementById('feeBreakdown');
            const feeRow = document.getElementById('feeRow');
            
            if (amount > 0) {
                feeBreakdown.style.display = 'block';
                
                let fee = 0;
                let netAmount = amount;
                
                if (payoutType === 'instant') {
                    fee = amount * 0.02; // 2% fee
                    netAmount = amount - fee;
                    feeRow.style.display = 'flex';
                } else {
                    feeRow.style.display = 'none';
                }
                
                document.getElementById('withdrawalAmount').textContent = 'GH₵' + amount.toFixed(2);
                document.getElementById('processingFee').textContent = 'GH₵' + fee.toFixed(2);
                document.getElementById('netAmount').textContent = 'GH₵' + netAmount.toFixed(2);
            } else {
                feeBreakdown.style.display = 'none';
            }
        }
        
        // Update fee when amount changes
        document.getElementById('withdrawAmount').addEventListener('input', updatePayoutFee);
        
        async function submitWithdrawal() {
            const amount = parseFloat(document.getElementById('withdrawAmount').value);
            const payoutType = document.getElementById('payoutType').value;
            const accountId = document.getElementById('payoutAccount').value;
            
            if (!amount || amount < 50) {
                alert('Minimum withdrawal amount is GH₵50');
                return;
            }
            
            if (!accountId) {
                alert('Please select a payout account');
                return;
            }
            
            const availableBalance = <?php echo $stats['available_balance'] ?? 0; ?>;
            if (amount > availableBalance) {
                alert('Insufficient balance');
                return;
            }
            
            const fee = payoutType === 'instant' ? amount * 0.02 : 0;
            const netAmount = amount - fee;
            
            const confirmMessage = payoutType === 'instant' 
                ? `Withdraw GH₵${amount.toFixed(2)} with 2% fee (GH₵${fee.toFixed(2)})?\n\nYou'll receive: GH₵${netAmount.toFixed(2)}\nProcessing: Immediate (priority)`
                : `Withdraw GH₵${amount.toFixed(2)} (FREE)?\n\nYou'll receive: GH₵${netAmount.toFixed(2)}\nProcessing: Within 24 hours`;
            
            if (!confirm(confirmMessage)) return;
            
            // Disable button and show loading
            const submitBtn = document.querySelector('#withdrawModal .btn-primary');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            try {
                const response = await fetch('../actions/request_payout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: amount,
                        payout_type: payoutType,
                        account_id: accountId
                    })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert(result.message);
                    closeWithdrawModal();
                    // Reload to show updated balance
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Request Withdrawal';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to process withdrawal request. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Request Withdrawal';
            }
        }
        
        // History Modal Functions
        function openHistoryModal() {
            document.getElementById('historyModal').classList.add('show');
            loadTransactionHistory();
        }
        
        function closeHistoryModal() {
            document.getElementById('historyModal').classList.remove('show');
        }
        
        async function loadTransactionHistory() {
            const historyContent = document.getElementById('historyContent');
            historyContent.innerHTML = '<div class="loading">Loading...</div>';
            
            try {
                const response = await fetch('../actions/get_payout_history.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    const payouts = result.payouts;
                    
                    if (!payouts || payouts.length === 0) {
                        historyContent.innerHTML = '<div class="loading">No withdrawal history yet</div>';
                        return;
                    }
                    
                    let html = '';
                    payouts.forEach(payout => {
                        const statusColor = payout.payout_status === 'completed' ? '#10B981' : 
                                          payout.payout_status === 'failed' ? '#EF4444' : '#FFA500';
                        const statusText = payout.payout_status.charAt(0).toUpperCase() + payout.payout_status.slice(1);
                        const date = new Date(payout.requested_at).toLocaleDateString();
                        const payoutTypeText = payout.payout_type === 'instant' ? 'Instant' : 'Next-Day';
                        
                        html += `
                            <div class="history-item">
                                <div class="history-header">
                                    <div class="history-title">Withdrawal - ${payoutTypeText}</div>
                                    <div class="history-amount" style="color: ${statusColor};">GH₵${parseFloat(payout.net_amount).toFixed(2)}</div>
                                </div>
                                <div class="history-date">${date} - ${statusText}</div>
                            </div>
                        `;
                    });
                    
                    historyContent.innerHTML = html;
                } else {
                    historyContent.innerHTML = '<div class="loading">Failed to load history</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                historyContent.innerHTML = '<div class="loading">Error loading history</div>';
            }
        }
    </script>
</body>
</html>
