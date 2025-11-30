<?php
/**
 * Dispute Controller
 * Business logic for dispute operations
 */

require_once(__DIR__ . '/../classes/dispute_class.php');

/**
 * Open a new dispute
 */
function open_dispute_ctr($booking_id, $customer_id, $worker_id, $reason, $description, $evidence_photos = null)
{
    $dispute = new Dispute();
    return $dispute->create_dispute($booking_id, $customer_id, $worker_id, $reason, $description, $evidence_photos);
}

/**
 * Get dispute details
 */
function get_dispute_details_ctr($dispute_id)
{
    $dispute = new Dispute();
    return $dispute->get_dispute_by_id($dispute_id);
}

/**
 * Validate if booking is eligible for dispute
 */
function validate_dispute_eligibility_ctr($booking_id)
{
    $dispute = new Dispute();
    
    // Check if within 48-hour window
    if (!$dispute->validate_dispute_window($booking_id)) {
        return ['eligible' => false, 'reason' => 'Dispute window expired (48 hours from completion)'];
    }
    
    // Check if already has open dispute
    if ($dispute->has_open_dispute($booking_id)) {
        return ['eligible' => false, 'reason' => 'Booking already has an open dispute'];
    }
    
    return ['eligible' => true];
}

/**
 * Get all disputes (for admin)
 */
function get_all_disputes_ctr($status = null)
{
    $dispute = new Dispute();
    return $dispute->get_all_disputes($status);
}

/**
 * Add worker response to dispute
 */
function add_worker_response_ctr($dispute_id, $response)
{
    $dispute = new Dispute();
    return $dispute->add_worker_response($dispute_id, $response);
}

/**
 * Resolve dispute (admin only)
 */
function resolve_dispute_ctr($dispute_id, $outcome, $resolution_notes, $refund_amount, $resolved_by)
{
    $dispute = new Dispute();
    return $dispute->resolve_dispute($dispute_id, $resolution_notes, $outcome, $refund_amount, $resolved_by);
}

/**
 * Get worker's disputes
 */
function get_worker_disputes_ctr($worker_id)
{
    $dispute = new Dispute();
    return $dispute->get_worker_disputes($worker_id);
}

/**
 * Delete/dismiss a dispute (admin only)
 */
function delete_dispute_ctr($dispute_id)
{
    $dispute = new Dispute();
    return $dispute->delete_dispute($dispute_id);
}
?>


/**
 * Delete/Cancel a dispute
 */
function delete_dispute_ctr($dispute_id, $user_id)
{
    $dispute = new Dispute();
    
    // Get dispute details to verify ownership
    $dispute_details = $dispute->get_dispute_by_id($dispute_id);
    if (!$dispute_details) {
        return ['success' => false, 'message' => 'Dispute not found'];
    }
    
    // Only customer who opened the dispute or admin can delete it
    if ($dispute_details['customer_id'] != $user_id && !is_admin()) {
        return ['success' => false, 'message' => 'Unauthorized'];
    }
    
    if ($dispute->delete_dispute($dispute_id)) {
        return ['success' => true, 'message' => 'Dispute cancelled successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to cancel dispute or dispute is already resolved'];
}
