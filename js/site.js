// QuietGo Site JavaScript - Minimal interactions
// No frameworks, just vanilla JavaScript

// App configuration

// Mobile menu functionality
function toggleMobileMenu() {
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.toggle('open');
}

// Smooth scrolling to sections
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        element.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
    // Close mobile menu if open
    const mobileMenu = document.getElementById('mobileMenu');
    mobileMenu.classList.remove('open');
}

// Handle login redirect
function handleLogin() {
    // Redirect to QuietGo Hub or authentication
    window.location.href = '/hub/login.php';
}

// Handle get started action
function handleGetStarted() {
    // Redirect to sign up or login
    window.location.href = '/hub/login.php';
}

// Handle app store links
function handleAppStore() {
    // Replace with actual App Store URL when ready
    alert('App Store link - Replace with actual URL when app is published');
    // window.open('https://apps.apple.com/app/quietgo', '_blank');
}

function handlePlayStore() {
    // Replace with actual Play Store URL when ready
    alert('Play Store link - Replace with actual URL when app is published');
    // window.open('https://play.google.com/store/apps/details?id=com.quietgo.app', '_blank');
}



// Handle keyboard events
function setupKeyboardEvents() {
    document.addEventListener('keydown', function(e) {
        // Close mobile menu on Escape key
        if (e.key === 'Escape') {
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu.classList.contains('open')) {
                mobileMenu.classList.remove('open');
            }
        }
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupKeyboardEvents();
    
    console.log('QuietGo site initialized');
});

// Add some utility functions for potential future use
function updateQueryParam(param, value) {
    const url = new URL(window.location);
    url.searchParams.set(param, value);
    window.history.replaceState({}, '', url);
}

function getQueryParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Simple analytics event tracking (placeholder)
function trackEvent(eventName, properties = {}) {
    // Add your analytics tracking here
    console.log('Event tracked:', eventName, properties);
}

// Track button clicks for analytics
function trackButtonClick(buttonName) {
    trackEvent('button_click', { button: buttonName });
}

// Enhanced button handlers with tracking
const originalHandleLogin = handleLogin;
handleLogin = function() {
    trackButtonClick('login');
    originalHandleLogin();
};

const originalHandleGetStarted = handleGetStarted;
handleGetStarted = function() {
    trackButtonClick('get_started');
    originalHandleGetStarted();
};

const originalHandleAppStore = handleAppStore;
handleAppStore = function() {
    trackButtonClick('app_store');
    originalHandleAppStore();
};

const originalHandlePlayStore = handlePlayStore;
handlePlayStore = function() {
    trackButtonClick('play_store');
    originalHandlePlayStore();
};

// Add smooth reveal animations on scroll (optional enhancement)
function addScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe cards and sections
    const animatedElements = document.querySelectorAll('.card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Initialize scroll animations after page load
window.addEventListener('load', function() {
    addScrollAnimations();
});
// QuietGo auto-branding
