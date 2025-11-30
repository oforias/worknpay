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
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="apple-touch-icon" href="../favicon.png">
    <title>Login - WorkNPay</title>
    <link rel="stylesheet" href="../css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
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
            max-width: 450px;
            width: 100%;
            padding: 48px;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
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
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6EE7B7;
        }
        
        .form-group {
            margin-bottom: 24px;
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
        
        input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 14px;
            color: #111827;
            background: #FFFFFF;
            transition: all 0.3s ease;
        }
        
        body.dark-mode input {
            border-color: rgba(255, 215, 0, 0.2);
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }
        
        input::placeholder {
            color: #6B7280;
            opacity: 0.8;
        }
        
        body.dark-mode input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        input:focus {
            outline: none;
            border-color: rgba(255, 215, 0, 0.5);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.1);
        }
        
        .forgot-password {
            text-align: right;
            margin-top: 8px;
        }
        
        .forgot-password a {
            color: #FFD700;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: #FFA500;
        }
        
        .btn-login {
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
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(255, 215, 0, 0.6);
        }
        
        .btn-login:active {
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
        
        .register-link {
            text-align: center;
            color: #6B7280;
            font-size: 14px;
        }
        
        body.dark-mode .register-link {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .register-link a {
            color: #FFD700;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>WorkNPay</h1>
            <p>Welcome back! Please login to your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php
                switch ($error) {
                    case 'invalid_credentials':
                        echo '❌ Invalid email or password';
                        break;
                    case 'access_denied':
                        echo '🚫 Access denied. Please login.';
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
        
        <?php if ($message === 'logged_out'): ?>
            <div class="alert alert-success">
                ✅ You have been logged out successfully
            </div>
        <?php elseif ($message === 'registration_success'): ?>
            <div class="alert alert-success">
                ✅ Registration successful! Please login to continue
            </div>
        <?php endif; ?>
        
        <form action="../actions/login_action.php" method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon">📧</span>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email"
                        required
                        autocomplete="email"
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
                        name="password" 
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>
                <div class="forgot-password">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
        </form>
        
        <div class="divider">
            <span>or</span>
        </div>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Sign Up</a>
        </div>
        
        <div class="back-home">
            <a href="../index.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>
