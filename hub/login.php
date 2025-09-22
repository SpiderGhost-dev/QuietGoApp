<?php
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    // Clear hub session data
    session_destroy();
    setcookie('hub_auth', '', time() - 3600, '/');
    header('Location: /');
    exit;
}

// Handle admin redirect message
$adminRedirect = isset($_GET['msg']) && $_GET['msg'] === 'admin_redirect';

// Check if already logged into hub
if (isset($_SESSION['hub_user']) || isset($_COOKIE['hub_auth'])) {
    header('Location: /hub/');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Basic demo authentication - replace with real auth system
    if (!empty($email) && !empty($password)) {
        // Set hub session
        $_SESSION['hub_user'] = [
            'email' => $email,
            'name' => explode('@', $email)[0],
            'login_time' => time()
        ];
        
        // Set auth cookie
        setcookie('hub_auth', 'valid', time() + (24 * 60 * 60), '/');
        
        header('Location: /hub/');
        exit;
    } else {
        $error = 'Please enter both email and password.';
    }
}
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
            <div style="text-align: center; margin-bottom: 24px;">
                <img src="/assets/images/logo-graphic.png" alt="QuietGo logo" width="48" height="48" style="margin-bottom: 16px;">
                <h1>QuietGo Hub</h1>
                <p class="muted">Access your health tracking dashboard</p>
            </div>
            
            <?php if ($adminRedirect): ?>
                <div style="background: rgba(206, 152, 140, 0.1); border: 1px solid var(--go-color); border-radius: var(--border-radius); padding: 16px; margin-bottom: 24px; text-align: center;">
                    <p style="color: var(--go-color); margin: 0; font-size: 0.875rem;">
                        Please log in with your QuietGo Hub account (not admin credentials).
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; border-radius: var(--border-radius); padding: 16px; margin-bottom: 24px; text-align: center;">
                    <p style="color: #ef4444; margin: 0; font-size: 0.875rem;">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="email" name="email" placeholder="Email address" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Access Hub</button>
            </form>
            
            <div style="margin-top: 24px; text-align: center;">
                <p class="muted" style="font-size: 0.875rem;">
                    New to QuietGo? <a href="/" style="color: var(--green-color);">Download the app</a>
                </p>
                <a href="/" style="color: var(--muted-text); font-size: 0.875rem; text-decoration: none;">
                    ← Return to QuietGo
                </a>
            </div>
        </div>
    </main>
</body>
</html>
