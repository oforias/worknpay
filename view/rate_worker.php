<?php
require_once '../settings/core.php';
require_once '../controllers/booking_controller.php';
require_login('login.php');

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$customer_id = get_user_id();

if ($booking_id <= 0) {
    header('Location: my_bookings.php');
    exit();
}

// Get booking details
$booking = get_booking_by_id_ctr($booking_id);

if (!$booking || $booking['customer_id'] != $customer_id) {
    header('Location: my_bookings.php?error=invalid_booking');
    exit();
}

// Check if booking is completed
if ($booking['booking_status'] !== 'completed') {
    header('Location: my_bookings.php?error=not_completed');
    exit();
}

// Check if already rated
require_once '../settings/db_class.php';
$db = new db_connection();
$check_query = "SELECT review_id FROM reviews WHERE booking_id = $booking_id LIMIT 1";
$existing_review = $db->db_fetch_one($check_query);

if ($existing_review) {
    header('Location: my_bookings.php?message=already_rated');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Worker - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: var(--bg-secondary);
            padding: 40px;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .header h1 {
            font-size: 28px;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .header p {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .worker-info {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 32px;
            border: 1px solid var(--border-color);
        }
        
        .worker-info h3 {
            font-size: 18px;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .worker-info p {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        
        .star-rating {
            display: flex;
            gap: 8px;
            font-size: 40px;
            cursor: pointer;
            justify-content: center;
            margin-bottom: 8px;
        }
        
        .star {
            color: #E5E7EB;
            transition: all 0.2s ease;
        }
        
        .star.active,
        .star:hover {
            color: #FFD700;
            transform: scale(1.1);
        }
        
        .star:hover ~ .star {
            color: #E5E7EB;
        }
        
        .rating-text {
            text-align: center;
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 16px;
        }
        
        textarea {
            width: 100%;
            padding: 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            color: var(--text-primary);
            background: var(--input-bg);
            resize: vertical;
            min-height: 120px;
            transition: all 0.3s ease;
        }
        
        textarea:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        textarea::placeholder {
            color: var(--text-secondary);
            opacity: 0.6;
        }
        
        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 32px;
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
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            background: var(--bg-primary);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #DC2626;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10B981;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 24px;
            }
            
            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rate Your Experience</h1>
            <p>Help others by sharing your feedback</p>
        </div>
        
        <div class="worker-info">
            <h3><?php echo htmlspecialchars($booking['worker_name']); ?></h3>
            <p>Booking: <?php echo htmlspecialchars($booking['booking_reference']); ?></p>
        </div>
        
        <div id="alertBox"></div>
        
        <form id="ratingForm">
            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
            <input type="hidden" name="worker_id" value="<?php echo $booking['worker_id']; ?>">
            <input type="hidden" name="rating" id="ratingValue" value="0">
            
            <div class="form-group">
                <label>Your Rating</label>
                <div class="star-rating" id="starRating">
                    <span class="star" data-rating="1">★</span>
                    <span class="star" data-rating="2">★</span>
                    <span class="star" data-rating="3">★</span>
                    <span class="star" data-rating="4">★</span>
                    <span class="star" data-rating="5">★</span>
                </div>
                <div class="rating-text" id="ratingText">Click to rate</div>
            </div>
            
            <div class="form-group">
                <label for="review">Your Review (Optional)</label>
                <textarea 
                    id="review" 
                    name="review" 
                    placeholder="Share your experience with this worker..."
                ></textarea>
            </div>
            
            <div class="buttons">
                <button type="submit" class="btn btn-primary" id="submitBtn">Submit Rating</button>
                <a href="my_bookings.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <script>
        let selectedRating = 0;
        const stars = document.querySelectorAll('.star');
        const ratingText = document.getElementById('ratingText');
        const ratingValue = document.getElementById('ratingValue');
        
        const ratingLabels = {
            1: 'Poor',
            2: 'Fair',
            3: 'Good',
            4: 'Very Good',
            5: 'Excellent'
        };
        
        // Star rating interaction
        stars.forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = parseInt(this.dataset.rating);
                ratingValue.value = selectedRating;
                updateStars(selectedRating);
                ratingText.textContent = ratingLabels[selectedRating];
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                updateStars(rating);
            });
        });
        
        document.getElementById('starRating').addEventListener('mouseleave', function() {
            updateStars(selectedRating);
            if (selectedRating > 0) {
                ratingText.textContent = ratingLabels[selectedRating];
            } else {
                ratingText.textContent = 'Click to rate';
            }
        });
        
        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
        }
        
        // Form submission
        document.getElementById('ratingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (selectedRating === 0) {
                showAlert('Please select a rating', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            const formData = {
                booking_id: document.querySelector('[name="booking_id"]').value,
                worker_id: document.querySelector('[name="worker_id"]').value,
                rating: selectedRating,
                review: document.getElementById('review').value
            };
            
            try {
                console.log('Submitting review with data:', formData);
                
                const response = await fetch('../actions/submit_review.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response was:', responseText);
                    showAlert('Server returned invalid response. Check console for details.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Rating';
                    return;
                }
                
                console.log('Parsed result:', result);
                
                if (result.status === 'success') {
                    showAlert('Thank you for your feedback!', 'success');
                    setTimeout(() => {
                        window.location.href = 'my_bookings.php?message=review_submitted';
                    }, 2000);
                } else {
                    showAlert(result.message || 'Failed to submit review', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Rating';
                }
            } catch (error) {
                console.error('Fetch error:', error);
                showAlert('An error occurred. Please try again. Check console for details.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Rating';
            }
        });
        
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => alertBox.innerHTML = '', 5000);
        }
    </script>
    
    <script src="../js/theme-toggle.js"></script>
</body>
</html>
