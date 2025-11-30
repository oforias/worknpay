<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

$worker_id = isset($_GET['worker_id']) ? (int)$_GET['worker_id'] : 0;
$customer_id = get_user_id();
$customer_email = get_user_email();

if ($worker_id <= 0) {
    header('Location: home.php');
    exit();
}

// Fetch real worker data
$db = new db_connection();
$worker_query = "SELECT u.user_id, u.user_name, u.user_city,
                 COALESCE(wp.skills, 'Service Provider') as skills, 
                 COALESCE(wp.hourly_rate, 50.00) as hourly_rate
                 FROM users u
                 LEFT JOIN worker_profiles wp ON u.user_id = wp.user_id
                 WHERE u.user_id = $worker_id AND u.user_role = 2 AND u.is_active = 1";
$worker_data = $db->db_fetch_one($worker_query);

if (!$worker_data) {
    header('Location: home.php?error=worker_not_found');
    exit();
}

$worker = [
    'id' => $worker_data['user_id'],
    'name' => $worker_data['user_name'],
    'role' => $worker_data['skills'],
    'hourly_rate' => (float)$worker_data['hourly_rate'],
    'location' => $worker_data['user_city'] ?? 'Ghana'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Service - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            padding-bottom: 120px;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            transition: all 0.3s ease;
        }
        

        
        @media (max-width: 768px) {
            body {
                max-width: 100%;
            }
        }
        
        .header {
            background: var(--header-bg);
            backdrop-filter: blur(20px);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid var(--border-color);
            position: relative;
            z-index: 10;
            color: white;
        }
        
        .back-btn {
            font-size: 28px;
            cursor: pointer;
            color: white;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateX(-4px);
            color: #FFD700;
        }
        
        .header-title {
            font-size: 20px;
            font-weight: 700;
            color: white;
        }
        
        .section {
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            padding: 24px 20px;
            margin: 0 20px 16px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            position: relative;
            z-index: 1;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
        }
        
        .worker-summary {
            display: flex;
            gap: 16px;
            padding: 20px;
            background: var(--bg-tertiary);
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }
        
        .worker-avatar {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            border: 3px solid rgba(255, 215, 0, 0.3);
            box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
        }
        
        .worker-info h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 6px;
        }
        
        .worker-info p {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-size: 15px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 10px;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            color: var(--text-primary);
            font-family: inherit;
            background: var(--bg-tertiary);
            transition: all 0.3s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: rgba(255, 215, 0, 0.5);
            box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.1);
            background: var(--bg-secondary);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .duration-selector {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }
        
        .duration-btn {
            padding: 14px;
            border: 2px solid var(--border-color);
            background: var(--bg-tertiary);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--text-primary);
        }
        
        .duration-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
            border-color: rgba(255, 215, 0, 0.3);
        }
        
        .duration-btn.active {
            border-color: rgba(255, 215, 0, 0.6);
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 165, 0, 0.15) 100%);
            font-weight: 700;
            box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
            color: #FFD700;
        }
        
        .price-breakdown {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 14px;
            font-size: 15px;
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .price-row.total {
            font-size: 22px;
            font-weight: 800;
            padding-top: 16px;
            border-top: 2px solid var(--border-color);
        }
        
        .price-row.total span:last-child {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .bottom-actions {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            max-width: 1200px;
            width: 100%;
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            padding: 20px;
            box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.1);
            border-top: 1px solid var(--border-color);
            z-index: 1000;
        }
        
        .book-btn {
            width: 100%;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            padding: 18px;
            border-radius: 16px;
            border: none;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 24px rgba(255, 215, 0, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .book-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }
        
        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(255, 215, 0, 0.6);
        }
        
        .book-btn:hover::before {
            left: 100%;
        }
        
        .book-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .book-btn:disabled::before {
            display: none;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .alert-error {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            color: #991B1B;
            border: 2px solid #FCA5A5;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            color: #065F46;
            border: 2px solid #6EE7B7;
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="back-btn" onclick="window.history.back()">←</span>
        <h1 class="header-title">Book Service</h1>
    </div>
    
    <div class="section">
        <div class="worker-summary">
            <div class="worker-avatar">👨‍🔧</div>
            <div class="worker-info">
                <h3><?php echo htmlspecialchars($worker['name']); ?></h3>
                <p><?php echo htmlspecialchars($worker['role']); ?> • <?php echo htmlspecialchars($worker['location']); ?></p>
            </div>
        </div>
    </div>
    
    <div class="section">
        <h2 class="section-title">Booking Details</h2>
        
        <div id="alertBox"></div>
        
        <form id="bookingForm">
            <div class="form-group">
                <label>Service Duration</label>
                <div class="duration-selector">
                    <button type="button" class="duration-btn active" data-hours="1">1 Hour</button>
                    <button type="button" class="duration-btn" data-hours="2">2 Hours</button>
                    <button type="button" class="duration-btn" data-hours="3">3 Hours</button>
                    <button type="button" class="duration-btn" data-hours="4">4 Hours</button>
                    <button type="button" class="duration-btn" data-hours="6">6 Hours</button>
                    <button type="button" class="duration-btn" data-hours="8">Full Day</button>
                </div>
                <input type="hidden" id="duration" name="duration" value="1">
            </div>
            
            <div class="form-group">
                <label for="booking_date">Preferred Date</label>
                <input type="date" id="booking_date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="booking_time">Preferred Time</label>
                <input type="time" id="booking_time" name="booking_time" required>
            </div>
            
            <div class="form-group">
                <label for="service_address">Service Address</label>
                <textarea id="service_address" name="service_address" required placeholder="Enter the full address where service is needed"></textarea>
            </div>
            
            <div class="form-group">
                <label for="customer_notes">Additional Notes (Optional)</label>
                <textarea id="customer_notes" name="customer_notes" placeholder="Any specific requirements or details..."></textarea>
            </div>
        </form>
    </div>
    
    <div class="section">
        <h2 class="section-title">Price Breakdown</h2>
        <div class="price-breakdown">
            <div class="price-row">
                <span>Hourly Rate</span>
                <span>GH₵<?php echo $worker['hourly_rate']; ?>/hr</span>
            </div>
            <div class="price-row">
                <span>Duration</span>
                <span id="duration-display">1 hour</span>
            </div>
            <div class="price-row">
                <span>Service Fee (7%)</span>
                <span id="service-fee">GH₵5.60</span>
            </div>
            <div class="price-row total">
                <span>Total Amount</span>
                <span id="total-amount">GH₵85.60</span>
            </div>
        </div>
    </div>
    
    <div class="bottom-actions">
        <button class="book-btn" id="bookBtn" onclick="proceedToPayment()">
            Proceed to Payment
        </button>
    </div>
    
    <script>
        const hourlyRate = <?php echo $worker['hourly_rate']; ?>;
        const commissionRate = 0.07; // 7% customer commission
        let selectedHours = 1;
        
        // Duration selector
        document.querySelectorAll('.duration-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.duration-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                selectedHours = parseInt(this.dataset.hours);
                document.getElementById('duration').value = selectedHours;
                updatePricing();
            });
        });
        
        function updatePricing() {
            const subtotal = hourlyRate * selectedHours;
            const serviceFee = subtotal * commissionRate;
            const total = subtotal + serviceFee;
            
            document.getElementById('duration-display').textContent = selectedHours + (selectedHours === 1 ? ' hour' : ' hours');
            document.getElementById('service-fee').textContent = 'GH₵' + serviceFee.toFixed(2);
            document.getElementById('total-amount').textContent = 'GH₵' + total.toFixed(2);
        }
        
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => alertBox.innerHTML = '', 5000);
        }
        
        async function proceedToPayment() {
            const form = document.getElementById('bookingForm');
            
            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const bookBtn = document.getElementById('bookBtn');
            bookBtn.disabled = true;
            bookBtn.textContent = 'Processing...';
            
            const subtotal = hourlyRate * selectedHours;
            const serviceFee = subtotal * commissionRate;
            const totalAmount = subtotal + serviceFee;
            
            const bookingData = {
                worker_id: <?php echo $worker_id; ?>,
                worker_name: '<?php echo addslashes($worker['name']); ?>',
                duration: selectedHours,
                booking_date: document.getElementById('booking_date').value,
                booking_time: document.getElementById('booking_time').value,
                service_address: document.getElementById('service_address').value,
                customer_notes: document.getElementById('customer_notes').value,
                amount: totalAmount,
                email: '<?php echo $customer_email; ?>'
            };
            
            // Store booking data in session
            sessionStorage.setItem('pending_booking', JSON.stringify(bookingData));
            
            try {
                // Initialize Paystack payment with custom callback
                const response = await fetch('../actions/booking_payment_init.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        amount: totalAmount,
                        email: bookingData.email,
                        booking_data: bookingData
                    })
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Redirect to Paystack
                    window.location.href = data.authorization_url;
                } else {
                    showAlert(data.message || 'Payment initialization failed', 'error');
                    bookBtn.disabled = false;
                    bookBtn.textContent = 'Proceed to Payment';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Connection error. Please try again.', 'error');
                bookBtn.disabled = false;
                bookBtn.textContent = 'Proceed to Payment';
            }
        }
        
        // Set minimum date to today
        document.getElementById('booking_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
