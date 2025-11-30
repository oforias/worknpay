<?php
/**
 * Upload Completion Photo Action
 * Handles photo upload when worker completes a job
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../settings/file_upload_utils.php';
require_once '../controllers/booking_controller.php';

// Check if user is logged in and is a worker
require_login();
if (!is_worker()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Workers only.'
    ]);
    exit();
}

// Validate required fields
if (!isset($_POST['booking_id']) || !isset($_FILES['completion_photo'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking ID and photo are required'
    ]);
    exit();
}

$booking_id = (int)$_POST['booking_id'];
$worker_id = get_user_id();
$completion_notes = isset($_POST['completion_notes']) ? trim($_POST['completion_notes']) : '';

// Verify booking belongs to this worker
if (!verify_worker_booking_ctr($booking_id, $worker_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking not found or access denied'
    ]);
    exit();
}

// Get booking details
$booking = get_booking_by_id_ctr($booking_id);

// Verify booking is in progress
if ($booking['booking_status'] !== 'in_progress') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking must be in progress to upload completion photo'
    ]);
    exit();
}

// Validate file upload using utility function
$file = $_FILES['completion_photo'];
$validation = validate_image_upload($file);

if (!$validation['valid']) {
    echo json_encode([
        'status' => 'error',
        'message' => $validation['error']
    ]);
    exit();
}

// Generate unique filename
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = generate_unique_filename('completion', $booking_id, $extension);

// Set upload path
$upload_dir = __DIR__ . '/../uploads/completions/';
$filepath = $upload_dir . $filename;

// Save file using utility function
$save_result = save_uploaded_file($file, $filepath);

if ($save_result['success']) {
    // Store photo URL in database (relative path)
    $photo_url = 'uploads/completions/' . $filename;
    
    // Add photo to booking
    if (add_completion_photo_ctr($booking_id, $photo_url)) {
        // Mark booking as completed
        if (complete_booking_ctr($booking_id)) {
            // Update worker stats
            require_once '../settings/db_class.php';
            $db = new db_connection();
            
            $stats_query = "UPDATE worker_profiles 
                           SET total_jobs_completed = total_jobs_completed + 1
                           WHERE user_id = $worker_id";
            $db->db_query($stats_query);
            
            // Set auto-release date for escrow (24 hours from now)
            $release_query = "UPDATE payments 
                             SET auto_release_date = DATE_ADD(NOW(), INTERVAL 24 HOUR)
                             WHERE booking_id = $booking_id AND escrow_status = 'held'";
            $db->db_query($release_query);
            
            // Get payment details for breakdown
            $payment_query = "SELECT amount, worker_commission, worker_payout 
                             FROM payments 
                             WHERE booking_id = $booking_id";
            $payment = $db->db_fetch_one($payment_query);
            
            // Calculate payment breakdown
            if ($payment) {
                $job_charge = (float)$payment['amount'];
                $platform_fee = (float)$payment['worker_commission'];
                $worker_payout = (float)$payment['worker_payout'];
            } else {
                // Fallback if no payment record
                $job_charge = (float)$booking['estimated_price'];
                $platform_fee = $job_charge * 0.05;
                $worker_payout = $job_charge - $platform_fee;
                error_log("Warning: No payment record found for booking #$booking_id");
            }
            
            $instant_fee = $worker_payout * 0.02; // 2% instant payout fee
            $instant_amount = $worker_payout - $instant_fee;
            
            error_log("Completion photo uploaded for booking #$booking_id by worker #$worker_id");
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Job completed successfully!',
                'photo_url' => $photo_url,
                'show_payout_options' => true,
                'payment_breakdown' => [
                    'job_charge' => (float)$job_charge,
                    'platform_fee' => (float)$platform_fee,
                    'platform_fee_percent' => 5,
                    'worker_payout' => (float)$worker_payout,
                    'instant_fee' => (float)$instant_fee,
                    'instant_fee_percent' => 2,
                    'instant_amount' => (float)$instant_amount
                ],
                'booking_id' => $booking_id
            ]);
        } else {
            // Delete uploaded file if completion fails
            delete_uploaded_file($filepath);
            error_log("Failed to complete booking #$booking_id - complete_booking_ctr returned false");
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to mark booking as completed. Please try again or contact support.'
            ]);
        }
    } else {
        // Delete uploaded file if database update fails
        delete_uploaded_file($filepath);
        error_log("Failed to save completion photo for booking #$booking_id - add_completion_photo_ctr returned false");
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to save photo to database. Please try again.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => $save_result['error']
    ]);
}
?>
