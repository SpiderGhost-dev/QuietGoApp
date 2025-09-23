<?php
$adminHeaderMode = $adminHeaderMode ?? 'dashboard';
$showAdminActions = $adminHeaderMode !== 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuietGo Admin Dashboard</title>
    <meta name="description" content="QuietGo administration dashboard for managing users, content, and system settings.">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin Styles -->
    <link href="/admin/css/admin.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon_io/favicon.ico">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a class="header-brand" href="/admin/dashboard.php">
                    <img src="/assets/images/logo-graphic.png" alt="QuietGo logo" width="48" height="48" loading="lazy">
                    <div class="brand-stack">
                        <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span>
                        <span class="brand-tagline">Plate to Pattern</span>
                    </div>
                </a>
                <div class="section-identifier">
                    <span class="section-label">Admin</span>
                </div>
                <nav class="header-nav" aria-label="Admin navigation">
                    <?php if ($showAdminActions): ?>
                        <a href="/" class="header-nav-link">Main</a>
                        <a href="/hub/" class="header-nav-link">Dashboard</a>
                        <div class="admin-user-info">
                            <span id="admin-info">Admin User</span>
                            <button class="logout-btn" type="button" onclick="logout()">Logout</button>
                        </div>
                    <?php else: ?>
                        <a href="/" class="header-nav-link">Return to QuietGo</a>
                    <?php endif; ?>
                </nav>
                <button class="mobile-menu-btn" type="button" aria-expanded="false" aria-controls="mobileMenu" onclick="toggleMobileMenu(this)">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span class="sr-only">Toggle navigation</span>
                </button>
            </div>
            <div class="mobile-menu" id="mobileMenu">
                <?php if ($showAdminActions): ?>
                    <a href="/" class="header-nav-link">Main</a>
                    <a href="/hub/" class="header-nav-link">Dashboard</a>
                    <div class="mobile-menu-actions">
                        <button class="logout-btn" type="button" onclick="logout()">Logout</button>
                    </div>
                <?php else: ?>
                    <a href="/" class="header-nav-link">Return to QuietGo</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu(button) {
            const mobileMenu = document.getElementById('mobileMenu');
            const isOpen = mobileMenu.classList.contains('open');
            
            if (isOpen) {
                mobileMenu.classList.remove('open');
                button.setAttribute('aria-expanded', 'false');
            } else {
                mobileMenu.classList.add('open');
                button.setAttribute('aria-expanded', 'true');
            }
        }

        // Logout function
        function logout() {
            localStorage.removeItem('admin_logged_in');
            window.location.href = '/admin/login.php';
        }
    </script>