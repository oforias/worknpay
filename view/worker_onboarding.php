<?php
require_once '../settings/core.php';
require_once '../settings/db_class.php';
require_login('login.php');

// Only workers can access this page
if (!is_worker()) {
    header('Location: login.php?error=access_denied');
    exit();
}

$worker_id = get_user_id();
$worker_name = get_user_name();

// Check if worker profile already exists
$db = new db_connection();
$profile_check = "SELECT user_id FROM worker_profiles WHERE user_id = $worker_id";
$existing_profile = $db->db_fetch_one($profile_check);

// If profile exists, redirect to dashboard
if ($existing_profile) {
    header('Location: worker_dashboard_new.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Worker Profile - WorkNPay</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 50%, #1a1f36 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 30%, rgba(255, 215, 0, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 165, 0, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 215, 0, 0.2);
            position: relative;
            z-index: 1;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .header-title {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        .header-subtitle {
            font-size: 16px;
            color: #666;
            font-weight: 500;
        }
        
        .progress-bar {
            height: 6px;
            background: rgba(255, 215, 0, 0.2);
            border-radius: 10px;
            margin-bottom: 40px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            font-size: 15px;
            font-weight: 700;
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        
        label .required {
            color: #EF4444;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid rgba(255, 215, 0, 0.2);
            border-radius: 12px;
            font-size: 14px;
            color: #1a1f36;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            font-family: inherit;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: rgba(255, 215, 0, 0.5);
            box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.1);
            background: white;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .hint {
            font-size: 13px;
            color: #666;
            margin-top: 6px;
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1a1f36 0%, #2d3561 100%);
            color: white;
            box-shadow: 0 6px 24px rgba(26, 31, 54, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.2), transparent);
            transition: left 0.6s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(26, 31, 54, 0.4);
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            color: #991B1B;
            border: 2px solid #FCA5A5;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            color: #065F46;
            border: 2px solid #6EE7B7;
        }
        
        .skills-input-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .skill-tag {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.15) 0%, rgba(255, 165, 0, 0.15) 100%);
            color: #1a1f36;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid rgba(255, 215, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .skill-tag .remove {
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-icon">👨‍🔧</div>
            <h1 class="header-title">Complete Your Worker Profile</h1>
            <p class="header-subtitle">Let customers know about your skills and experience</p>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
        </div>
        
        <div id="alertBox"></div>
        
        <form id="workerProfileForm">
            <div class="form-group">
                <label for="skills">Skills <span class="required">*</span></label>
                <input type="text" id="skills" placeholder="e.g., Electrical Wiring, Plumbing, Tutoring">
                <div class="hint">Enter your main skills (comma-separated)</div>
            </div>
            
            <div class="form-group">
                <label for="bio">About You <span class="required">*</span></label>
                <textarea id="bio" placeholder="Tell customers about your experience and what makes you great at what you do..." required></textarea>
                <div class="hint">Minimum 50 characters</div>
            </div>
            
            <div class="form-group">
                <label for="experience_years">Years of Experience <span class="required">*</span></label>
                <select id="experience_years" required>
                    <option value="">Select experience</option>
                    <option value="1">Less than 1 year</option>
                    <option value="2">1-2 years</option>
                    <option value="3">3-5 years</option>
                    <option value="5">5-10 years</option>
                    <option value="10">10+ years</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="hourly_rate">Hourly Rate (GH₵) <span class="required">*</span></label>
                <input type="number" id="hourly_rate" min="10" step="5" placeholder="e.g., 50" required>
                <div class="hint">Set your hourly rate. You can adjust this later.</div>
            </div>
            
            <button type="submit" class="btn btn-primary" id="submitBtn">
                Complete Profile & Start Working
            </button>
        </form>
    </div>
    
    <script>
        const form = document.getElementById('workerProfileForm');
        const submitBtn = document.getElementById('submitBtn');
        const progressFill = document.getElementById('progressFill');
        
        // Update progress as user fills form
        const requiredFields = ['skills', 'bio', 'experience_years', 'hourly_rate'];
        
        requiredFields.forEach(fieldId => {
            document.getElementById(fieldId).addEventListener('input', updateProgress);
        });
        
        function updateProgress() {
            let filledFields = 0;
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field.value.trim()) filledFields++;
            });
            
            const progress = (filledFields / requiredFields.length) * 100;
            progressFill.style.width = progress + '%';
        }
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const bio = document.getElementById('bio').value.trim();
            
            // Validate bio length
            if (bio.length < 50) {
                showAlert('Please write at least 50 characters about yourself', 'error');
                return;
            }
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating your profile...';
            
            const formData = {
                skills: document.getElementById('skills').value.trim(),
                bio: bio,
                experience_years: document.getElementById('experience_years').value,
                hourly_rate: document.getElementById('hourly_rate').value
            };
            
            try {
                const response = await fetch('../actions/create_worker_profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    showAlert('Profile created successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'worker_dashboard_new.php';
                    }, 1500);
                } else {
                    showAlert(data.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Complete Profile & Start Working';
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Failed to create profile. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Complete Profile & Start Working';
            }
        });
        
        function showAlert(message, type) {
            const alertBox = document.getElementById('alertBox');
            alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            
            setTimeout(() => {
                alertBox.innerHTML = '';
            }, 5000);
        }
    </script>
</body>
</html>
