<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

if (!is_worker()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$worker_id = get_user_id();
$db = new db_connection();

// Get worker profile and user info
$query = "SELECT u.*, wp.* 
          FROM users u
          LEFT JOIN worker_profiles wp ON u.user_id = wp.user_id
          WHERE u.user_id = $worker_id";
$worker = $db->db_fetch_one($query);

if (!$worker) {
    header('Location: worker_onboarding.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - WorkNPay</title>
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
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        .header {
            background: linear-gradient(135deg, rgba(10, 14, 26, 0.95) 0%, rgba(20, 24, 36, 0.95) 100%);
            backdrop-filter: blur(20px);
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 215, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .back-btn {
            padding: 10px 20px;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 8px;
            color: #FFD700;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: rgba(255, 215, 0, 0.2);
            transform: translateY(-2px);
        }
        
        .settings-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .settings-card h2 {
            font-size: 20px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
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
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group .hint {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 6px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 215, 0, 0.5);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10B981;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #EF4444;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ Settings</h1>
            <a href="worker_dashboard_new.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <div class="settings-card">
            <h2>Personal Information</h2>
            <div id="personalMessage" class="success-message"></div>
            <div id="personalError" class="error-message"></div>
            
            <form id="personalInfoForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="userName">Full Name</label>
                        <input type="text" id="userName" value="<?php echo htmlspecialchars($worker['user_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="userPhone">Phone Number</label>
                        <input type="tel" id="userPhone" value="<?php echo htmlspecialchars($worker['user_phone']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="userEmail">Email Address</label>
                    <input type="email" id="userEmail" value="<?php echo htmlspecialchars($worker['user_email']); ?>" required>
                </div>
                
                <button type="submit" class="btn-primary">Save Personal Information</button>
            </form>
        </div>
        
        <div class="settings-card">
            <h2>Professional Profile</h2>
            <div id="profileMessage" class="success-message"></div>
            <div id="profileError" class="error-message"></div>
            
            <form id="professionalProfileForm">
                <div class="form-group">
                    <label for="skills">Skills & Services</label>
                    <textarea id="skills" placeholder="e.g., Plumbing, Electrical work, Phone repair..."><?php echo htmlspecialchars($worker['skills'] ?? ''); ?></textarea>
                    <div class="hint">List your skills and services separated by commas</div>
                </div>
                
                <div class="form-group">
                    <label for="bio">Professional Bio</label>
                    <textarea id="bio" placeholder="Tell customers about your experience and expertise..."><?php echo htmlspecialchars($worker['bio'] ?? ''); ?></textarea>
                    <div class="hint">This will be shown on your profile</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="yearsExperience">Years of Experience</label>
                        <input type="number" id="yearsExperience" min="0" value="<?php echo htmlspecialchars($worker['experience_years'] ?? '0'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="hourlyRate">Hourly Rate (GH₵)</label>
                        <input type="number" id="hourlyRate" min="0" step="10" value="<?php echo htmlspecialchars($worker['hourly_rate'] ?? '0'); ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">Save Professional Profile</button>
            </form>
        </div>
        
        <div class="settings-card">
            <h2>Change Password</h2>
            <div id="passwordMessage" class="success-message"></div>
            <div id="passwordError" class="error-message"></div>
            
            <form id="changePasswordForm">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" required>
                </div>
                
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" required minlength="6">
                    <div class="hint">Minimum 6 characters</div>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" required>
                </div>
                
                <button type="submit" class="btn-primary">Change Password</button>
            </form>
        </div>
    </div>
    
    <script>
        // Personal Information Form
        document.getElementById('personalInfoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('.btn-primary');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            const data = {
                user_name: document.getElementById('userName').value,
                user_phone: document.getElementById('userPhone').value,
                user_email: document.getElementById('userEmail').value
            };
            
            try {
                const response = await fetch('../actions/update_personal_info.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showMessage('personalMessage', result.message);
                } else {
                    showError('personalError', result.message);
                }
            } catch (error) {
                showError('personalError', 'Failed to update information');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Personal Information';
            }
        });
        
        // Professional Profile Form
        document.getElementById('professionalProfileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('.btn-primary');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            const data = {
                skills: document.getElementById('skills').value,
                bio: document.getElementById('bio').value,
                years_experience: document.getElementById('yearsExperience').value,
                hourly_rate: document.getElementById('hourlyRate').value
            };
            
            try {
                const response = await fetch('../actions/update_worker_profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showMessage('profileMessage', result.message);
                } else {
                    showError('profileError', result.message);
                }
            } catch (error) {
                showError('profileError', 'Failed to update profile');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Professional Profile';
            }
        });
        
        // Change Password Form
        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                showError('passwordError', 'Passwords do not match');
                return;
            }
            
            const submitBtn = this.querySelector('.btn-primary');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Changing...';
            
            const data = {
                current_password: document.getElementById('currentPassword').value,
                new_password: newPassword
            };
            
            try {
                const response = await fetch('../actions/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showMessage('passwordMessage', result.message);
                    this.reset();
                } else {
                    showError('passwordError', result.message);
                }
            } catch (error) {
                showError('passwordError', 'Failed to change password');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Change Password';
            }
        });
        
        function showMessage(elementId, message) {
            const element = document.getElementById(elementId);
            element.textContent = message;
            element.style.display = 'block';
            setTimeout(() => {
                element.style.display = 'none';
            }, 5000);
        }
        
        function showError(elementId, message) {
            const element = document.getElementById(elementId);
            element.textContent = message;
            element.style.display = 'block';
            setTimeout(() => {
                element.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>
