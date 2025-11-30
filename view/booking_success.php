<?php
require_once '../settings/core.php';
require_login('login.php');

$booking_ref = isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - WorkNPay</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .container {
            background: var(--bg-secondary);
            padding: 48px;
            border-radius: 24px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto 24px;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        h1 {
            font-size: 28px;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        
        p {
            color: var(--text-secondary);
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        
        .booking-ref {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 32px;
        }
        
        .booking-ref-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }
        
        .booking-ref-value {
            font-size: 18px;
            font-weight: 700;
            color: #10B981;
            font-family: 'Courier New', monospace;
        }
        
        .buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            min-width: 140px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            background: var(--bg-primary);
        }
        
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 16px;
            border-radius: 12px;
            margin-top: 24px;
            text-align: left;
        }
        
        .info-box h3 {
            font-size: 14px;
            color: #3B82F6;
            margin-bottom: 8px;
        }
        
        .info-box ul {
            list-style: none;
            padding: 0;
        }
        
        .info-box li {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 6px;
            padding-left: 20px;
            position: relative;
        }
        
        .info-box li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #3B82F6;
            font-weight: bold;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 32px 24px;
            }
            
            .buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Booking Confirmed!</h1>
        <p>Your service booking has been successfully created and payment confirmed. The worker will be notified shortly.</p>
        
        <?php if ($booking_ref): ?>
        <div class="booking-ref">
            <div class="booking-ref-label">Booking Reference</div>
            <div class="booking-ref-value"><?php echo $booking_ref; ?></div>
        </div>
        <?php endif; ?>
        
        <div class="buttons">
            <a href="my_bookings.php" class="btn btn-primary">View My Bookings</a>
            <a href="home.php" class="btn btn-secondary">Back to Home</a>
        </div>
        
        <div class="info-box">
            <h3>What happens next?</h3>
            <ul>
                <li>Worker will review and accept your booking</li>
                <li>You'll receive notifications on booking status</li>
                <li>Payment is held securely in escrow</li>
                <li>Funds released after service completion</li>
            </ul>
        </div>
    </div>
    
    <script src="../js/theme-toggle.js"></script>
</body>
</html>
