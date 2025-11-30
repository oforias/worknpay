<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    require_once '../settings/core.php';
    
    if (is_worker()) {
        header('Location: worker_dashboard_new.php');
    } elseif (is_customer()) {
        header('Location: home.php');
    } elseif (is_admin()) {
        header('Location: admin_payouts.php');
    }
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
        }
        
        body.dark-mode {
            background: #0A0E1A;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255, 215, 0, 0.1) 0%, transparent 50%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }
        
        .container {
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            max-width: 500px;
            width: 100%;
            padding: 48px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
            max-height: 90vh;
            overflow-y: auto;
        }
        

        
        .logo {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo h1 {
            font-size: 36px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            font-weight: 900;
        }
        
        .logo p {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        body.dark-mode .logo p {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }
        
        body.dark-mode label {
            color: rgba(255, 255, 255, 0.95);
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 215, 0, 0.6);
            font-size: 18px;
        }
        
        input, select {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 14px;
            color: #111827;
            background: #FFFFFF;
            transition: all 0.3s ease;
        }
        
        body.dark-mode input,
        body.dark-mode select {
            border-color: rgba(255, 215, 0, 0.2);
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }
        
        select {
            cursor: pointer;
        }
        
        select option {
            background: #1a1f36;
            color: white;
        }
        
        input::placeholder {
            color: #6B7280;
            opacity: 0.8;
        }
        
        body.dark-mode input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: rgba(255, 215, 0, 0.5);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .role-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .role-option {
            padding: 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.03);
        }
        
        .role-option:hover {
            border-color: rgba(255, 215, 0, 0.3);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .role-option.selected {
            border-color: #FFD700;
            background: rgba(255, 215, 0, 0.1);
        }
        
        .role-option .icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .role-option .title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        
        .role-option .desc {
            font-size: 12px;
            color: #6B7280;
        }
        
        body.dark-mode .role-option .title {
            color: white;
        }
        
        body.dark-mode .role-option .desc {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .btn-register {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.4);
            margin-top: 8px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(255, 215, 0, 0.6);
        }
        
        .btn-register:active {
            transform: translateY(0);
        }
        
        .divider {
            text-align: center;
            margin: 32px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .divider span {
            background: var(--bg-secondary);
            padding: 0 16px;
            color: #6B7280;
            font-size: 13px;
            position: relative;
            z-index: 1;
        }
        
        body.dark-mode .divider span {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .login-link {
            text-align: center;
            color: #6B7280;
            font-size: 14px;
        }
        
        body.dark-mode .login-link {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .login-link a {
            color: #FFD700;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #FFA500;
        }
        
        .back-home {
            text-align: center;
            margin-top: 24px;
        }
        
        .back-home a {
            color: #6B7280;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.3s ease;
        }
        
        .back-home a:hover {
            color: #FFD700;
        }
        
        body.dark-mode .back-home a {
            color: rgba(255, 255, 255, 0.5);
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 32px 24px;
            }
            
            .logo h1 {
                font-size: 28px;
            }
            
            .role-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>WorkNPay</h1>
            <p>Create your account and get started</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert">
                <?php
                switch ($error) {
                    case 'email_exists':
                        echo '❌ Email already registered';
                        break;
                    case 'password_mismatch':
                        echo '❌ Passwords do not match';
                        break;
                    case 'empty_fields':
                        echo '⚠️ Please fill in all fields';
                        break;
                    default:
                        echo '❌ An error occurred. Please try again.';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <form action="../actions/register_action.php" method="POST">
            <div class="form-group">
                <label>I want to:</label>
                <div class="role-selector">
                    <div class="role-option selected" onclick="selectRole(1, this)">
                        <div class="icon">👤</div>
                        <div class="title">Find Workers</div>
                        <div class="desc">Book services</div>
                    </div>
                    <div class="role-option" onclick="selectRole(2, this)">
                        <div class="icon">👷</div>
                        <div class="title">Offer Services</div>
                        <div class="desc">Become a worker</div>
                    </div>
                </div>
                <input type="hidden" name="user_role" id="user_role" value="1">
            </div>
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input 
                        type="text" 
                        id="name" 
                        name="customer_name" 
                        placeholder="Enter your full name"
                        required
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">📧</span>
                    <input 
                        type="email" 
                        id="email" 
                        name="customer_email" 
                        placeholder="Enter your email"
                        required
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <div class="input-wrapper">
                    <span class="input-icon">📱</span>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="customer_contact" 
                        placeholder="0244123456"
                        required
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input 
                        type="password" 
                        id="password" 
                        name="customer_pass" 
                        placeholder="Create a password"
                        required
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm your password"
                        required
                    >
                </div>
            </div>
            
            <button type="submit" class="btn-register">Create Account</button>
        </form>
        
        <div class="divider">
            <span>or</span>
        </div>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login</a>
        </div>
        
        <div class="back-home">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>
    
    <script>
        function selectRole(role, element) {
            // Remove selected class from all options
            document.querySelectorAll('.role-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Update hidden input
            document.getElementById('user_role').value = role;
        }
    </script>
</body>
</html>
