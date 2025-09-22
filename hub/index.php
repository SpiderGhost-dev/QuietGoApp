<?php
// Hub authentication check - allow admin access
session_start();

// Check if user has valid hub session OR is admin
$isAdminLoggedIn = isset($_SESSION['admin_logged_in']) || 
                   (isset($_COOKIE['admin_logged_in']) && $_COOKIE['admin_logged_in'] === 'true') ||
                   (isset($_SESSION['hub_user']['is_admin_impersonation']));
                   
if (!isset($_SESSION['hub_user']) && !isset($_COOKIE['hub_auth']) && !$isAdminLoggedIn) {
    // Not logged into hub and not admin - redirect to hub login
    header('Location: /hub/login.php');
    exit;
}

// If admin but no hub session, create one for demo purposes
if ($isAdminLoggedIn && !isset($_SESSION['hub_user'])) {
    $_SESSION['hub_user'] = [
        'email' => 'admin@quietgo.app',
        'name' => 'Admin User',
        'login_time' => time(),
        'is_admin_impersonation' => true
    ];
}

include __DIR__ . '/includes/header-hub.php';
?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="container text-center">
            <h1 class="hero-title">Welcome to QuietGo Hub</h1>
            <p class="hero-subtitle">Your personal digestive health tracking dashboard</p>
        </div>
    </section>

    <section class="hub-dashboard">
        <div class="container">
            <div class="dashboard-grid">
                <!-- Quick Stats -->
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h3>Today's Summary</h3>
                    </div>
                    <div class="stats-overview">
                        <div class="stat-item">
                            <span class="stat-number">3</span>
                            <span class="stat-label">Meals logged</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">1</span>
                            <span class="stat-label">Health entries</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">7</span>
                            <span class="stat-label">Day streak</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div class="action-buttons">
                        <button class="btn btn-primary">📸 Log Meal</button>
                        <button class="btn btn-outline">🚽 Health Entry</button>
                        <button class="btn btn-outline">📊 View Patterns</button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h3>Recent Activity</h3>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <span class="activity-icon">🥗</span>
                            <div class="activity-content">
                                <div class="activity-title">Lunch logged</div>
                                <div class="activity-time">2 hours ago</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <span class="activity-icon">💊</span>
                            <div class="activity-content">
                                <div class="activity-title">Health entry added</div>
                                <div class="activity-time">5 hours ago</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <span class="activity-icon">🍳</span>
                            <div class="activity-content">
                                <div class="activity-title">Breakfast logged</div>
                                <div class="activity-time">8 hours ago</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Progress -->
                <div class="card dashboard-card full-width">
                    <div class="card-header">
                        <h3>This Week's Progress</h3>
                    </div>
                    <div class="progress-chart">
                        <p class="muted">Connect to see your weekly tracking patterns and insights.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// Basic hub functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is logged in (this would connect to your auth system)
    console.log('Hub loaded for user');
    
    // Add any interactive functionality here
});
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>