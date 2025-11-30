<?php
// Landing page - redirect logged-in users to appropriate dashboard
session_start();

if (isset($_SESSION['user_id'])) {
    require_once 'settings/core.php';
    
    if (is_worker()) {
        header('Location: view/worker_dashboard_new.php');
    } elseif (is_customer()) {
        header('Location: view/home.php');
    } elseif (is_admin()) {
        header('Location: view/admin_payouts.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorkNPay - Connect with Skilled Workers in Ghana</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="apple-touch-icon" href="favicon.png">
    <link rel="stylesheet" href="css/theme-variables.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #0A0E1A;
            color: rgba(255, 255, 255, 0.95);
            overflow-x: hidden;
            transition: all 0.3s ease;
        }
        
        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(10, 14, 26, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-links {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        

        
        .nav-links a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            color: white;
        }
        
        .btn-login {
            padding: 10px 24px;
            border-radius: 8px;
            border: 1px solid rgba(255, 215, 0, 0.3);
            background: transparent;
            color: #FFD700;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-login:hover {
            background: rgba(255, 215, 0, 0.1);
            border-color: #FFD700;
        }
        
        .btn-primary {
            padding: 12px 32px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #0A0E1A;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(255, 215, 0, 0.6);
        }
        
        /* Hero Section with Epic Background */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 100px 40px 60px;
            background: 
                linear-gradient(135deg, rgba(10, 14, 26, 0.85) 0%, rgba(10, 14, 26, 0.7) 50%, rgba(10, 14, 26, 0.9) 100%),
                linear-gradient(to bottom, rgba(255, 107, 0, 0.3) 0%, rgba(10, 14, 26, 0.9) 100%),
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><linearGradient id="sky" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:%23FF6B00;stop-opacity:1"/><stop offset="50%" style="stop-color:%23FF8C42;stop-opacity:1"/><stop offset="100%" style="stop-color:%23FFB347;stop-opacity:1"/></linearGradient></defs><rect fill="url(%23sky)" width="1200" height="400"/><ellipse cx="600" cy="350" rx="80" ry="80" fill="%23FFD700" opacity="0.8"/><rect fill="%23000000" y="400" width="1200" height="400"/></svg>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255, 215, 0, 0.15) 0%, transparent 50%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.5; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
        }
        
        .hero-content {
            max-width: 1200px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .hero-badge {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 20px;
            font-size: 14px;
            color: #FFD700;
            margin-bottom: 24px;
            font-weight: 600;
        }
        
        .hero h1 {
            font-size: 72px;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #FFFFFF 0%, rgba(255, 255, 255, 0.7) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero h1 .highlight {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero p {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        /* Stats Section */
        .stats {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-top: 80px;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 48px;
            font-weight: 800;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        
        /* Features Section */
        .features {
            padding: 120px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 60px;
            background: linear-gradient(135deg, #FFFFFF 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 215, 0, 0.1);
            border-radius: 24px;
            padding: 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 0%, var(--glow-color) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .feature-card:nth-child(1) { --glow-color: rgba(16, 185, 129, 0.2); }
        .feature-card:nth-child(2) { --glow-color: rgba(59, 130, 246, 0.2); }
        .feature-card:nth-child(3) { --glow-color: rgba(255, 215, 0, 0.2); }
        .feature-card:nth-child(4) { --glow-color: rgba(168, 85, 247, 0.2); }
        .feature-card:nth-child(5) { --glow-color: rgba(239, 68, 68, 0.2); }
        .feature-card:nth-child(6) { --glow-color: rgba(236, 72, 153, 0.2); }
        
        .feature-card:hover {
            border-color: rgba(255, 215, 0, 0.3);
            transform: translateY(-12px);
            box-shadow: 0 20px 60px rgba(255, 215, 0, 0.2);
            background: rgba(255, 255, 255, 0.05);
        }
        
        .feature-card:hover::before {
            opacity: 1;
        }
        
        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .feature-card:nth-child(1) .feature-icon { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
        .feature-card:nth-child(2) .feature-icon { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); }
        .feature-card:nth-child(3) .feature-icon { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
        .feature-card:nth-child(4) .feature-icon { background: linear-gradient(135deg, #A855F7 0%, #7C3AED 100%); }
        .feature-card:nth-child(5) .feature-icon { background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); }
        .feature-card:nth-child(6) .feature-icon { background: linear-gradient(135deg, #EC4899 0%, #DB2777 100%); }
        
        .feature-card h3 {
            font-size: 24px;
            margin-bottom: 12px;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .feature-card p {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta {
            padding: 120px 40px;
            text-align: center;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.05) 0%, rgba(255, 165, 0, 0.05) 100%);
            border-top: 1px solid rgba(255, 215, 0, 0.1);
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .cta h2 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 24px;
            color: rgba(255, 255, 255, 0.95);
        }
        
        .cta p {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 40px;
        }
        
        /* Footer */
        footer {
            padding: 60px 40px 40px;
            max-width: 1200px;
            margin: 0 auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-section h4 {
            margin-bottom: 16px;
            color: #FFD700;
        }
        
        .footer-section a {
            display: block;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            margin-bottom: 8px;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .footer-section a:hover {
            color: #FFD700;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                padding: 16px 20px;
            }
            
            .nav-links {
                display: none;
            }
            
            .hero h1 {
                font-size: 40px;
            }
            
            .hero p {
                font-size: 16px;
            }
            
            .section-title {
                font-size: 32px;
            }
            
            .stats {
                gap: 40px;
            }
            
            .stat-value {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <div class="logo">WorkNPay</div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#about">About</a>
                <a href="view/login.php" class="btn-login">Login</a>
                <a href="view/register.php" class="btn-primary">Get Started</a>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">🇬🇭 Trusted by 10,000+ Ghanaians</div>
            <h1>
                Find <span class="highlight">skilled workers</span><br>
                in real-time
            </h1>
            <p>
                Connect with verified electricians, plumbers, tutors, and more. 
                Book services, make secure payments, and get work done—all in one platform.
            </p>
            <div class="hero-buttons">
                <a href="view/register.php" class="btn-primary">Find a Worker</a>
                <a href="view/register.php" class="btn-login">Become a Worker</a>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-value">10K+</div>
                    <div class="stat-label">Active Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">5K+</div>
                    <div class="stat-label">Verified Workers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">50K+</div>
                    <div class="stat-label">Jobs Completed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">4.8★</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title">Why Choose WorkNPay?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">✓</div>
                <h3>Verified Workers</h3>
                <p>All workers are ID-verified and background-checked for your safety and peace of mind.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💳</div>
                <h3>Secure Payments</h3>
                <p>Escrow system holds payment until work is completed. Pay with mobile money or card.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Instant Booking</h3>
                <p>Find and book workers in minutes. Real-time availability and instant confirmations.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⭐</div>
                <h3>Ratings & Reviews</h3>
                <p>Read honest reviews from real customers. Rate workers after service completion.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Dispute Resolution</h3>
                <p>Fair dispute handling by our team. Your satisfaction is guaranteed.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>In-App Chat</h3>
                <p>Communicate directly with workers. Share details, photos, and updates easily.</p>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta">
        <h2>Ready to get started?</h2>
        <p>Join thousands of satisfied customers and workers on WorkNPay</p>
        <div class="hero-buttons">
            <a href="view/register.php" class="btn-primary">Create Free Account</a>
            <a href="view/login.php" class="btn-login">Sign In</a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h4>WorkNPay</h4>
                <a href="#about">About Us</a>
                <a href="#careers">Careers</a>
                <a href="#press">Press</a>
                <a href="#blog">Blog</a>
            </div>
            <div class="footer-section">
                <h4>For Customers</h4>
                <a href="#how-it-works">How It Works</a>
                <a href="#services">Browse Services</a>
                <a href="#safety">Safety</a>
                <a href="#support">Support</a>
            </div>
            <div class="footer-section">
                <h4>For Workers</h4>
                <a href="view/register.php">Become a Worker</a>
                <a href="#worker-benefits">Benefits</a>
                <a href="#worker-resources">Resources</a>
                <a href="#worker-support">Worker Support</a>
            </div>
            <div class="footer-section">
                <h4>Legal</h4>
                <a href="#terms">Terms of Service</a>
                <a href="#privacy">Privacy Policy</a>
                <a href="#cookies">Cookie Policy</a>
                <a href="#guidelines">Community Guidelines</a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 WorkNPay. All rights reserved. Made with ❤️ in Ghana 🇬🇭</p>
        </div>
    </footer>
</body>
</html>
