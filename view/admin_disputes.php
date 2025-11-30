<?php
require_once '../settings/core.php';
require_once '../controllers/dispute_controller.php';

require_login('login.php');

if (!is_admin()) {
    header('Location: login.php?error=access_denied');
    exit();
}

// Get all disputes
$disputes = get_all_disputes_ctr();
$open_disputes = array_filter($disputes, fn($d) => $d['dispute_status'] === 'open');
$resolved_disputes = array_filter($disputes, fn($d) => $d['dispute_status'] === 'resolved');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispute Management - Admin</title>
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
        .resolution-form {
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
        select, textarea, input {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-family: inherit;
        }
        textarea {
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
        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
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
    </style>
</head>
<body>
    <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
    
    <div class="header">
        <h1>Dispute Management</h1>
        <p>Review and resolve customer disputes</p>
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
    
    <div id="open-tab" class="tab-content active">
        <?php if (empty($open_disputes)): ?>
            <div class="dispute-card">
                <p style="text-align: center; color: var(--text-secondary);">No open disputes</p>
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
                            <span class="detail-label">Worker:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($dispute['worker_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Amount:</span>
                            <span class="detail-value">GH₵<?php echo number_format($dispute['payment_amount'], 2); ?></span>
                        </div>
                    </div>
                    
                    <!-- Customer's Side -->
                    <div style="background: rgba(239, 68, 68, 0.05); padding: 16px; border-radius: 12px; margin-bottom: 12px; border-left: 4px solid #EF4444;">
                        <h4 style="color: #EF4444; margin-bottom: 8px; font-size: 14px;">👤 Customer's Complaint</h4>
                        <p style="color: var(--text-primary); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($dispute['dispute_description'])); ?></p>
                    </div>
                    
                    <!-- Worker's Side -->
                    <?php if ($dispute['worker_response']): ?>
                    <div style="background: rgba(59, 130, 246, 0.05); padding: 16px; border-radius: 12px; margin-bottom: 16px; border-left: 4px solid #3B82F6;">
                        <h4 style="color: #3B82F6; margin-bottom: 8px; font-size: 14px;">🔧 Worker's Response</h4>
                        <p style="color: var(--text-primary); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($dispute['worker_response'])); ?></p>
                        <p style="color: var(--text-secondary); font-size: 12px; margin-top: 8px;">Responded: <?php echo date('M d, Y H:i', strtotime($dispute['worker_response_date'])); ?></p>
                    </div>
                    <?php else: ?>
                    <div style="background: rgba(255, 193, 7, 0.05); padding: 16px; border-radius: 12px; margin-bottom: 16px; border-left: 4px solid #FFA000;">
                        <h4 style="color: #FFA000; margin-bottom: 8px; font-size: 14px;">⏳ Waiting for Worker Response</h4>
                        <p style="color: var(--text-secondary); font-size: 14px;">The worker has not yet responded to this dispute.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="resolution-form">
                        <h3 style="margin-bottom: 16px;">Resolve Dispute</h3>
                        <form onsubmit="resolveDispute(event, <?php echo $dispute['dispute_id']; ?>, <?php echo $dispute['payment_amount']; ?>)">
                            <div class="form-group">
                                <label>Resolution Outcome</label>
                                <select id="outcome-<?php echo $dispute['dispute_id']; ?>" required onchange="toggleRefundAmount(<?php echo $dispute['dispute_id']; ?>)">
                                    <option value="">Select outcome...</option>
                                    <option value="refund_customer">Full Refund to Customer</option>
                                    <option value="pay_worker">Pay Worker (No Refund)</option>
                                    <option value="partial_refund">Partial Refund</option>
                                    <option value="no_action">No Action (Release to Worker)</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="refund-amount-<?php echo $dispute['dispute_id']; ?>" style="display: none;">
                                <label>Refund Amount (GH₵)</label>
                                <input type="number" step="0.01" min="0" max="<?php echo $dispute['payment_amount']; ?>" id="refund-<?php echo $dispute['dispute_id']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Resolution Notes</label>
                                <textarea id="notes-<?php echo $dispute['dispute_id']; ?>" required placeholder="Explain your decision..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Resolve Dispute</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div id="resolved-tab" class="tab-content">
        <?php if (empty($resolved_disputes)): ?>
            <div class="dispute-card">
                <p style="text-align: center; color: var(--text-secondary);">No resolved disputes</p>
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
                            <span class="detail-label">Resolution:</span>
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
        
        function toggleRefundAmount(disputeId) {
            const outcome = document.getElementById(`outcome-${disputeId}`).value;
            const refundDiv = document.getElementById(`refund-amount-${disputeId}`);
            const refundInput = document.getElementById(`refund-${disputeId}`);
            
            if (outcome === 'partial_refund') {
                refundDiv.style.display = 'block';
                refundInput.required = true;
            } else {
                refundDiv.style.display = 'none';
                refundInput.required = false;
            }
        }
        
        async function resolveDispute(event, disputeId, paymentAmount) {
            event.preventDefault();
            
            const outcome = document.getElementById(`outcome-${disputeId}`).value;
            const notes = document.getElementById(`notes-${disputeId}`).value;
            const refundAmount = document.getElementById(`refund-${disputeId}`)?.value;
            
            if (!confirm(`Are you sure you want to resolve this dispute with outcome: ${outcome.replace('_', ' ')}?`)) {
                return;
            }
            
            const data = {
                dispute_id: disputeId,
                outcome: outcome,
                resolution_notes: notes
            };
            
            if (outcome === 'partial_refund' && refundAmount) {
                data.refund_amount = parseFloat(refundAmount);
            }
            
            try {
                const response = await fetch('../actions/resolve_dispute.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to resolve dispute. Please try again.');
            }
        }
    </script>
</body>
</html>
