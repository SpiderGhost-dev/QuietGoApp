<?php
// Set header mode to prevent default header from loading
$adminHeaderMode = 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuietGo Admin Login</title>
    <meta name="description" content="QuietGo administration portal login.">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin Styles -->
    <link href="/admin/css/admin.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon_io/favicon.ico">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="/assets/images/logo-graphic.png" alt="QuietGo logo" width="48" height="48" style="margin-bottom: 16px;">
                <h1>QuietGo Admin</h1>
                <p class="muted">Administration Portal</p>
            </div>
            
            <form id="loginForm">
                <input type="text" id="username" placeholder="Username" required>
                <input type="password" id="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            
            <div style="margin-top: 24px; text-align: center;">
                <a href="/" style="color: var(--muted-text); font-size: 0.875rem; text-decoration: none;">
                    ← Return to QuietGo
                </a>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            try {
                const response = await fetch('/admin/api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Store user info in localStorage for client-side checks
                    localStorage.setItem('admin_logged_in', 'true');
                    localStorage.setItem('admin_user', JSON.stringify(data.user));
                    window.location.href = '/admin/dashboard.php';
                } else {
                    alert('Invalid admin credentials');
                }
            } catch (error) {
                alert('Login error. Please try again.');
            }
        });
        
        // Check if already logged in
        if (localStorage.getItem('admin_logged_in') === 'true') {
            window.location.href = '/admin/dashboard.php';
        }
    </script>
</body>
</html>
