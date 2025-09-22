<?php $pageTitle = 'QuietGo — Your gut talks. QuietGo translates.'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?></title>
  <meta name="description" content="Discover digestive health insights with private, AI-powered stool and meal tracking. Download QuietGo on iOS and Android.">
  <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
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
            <img src="assets/logo_1757613890711.png" alt="QuietGo" class="hero-logo">
            
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
                We believe digestive health insights should be private, actionable, and designed for real life.
                No social features, no judgment—just patterns that help you understand your body better.
            </p>
            
            <!-- App Download Buttons -->
            <div class="flex flex-center gap-lg" style="margin-bottom: 32px; flex-wrap: wrap;">
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
            <div class="text-center" style="margin-bottom: 64px;">
                <h2>Snap it. Understand it. Build a routine.</h2>
                <p class="subheading" style="font-size: 1.125rem; max-width: 600px; margin: 16px auto 0;">
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

    <!-- Download Section -->
    <section id="download" class="section">
        <div class="container text-center">
            <h2>Download QuietGo</h2>
            <p class="subheading" style="font-size: 1.125rem; margin-bottom: 48px;">Available on iOS and Android - Start tracking your wellness journey today</p>
            
            <div class="pricing-grid" style="max-width: 800px;">
                <!-- iOS App -->
                <div class="card pricing-card">
                    <div class="card-icon" style="margin-bottom: 24px;">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="color: var(--green-color); width: 48px; height: 48px;">
                            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                        </svg>
                    </div>
                    <h3>iOS App Store</h3>
                    <p class="muted" style="margin-bottom: 24px;">
                        Free download with in-app purchases for Pro features and CalcuPlate meal analysis.
                    </p>
                    <ul class="feature-list" style="text-align: left; margin-bottom: 32px;">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            iOS 15+ compatible
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            HealthKit integration
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            iCloud sync
                        </li>
                    </ul>
                    <button class="btn btn-primary" onclick="handleGetStarted()" style="width: 100%;">
                        Download on App Store
                    </button>
                </div>

                <!-- Android App -->
                <div class="card pricing-card">
                    <div class="card-icon" style="margin-bottom: 24px;">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor" style="color: var(--accent-color); width: 48px; height: 48px;">
                            <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
                        </svg>
                    </div>
                    <h3>Google Play Store</h3>
                    <p class="muted" style="margin-bottom: 24px;">
                        Free download with in-app purchases for Pro features and CalcuPlate meal analysis.
                    </p>
                    <ul class="feature-list" style="text-align: left; margin-bottom: 32px;">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Android 8+ compatible
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Google Fit integration
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Cloud backup
                        </li>
                    </ul>
                    <button class="btn btn-accent" onclick="handleGetStarted()" style="width: 100%;">
                        Get it on Google Play
                    </button>
                </div>
            </div>
            
            <!-- Subscription Info -->
            <div style="margin-top: 48px; padding: 24px; background-color: var(--card-bg); border-radius: var(--border-radius); max-width: 600px; margin-left: auto; margin-right: auto;">
                <h3 style="margin-bottom: 16px;">In-App Subscriptions</h3>
                <p class="muted" style="margin-bottom: 16px;">
                    Start with our free features, then upgrade within the app when you're ready for AI analysis, advanced correlations, and professional reports.
                </p>
                <div style="display: flex; gap: 24px; justify-content: center; flex-wrap: wrap;">
                    <div style="text-align: center;">
                        <strong style="color: var(--text-color);">Pro Monthly</strong>
                        <div style="font-size: 0.875rem; opacity: 0.8;">$4.99/month</div>
                    </div>
                    <div style="text-align: center;">
                        <strong style="color: var(--text-color);">Pro Annual</strong>
                        <div style="font-size: 0.875rem; opacity: 0.8;">$39.99/year</div>
                    </div>
                    <div style="text-align: center;">
                        <strong style="color: var(--text-color);">CalcuPlate Add-on</strong>
                        <div style="font-size: 0.875rem; opacity: 0.8;">$1.99/month</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Privacy & Security -->
    <section id="privacy" class="section">
        <div class="container">
            <div class="text-center" style="margin-bottom: 48px;">
                <h2>Your privacy, our priority</h2>
                <p class="subheading" style="font-size: 1.125rem;">
                    Built from the ground up with healthcare-grade privacy and security standards.
                </p>
            </div>
            
            <div class="grid grid-2" style="max-width: 1000px; margin: 0 auto;">
                <div>
                    <h3 style="display: flex; align-items: center; margin-bottom: 24px;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color); margin-right: 12px;">
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
                    <h3 style="display: flex; align-items: center; margin-bottom: 24px;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color); margin-right: 12px;">
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
    <section class="section" style="padding-top: 32px; padding-bottom: 32px;">
        <div class="container">
            <div class="flex flex-center gap-lg" style="flex-wrap: wrap;">
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
<?php include __DIR__ . '/includes/footer-public.php'; ?>
</body>
</html>
