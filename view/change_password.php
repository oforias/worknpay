<?php
require_once '../settings/core.php';
require_login('login.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - WorkNPay</title>
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
        
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        
        .info-box h3 {
            font-size: 14px;
            color: #3B82F6;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .info-box ul {
            list-style: none;
            padding: 0;
        }
        
        .info-box li {
            font-size: 13px;
            color: var(--text-secondary);
            margin-bottom: 4px;
            padding-left: 20px;
            position: relative;
        }
        
        .info-box li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #3B82F6;
            font-weight: bold;
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
        
        .password-input-wrapper {
            position: relative;
        }
        
        input {
            width: 100%;
            padding: 14px 48px 14px 16px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 14px;
            background: var(--input-bg);
            color: var(--text-primary);
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        input:focus {
            outline: none;
            border-color: #FFD700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
            user-select: none;
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
        
        .password-strength {
            height: 4px;
            background: #E5E7EB;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
        }
        
        .password-strength-bar.weak {
            width: 33%;
            background: #EF4444;
        }
        
        .password-strength-bar.medium {
            width: 66%;
            background: #F59E0B;
        }
        
        .password-strength-bar.strong {
            width: 100%;
            background: #10B981;
        }
        
        .password-hint {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="back-btn" onclick="window.history.back()">←</span>
        <h1 class="header-title">Change Password</h1>
    </div>
    
    <div class="container">
        <div class="form-card">
            <div class="info-box">
                <h3>Password Requirements</h3>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Include uppercase and lowercase letters</li>
                    <li>Include at least one number</li>
                    <li>Use a unique password</li>
                </ul>
            </div>
            
            <div id="alertBox"></div>
            
            <form id="changePasswordForm">
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            required
                        >
                        <span class="toggle-password" onclick="togglePassword('current_password')">👁️</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            required
                            oninput="checkPasswordStrength()"
                        >
                        <span class="toggle-password" onclick="togglePassword('new_password')">👁️</span>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="password-hint" id="strengthText"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            required
                        >
                        <span class="toggle-password" onclick="togglePassword('confirm_password')">👁️</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary" id="submitBtn">
                    Change Password
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }
        
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            
            if (strength <= 1) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = '#EF4444';
            } else if (strength <= 2) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Medium strength';
                strengthText.style.color = '#F59E0B';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = '#10B981';
            }
        }
        
        document.getElementById('changePasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validate passwords match
            if (newPassword !== confirmPassword) {
                showAlert('New passwords do not match', 'error');
                return;
            }
            
            // Validate password length
            if (newPassword.length < 8) {
                showAlert('Password must be at least 8 characters long', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Changing Password...';
            
            try {
                const response = await fetch('../actions/change_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        current_password: currentPassword,
                        new_password: newPassword
                    })
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    showAlert('Password changed successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = 'profile.php';
                    }, 1500);
                } else {
                    showAlert(result.message || 'Failed to change password', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Change Password';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Change Password';
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
