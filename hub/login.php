<?php
// Handle logout
if (isset($_GET['logout'])) {
    // Clear any hub session data
    session_start();
    session_destroy();
    
    // Clear any cookies or local storage (would be handled by JavaScript)
    header('Location: /');
    exit;
}

// Set header mode for login
$hubHeaderMode = 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuietGo Hub Login</title>
    <meta name="description" content="Access your QuietGo Hub dashboard for health tracking insights.">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Hub Styles -->
    <link href="/hub/css/hub.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon_io/favicon.ico">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a class="header-brand" href="/">
                    <img src="/assets/images/logo-graphic.png" alt="QuietGo logo" width="36" height="36" loading="lazy">
                    <div class="brand-text">
                        <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span>
                        <span class="hub-text">Hub</span>
                    </div>
                </a>
                <nav class="header-nav" aria-label="Hub navigation">
                    <a href="/" class="header-nav-link">Return to QuietGo</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="login-container">
        <div class="login-card">
            <h1>Access QuietGo Hub</h1>
            <p class="muted" style="margin-bottom: 24px;">
                Sign in to view your health tracking dashboard and insights.
            </p>
            
            <form id="hubLoginForm" onsubmit="handleHubLogin(event)">
                <input type="email" placeholder="Email address" required>
                <input type="password" placeholder="Password" required>
                <button type="submit">Access Hub</button>
            </form>
            
            <div style="margin-top: 24px; text-align: center;">
                <p class="muted" style="font-size: 0.875rem;">
                    New to QuietGo? <a href="/" style="color: var(--green-color);">Download the app</a>
                </p>
            </div>
        </div>
    </main>

    <script>
        function handleHubLogin(event) {
            event.preventDefault();
            
            // This would connect to your authentication system
            // For now, just redirect to hub
            alert('Hub login functionality would connect to your authentication system here.');
            
            // Example redirect to hub after successful login:
            // window.location.href = '/hub/';
        }
    </script>
</body>
</html>
