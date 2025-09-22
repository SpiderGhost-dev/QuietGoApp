<?php $pageTitle = 'About QuietGo — Privacy-First Digestive Wellness'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?></title>
  <meta name="description" content="Learn about QuietGo's mission to deliver private, actionable digestive health insights through AI-powered analysis. Meet our team and discover our privacy-first approach.">
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
            <h1 class="hero-title" style="font-size: 2.5rem;">About QuietGo</h1>
            <p class="hero-subtitle">Privacy-first digestive wellness platform</p>
            <p class="hero-description">
                We believe digestive health insights should be private, actionable, and designed for real life.
            </p>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="section">
        <div class="container" style="max-width: 1000px;">
            <div class="text-center" style="margin-bottom: 64px;">
                <h2>Our Mission &amp; Vision</h2>
                <p class="subheading" style="font-size: 1.125rem;">Transforming everyday snapshots into actionable insights</p>
            </div>
            
            <div class="grid grid-2" style="gap: 48px;">
                <div class="card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <h3>Our Mission</h3>
                    <p>
                        To deliver actionable, judgment-free digestive health insights through seamless AI-powered analysis, 
                        designed for real life. QuietGo is committed to ensuring privacy, clarity, and usability—without 
                        social feeds or judgment.
                    </p>
                </div>
                
                <div class="card">
                    <div class="card-icon accent">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                            <line x1="9" y1="9" x2="9.01" y2="9"/>
                            <line x1="15" y1="9" x2="15.01" y2="9"/>
                        </svg>
                    </div>
                    <h3>Our Vision</h3>
                    <p>
                        We envision a world where gut health insights are as private and routine as tracking daily steps. 
                        By combining meal and stool photo analysis with intelligent AI-driven correlations, we empower users 
                        to understand how diet and lifestyle influence digestive outcomes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- What Makes Us Different -->
    <section class="section" style="background-color: var(--card-bg);">
        <div class="container" style="max-width: 1000px;">
            <div class="text-center" style="margin-bottom: 64px;">
                <h2>What Makes Us Different</h2>
                <p class="subheading" style="font-size: 1.125rem;">
                    <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span> 
                    uniquely closes the loop: Plate → Stool → Pattern
                </p>
            </div>
            
            <div class="features-grid">
                <div class="card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h3>Privacy-First</h3>
                    <p>
                        Photos auto-delete after analysis by default. No social features, no judgment—just patterns 
                        that help you understand your body better with end-to-end encryption and HIPAA-aware design.
                    </p>
                </div>

                <div class="card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.29,7 12,12 20.71,7"/>
                            <line x1="12" y1="22" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <h3>Seamless Integration</h3>
                    <p>
                        Automatic logging and AI analysis across both meals and stool. Our platform correlates the two 
                        to surface insights, trends, and simple next steps for better digestive health.
                    </p>
                </div>

                <div class="card">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M9 12l2 2 4-4"/>
                            <circle cx="12" cy="12" r="10"/>
                        </svg>
                    </div>
                    <h3>Flexible Model</h3>
                    <p>
                        Freemium → Pro → CalcuPlate provides tailored value paths for both casual users and comprehensive 
                        digestive health explorers. Start free, upgrade when ready.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Team -->
    <section class="section">
        <div class="container" style="max-width: 800px;">
            <div class="text-center" style="margin-bottom: 64px;">
                <h2>Our Team</h2>
                <p class="subheading" style="font-size: 1.125rem;">
                    Founded by experts in health technology, business strategy, and user experience design
                </p>
            </div>
            
            <div class="features-grid">
                <div class="card text-center">
                    <div style="margin-bottom: 24px;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--green-color), var(--accent-color)); margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: bold;">
                            SB
                        </div>
                    </div>
                    <h3>Savannah J. Baker</h3>
                    <p class="muted" style="font-weight: 500; margin-bottom: 16px;">Founder, CEO</p>
                    <p style="font-size: 0.875rem;">
                        Head of Strategic Planning with extensive experience in health technology and business development. 
                        Drives QuietGo's vision and market strategy.
                    </p>
                </div>

                <div class="card text-center">
                    <div style="margin-bottom: 24px;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--accent-color), var(--green-color)); margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: bold;">
                            DB
                        </div>
                    </div>
                    <h3>David M. Baker, MBA</h3>
                    <p class="muted" style="font-weight: 500; margin-bottom: 16px;">Partner, CTO</p>
                    <p style="font-size: 0.875rem;">
                        Head of Development with an MBA and deep expertise in scalable technology architecture. 
                        Leads QuietGo's technical infrastructure and AI implementation.
                    </p>
                </div>

                <div class="card text-center">
                    <div style="margin-bottom: 24px;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--go-color), var(--accent-dark)); margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: bold;">
                            OB
                        </div>
                    </div>
                    <h3>O. Stone Baker</h3>
                    <p class="muted" style="font-weight: 500; margin-bottom: 16px;">Partner, Lead Designer</p>
                    <p style="font-size: 0.875rem;">
                        Lead UI/UX Design specialist focused on creating intuitive, accessible interfaces. 
                        Ensures QuietGo's user experience is both beautiful and functional.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Approach -->
    <section class="section" style="background-color: var(--card-bg);">
        <div class="container" style="max-width: 1000px;">
            <div class="text-center" style="margin-bottom: 64px;">
                <h2>Our Approach</h2>
                <p class="subheading" style="font-size: 1.125rem;">
                    Built on privacy-by-design principles with a commitment to excellence
                </p>
            </div>
            
            <div class="grid grid-2" style="gap: 48px;">
                <div>
                    <h3 style="display: flex; align-items: center; margin-bottom: 24px;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color); margin-right: 12px;">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1"/>
                        </svg>
                        Development Excellence
                    </h3>
                    <ul class="feature-list">
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Weekly sprint cycles for rapid iteration
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Strict SLA on reliability and performance
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Privacy-by-design technical foundation
                        </li>
                        <li>
                            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                            Continuous improvement based on user feedback
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 style="display: flex; align-items: center; margin-bottom: 24px;">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color); margin-right: 12px;">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        Privacy Commitment
                    </h3>
                    <ul class="feature-list">
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
                            Photos auto-delete after analysis by default
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
                            No social features or public sharing
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="section">
        <div class="container text-center" style="max-width: 600px;">
            <h2>Ready to Start Your Journey?</h2>
            <p style="font-size: 1.125rem; margin-bottom: 32px;">
                Join thousands of users who trust QuietGo with their digestive health insights.
            </p>
            <div class="flex flex-center gap-lg" style="flex-wrap: wrap;">
                <button class="btn btn-primary btn-large" onclick="handleGetStarted()">Get Started Free</button>
                <a href="/contact.php" class="btn btn-outline btn-large">Contact Us</a>
            </div>
        </div>
    </section>
</main>
<?php include __DIR__ . '/includes/footer-public.php'; ?>
</body>
</html>
