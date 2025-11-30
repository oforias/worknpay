<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

$user_id = get_user_id();
$db = new db_connection();

// Get user details
$user_query = "SELECT * FROM users WHERE user_id = $user_id";
$user = $db->db_fetch_one($user_query);

$success_message = '';
$error_message = '';

if (isset($_GET['success'])) {
    $success_message = 'Profile updated successfully!';
}
if (isset($_GET['error'])) {
    $error_message = 'Failed to update profile. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        .header {
            background: var(--header-bg);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .back-btn {
            font-size: 28px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            transform: translateX(-4px);
        }
        
        .header-title {
            font-size: 20px;
            font-weight: 700;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 24px 20px;
        }
        
        .form-card {
            background: var(--bg-secondary);
            padding: 32px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        input, select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            background: var(--input-bg);
            color: var(--text-primary);
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        input::placeholder {
            color: var(--text-secondary);
            opacity: 0.6;
        }
        
        .btn-primary {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.4);
            margin-top: 8px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(255, 215, 0, 0.6);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10B981;
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #DC2626;
        }
        
        .input-hint {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="back-btn" onclick="window.history.back()">←</span>
        <h1 class="header-title">Edit Profile</h1>
    </div>
    
    <div class="container">
        <div class="form-card">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div id="alertBox"></div>
            
            <form id="editProfileForm">
                <div class="form-group">
                    <label for="user_name">Full Name *</label>
                    <input 
                        type="text" 
                        id="user_name" 
                        name="user_name" 
                        value="<?php echo htmlspecialchars($user['user_name']); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="user_email">Email Address *</label>
                    <input 
                        type="email" 
                        id="user_email" 
                        name="user_email" 
                        value="<?php echo htmlspecialchars($user['user_email']); ?>"
                        required
                    >
                    <div class="input-hint">Used for login and notifications</div>
                </div>
                
                <div class="form-group">
                    <label for="user_phone">Phone Number</label>
                    <input 
                        type="tel" 
                        id="user_phone" 
                        name="user_phone" 
                        value="<?php echo htmlspecialchars($user['user_phone'] ?? ''); ?>"
                        placeholder="0XX XXX XXXX"
                    >
                    <div class="input-hint">10-digit phone number</div>
                </div>
                
                <div class="form-group">
                    <label for="user_city">City/Location</label>
                    <input 
                        type="text" 
                        id="user_city" 
                        name="user_city" 
                        value="<?php echo htmlspecialchars($user['user_city'] ?? ''); ?>"
                        placeholder="e.g., Accra, Kumasi"
                    >
                </div>
                
                <button type="submit" class="btn-primary" id="submitBtn">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            const formData = {
                user_name: document.getElementById('user_name').value,
                user_email: document.getElementById('user_email').value,
                user_phone: document.getElementById('user_phone').value,
                user_city: document.getElementById('user_city').value
            };
            
            try {
                const response = await fetch('../actions/update_personal_info.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert('Profile updated successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = 'profile.php';
                    }, 1500);
                } else {
                    showAlert(result.message || 'Failed to update profile', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Changes';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Changes';
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
