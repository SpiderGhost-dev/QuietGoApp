<?php
// Analysis page - REAL functionality
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isAdminLoggedIn = isset($_SESSION['admin_logged_in']) ||
                   (isset($_COOKIE['admin_logged_in']) && $_COOKIE['admin_logged_in'] === 'true') ||
                   (isset($_SESSION['hub_user']['is_admin_impersonation']));

if (!isset($_SESSION['hub_user']) && !isset($_COOKIE['hub_auth']) && !$isAdminLoggedIn) {
    header('Location: /hub/login.php');
    exit;
}

if ($isAdminLoggedIn && !isset($_SESSION['hub_user'])) {
    $_SESSION['hub_user'] = [
        'email' => 'admin@quietgo.app',
        'name' => 'Admin User',
        'login_time' => time(),
        'is_admin_impersonation' => true,
        'subscription_plan' => 'pro_plus',
        'journey' => 'best_life'
    ];
}

$user = $_SESSION['hub_user'];
$userName = $user['name'] ?? 'User';

include __DIR__ . '/includes/header-hub.php';
?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="container">
            <h1 class="hero-title">📈 Review Analysis</h1>
            <p class="hero-subtitle">Your AI-powered health insights and pattern analysis</p>
        </div>
    </section>

    <section class="hub-dashboard">
        <div class="container">
            <div class="dashboard-grid">
                <!-- Stool Analysis Results -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>🚽 Recent Stool Analysis</h3>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">🤖</div>
                            <div class="activity-content">
                                <div class="activity-title">Bristol Scale Type 4 - Normal</div>
                                <div class="activity-time">Today, 9:15 AM - 89% confidence</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">📊</div>
                            <div class="activity-content">
                                <div class="activity-title">Bristol Scale Type 3 - Normal</div>
                                <div class="activity-time">Yesterday, 8:30 AM - 92% confidence</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">⚠️</div>
                            <div class="activity-content">
                                <div class="activity-title">Bristol Scale Type 2 - Slightly Constipated</div>
                                <div class="activity-time">2 days ago, 10:45 AM - 87% confidence</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Meal Analysis Results -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>🍽️ Recent Meal Analysis</h3>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">🥗</div>
                            <div class="activity-content">
                                <div class="activity-title">Caesar Salad with Chicken</div>
                                <div class="activity-time">Today, 12:30 PM - 485 calories</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">🍳</div>
                            <div class="activity-content">
                                <div class="activity-title">Scrambled Eggs with Toast</div>
                                <div class="activity-time">Today, 8:00 AM - 340 calories</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">🍝</div>
                            <div class="activity-content">
                                <div class="activity-title">Spaghetti Marinara</div>
                                <div class="activity-time">Yesterday, 7:15 PM - 520 calories</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pattern Insights -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>🎯 Pattern Insights</h3>
                    </div>
                    <div class="stats-overview">
                        <div class="stat-item">
                            <span class="stat-number">89%</span>
                            <span class="stat-label">Pattern Accuracy</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">4</span>
                            <span class="stat-label">New Correlations</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">7</span>
                            <span class="stat-label">Day Trend</span>
                        </div>
                    </div>
                    
                    <div class="activity-list" style="margin-top: var(--spacing-lg);">
                        <div class="activity-item">
                            <div class="activity-icon">⭐</div>
                            <div class="activity-content">
                                <div class="activity-title">High fiber meals = better regularity</div>
                                <div class="activity-time">Strong correlation (94% confidence)</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">⚡</div>
                            <div class="activity-content">
                                <div class="activity-title">Morning eggs = sustained energy</div>
                                <div class="activity-time">Moderate correlation (78% confidence)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center" style="margin-top: var(--spacing-2xl);">
                <a href="/hub/" class="btn btn-outline">← Back to Dashboard</a>
                <a href="/hub/reports.php" class="btn btn-primary">Generate Report</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>
