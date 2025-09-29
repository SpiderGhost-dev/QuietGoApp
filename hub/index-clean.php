<?php
// Hub authentication check - allow admin access
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user has valid hub session OR is admin
$isAdminLoggedIn = isset($_SESSION['admin_logged_in']) ||
                   (isset($_COOKIE['admin_logged_in']) && $_COOKIE['admin_logged_in'] === 'true') ||
                   (isset($_SESSION['hub_user']['is_admin_impersonation']));

if (!isset($_SESSION['hub_user']) && !isset($_COOKIE['hub_auth']) && !$isAdminLoggedIn) {
    header('Location: /hub/login.php');
    exit;
}

// If admin but no hub session, create one for demo purposes
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

// Get user data for smart dashboard
$user = $_SESSION['hub_user'];
$userName = $user['name'] ?? 'User';
$subscriptionPlan = $user['subscription_plan'] ?? 'free';
$userJourney = $user['journey'] ?? 'best_life';

$lastSync = time() - (2 * 60); // Demo sync time
$todayStats = [
    'meals_logged' => 2,
    'health_entries' => 1,
    'streak_days' => 7
];

include __DIR__ . '/includes/header-hub.php';
?>

<style>
:root {
    --success-color: #6c985f;
    --primary-blue: #4682b4;
    --accent-teal: #3c9d9b;
    --card-bg: #2a2a2a;
    --card-border: #404040;
    --text-primary: #ffffff;
    --text-secondary: #b0b0b0;
    --text-muted: #808080;
}

.dashboard-header {
    background: #1a1a1a;
    padding: 2rem 0;
    border-bottom: 1px solid var(--card-border);
    text-align: center;
}

.dashboard-title {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.dashboard-subtitle {
    font-size: 1rem;
    color: var(--text-secondary);
    margin: 0.5rem 0 0 0;
}

.sync-section {
    background: var(--primary-blue);
    padding: 1.5rem 0;
    text-align: center;
}

.sync-btn-main {
    background: var(--success-color);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.sync-btn-main:hover {
    background: #5a7d4f;
    transform: translateY(-2px);
}

.info-section {
    padding: 2rem 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.info-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 1.5rem;
    color: var(--text-primary);
}

.info-card h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 1rem 0;
    color: var(--text-primary);
}

.info-card p {
    color: var(--text-secondary);
    margin: 0.5rem 0;
}

.actions-section {
    padding: 2rem 0;
    border-top: 1px solid var(--card-border);
}

.actions-title {
    text-align: center;
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 2rem;
}

.actions-menu {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1rem;
    max-width: 1000px;
    margin: 0 auto;
}

.action-item {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.action-item:hover {
    background: #333;
    border-color: var(--success-color);
    transform: translateY(-2px);
}

.action-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #333;
    border-radius: 12px;
    border: 1px solid var(--card-border);
}

.action-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.action-content p {
    color: var(--text-secondary);
    margin: 0;
    font-size: 0.95rem;
}

.action-badge {
    margin-left: auto;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-pro {
    background: var(--success-color);
    color: white;
}

.badge-beta {
    background: var(--accent-teal);
    color: white;
}
</style>

<main class="hub-main">
    <!-- Header -->
    <section class="dashboard-header">
        <div class="container">
            <h1 class="dashboard-title">Welcome back, <?php echo htmlspecialchars($userName); ?>!</h1>
            <p class="dashboard-subtitle">
                <?php
                $syncTime = time() - $lastSync;
                if ($syncTime < 300) {
                    echo "Last synced " . ($syncTime < 60 ? "just now" : floor($syncTime/60) . " minutes ago");
                } else {
                    echo "Ready to sync with your mobile app";
                }
                ?>
            </p>
        </div>
    </section>

    <!-- Sync Section -->
    <section class="sync-section">
        <div class="container">
            <button class="sync-btn-main" onclick="startFullSync()">
                🔄 Sync from Mobile App
            </button>
            <div style="color: white; opacity: 0.8; margin-top: 0.5rem;">
                Pulls all photos, logs & reports
            </div>
        </div>
    </section>

    <!-- Info Cards -->
    <section class="info-section">
        <div class="container">
            <div class="info-grid">
                <div class="info-card">
                    <h3>📊 Account Status</h3>
                    <p><strong><?php echo ucfirst($subscriptionPlan); ?></strong> subscriber</p>
                    <p>Last sync: <?php echo $syncTime < 60 ? 'Just now' : floor($syncTime/60) . ' min ago'; ?></p>
                    <p>Streak: 🔥 <?php echo $todayStats['streak_days']; ?> days</p>
                </div>

                <div class="info-card">
                    <h3>📱 Recent Activity</h3>
                    <p>Meals today: <?php echo $todayStats['meals_logged']; ?></p>
                    <p>Health entries: <?php echo $todayStats['health_entries']; ?></p>
                    <p>Analysis ready: Complete</p>
                </div>

                <div class="info-card">
                    <h3>✨ Health Insights</h3>
                    <p>Pattern accuracy: 89%</p>
                    <p>New insights: 3 this week</p>
                    <p>Correlations found: 12 active</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Actions Menu -->
    <section class="actions-section">
        <div class="container">
            <h2 class="actions-title">What would you like to do?</h2>

            <div class="actions-menu">
                <!-- Upload Individual Items -->
                <a href="/hub/upload.php" class="action-item">
                    <div class="action-icon">📤</div>
                    <div class="action-content">
                        <h3>Upload Individual Items</h3>
                        <p>Add specific photos, logs, or reports between syncs</p>
                    </div>
                </a>

                <!-- Review Analysis -->
                <a href="/hub/analysis.php" class="action-item">
                    <div class="action-icon">📈</div>
                    <div class="action-content">
                        <h3>Review Analysis</h3>
                        <p>View patterns, correlations, and health insights from your data</p>
                    </div>
                </a>

                <!-- View Reports -->
                <a href="/hub/reports.php" class="action-item">
                    <div class="action-icon">📄</div>
                    <div class="action-content">
                        <h3>View Reports</h3>
                        <p>Access automatically generated health reports and insights</p>
                    </div>
                </a>

                <!-- Generate Custom Reports -->
                <a href="/hub/custom-reports.php" class="action-item">
                    <div class="action-icon">📋</div>
                    <div class="action-content">
                        <h3>Generate Custom Reports</h3>
                        <p>Create specific reports for appointments or personal tracking</p>
                    </div>
                </a>

                <!-- Share Health Journey -->
                <a href="/hub/share.php" class="action-item">
                    <div class="action-icon">🤝</div>
                    <div class="action-content">
                        <h3>Share Health Journey</h3>
                        <p>Send secure reports to healthcare providers, trainers, or family</p>
                    </div>
                </a>

                <!-- Smart Templates (Pro) -->
                <?php if ($subscriptionPlan === 'pro' || $subscriptionPlan === 'pro_plus'): ?>
                <a href="/hub/templates.php" class="action-item">
                    <div class="action-icon">⚡</div>
                    <div class="action-content">
                        <h3>Smart Templates</h3>
                        <p>Quick-log favorite meals and create reusable health templates</p>
                    </div>
                    <div class="action-badge badge-pro">Pro</div>
                </a>
                <?php endif; ?>

                <!-- Advanced Tools -->
                <a href="/hub/advanced.php" class="action-item">
                    <div class="action-icon">🧪</div>
                    <div class="action-content">
                        <h3>Advanced Tools</h3>
                        <p>Correlation analysis, meal planning, and multi-month comparisons</p>
                    </div>
                    <div class="action-badge badge-beta">Beta</div>
                </a>

                <!-- Browse & Manage Data -->
                <a href="/hub/data.php" class="action-item">
                    <div class="action-icon">🔍</div>
                    <div class="action-content">
                        <h3>Browse & Manage Data</h3>
                        <p>Search, filter, organize, and export your complete health tracking history</p>
                    </div>
                </a>
            </div>
        </div>
    </section>
</main>

<script>
console.log('QuietGo Hub Dashboard Loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('Setting up dashboard functionality');
    
    // Handle action item clicks
    document.querySelectorAll('.action-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            const featureName = this.querySelector('h3').textContent;
            
            // Allow upload to work normally
            if (href && href.includes('upload.php')) {
                return; // Let it navigate normally
            }
            
            // Prevent other navigation and show feature-specific functionality
            e.preventDefault();
            handleFeatureClick(featureName);
        });
    });
});

function startFullSync() {
    window.location.href = '/hub/sync.php';
}

function handleFeatureClick(featureName) {
    switch(featureName) {
        case 'Review Analysis':
            showAnalysisModal();
            break;
        case 'View Reports':
            showReportsModal();
            break;
        case 'Generate Custom Reports':
            showCustomReportsModal();
            break;
        case 'Share Health Journey':
            showSharingModal();
            break;
        case 'Browse & Manage Data':
            showDataBrowserModal();
            break;
        default:
            alert(featureName + ' coming soon!\n\nThis feature is currently in development.');
    }
}

function showAnalysisModal() {
    alert('📈 Analysis Dashboard\n\nShowing:\n• Recent AI analysis results\n• Pattern insights\n• Correlation findings\n• Health trend analysis\n\nFull analysis dashboard coming soon!');
}

function showReportsModal() {
    alert('📄 Health Reports\n\nAvailable:\n• Weekly summary reports\n• Monthly progress analysis\n• Symptom correlation reports\n• Provider-ready documents\n\nReport system coming soon!');
}

function showCustomReportsModal() {
    alert('📋 Custom Report Generator\n\nCreate:\n• Appointment-ready reports\n• Date range analysis\n• Specific symptom tracking\n• Custom data exports\n\nReport builder coming soon!');
}

function showSharingModal() {
    alert('🤝 Share Health Journey\n\nShare with:\n• Healthcare providers (clinical format)\n• Personal trainers (performance focus)\n• Family members (progress updates)\n\nSecure sharing system coming soon!');
}

function showDataBrowserModal() {
    alert('🔍 Data Browser & Manager\n\nBrowse:\n• All uploaded photos\n• Manual logs and entries\n• AI analysis results\n• Export and organize data\n\nData management system coming soon!');
}
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>
