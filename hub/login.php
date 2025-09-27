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

// Handle mobile sync account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sync_mobile'])) {
    $mobileEmail = $_POST['mobile_email'] ?? '';
    $mobilePassword = $_POST['mobile_password'] ?? '';

    if (!empty($mobileEmail) && !empty($mobilePassword)) {
        // Include storage helper for sync process
        require_once __DIR__ . '/includes/storage-helper.php';
        $storage = getQuietGoStorage();

        try {
            // TODO: Authenticate with mobile API (for now, simulate success)
            // $mobileAuth = authenticateWithMobileAPI($mobileEmail, $mobilePassword);

            // Simulate successful mobile authentication for demo
            $mobileAuth = [
                'success' => true,
                'user_data' => [
                    'email' => $mobileEmail,
                    'name' => explode('@', $mobileEmail)[0],
                    'subscription_plan' => 'pro_plus', // Demo: assume Pro+ user
                    'journey' => 'best_life',
                    'profile_data' => [
                        'created_date' => '2024-01-15',
                        'last_mobile_sync' => date('Y-m-d H:i:s')
                    ]
                ],
                'mobile_data' => [
                    'photos' => [], // Would contain mobile photos in real implementation
                    'logs' => [],   // Would contain mobile logs
                    'settings' => []
                ]
            ];

            if ($mobileAuth['success']) {
                // Create organized storage structure for user
                $storage->createUserStructure($mobileEmail);

                // Sync all mobile data (in real app, this would pull from mobile API)
                $syncResults = $storage->handleMobileSync($mobileEmail, $mobileAuth['mobile_data']);

                // Create Hub session with mobile account data
                $_SESSION['hub_user'] = [
                    'email' => $mobileAuth['user_data']['email'],
                    'name' => $mobileAuth['user_data']['name'],
                    'subscription_plan' => $mobileAuth['user_data']['subscription_plan'],
                    'journey' => $mobileAuth['user_data']['journey'],
                    'login_time' => time(),
                    'mobile_sync' => true,
                    'sync_results' => $syncResults
                ];

                setcookie('hub_auth', 'mobile_synced', time() + (24 * 60 * 60), '/');

                // Redirect to hub with success message
                header('Location: /hub/?sync=success');
                exit;
            } else {
                $syncError = 'Failed to authenticate with mobile app. Please check your credentials.';
            }
        } catch (Exception $e) {
            $syncError = 'Sync failed: ' . $e->getMessage();
        }
    } else {
        $syncError = 'Please enter your mobile app email and password.';
    }
}

// Check if already logged into hub
if (isset($_SESSION['hub_user']) || isset($_COOKIE['hub_auth'])) {
    header('Location: /hub/');
    exit;
}

// Handle regular login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['sync_mobile'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check for admin credentials for impersonation access
    if (($email === 'admin' && $password === 'admin123') ||
        ($email === 'spiderghost' && $password === 'TempAdmin2024')) {
        // Admin accessing hub - set up impersonation session
        $_SESSION['hub_user'] = [
            'email' => 'admin@quietgo.app',
            'name' => 'Admin User',
            'login_time' => time(),
            'is_admin_impersonation' => true,
            'subscription_plan' => 'pro_plus',
            'journey' => 'best_life'
        ];

        setcookie('hub_auth', 'admin_access', time() + (24 * 60 * 60), '/');
        header('Location: /hub/');
        exit;
    }
    // Demo credentials for testing
    elseif (!empty($email) && !empty($password)) {
        // Accept any valid email/password for demo purposes
        $_SESSION['hub_user'] = [
            'email' => $email,
            'name' => explode('@', $email)[0],
            'subscription_plan' => 'pro',
            'journey' => 'best_life',
            'login_time' => time()
        ];

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
<body class="login-page">
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
                    <a href="/" class="header-nav-link">Return to <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span></a>
                </nav>
            </div>
        </div>
    </header>

    <main class="login-container">
        <!-- Main Login Box -->
        <div class="login-card" id="login-box">
            <div class="logo-container">
                <img src="/assets/images/logo.png" alt="QuietGo" width="400" height="400">
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="email" name="email" placeholder="Email address" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Access Hub</button>
            </form>
        </div>


        <!-- Mobile Sync Form (hidden by default) -->
        <div class="login-card sync-form-hidden" id="sync-box">
            <div class="logo-container">
                <img src="/assets/images/logo.png" alt="QuietGo" width="400" height="400">
            </div>

            <?php if (isset($syncError)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($syncError); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="sync_mobile" value="1">
                <input type="email" name="mobile_email" placeholder="Mobile app email" required value="<?php echo htmlspecialchars($_POST['mobile_email'] ?? ''); ?>">
                <input type="password" name="mobile_password" placeholder="Mobile app password" required>
                <button type="submit" class="sync-submit-btn">
                    🔄 Connect & Sync All Data
                </button>
            </form>

            <div class="text-center">
                <button onclick="hideSyncForm()" class="sync-back-btn">
                    ← Back to Login
                </button>
            </div>
        </div>
    </main>

    <!-- Sync Mobile App Section -->
    <div class="sync-setup-section">
        <h3 class="sync-setup-title">
            First Time <span class="quiet">Quiet</span><span class="go">Go</span> Hub Set-up
        </h3>
        <button onclick="showSyncForm()" class="sync-mobile-btn">
            Sync Mobile App
        </button>
    </div>

    <script>
    function showSyncForm() {
        document.getElementById('login-box').classList.add('sync-form-hidden');
        document.getElementById('sync-box').classList.remove('sync-form-hidden');
        document.querySelector('.sync-setup-section').style.display = 'none';
    }

    function hideSyncForm() {
        document.getElementById('sync-box').classList.add('sync-form-hidden');
        document.getElementById('login-box').classList.remove('sync-form-hidden');
        document.querySelector('.sync-setup-section').style.display = 'block';
    }

    // Auto-show sync form if there was a sync error
    <?php if (isset($syncError)): ?>
    showSyncForm();
    <?php endif; ?>
    </script>
</body>
</html>

