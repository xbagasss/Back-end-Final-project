<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Passionate about Progress</title>
    <link rel="stylesheet" href="assets/navbar.css">
    <link rel="stylesheet" href="assets/landing.css">
</head>
<body>
    <!-- Auto-hide Navbar -->
    <nav class="navbar">
        <div class="navbar-top">
            <a href="#" class="navbar-logo">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="18" stroke="currentColor" stroke-width="2"/>
                    <path d="M20 8L28 20L20 32L12 20L20 8Z" fill="currentColor"/>
                </svg>
                <span>SmartHealthy</span>
            </a>
            
            <div class="navbar-top-links">
                <a href="#about">About Us</a>
                <a href="#news">News & Media</a>
                <a href="#careers">Careers</a>
                <a href="#suppliers">Suppliers</a>
                <a href="#contact">Contact Us</a>
            </div>
            
            <div class="navbar-icons">
                <a href="#" class="navbar-icon" title="Language">🌐</a>
                <a href="#" class="navbar-icon" title="Search">🔍</a>
                <a href="profile.php" class="navbar-icon" title="Profile">👤</a>
            </div>
        </div>
        
        <div class="navbar-bottom">
            <a href="dashboard.php">Dashboard</a>
            <a href="#sustainability">Sustainability</a>
            <a href="analytics.php">Analytics</a>
            <a href="meal_plan.php">Meal Plan</a>
            <a href="weight_tracker.php">Weight Tracker</a>
            <a href="#stories">Our Stories</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <img src="assets/img/hero_background.png" alt="Hero Background" class="hero-background">
        <div class="hero-overlay"></div>
        
        <div class="hero-content">
            <h1 class="hero-title">Passionate about Progress</h1>
            <p class="hero-subtitle">
                Our passion for progress drives us to create better solutions that benefit people, our partners, 
                and the planet. Together, we're building a healthier future.
            </p>
            <a href="#explore" class="hero-cta">
                <span>Read more</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        
        <div class="scroll-indicator">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </div>
    </section>

    <!-- Navigation Cards Section -->
    <section class="nav-cards-section" id="explore">
        <div class="nav-cards-container">
            <div class="nav-card" onclick="window.location.href='dashboard.php'">
                <h3 class="nav-card-title">Nutrition Dashboard</h3>
                <p class="nav-card-description">
                    Track your daily nutrition intake and monitor your progress towards your health goals.
                </p>
            </div>
            
            <div class="nav-card" onclick="window.location.href='analytics.php'">
                <h3 class="nav-card-title">Analytics & Insights</h3>
                <p class="nav-card-description">
                    Gain valuable insights from your nutrition data with comprehensive analytics and reports.
                </p>
            </div>
            
            <div class="nav-card" onclick="window.location.href='meal_plan.php'">
                <h3 class="nav-card-title">Meal Planning</h3>
                <p class="nav-card-description">
                    Create and manage personalized meal plans tailored to your dietary needs and preferences.
                </p>
            </div>
            
            <div class="nav-card" onclick="window.location.href='weight_tracker.php'">
                <h3 class="nav-card-title">Weight Management</h3>
                <p class="nav-card-description">
                    Monitor your weight journey with detailed tracking and progress visualization tools.
                </p>
            </div>
            
            <div class="nav-card" onclick="window.location.href='calendar.php'">
                <h3 class="nav-card-title">Calendar View</h3>
                <p class="nav-card-description">
                    View your nutrition history and plan ahead with our interactive calendar interface.
                </p>
            </div>
        </div>
    </section>

    <!-- Scripts -->
    <script src="assets/navbar.js"></script>
</body>
</html>
