<?php $pageTitle = 'Contact QuietGo — Get in Touch'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?></title>
  <meta name="description" content="Contact QuietGo for support, partnerships, or general inquiries. Located in Pensacola, FL with comprehensive support options.">
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
            <h1 class="hero-title" style="font-size: 2.5rem;">Contact Us</h1>
            <p class="hero-subtitle">We're here to help with any questions about QuietGo</p>
            <p class="hero-description">
                Whether you need technical support, have partnership inquiries, or just want to say hello, 
                we'd love to hear from you.
            </p>
        </div>
    </section>

    <!-- Contact Information -->
    <section class="section">
        <div class="container" style="max-width: 1200px;">
            <div class="grid grid-3" style="margin-bottom: 64px;">
                <!-- Phone Support -->
                <div class="card text-center">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <h3>Phone Support</h3>
                    <p class="muted" style="margin-bottom: 16px;">Call us directly for immediate assistance</p>
                    <a href="tel:+18504189100" style="color: var(--green-color); font-weight: 500; font-size: 1.25rem; text-decoration: none;">
                        (850) 418-9100
                    </a>
                    <p style="font-size: 0.875rem; margin-top: 8px; opacity: 0.7;">
                        Monday - Friday, 9 AM - 6 PM EST
                    </p>
                </div>

                <!-- Email Support -->
                <div class="card text-center">
                    <div class="card-icon accent">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--accent-color);">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <h3>Email Support</h3>
                    <p class="muted" style="margin-bottom: 16px;">Send us a detailed message</p>
                    <a href="mailto:support@QuietGo.app" style="color: var(--accent-color); font-weight: 500; font-size: 1.125rem; text-decoration: none;">
                        support@QuietGo.app
                    </a>
                    <p style="font-size: 0.875rem; margin-top: 8px; opacity: 0.7;">
                        We typically respond within 24 hours
                    </p>
                </div>

                <!-- Office Location -->
                <div class="card text-center">
                    <div class="card-icon">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: var(--green-color);">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <h3>Our Office</h3>
                    <p class="muted" style="margin-bottom: 16px;">Visit us in beautiful Pensacola</p>
                    <address style="font-style: normal; color: var(--text-color); font-weight: 500;">
                        503 Port Royal Way<br>
                        Pensacola, FL 32502
                    </address>
                    <p style="font-size: 0.875rem; margin-top: 8px; opacity: 0.7;">
                        By appointment only
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Map -->
    <section class="section" style="background-color: var(--card-bg); padding: 64px 0;">
        <div class="container" style="max-width: 1200px;">
            <div class="text-center" style="margin-bottom: 48px;">
                <h2>Find Us</h2>
                <p class="subheading" style="font-size: 1.125rem;">
                    Located in the heart of Pensacola, Florida
                </p>
            </div>
            
            <div class="card" style="padding: 0; overflow: hidden;">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3449.1833865194543!2d-87.2140687!3d30.4213289!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8890bf6c6c4e7e8d%3A0x1234567890abcdef!2s503%20Port%20Royal%20Way%2C%20Pensacola%2C%20FL%2032502!5e0!3m2!1sen!2sus!4v1234567890123!5m2!1sen!2sus"
                    width="100%" 
                    height="400" 
                    style="border:0; border-radius: var(--border-radius);" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade"
                    aria-label="Google Maps showing QuietGo office location at 503 Port Royal Way, Pensacola, FL 32502">
                </iframe>
            </div>
            
            <div style="margin-top: 32px; text-align: center;">
                <p style="color: var(--muted-text); font-size: 0.875rem;">
                    <strong>Getting Here:</strong> We're conveniently located near downtown Pensacola with easy access from I-10. 
                    Parking is available on-site for visitors.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section class="section">
        <div class="container" style="max-width: 800px;">
            <div class="text-center" style="margin-bottom: 48px;">
                <h2>Send Us a Message</h2>
                <p class="subheading" style="font-size: 1.125rem;">
                    Have a question? We'd love to hear from you.
                </p>
            </div>
            
            <div class="card">
                <form id="contactForm" onsubmit="handleContactForm(event)">
                    <div class="grid grid-2" style="gap: 24px; margin-bottom: 24px;">
                        <div>
                            <label for="firstName" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">
                                First Name *
                            </label>
                            <input 
                                type="text" 
                                id="firstName" 
                                name="firstName" 
                                required
                                style="width: 100%; padding: 12px 16px; background-color: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); color: var(--text-color); font-size: 1rem;"
                                placeholder="Enter your first name">
                        </div>
                        <div>
                            <label for="lastName" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">
                                Last Name *
                            </label>
                            <input 
                                type="text" 
                                id="lastName" 
                                name="lastName" 
                                required
                                style="width: 100%; padding: 12px 16px; background-color: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); color: var(--text-color); font-size: 1rem;"
                                placeholder="Enter your last name">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 24px;">
                        <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">
                            Email Address *
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            style="width: 100%; padding: 12px 16px; background-color: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); color: var(--text-color); font-size: 1rem;"
                            placeholder="your.email@example.com">
                    </div>
                    
                    <div style="margin-bottom: 24px;">
                        <label for="subject" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">
                            Subject *
                        </label>
                        <select 
                            id="subject" 
                            name="subject" 
                            required
                            style="width: 100%; padding: 12px 16px; background-color: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); color: var(--text-color); font-size: 1rem;">
                            <option value="">Select a topic</option>
                            <option value="support">Technical Support</option>
                            <option value="billing">Billing Question</option>
                            <option value="partnership">Partnership Inquiry</option>
                            <option value="press">Press Inquiry</option>
                            <option value="feedback">Product Feedback</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 32px;">
                        <label for="message" style="display: block; margin-bottom: 8px; font-weight: 500; color: var(--text-color);">
                            Message *
                        </label>
                        <textarea 
                            id="message" 
                            name="message" 
                            required
                            rows="6"
                            style="width: 100%; padding: 12px 16px; background-color: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); color: var(--text-color); font-size: 1rem; resize: vertical;"
                            placeholder="Tell us how we can help you..."></textarea>
                    </div>
                    
                    <div style="text-align: center;">
                        <button type="submit" class="btn btn-primary btn-large">
                            Send Message
                        </button>
                        <p style="font-size: 0.875rem; color: var(--muted-text); margin-top: 16px;">
                            We'll get back to you within 24 hours.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="section" style="background-color: var(--card-bg);">
        <div class="container" style="max-width: 800px;">
            <div class="text-center" style="margin-bottom: 48px;">
                <h2>Frequently Asked Questions</h2>
                <p class="subheading" style="font-size: 1.125rem;">
                    Quick answers to common questions
                </p>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div class="card">
                    <h3 style="margin-bottom: 12px; color: var(--green-color);">How do I get started with QuietGo?</h3>
                    <p>
                        Download the QuietGo app from the App Store or Google Play Store. You can start with our free 
                        3-day AI trial that includes 12 stool analyses at no cost.
                    </p>
                </div>
                
                <div class="card">
                    <h3 style="margin-bottom: 12px; color: var(--green-color);">Is my data private and secure?</h3>
                    <p>
                        Absolutely. We use end-to-end encryption, photos auto-delete after analysis by default, 
                        and we follow HIPAA-aware design principles. Your privacy is our top priority.
                    </p>
                </div>
                
                <div class="card">
                    <h3 style="margin-bottom: 12px; color: var(--green-color);">What's the difference between Pro and CalcuPlate?</h3>
                    <p>
                        Pro ($4.99/month) unlocks AI stool analysis and Pattern reports. CalcuPlate ($2.99/month additional) 
                        adds advanced meal photo analysis with automatic food identification and calorie tracking.
                    </p>
                </div>
                
                <div class="card">
                    <h3 style="margin-bottom: 12px; color: var(--green-color);">Do you offer refunds?</h3>
                    <p>
                        Yes, refunds are processed according to App Store and Google Play policies. Contact our support 
                        team if you have any issues with your subscription.
                    </p>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
function handleContactForm(event) {
    event.preventDefault();
    
    // Get form data
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    // Basic validation
    if (!data.firstName || !data.lastName || !data.email || !data.subject || !data.message) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Simulate form submission
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        alert('Thank you for your message! We\'ll get back to you within 24 hours.');
        event.target.reset();
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    }, 1500);
    
    // In a real implementation, you would send this data to your backend:
    // fetch('/api/contact', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify(data)
    // });
}
</script>

<?php include __DIR__ . '/includes/footer-public.php'; ?>
</body>
</html>
