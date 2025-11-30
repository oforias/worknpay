<?php
require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';
require_login('login.php');

$user_name = get_user_name();
$customer_id = get_user_id();

// Get filter from URL (default to 'all')
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Fetch real bookings from database
$all_bookings = get_customer_bookings_ctr($customer_id);

// Filter bookings based on status
$bookings = [];
if ($all_bookings) {
    foreach ($all_bookings as $booking) {
        $status = strtolower($booking['booking_status']);
        
        // Apply filter
        if ($filter === 'all' || 
            ($filter === 'active' && in_array($status, ['pending', 'accepted', 'in_progress'])) ||
            ($filter === 'completed' && $status === 'completed') ||
            ($filter === 'cancelled' && in_array($status, ['cancelled', 'rejected']))) {
            
            $bookings[] = [
                'id' => $booking['booking_id'],
                'booking_reference' => $booking['booking_reference'],
                'worker_name' => $booking['worker_name'],
                'worker_role' => $booking['service_title'] ?? 'Service Provider',
                'date' => $booking['booking_date'],
                'time' => date('g:i A', strtotime($booking['booking_time'])),
                'status' => $status,
                'amount' => $booking['estimated_price'],
                'address' => $booking['service_address'],
                'notes' => $booking['customer_notes']
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - WorkNPay</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0A0E1A;
            min-height: 100vh;
            padding-bottom: 80px;
            max-width: 1200px;
            margin: 0 auto;
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
            background: 
                radial-gradient(circle at 20% 30%, rgba(255, 215, 0, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 165, 0, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        @media (max-width: 768px) {
            body {
                max-width: 100%;
            }
        }
        
        .header {
            background: linear-gradient(135deg, rgba(10, 14, 26, 0.95) 0%, rgba(20, 24, 36, 0.95) 100%);
            backdrop-filter: blur(20px);
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            position: relative;
            z-index: 10;
        }
        
        .header-title {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #FFFFFF 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .tabs {
            display: flex;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 0 20px;
            gap: 32px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            position: relative;
            z-index: 10;
        }
        
        .tab {
            padding: 18px 0;
            font-size: 15px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.6);
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .tab:hover {
            color: rgba(255, 255, 255, 0.9);
        }
        
        .tab.active {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            border-bottom-color: #FFD700;
            font-weight: 700;
        }
        
        .bookings-list {
            padding: 24px 20px;
            position: relative;
            z-index: 1;
        }
        
        .booking-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            padding: 20px;
            border-radius: 20px;
            margin-bottom: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.1);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .booking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.1), transparent);
            transition: left 0.6s ease;
        }
        
        .booking-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.3);
        }
        
        .booking-card:hover::before {
            left: 100%;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }
        
        .worker-info h3 {
            font-size: 18px;
            font-weight: 700;
            background: linear-gradient(135deg, #FFFFFF 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 6px;
        }
        
        .worker-info p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 500;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 24px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1px solid;
        }
        
        .status-badge.pending {
            background: linear-gradient(135deg, #FFF3E0 0%, #FFE0B2 100%);
            color: #E65100;
            border-color: rgba(230, 81, 0, 0.2);
        }
        
        .status-badge.accepted {
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            color: #1565C0;
            border-color: rgba(21, 101, 192, 0.2);
        }
        
        .status-badge.in_progress {
            background: linear-gradient(135deg, #F3E5F5 0%, #E1BEE7 100%);
            color: #6A1B9A;
            border-color: rgba(106, 27, 154, 0.2);
        }
        
        .status-badge.completed {
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            color: #065F46;
            border-color: rgba(6, 95, 70, 0.2);
        }
        
        .status-badge.cancelled,
        .status-badge.rejected {
            background: linear-gradient(135deg, #FFEBEE 0%, #FFCDD2 100%);
            color: #C62828;
            border-color: rgba(198, 40, 40, 0.2);
        }
        
        .booking-details {
            display: flex;
            gap: 20px;
            margin-bottom: 12px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .booking-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .booking-amount {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .btn-small {
            padding: 10px 20px;
            border-radius: 12px;
            border: 2px solid transparent;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-small::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }
        
        .btn-small:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(255, 215, 0, 0.5);
        }
        
        .btn-small:hover::before {
            left: 100%;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.6;
        }
        
        .empty-text {
            font-size: 18px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            max-width: 1200px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 16px 0;
            box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.1);
            border-top: 1px solid rgba(255, 215, 0, 0.2);
            display: flex;
            justify-content: space-around;
            z-index: 1000;
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            color: #666;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 12px 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 165, 0, 0.15) 100%);
            border: 1px solid rgba(255, 215, 0, 0.3);
        }
        
        .nav-item.active .nav-icon {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-item.active div:last-child {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }
        
        .nav-item:hover {
            background: rgba(255, 215, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .nav-icon {
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="header-title">My Bookings</h1>
    </div>
    
    <div class="tabs">
        <div class="tab <?php echo $filter === 'all' ? 'active' : ''; ?>" data-filter="all">All</div>
        <div class="tab <?php echo $filter === 'active' ? 'active' : ''; ?>" data-filter="active">Active</div>
        <div class="tab <?php echo $filter === 'completed' ? 'active' : ''; ?>" data-filter="completed">Completed</div>
        <div class="tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>" data-filter="cancelled">Cancelled</div>
    </div>
    
    <div class="bookings-list">
        <?php if (count($bookings) > 0): ?>
            <?php foreach ($bookings as $booking): ?>
            <div class="booking-card">
                <div class="booking-header">
                    <div class="worker-info">
                        <h3><?php echo htmlspecialchars($booking['worker_name']); ?></h3>
                        <p><?php echo htmlspecialchars($booking['worker_role']); ?></p>
                    </div>
                    <span class="status-badge <?php echo $booking['status']; ?>">
                        <?php echo ucfirst($booking['status']); ?>
                    </span>
                </div>
                
                <div class="booking-details">
                    <span>📅 <?php echo date('M d, Y', strtotime($booking['date'])); ?></span>
                    <span>🕐 <?php echo $booking['time']; ?></span>
                    <span>📍 <?php echo substr($booking['address'], 0, 30) . '...'; ?></span>
                </div>
                
                <div class="booking-footer">
                    <div>
                        <div style="font-size: 11px; color: #757575; margin-bottom: 2px;">Ref: <?php echo $booking['booking_reference']; ?></div>
                        <span class="booking-amount">GH₵<?php echo number_format($booking['amount'], 2); ?></span>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <?php if ($booking['status'] === 'pending'): ?>
                            <button class="btn-small btn-cancel" onclick="cancelBooking(<?php echo $booking['id']; ?>)" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);">Cancel</button>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'completed'): ?>
                            <a href="rate_worker.php?booking_id=<?php echo $booking['id']; ?>" class="btn-small" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #0A0E1A; text-decoration: none;">Rate Worker</a>
                            <a href="open_dispute.php?booking_id=<?php echo $booking['id']; ?>" class="btn-small" style="background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%); text-decoration: none;">Open Dispute</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <p class="empty-text">No bookings yet</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="bottom-nav">
        <a href="home.php" class="nav-item">
            <div class="nav-icon">🏠</div>
            <div>Home</div>
        </a>
        <a href="my_bookings.php" class="nav-item active">
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
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const filter = this.dataset.filter;
                window.location.href = `my_bookings.php?filter=${filter}`;
            });
        });
        
        // View booking details
        function viewBooking(bookingId) {
            alert(`Viewing booking #${bookingId}... (Booking details page coming soon!)`);
            // In production: window.location.href = `booking_details.php?id=${bookingId}`;
        }
        
        // Cancel booking
        async function cancelBooking(bookingId) {
            if (!confirm('Are you sure you want to cancel this booking? If payment was made, a refund will be processed.')) {
                return;
            }
            
            try {
                const response = await fetch('../actions/cancel_booking.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ booking_id: bookingId })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to cancel booking');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }
    </script>
</body>
</html>
