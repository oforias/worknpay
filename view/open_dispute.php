<?php
require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';
require_login('login.php');

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$user_id = get_user_id();

if ($booking_id <= 0) {
    header('Location: my_bookings.php');
    exit();
}

// Get booking details
$booking = get_booking_by_id_ctr($booking_id);

if (!$booking) {
    header('Location: my_bookings.php?error=booking_not_found');
    exit();
}

// Verify user is customer or worker
$is_customer = ($booking['customer_id'] == $user_id);
$is_worker = ($booking['worker_id'] == $user_id);

if (!$is_customer && !$is_worker) {
    header('Location: my_bookings.php?error=unauthorized');
    exit();
}

// Check if booking is completed
if ($booking['booking_status'] !== 'completed') {
    header('Location: my_bookings.php?error=not_completed');
    exit();
}

// Calculate hours since completion
$completion_time = strtotime($booking['completion_date']);
$hours_since = floor((time() - $completion_time) / 3600);
$hours_remaining = max(0, 48 - $hours_since);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Dispute - WorkNPay</title>
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0A0E1A;
            min-height: 100vh;
            padding-bottom: 40px;
            color: white;
            position: relative;
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
        
        .header {
            background: linear-gradient(135deg, rgba(10, 14, 26, 0.95) 0%, rgba(20, 24, 36, 0.95) 100%);
            backdrop-filter: blur(20px);
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .header p {
            opacity: 0.8;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .container {
            max-width: 800px;
            margin: 20px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .warning-box {
            background: rgba(255, 165, 0, 0.1);
            border: 2px solid rgba(255, 165, 0, 0.3);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            color: #FFA500;
        }
        
        .warning-box strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .booking-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 14px;
        }
        
        .info-label {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .info-value {
            color: rgba(255, 255, 255, 0.95);
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 8px;
        }
        
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-family: inherit;
        }
        
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .file-upload {
            border: 2px dashed rgba(255, 215, 0, 0.3);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.02);
        }
        
        .file-upload:hover {
            border-color: rgba(255, 215, 0, 0.5);
            background: rgba(255, 215, 0, 0.05);
        }
        
        .file-upload input {
            display: none;
        }
        
        .file-list {
            margin-top: 16px;
        }
        
        .file-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            border: none;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.5);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 8px;
            color: #FFD700;
            text-decoration: none;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: rgba(255, 215, 0, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.2);
        }
        
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid rgba(16, 185, 129, 0.3);
            color: #6EE7B7;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="my_bookings.php" class="back-link">← Back to My Bookings</a>
        <h1>Open Dispute</h1>
        <p>Report an issue with your completed booking</p>
    </div>
    
    <div class="container">
        <div class="card">
            <?php if ($hours_remaining > 0): ?>
                <div class="warning-box">
                    <strong>⏰ Time Remaining: <?php echo $hours_remaining; ?> hours</strong>
                    Disputes must be opened within 48 hours of job completion
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    The 48-hour dispute window has expired for this booking.
                </div>
            <?php endif; ?>
            
            <div class="card-title">Booking Details</div>
            <div class="booking-info">
                <div class="info-row">
                    <span class="info-label">Booking Reference:</span>
                    <span class="info-value"><?php echo htmlspecialchars($booking['booking_reference']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><?php echo $is_customer ? 'Worker' : 'Customer'; ?>:</span>
                    <span class="info-value"><?php echo htmlspecialchars($is_customer ? $booking['worker_name'] : $booking['customer_name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Service Date:</span>
                    <span class="info-value"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Amount:</span>
                    <span class="info-value">GH₵<?php echo number_format($booking['estimated_price'], 2); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Completed:</span>
                    <span class="info-value"><?php echo date('M d, Y H:i', strtotime($booking['completion_date'])); ?></span>
                </div>
            </div>
            
            <?php if ($hours_remaining > 0): ?>
                <div id="alertBox"></div>
                
                <form id="disputeForm" enctype="multipart/form-data">
                    <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                    
                    <div class="form-group">
                        <label for="reason">Dispute Reason *</label>
                        <select id="reason" name="reason" required>
                            <option value="">Select a reason...</option>
                            <option value="service_not_completed">Service Not Completed</option>
                            <option value="poor_quality">Poor Quality Work</option>
                            <option value="overcharged">Overcharged</option>
                            <option value="damaged_property">Damaged Property</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Detailed Description *</label>
                        <textarea id="description" name="description" required placeholder="Please provide a detailed explanation of the issue..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Evidence Photos (Optional)</label>
                        <div class="file-upload" onclick="document.getElementById('evidence_photos').click()">
                            <div style="font-size: 48px; margin-bottom: 12px;">📷</div>
                            <div style="font-size: 14px; color: rgba(255, 255, 255, 0.7);">
                                Click to upload photos (Max 5 photos, 5MB each)
                            </div>
                        </div>
                        <input type="file" id="evidence_photos" name="evidence_photos[]" multiple accept="image/*">
                        <div id="fileList" class="file-list"></div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit Dispute</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const fileInput = document.getElementById('evidence_photos');
        const fileList = document.getElementById('fileList');
        
        fileInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            const files = Array.from(this.files);
            
            if (files.length > 5) {
                alert('Maximum 5 photos allowed');
                this.value = '';
                return;
            }
            
            files.forEach((file, index) => {
                if (file.size > 5 * 1024 * 1024) {
                    alert(`File ${file.name} is too large. Maximum 5MB per file.`);
                    this.value = '';
                    fileList.innerHTML = '';
                    return;
                }
                
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <span>${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                    <span style="color: #10B981;">✓</span>
                `;
                fileList.appendChild(fileItem);
            });
        });
        
        document.getElementById('disputeForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            const formData = new FormData(this);
            
            // Convert FormData to JSON
            const data = {
                booking_id: formData.get('booking_id'),
                reason: formData.get('reason'),
                description: formData.get('description')
            };
            
            try {
                const response = await fetch('../actions/open_dispute.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert(result.message, 'success');
                    setTimeout(() => window.location.href = 'my_bookings.php', 2000);
                } else {
                    showAlert(result.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Dispute';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Failed to submit dispute. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Dispute';
            }
        });
        
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => alertBox.innerHTML = '', 5000);
        }
    </script>
</body>
</html>
