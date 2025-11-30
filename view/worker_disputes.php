<?php
require_once '../settings/core.php';
require_once '../controllers/dispute_controller.php';

require_login('login.php');

if (!is_worker()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$worker_id = get_user_id();

// Get worker's disputes
$disputes = get_worker_disputes_ctr($worker_id);
$open_disputes = array_filter($disputes, fn($d) => $d['dispute_status'] === 'open');
$resolved_disputes = array_filter($disputes, fn($d) => $d['dispute_status'] === 'resolved');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Disputes - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
        }
        .header {
            background: var(--header-bg);
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            color: white;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        .back-link {
            display: inline-block;
            color: #FFD700;
            text-decoration: none;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
        }
        .tab {
            padding: 12px 24px;
            border-radius: 8px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .tab.active {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            border-color: #FFD700;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .dispute-card {
            background: var(--bg-secondary);
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-color);
        }
        .dispute-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
        }
        .dispute-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        .dispute-meta {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 4px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.open {
            background: rgba(255, 193, 7, 0.1);
            color: #FFA000;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        .status-badge.resolved {
            background: rgba(16, 185, 129, 0.1);
            color: #10B981;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .dispute-details {
            background: var(--bg-tertiary);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: var(--text-secondary);
        }
        .detail-value {
            color: var(--text-primary);
        }
        .response-form {
            background: var(--bg-tertiary);
            padding: 20px;
            border-radius: 12px;
            margin-top: 16px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-family: inherit;
            min-height: 100px;
            resize: vertical;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
        }
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10B981;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #EF4444;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }
        .empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <a href="worker_dashboard_new.php" class="back-link">← Back to Dashboard</a>
    
    <div class="header">
        <h1>My Disputes</h1>
        <p>View and respond to customer disputes</p>
    </div>
    
    <div class="stats">
        <div class="stat-card">
            <div class="stat-value"><?php echo count($open_disputes); ?></div>
            <div class="stat-label">Open Disputes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo count($resolved_disputes); ?></div>
            <div class="stat-label">Resolved Disputes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo count($disputes); ?></div>
            <div class="stat-label">Total Disputes</div>
        </div>
    </div>
    
    <div class="tabs">
        <div class="tab active" onclick="switchTab('open')">Open Disputes</div>
        <div class="tab" onclick="switchTab('resolved')">Resolved</div>
    </div>
    
    <div id="alertBox"></div>
    
    <div id="open-tab" class="tab-content active">
        <?php if (empty($open_disputes)): ?>
            <div class="empty-state">
                <div class="empty-icon">✅</div>
                <h3>No open disputes</h3>
                <p style="color: var(--text-secondary); margin-top: 8px;">Great job! You have no pending disputes.</p>
            </div>
        <?php else: ?>
            <?php foreach ($open_disputes as $dispute): ?>
                <div class="dispute-card">
                    <div class="dispute-header">
                        <div>
                            <div class="dispute-title">Dispute #<?php echo $dispute['dispute_id']; ?> - <?php echo ucfirst(str_replace('_', ' ', $dispute['dispute_reason'])); ?></div>
                            <div class="dispute-meta">Booking: <?php echo $dispute['booking_reference']; ?></div>
                            <div class="dispute-meta">Opened: <?php echo date('M d, Y H:i', strtotime($dispute['created_at'])); ?></div>
                        </div>
                        <span class="status-badge open">Open</span>
                    </div>
                    
                    <div class="dispute-details">
                        <div class="detail-row">
                            <span class="detail-label">Customer:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($dispute['customer_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Amount:</span>
                            <span class="detail-value">GH₵<?php echo number_format($dispute['estimated_price'], 2); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Customer's Complaint:</span>
                            <span class="detail-value"><?php echo nl2br(htmlspecialchars($dispute['dispute_description'])); ?></span>
                        </div>
                    </div>
                    
                    <?php if (!$dispute['worker_response']): ?>
                        <div class="response-form">
                            <h3 style="margin-bottom: 16px;">Your Response</h3>
                            <form onsubmit="submitResponse(event, <?php echo $dispute['dispute_id']; ?>)">
                                <div class="form-group">
                                    <label for="response-<?php echo $dispute['dispute_id']; ?>">Explain your side of the story</label>
                                    <textarea id="response-<?php echo $dispute['dispute_id']; ?>" required placeholder="Provide your response to this dispute..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Response</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="dispute-details">
                            <div class="detail-row">
                                <span class="detail-label">Your Response:</span>
                                <span class="detail-value"><?php echo nl2br(htmlspecialchars($dispute['worker_response'])); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Responded:</span>
                                <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($dispute['worker_response_date'])); ?></span>
                            </div>
                        </div>
                        <p style="margin-top: 16px; color: var(--text-secondary); font-size: 14px;">
                            ⏳ Waiting for admin review. You will be notified of the decision.
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div id="resolved-tab" class="tab-content">
        <?php if (empty($resolved_disputes)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No resolved disputes</h3>
                <p style="color: var(--text-secondary); margin-top: 8px;">No disputes have been resolved yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($resolved_disputes as $dispute): ?>
                <div class="dispute-card">
                    <div class="dispute-header">
                        <div>
                            <div class="dispute-title">Dispute #<?php echo $dispute['dispute_id']; ?> - <?php echo ucfirst(str_replace('_', ' ', $dispute['dispute_reason'])); ?></div>
                            <div class="dispute-meta">Booking: <?php echo $dispute['booking_reference']; ?></div>
                            <div class="dispute-meta">Resolved: <?php echo date('M d, Y H:i', strtotime($dispute['resolved_at'])); ?></div>
                        </div>
                        <span class="status-badge resolved">Resolved</span>
                    </div>
                    
                    <div class="dispute-details">
                        <div class="detail-row">
                            <span class="detail-label">Customer:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($dispute['customer_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Outcome:</span>
                            <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $dispute['resolution_outcome'])); ?></span>
                        </div>
                        <?php if ($dispute['refund_amount'] > 0): ?>
                        <div class="detail-row">
                            <span class="detail-label">Refund Amount:</span>
                            <span class="detail-value">GH₵<?php echo number_format($dispute['refund_amount'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="detail-label">Admin Decision:</span>
                            <span class="detail-value"><?php echo nl2br(htmlspecialchars($dispute['resolution'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            if (tab === 'open') {
                document.querySelector('.tab:first-child').classList.add('active');
                document.getElementById('open-tab').classList.add('active');
            } else {
                document.querySelector('.tab:last-child').classList.add('active');
                document.getElementById('resolved-tab').classList.add('active');
            }
        }
        
        async function submitResponse(event, disputeId) {
            event.preventDefault();
            
            const textarea = document.getElementById(`response-${disputeId}`);
            const response = textarea.value.trim();
            
            if (!response) {
                showAlert('Please provide a response', 'error');
                return;
            }
            
            const submitBtn = event.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            try {
                const res = await fetch('../actions/respond_to_dispute.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        dispute_id: disputeId,
                        response: response
                    })
                });
                
                const result = await res.json();
                
                if (result.status === 'success') {
                    showAlert(result.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert(result.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Response';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Failed to submit response. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Response';
            }
        }
        
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => alertBox.innerHTML = '', 5000);
        }
    </script>
</body>
</html>
