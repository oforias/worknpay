<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to WorkNPay</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0052CC 0%, #2684FF 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .onboarding-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        
        .slides-container {
            flex: 1;
            position: relative;
            overflow: hidden;
        }
        
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 30px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.5s ease;
        }
        
        .slide.active {
            opacity: 1;
            transform: translateX(0);
        }
        
        .slide.prev {
            transform: translateX(-100%);
        }
        
        .slide-icon {
            font-size: 120px;
            margin-bottom: 40px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .slide-title {
            font-size: 28px;
            font-weight: 700;
            color: white;
            text-align: center;
            margin-bottom: 16px;
        }
        
        .slide-description {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
            line-height: 1.6;
            max-width: 320px;
        }
        
        .dots-container {
            display: flex;
            gap: 8px;
            justify-content: center;
            padding: 20px;
        }
        
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .dot.active {
            width: 24px;
            border-radius: 4px;
            background: white;
        }
        
        .buttons-container {
            padding: 20px 30px 40px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .btn {
            padding: 16px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary {
            background: white;
            color: #0052CC;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .skip-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            z-index: 10;
        }
        
        .skip-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>
<body>
    <div class="onboarding-container">
        <div class="skip-btn" onclick="skipOnboarding()">Skip</div>
        
        <div class="slides-container">
            <!-- Slide 1: Welcome -->
            <div class="slide active" data-slide="0">
                <div class="slide-icon">👋</div>
                <h1 class="slide-title">Welcome to WorkNPay</h1>
                <p class="slide-description">
                    Connect with verified skilled workers in Ghana. Book services, make secure payments, and get the job done right.
                </p>
            </div>
            
            <!-- Slide 2: Find Workers -->
            <div class="slide" data-slide="1">
                <div class="slide-icon">🔍</div>
                <h1 class="slide-title">Find Skilled Workers</h1>
                <p class="slide-description">
                    Browse verified electricians, plumbers, tutors, and more. Check ratings, reviews, and prices before booking.
                </p>
            </div>
            
            <!-- Slide 3: Secure Payments -->
            <div class="slide" data-slide="2">
                <div class="slide-icon">💳</div>
                <h1 class="slide-title">Secure Payments</h1>
                <p class="slide-description">
                    Pay safely with Paystack. Your money is held in escrow until the job is completed to your satisfaction.
                </p>
            </div>
            
            <!-- Slide 4: Track Progress -->
            <div class="slide" data-slide="3">
                <div class="slide-icon">📱</div>
                <h1 class="slide-title">Track Your Bookings</h1>
                <p class="slide-description">
                    Stay updated on your service bookings. Chat with workers, track progress, and leave reviews when done.
                </p>
            </div>
        </div>
        
        <div class="dots-container">
            <div class="dot active" data-dot="0"></div>
            <div class="dot" data-dot="1"></div>
            <div class="dot" data-dot="2"></div>
            <div class="dot" data-dot="3"></div>
        </div>
        
        <div class="buttons-container">
            <a href="register.php" class="btn btn-primary">Get Started</a>
            <a href="login.php" class="btn btn-secondary">I Already Have an Account</a>
        </div>
    </div>
    
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const totalSlides = slides.length;
        
        // Auto-advance slides
        let autoSlideInterval = setInterval(nextSlide, 4000);
        
        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            slides[currentSlide].classList.add('prev');
            dots[currentSlide].classList.remove('active');
            
            currentSlide = (currentSlide + 1) % totalSlides;
            
            slides[currentSlide].classList.remove('prev');
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }
        
        function goToSlide(index) {
            slides[currentSlide].classList.remove('active');
            dots[currentSlide].classList.remove('active');
            
            currentSlide = index;
            
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
            
            // Reset auto-slide timer
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(nextSlide, 4000);
        }
        
        // Click on dots to navigate
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });
        
        // Swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        document.querySelector('.slides-container').addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        document.querySelector('.slides-container').addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            if (touchEndX < touchStartX - 50) {
                // Swipe left - next slide
                nextSlide();
            }
            if (touchEndX > touchStartX + 50) {
                // Swipe right - previous slide
                const prevIndex = (currentSlide - 1 + totalSlides) % totalSlides;
                goToSlide(prevIndex);
            }
        }
        
        function skipOnboarding() {
            // Mark onboarding as completed
            localStorage.setItem('onboarding_completed', 'true');
            window.location.href = 'register.php';
        }
        
        // If user has already seen onboarding, redirect to login
        if (localStorage.getItem('onboarding_completed') === 'true') {
            window.location.href = 'login.php';
        }
    </script>
</body>
</html>
