<?php
/**
 * Booking Payment Callback
 * Handles payment verification and booking creation
 */

require_once '../settings/core.php';
require_once '../settings/paystack_config.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Get reference from URL
$reference = isset($_GET['reference']) ? trim($_GET['reference']) : null;

if (!$reference) {
    header('Location: home.php?error=no_reference');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Booking - WorkNPay</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #F5F5F5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #E0E0E0;
            border-top: 4px solid #0052CC;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 24px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h1 {
            font-size: 24px;
            color: #1A1A1A;
            margin-bottom: 12px;
        }
        
        p {
            color: #757575;
            font-size: 14px;
        }
        
        .error {
            color: #DC2626;
            background: #FEE2E2;
            padding: 16px;
            border-radius: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner" id="spinner"></div>
        <h1>Processing Your Booking</h1>
        <p>Please wait while we verify your payment and create your booking...</p>
        <div id="errorBox"></div>
    </div>

    <script>
        async function processBooking() {
            const reference = '<?php echo htmlspecialchars($reference); ?>';
            
            try {
                // Process booking with payment verification
                const response = await fetch('../actions/process_booking_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reference: reference })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    // Redirect to success page
                    window.location.href = `booking_success.php?ref=${result.booking_reference}`;
                } else {
                    showError(result.message || 'Failed to process booking');
                    setTimeout(() => window.location.href = 'my_bookings.php', 5000);
                }
                
            } catch (error) {
                console.error('Error:', error);
                showError('An error occurred. Please contact support with reference: ' + reference);
                setTimeout(() => window.location.href = 'my_bookings.php', 5000);
            }
        }
        
        function showError(message) {
            document.getElementById('spinner').style.display = 'none';
            document.getElementById('errorBox').innerHTML = `<div class="error">${message}</div>`;
        }
        
        // Start processing when page loads
        window.addEventListener('load', processBooking);
    </script>
</body>
</html>
