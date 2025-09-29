<?php $pageTitle = 'QuietGo — Your gut talks. QuietGo translates.'; ?>
<!-- Force deploy: privacy section 740
  --!>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?></title>
  <meta name="description" content="Discover digestive health insights with private, AI-powered stool and meal tracking. Download QuietGo on iOS and Android.">
  <link rel="icon" type="image/x-icon" href="/assets/images/favicon_io/favicon.ico">
  <link rel="stylesheet" href="/css/public.css">
  <script src="/js/site.js" defer></script>
</head>
<body>
<?php include __DIR__ . '/includes/header-public.php'; ?>
<main>
<!-- Hero Section -->
    <section class="section-hero">
        <div class="container text-center">
            <!-- QuietGo Company Logo -->
            <img src="assets/images/logo.png" alt="QuietGo" class="hero-logo">

            <!-- Main Headline -->
            <h1 class="hero-title">
                Your gut talks.<br>
                <span class="quietgo-brand">
                    <span class="quiet">Quiet</span><span class="go">Go</span>
                </span> translates.
            </h1>

            <p class="hero-subtitle">
                Capture the moment. Connect the dots. Act with confidence.
            </p>

            <!-- We Believe Statement -->
            <p class="hero-description">
                We believe digestive health insights 760
                uld be private, actionable, and designed for real life.
                No social features, no judgment—just patterns that help you understand your body better.
            </p>

            <!-- App Download Buttons -->
            <div class="flex flex-center gap-lg hero-intro">
                <!-- iOS App Store button -->
                <a href="#" class="app-store-btn hover-scale" onclick="handleAppStore()">
                    <svg class="icon-lg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    <div>
                        <div class="app-store-text-small">Download on the</div>
                        <div class="app-store-text-large">App Store</div>
                    </div>
                </a>

                <!-- Google Play Store button -->
                <a href="#" class="app-store-btn hover-scale" onclick="handlePlayStore()">
                    <svg class="icon-lg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    <div>
                        <div class="app-store-text-small">Get it on</div>
                        <div class="app-store-text-large">Google Play</div>
                    </div>
                </a>
            </div>

            <div class="hero-features">
                ✨ Free to try • 🔒 Privacy-first • 📊 AI-powered insights
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
        <div class="container">
            <div class="section-intro">
                <h2>Snap it. Understand it. Build a routine.</h2>
                <p class="subheading">
                    AI-powered stool and meal tracking that reveals patterns in your health. Discreet, private, and designed for real insights.
                </p>
            </div>

            <div class="features-grid">
                <!-- AI Stool Analysis -->
                <div class="card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                    </div>
                    <h3>AI Stool Analysis</h3>
                    <p class="muted">
                        Snap a photo for instant Bristol scale classification, color analysis, and plain-English health context.
                    </p>
                    <span class="badge badge-outline badge-primary">
                        Educational insights, not diagnosis
                    </span>
                </div>

                <!-- Meal Photo AI -->
                <div class="card">
                    <div class="card-icon accent">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                            <path d="M3 2v7c0 6 4 10 9 10s9-4 9-10V2"/>
                            <path d="M7 15h10"/>
                            <path d="M12 9v9"/>
                        </svg>
                    </div>
                    <h3>CalcuPlate Photo AI</h3>
                    <p class="muted">
                        Automatically detect foods, estimate portions, and calculate calories and macros with one-tap edits.
                    </p>
                    <span class="badge badge-accent">
                        $2.99/mo add-on service
                    </span>
                </div>

                <!-- Pattern Recognition -->
                <div class="card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
                        </svg>
                    </div>
                    <h3>Pattern Recognition</h3>
                    <p class="muted">
                        Discover correlations between food, sleep, exercise, and digestive health over time.
                    </p>
                    <span class="badge badge-outline badge-primary">
                        Regularity windows • Weekly cycles
                    </span>
                </div>

                <!-- Privacy First -->
                <div class="card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h3>Privacy First</h3>
                    <p class="muted">
                        Photos auto-delete after analysis by default. Your data stays private with end-to-end encryption.
                    </p>
                    <span class="badge badge-outline badge-primary">
                        HIPAA-aware design
                    </span>
                </div>

                <!-- View-Only Sharing -->
                <div class="card">
                    <div class="card-icon accent">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </div>
                    <h3>Smart Sharing</h3>
                    <p class="muted">
                        Share insights with friends, family, trainers, or healthcare providers. View-only access, revokable anytime.
                    </p>
                    <span class="badge badge-accent">
                        No photos shared by default
                    </span>
                </div>

                <!-- Professional Reports -->
                <div class="card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10,9 9,9 8,9"/>
                        </svg>
                    </div>
                    <h3>Medical Reports</h3>
                    <p class="muted">
                        Generate weekly Rhythm PDFs and CSV exports ready for healthcare providers.
                    </p>
                    <span class="badge badge-outline badge-primary">
                        Doctor-ready formats
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Your Journey, Your Way Section -->
    <section id="journeys" class="section">
        <div class="container">
            <div class="journeys-intro">
                <h2>Your Journey, Your Way</h2>
                <p class="subheading">
                    Whether you're working with healthcare providers, training for peak performance, or 34
                    ply living your best life — QuietGo adapts to your unique goals and delivers personalized insights.
                </p>
            </div>

            <div class="features-grid">
                <!-- Clinical Focus -->
                <div class="card journey-card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5Z"/>
                            <path d="M12 5L8 21l4-7 4 7-4-16"/>
                        </svg>
                    </div>
                    <h3>🏥 Clinical Focus</h3>
                    <p class="journey-card-text">
                        Partner with healthcare providers for optimal gut health
                    </p>
                    <ul class="feature-list journey-feature-list">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Medical-grade reporting and exports
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Symptom pattern identification
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Provider collaboration tools
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            HIPAA-aware data handling
                        </li>
                    </ul>
                    <div class="journey-quote">
                        "Get insights your doctor can actually use"
                    </div>
                </div>

                <!-- Peak Performance -->
                <div class="card journey-card">
                    <div class="card-icon accent">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/>
                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                            <path d="M4 22h16"/>
                            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                        </svg>
                    </div>
                    <h3>💪 Peak Performance</h3>
                    <p class="journey-card-text">
                        Fuel your training and maximize athletic potential
                    </p>
                    <ul class="feature-list journey-feature-list">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Pre/post-workout nutrition analysis
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Performance correlation tracking
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Coach-ready data exports
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Training cycle organization
                        </li>
                    </ul>
                    <div class="journey-quote">
                        "Turn your plate into peak performance"
                    </div>
                </div>

                <!-- Best Life Mode -->
                <div class="card journey-card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                            <line x1="9" y1="9" x2="9.01" y2="9"/>
                            <line x1="15" y1="9" x2="15.01" y2="9"/>
                        </svg>
                    </div>
                    <h3>✨ Best Life Mode</h3>
                    <p class="journey-card-text">
                        Look great, feel amazing, live your best life daily
                    </p>
                    <ul class="feature-list journey-feature-list">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Energy optimization insights
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Feel-good meal discovery
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Lifestyle pattern recognition
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Wellness journey tracking
                        </li>
                    </ul>
                    <div class="journey-quote">
                        "Look great, feel amazing, every day"
                    </div>
                </div>
            </div>

            <div class="journey-section-intro">
                <p class="journey-final-text">
                    <strong class="journey-final-emphasis">One platform, three personalized experiences.</strong><br>
                    QuietGo adapts its interface, insights, and recommendations to match your wellness goals.
                </p>
                <div class="hero-features">
                    🎯 Goal-based insights • 📊 Personalized reports • 🔄 Seamless experience
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Plans Section -->
    <section id="pricing" class="section">
        <div class="container text-center">
            <h2 class="pricing-main-title">Start with a FREE 7-day PRO trial TODAY, and choose the plan that best fits your wellness journey.</h2>

            <!-- Pricing Comparison -->
            <div class="pricing-grid pricing-container">

                <!-- QuietGo PRO -->
                <div class="card pricing-card pricing-card-pro">
                    <div class="pricing-badge">Most Popular</div>

                    <div class="card-icon pricing-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="color: var(--green-color); width: 48px; height: 48px;">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>

                    <h3 class="pricing-title pricing-title-green">
                        <span class="quietgo-brand"><span class="quiet">QuietGo</span></span> <strong>PRO</strong>
                    </h3>

                    <div class="pricing-display">
                        <div class="pricing-amount">$4.99<span class="pricing-period">/month</span></div>
                        <div class="pricing-yearly">or $39.99/year (save $20)</div>
                    </div>

                    <ul class="feature-list pricing-features">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>AI Stool Analysis</strong> — Bristol scale, health insights
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>Manual Meal Logging</strong> — Comprehensive nutrition tracking
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>Pattern Reports</strong> — Weekly/monthly health insights
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>QuietGo Hub</strong> — Desktop analytics dashboard
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>Data Export</strong> — Share with healthcare providers
                        </li>
                    </ul>

                    <button class="btn btn-primary pricing-button pricing-button-green" onclick="handleAppStore()">
                        Get QuietGo PRO
                    </button>
                </div>

                <!-- QuietGo PRO+ -->
                <div class="card pricing-card pricing-card-pro-plus">
                    <div class="card-icon pricing-icon-plus">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="color: var(--accent-color); width: 48px; height: 48px;">
                            <path d="M3 2v7c0 6 4 10 9 10s9-4 9-10V2"/>
                            <path d="M7 15h10"/>
                            <path d="M12 9v9"/>
                        </svg>
                    </div>

                    <h3 class="pricing-title pricing-title-accent">
                        <span class="quietgo-brand"><span class="quiet">QuietGo</span></span> <strong>PRO+</strong>
                    </h3>

                    <div class="pricing-display">
                        <div class="pricing-amount">$7.98<span class="pricing-period">/month</span></div>
                        <div class="pricing-yearly-green">or $59.98/year (save $36)</div>
                        <div class="pricing-addon-note">PRO + CalcuPlate ($2.99/mo add-on)</div>
                    </div>

                    <ul class="feature-list pricing-features">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>Everything in PRO</strong> — All PRO features included
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>CalcuPlate AI</strong> — Automatic meal photo analysis
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>Auto Food Recognition</strong> — No manual logging needed
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>Instant Calories/Macros</strong> — Just take a picture
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            <strong>Advanced Patterns</strong> — AI meal + stool correlations
                        </li>
                    </ul>

                    <button class="btn btn-accent pricing-button pricing-button-accent" onclick="handleAppStore()">
                        Get QuietGo PRO+
                    </button>
                </div>
            </div>

            <!-- Download Buttons -->
            <div class="flex flex-center gap-lg download-section">
                <!-- iOS App Store button -->
                <a href="#" class="app-store-btn hover-scale" onclick="handleAppStore()">
                    <svg class="icon-lg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    <div>
                        <div class="app-store-text-small">Download on the</div>
                        <div class="app-store-text-large">App Store</div>
                    </div>
                </a>

                <!-- Google Play Store button -->
                <a href="#" class="app-store-btn hover-scale" onclick="handlePlayStore()">
                    <svg class="icon-lg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    <div>
                        <div class="app-store-text-small">Get it on</div>
                        <div class="app-store-text-large">Google Play</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Privacy & Security -->
    <section id="privacy" class="section">
        <div class="container">
            <div class="privacy-intro">
                <h2>Your privacy, our priority</h2>
                <p class="subheading">
                    Built from the ground up with healthcare-grade privacy and security standards.
                </p>
            </div>

            <div class="grid grid-2">
                <div>
                    <h3 class="privacy-column-title">
                        <svg class="icon privacy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Data Protection
                    </h3>
                    <ul class="feature-list">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Photos auto-delete after AI analysis by default
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            End-to-end encryption for all sensitive data
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            HIPAA-aware design and data handling
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Export or delete your data anytime
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="privacy-column-title">
                        <svg class="icon privacy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        Discreet Design
                    </h3>
                    <ul class="feature-list">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Subtle app icon that doesn't reveal purpose
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Professional, medical-grade interface
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            No social features or public sharing
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            View-only sharing with anyone you choose
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Additional App CTAs -->
    <section class="section cta-section">
        <div class="container">
            <div class="flex flex-center gap-lg download-section">
                <a href="#" class="app-store-btn hover-scale" onclick="handleAppStore()">
                    <svg class="icon-lg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    <div>
                        <div class="app-store-text-small">Download on the</div>
                        <div class="app-store-text-large">App Store</div>
                    </div>
                </a>
                <a href="#" class="app-store-btn hover-scale" onclick="handlePlayStore()">
                    <svg class="icon-lg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                    </svg>
                    <div>
                        <div class="app-store-text-small">Get it on</div>
                        <div class="app-store-text-large">Google Play</div>
                    </div>
                </a>
            </div>
        </div>
    </section>

</main>

<!-- 📱 SOCIAL & COMMUNITY -->
<section class="social-section">
    <div class="container">
        <div class="social-content">
            <!-- Social Media Links -->
            <div class="social-media">
                <h3 class="social-heading">Join Our Community</h3>
                <div class="social-links">
                    <a href="https://twitter.com/quietgoapp" class="social-link" aria-label="Follow QuietGo on Twitter" onmouseover="this.style.color='#1DA1F2'; this.style.transform='translateY(-2px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateY(0)'">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                    <a href="https://facebook.com/quietgoapp" class="social-link" aria-label="Like QuietGo on Facebook" onmouseover="this.style.color='#4267B2'; this.style.transform='translateY(-2px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateY(0)'">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="https://instagram.com/quietgoapp" class="social-link" aria-label="Follow QuietGo on Instagram" onmouseover="this.style.color='#E4405F'; this.style.transform='translateY(-2px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateY(0)'">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </a>
                    <a href="https://linkedin.com/company/quietgoapp" class="social-link" aria-label="Connect with QuietGo on LinkedIn" onmouseover="this.style.color='#0077B5'; this.style.transform='translateY(-2px)'" onmouseout="this.style.color='var(--text-muted)'; this.style.transform='translateY(0)'">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Social Proof Testimonials -->
            <div class="social-proof">
                <h3 class="testimonial-heading">Trusted by health-conscious individuals</h3>
                <p class="testimonial-intro">See what our users are saying about their <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span> experience</p>

                <div class="features-grid testimonial-grid">
                    <!-- Testimonial 1 -->
                    <div class="testimonial-card" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <div class="testimonial-icon testimonial-icon-green">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="width: 48px; height: 48px;">
                                <path d="M9 12l2 2 4-4"/>
                                <path d="M21 12c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"/>
                                <path d="M3 12c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"/>
                                <path d="M12 21c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"/>
                                <path d="M12 3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"/>
                                <path d="M12 2v20"/>
                                <path d="M2 12h20"/>
                            </svg>
                        </div>
                        <blockquote class="testimonial-quote">"Finally found patterns in my digestive health that doctors couldn't see. The AI analysis is incredibly accurate."</blockquote>
                        <div class="testimonial-author-green">— Sarah M.</div>
                        <div class="testimonial-role">Healthcare Professional</div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="testimonial-card" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <div class="testimonial-icon testimonial-icon-accent">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="width: 48px; height: 48px;">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                        </div>
                        <blockquote class="testimonial-quote">"CalcuPlate saves me hours every week. Just snap a photo and everything is logged automatically."</blockquote>
                        <div class="testimonial-author-accent">— Michael R.</div>
                        <div class="testimonial-role">Fitness Enthusiast</div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="testimonial-card" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <div class="testimonial-icon testimonial-icon-green">
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="width: 48px; height: 48px;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </div>
                        <blockquote class="testimonial-quote">"QuietGo helped me understand how my meals affect my energy levels. I feel more balanced and confident in my daily choices."</blockquote>
                        <div class="testimonial-author-green">— Ashley K.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer-public.php'; ?>

<!-- AI Support Chatbot -->
<?php include __DIR__ . '/includes/chatbot-widget.php'; ?>

</body>
</html>
