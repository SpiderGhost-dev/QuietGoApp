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
        'is_admin_impersonation' => true,
        'subscription_plan' => 'pro_plus',
        'journey' => 'best_life'  // clinical, performance, best_life
    ];
}

// Get user data for smart dashboard
$user = $_SESSION['hub_user'];
$userName = $user['name'] ?? 'User';
$subscriptionPlan = $user['subscription_plan'] ?? 'free';
$userJourney = $user['journey'] ?? 'best_life';

// Journey personalization (same system as upload.php)
$journeyConfig = [
    'clinical' => ['focus' => 'healthcare providers', 'tone' => 'clinical'],
    'performance' => ['focus' => 'training optimization', 'tone' => 'performance'],
    'best_life' => ['focus' => 'everyday wellness', 'tone' => 'lifestyle']
];
$currentJourney = $journeyConfig[$userJourney];

// Simulate sync and data status for demo (replace with real data later)
$lastSync = time() - (2 * 60); // 2 minutes ago
$hasIncompleteData = true;
$incompleteItems = [
    ['type' => 'meal', 'description' => '2 meals need CalcuPlate analysis', 'count' => 2],
    ['type' => 'stool', 'description' => '1 stool photo pending AI analysis', 'count' => 1]
];
$todayStats = [
    'meals_logged' => 2,
    'health_entries' => 1,
    'streak_days' => 7
];

// Determine dashboard state
$dashboardState = 'incomplete_data'; // incomplete_data, all_current, browsing_historical
if (!$hasIncompleteData) {
    $dashboardState = 'all_current';
}

include __DIR__ . '/includes/header-hub.php';
?>

<style>
/* 🏦 PROFESSIONAL BANK-LIKE DASHBOARD */
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

/* 📋 SIMPLE HEADER */
.dashboard-header {
    background: #1a1a1a;
    padding: 2rem 0;
    border-bottom: 1px solid var(--card-border);
}

.dashboard-header .container {
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

/* 🔄 PROMINENT SYNC SECTION */
.sync-section {
    background: var(--primary-blue);
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--card-border);
}

.sync-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2rem;
}

.sync-status {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: white;
    opacity: 0.9;
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
    box-shadow: 0 4px 12px rgba(108, 152, 95, 0.3);
}

.sync-btn-main:hover {
    background: #5a7d4f;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(108, 152, 95, 0.4);
}

/* 📊 INFO CARDS - GREY BACKGROUNDS */
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

/* 🏦 MAIN ACTIONS MENU */
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
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
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

/* 🚨 ALERTS */
.alert-banner {
    background: var(--logo-rose);
    color: white;
    padding: 1rem;
    text-align: center;
    margin-bottom: 1rem;
    border-radius: 8px;
}

/* 📱 RESPONSIVE */
@media (max-width: 768px) {
    .sync-container {
        flex-direction: column;
        gap: 1rem;
    }
    
    .info-grid, .actions-menu {
        grid-template-columns: 1fr;
    }
    
    .dashboard-title {
        font-size: 1.5rem;
    }
}
</style>

<main class="hub-main">
    <!-- 📋 SIMPLE CENTERED HEADER -->
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

    <!-- 🔄 PROMINENT SYNC SECTION -->
    <section class="sync-section">
        <div class="container">
            <div class="sync-container">
                <div class="sync-status">
                    <?php if ($syncTime < 300): ?>
                        ✅ Last synced <?php echo $syncTime < 60 ? 'just now' : floor($syncTime/60) . ' min ago'; ?>
                    <?php elseif ($syncTime < 86400): ?>
                        🔄 Last synced <?php echo floor($syncTime/3600); ?> hours ago
                    <?php else: ?>
                        ⚠️ Last synced <?php echo floor($syncTime/86400); ?> days ago
                    <?php endif; ?>
                </div>
                
                <button class="sync-btn-main" onclick="startFullSync()">
                    🔄 Sync from Mobile App
                </button>
                
                <div class="sync-details">
                    <small style="color: white; opacity: 0.8;">Pulls all photos, logs & reports</small>
                </div>
            </div>
        </div>
    </section>

    <?php if ($hasIncompleteData): ?>
    <!-- 🚨 ALERT FOR INCOMPLETE DATA -->
    <section class="info-section">
        <div class="container">
            <div class="alert-banner">
                ⚡ You have <?php echo count($incompleteItems); ?> item<?php echo count($incompleteItems) > 1 ? 's' : ''; ?> waiting for analysis
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- 📊 MINIMAL INFO CARDS -->
    <section class="info-section">
        <div class="container">
            <div class="info-grid">
                <!-- Account Status -->
                <div class="info-card">
                    <h3>📊 Account Status</h3>
                    <p><strong><?php echo ucfirst($subscriptionPlan); ?></strong> subscriber</p>
                    <p>Last sync: <?php echo $syncTime < 60 ? 'Just now' : floor($syncTime/60) . ' min ago'; ?></p>
                    <p>Streak: 🔥 <?php echo $todayStats['streak_days']; ?> days</p>
                </div>
                
                <!-- Recent Activity -->
                <div class="info-card">
                    <h3>📱 Recent Activity</h3>
                    <p>Meals today: <?php echo $todayStats['meals_logged']; ?></p>
                    <p>Health entries: <?php echo $todayStats['health_entries']; ?></p>
                    <p>Analysis ready: <?php echo $hasIncompleteData ? 'Pending' : 'Complete'; ?></p>
                </div>
                
                <!-- Data Insights -->
                <div class="info-card">
                    <h3>✨ Health Insights</h3>
                    <p>Pattern accuracy: 89%</p>
                    <p>New insights: 3 this week</p>
                    <p>Correlations found: 12 active</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 🏦 MAIN ACTIONS MENU -->
    <section class="actions-section">
        <div class="container">
            <h2 class="actions-title">What would you like to do?</h2>
            
            <div class="actions-menu">
                <!-- Sync Data -->
                <a href="/hub/sync.php" class="action-item">
                    <div class="action-icon">🔄</div>
                    <div class="action-content">
                        <h3>Full Sync</h3>
                        <p>Pull ALL data from mobile app with smart conflict resolution</p>
                    </div>
                </a>
                
                <!-- Upload Individual Items -->
                <a href="/hub/upload.php" class="action-item">
                    <div class="action-icon">📤</div>
                    <div class="action-content">
                        <h3>Upload Individual Items</h3>
                        <p>Add specific photos, logs, or reports between syncs</p>
                    </div>
                </a>
                
                <!-- Review Analysis -->
                <a href="/hub/patterns.php" class="action-item">
                    <div class="action-icon">📈</div>
                    <div class="action-content">
                        <h3>Review Analysis</h3>
                        <p>View patterns, correlations, and health insights from your data</p>
                    </div>
                </a>
                
                <!-- Generate Reports -->
                <a href="/hub/reports.php" class="action-item">
                    <div class="action-icon">📄</div>
                    <div class="action-content">
                        <h3>Generate Reports</h3>
                        <p>Create professional health reports for appointments</p>
                    </div>
                </a>
                
                <!-- Browse Records -->
                <a href="/hub/browse.php" class="action-item">
                    <div class="action-icon">🔍</div>
                    <div class="action-content">
                        <h3>Browse All Records</h3>
                        <p>Search and filter your complete health tracking history</p>
                    </div>
                </a>
                
                <!-- Share with Providers -->
                <a href="/hub/share.php" class="action-item">
                    <div class="action-icon">🤝</div>
                    <div class="action-content">
                        <h3>Share Your Health Journey</h3>
                        <p>Send secure reports to healthcare providers, loved ones, or support communities</p>
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
                
                <!-- Account & Privacy -->
                <a href="/hub/account.php" class="action-item">
                    <div class="action-icon">⚙️</div>
                    <div class="action-content">
                        <h3>Account & Privacy</h3>
                        <p>Manage subscription, privacy settings, and data controls</p>
                    </div>
                </a>
                
                <!-- Data Management -->
                <a href="/hub/data.php" class="action-item">
                    <div class="action-icon">🗂️</div>
                    <div class="action-content">
                        <h3>Data Management</h3>
                        <p>Export, organize, archive, or delete your health data</p>
                    </div>
                </a>
            </div>
        </div>
    </section>
</main>

<script>
// 🧠 Smart Hub Functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Professional QuietGo Hub loaded for <?php echo htmlspecialchars($userName); ?>');
});

function startFullSync() {
    // Redirect to enhanced sync page with conflict resolution
    window.location.href = '/hub/sync.php';
}

function useTemplate(templateId) {
    alert(`Loading template: ${templateId}`);
    // TODO: Implement template usage
}

function createTemplate() {
    alert('Template creation coming soon!');
    // TODO: Implement template creation
}

function showCalcuPlateUpgrade() {
    alert('CalcuPlate upgrade dialog coming soon!');
    // TODO: Implement CalcuPlate upgrade flow
}

function openCorrelationAnalysis() {
    alert('Advanced Correlation Analysis coming soon!');
    // TODO: Implement correlation analysis
}

function openMealPlanner() {
    alert('Drag & Drop Meal Planner coming soon!');
    // TODO: Implement meal planner
}

function openMultiMonthComparison() {
    alert('Multi-Month Comparison coming soon!');
    // TODO: Implement comparison tool
}
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>

<!-- AI Support Chatbot -->
<?php include __DIR__ . '/../includes/chatbot-widget.php'; ?>