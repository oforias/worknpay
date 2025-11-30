<?php
require_once '../settings/core.php';
require_once '../controllers/service_controller.php';

require_login('login.php');

if (!is_worker()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$worker_id = get_user_id();
$services = get_worker_services_ctr($worker_id, false); // Get all services including inactive
$categories = get_all_categories_ctr(); // Get all service categories
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            padding-bottom: 100px;
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
        .btn-danger {
            background: #DC2626;
            color: white;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .service-card {
            background: var(--bg-secondary);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }
        .service-card.inactive {
            opacity: 0.6;
        }
        .service-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
        }
        .service-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .service-category {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        .service-description {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 12px;
            line-height: 1.5;
        }
        .service-price {
            font-size: 24px;
            font-weight: 700;
            color: #FFD700;
            margin-bottom: 12px;
        }
        .service-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 12px;
            font-size: 14px;
            color: var(--text-secondary);
        }
        .service-actions {
            display: flex;
            gap: 8px;
        }
        .service-actions button {
            flex: 1;
            padding: 8px 16px;
            font-size: 14px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: var(--bg-secondary);
            padding: 32px;
            border-radius: 16px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
            color: var(--text-primary);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-tertiary);
            color: var(--text-primary);
            font-family: inherit;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .form-actions button {
            flex: 1;
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
        <h1>Manage Services</h1>
        <p>Create and manage your service offerings</p>
    </div>
    
    <div style="margin-bottom: 24px;">
        <button class="btn btn-primary" onclick="openCreateModal()">+ Add New Service</button>
    </div>
    
    <div id="alertBox"></div>
    
    <?php if (empty($services)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No services yet</h3>
            <p style="color: var(--text-secondary); margin-top: 8px;">Create your first service to start receiving bookings</p>
        </div>
    <?php else: ?>
        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <div class="service-card <?php echo $service['is_active'] ? '' : 'inactive'; ?>">
                    <div class="service-header">
                        <div>
                            <div class="service-title"><?php echo htmlspecialchars($service['service_title']); ?></div>
                            <div class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></div>
                        </div>
                        <?php if (!$service['is_active']): ?>
                            <span style="background: rgba(239, 68, 68, 0.1); color: #EF4444; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Inactive</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="service-description">
                        <?php echo nl2br(htmlspecialchars($service['service_description'])); ?>
                    </div>
                    
                    <div class="service-price">
                        GH₵<?php echo number_format($service['base_price'], 2); ?>
                    </div>
                    
                    <div class="service-stats">
                        <span>📅 <?php echo $service['booking_count']; ?> bookings</span>
                        <?php if ($service['estimated_duration']): ?>
                            <span>⏱️ <?php echo $service['estimated_duration']; ?> mins</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="service-actions">
                        <button class="btn btn-secondary" onclick='editService(<?php echo json_encode($service); ?>)'>Edit</button>
                        <?php if ($service['is_active']): ?>
                            <button class="btn btn-danger" onclick="deleteService(<?php echo $service['service_id']; ?>)">Delete</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Create/Edit Modal -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Add New Service</div>
            
            <form id="serviceForm">
                <input type="hidden" id="service_id" name="service_id">
                
                <div class="form-group">
                    <label for="service_name">Service Name *</label>
                    <input type="text" id="service_name" name="service_name" required placeholder="e.g., Phone Screen Repair">
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="service_description">Description *</label>
                    <textarea id="service_description" name="service_description" required placeholder="Describe what this service includes..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="base_price">Price (GH₵) *</label>
                    <input type="number" id="base_price" name="base_price" step="0.01" min="0.01" required placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label for="estimated_duration">Estimated Duration (minutes)</label>
                    <input type="number" id="estimated_duration" name="estimated_duration" min="1" placeholder="e.g., 60">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Service</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let isEditMode = false;
        
        function openCreateModal() {
            isEditMode = false;
            document.getElementById('modalTitle').textContent = 'Add New Service';
            document.getElementById('serviceForm').reset();
            document.getElementById('service_id').value = '';
            document.getElementById('serviceModal').classList.add('active');
        }
        
        function editService(service) {
            isEditMode = true;
            document.getElementById('modalTitle').textContent = 'Edit Service';
            document.getElementById('service_id').value = service.service_id;
            document.getElementById('service_name').value = service.service_title;
            document.getElementById('category_id').value = service.category_id;
            document.getElementById('service_description').value = service.service_description;
            document.getElementById('base_price').value = service.base_price;
            document.getElementById('estimated_duration').value = service.estimated_duration || '';
            document.getElementById('serviceModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('serviceModal').classList.remove('active');
        }
        
        document.getElementById('serviceForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            const url = isEditMode ? '../actions/update_service.php' : '../actions/create_service.php';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert(result.message, 'success');
                    closeModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert(result.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Service';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Failed to save service. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Service';
            }
        });
        
        async function deleteService(serviceId) {
            if (!confirm('Are you sure you want to delete this service? It will be hidden from customers.')) {
                return;
            }
            
            try {
                const response = await fetch('../actions/delete_service.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ service_id: serviceId })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert(result.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Failed to delete service. Please try again.', 'error');
            }
        }
        
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            setTimeout(() => alertBox.innerHTML = '', 5000);
        }
        
        // Close modal when clicking outside
        document.getElementById('serviceModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
