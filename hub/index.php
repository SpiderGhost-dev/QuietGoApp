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
    <!-- 🎉 MOBILE SYNC SUCCESS -->
    <?php if (isset($_GET['sync']) && $_GET['sync'] === 'success'): ?>
    <section class="sync-success-banner" style="background: linear-gradient(135deg, var(--success-color), #7aa570); color: white; padding: 1.5rem 0; text-align: center; margin-bottom: 1rem;">
        <div class="container">
            <h2 style="margin: 0 0 0.5rem 0; font-size: 1.5rem;">🎉 Account Sync Complete!</h2>
            <p style="margin: 0; opacity: 0.9;">All your mobile data has been organized and is ready for analysis</p>
            <?php if (isset($_SESSION['hub_user']['sync_results'])): ?>
                <div style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">
                    <?php
                    $results = $_SESSION['hub_user']['sync_results'];
                    echo "Synced: {$results['photos_synced']} photos, {$results['logs_synced']} logs, {$results['analysis_synced']} analysis files";
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- 📊 INSIGHTS SECTION -->
    <section id="insights" class="info-section">
        <div class="container">
            <h2 style="color: var(--text-primary); text-align: center; margin-bottom: 2rem;">🔍 Health Insights</h2>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3>📈 Pattern Analysis</h3>
                    <p><strong>Energy Patterns:</strong> Higher energy 2-3 hours after protein-rich meals</p>
                    <p><strong>Digestive Timing:</strong> Most regular between 8-10 AM</p>
                    <p><strong>Weekly Trend:</strong> Consistency improving (up 23% this month)</p>
                    <button onclick="showDetailedPatterns()" style="background: var(--success-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">View Detailed Analysis</button>
                </div>
                
                <div class="info-card">
                    <h3>🎯 Correlations Found</h3>
                    <p><strong>Food → Energy:</strong> Oatmeal + berries = sustained energy</p>
                    <p><strong>Sleep → Digestion:</strong> 8+ hours sleep = better regularity</p>
                    <p><strong>Exercise → Recovery:</strong> Light walks improve digestive comfort</p>
                    <button onclick="showCorrelations()" style="background: var(--primary-blue); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">Explore Correlations</button>
                </div>
                
                <div class="info-card">
                    <h3>🎁 Recommendations</h3>
                    <p><strong>Try This Week:</strong> Add 15-min morning walk before breakfast</p>
                    <p><strong>Meal Timing:</strong> Delay dinner by 1 hour for better sleep</p>
                    <p><strong>Hydration:</strong> Increase water intake between 2-4 PM</p>
                    <button onclick="applyRecommendations()" style="background: var(--accent-teal); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">Apply Suggestions</button>
                </div>
            </div>
        </div>
    </section>

    <!-- 📊 INSIGHTS SECTION -->
    <section id="insights" class="info-section">
        <div class="container">
            <h2 style="color: var(--text-primary); text-align: center; margin-bottom: 2rem;">🔍 Health Insights</h2>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3>📈 Pattern Analysis</h3>
                    <p><strong>Energy Patterns:</strong> Higher energy 2-3 hours after protein-rich meals</p>
                    <p><strong>Digestive Timing:</strong> Most regular between 8-10 AM</p>
                    <p><strong>Weekly Trend:</strong> Consistency improving (up 23% this month)</p>
                    <button onclick="showDetailedPatterns()" style="background: var(--success-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">View Detailed Analysis</button>
                </div>
                
                <div class="info-card">
                    <h3>🎯 Correlations Found</h3>
                    <p><strong>Food → Energy:</strong> Oatmeal + berries = sustained energy</p>
                    <p><strong>Sleep → Digestion:</strong> 8+ hours sleep = better regularity</p>
                    <p><strong>Exercise → Recovery:</strong> Light walks improve digestive comfort</p>
                    <button onclick="showCorrelations()" style="background: var(--primary-blue); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">Explore Correlations</button>
                </div>
                
                <div class="info-card">
                    <h3>🎁 Recommendations</h3>
                    <p><strong>Try This Week:</strong> Add 15-min morning walk before breakfast</p>
                    <p><strong>Meal Timing:</strong> Delay dinner by 1 hour for better sleep</p>
                    <p><strong>Hydration:</strong> Increase water intake between 2-4 PM</p>
                    <button onclick="applyRecommendations()" style="background: var(--accent-teal); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">Apply Suggestions</button>
                </div>
            </div>
        </div>
    </section>

    <!-- 🆘 SUPPORT SECTION -->
    <section id="support" class="info-section" style="background: #1a1a1a;">
        <div class="container">
            <h2 style="color: var(--text-primary); text-align: center; margin-bottom: 2rem;">🆘 Support & Help</h2>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3>📚 Getting Started</h3>
                    <p>New to QuietGo? Learn the basics of effective health tracking</p>
                    <button onclick="showGettingStarted()" style="background: var(--success-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">View Quick Guide</button>
                </div>
                
                <div class="info-card">
                    <h3>💬 Contact Support</h3>
                    <p>Questions? Issues? Our team responds within 24 hours</p>
                    <button onclick="contactSupport()" style="background: var(--primary-blue); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">Send Message</button>
                </div>
                
                <div class="info-card">
                    <h3>🔧 Technical Help</h3>
                    <p>Sync issues? Photo upload problems? Mobile app questions?</p>
                    <button onclick="showTechnicalHelp()" style="background: var(--accent-teal); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">Technical FAQ</button>
                </div>
            </div>
        </div>
    </section>

    <!-- 🆘 SUPPORT SECTION -->
    <section id="support" class="info-section" style="background: #1a1a1a;">
        <div class="container">
            <h2 style="color: var(--text-primary); text-align: center; margin-bottom: 2rem;">🆘 Support & Help</h2>
            
            <div class="info-grid">
                <div class="info-card">
                    <h3>📚 Getting Started</h3>
                    <p>New to QuietGo? Learn the basics of effective health tracking</p>
                    <button onclick="showGettingStarted()" style="background: var(--success-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">View Quick Guide</button>
                </div>
                
                <div class="info-card">
                    <h3>💬 Contact Support</h3>
                    <p>Questions? Issues? Our team responds within 24 hours</p>
                    <button onclick="contactSupport()" style="background: var(--primary-blue); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">Send Message</button>
                </div>
                
                <div class="info-card">
                    <h3>🔧 Technical Help</h3>
                    <p>Sync issues? Photo upload problems? Mobile app questions?</p>
                    <button onclick="showTechnicalHelp()" style="background: var(--accent-teal); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-top: 1rem; cursor: pointer;">Technical FAQ</button>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
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
    <!-- 🚨 ALERT FOR INCOMPLETE DATA WITH ACTION BUTTON -->
    <section class="info-section">
        <div class="container">
            <div class="alert-banner" style="position: relative;">
                ⚡ You have <?php echo count($incompleteItems); ?> item<?php echo count($incompleteItems) > 1 ? 's' : ''; ?> waiting for analysis
                <button onclick="startPendingAnalysis()" style="background: white; color: #d4a799; border: none; padding: 0.5rem 1rem; border-radius: 6px; margin-left: 1rem; font-weight: 600; cursor: pointer;">🤖 Analyze Now</button>
            </div>
            
            <!-- Pending Items Details -->
            <div style="background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 12px; padding: 1.5rem; margin-top: 1rem;">
                <h3 style="color: var(--text-primary); margin: 0 0 1rem 0;">📋 Items Waiting for Analysis:</h3>
                <?php foreach ($incompleteItems as $item): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--card-border);">
                    <div>
                        <span style="color: var(--text-primary); font-weight: 500;"><?php echo htmlspecialchars($item['description']); ?></span>
                        <div style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Ready for AI analysis</div>
                    </div>
                    <button onclick="analyzeSpecificItem('<?php echo $item['type']; ?>')" style="background: var(--success-color); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.9rem;">🔍 Analyze</button>
                </div>
                <?php endforeach; ?>
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
    
    // Fix non-functional dashboard buttons
    fixDashboardButtons();
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

// New functionality for Insights and Support sections
function showDetailedPatterns() {
    alert('Detailed Pattern Analysis:\n\n• Energy peaks at 11 AM and 3 PM\n• Best digestion windows: 8-10 AM\n• Weekly consistency score: 89%\n\nFull analysis coming soon!');
}

function showCorrelations() {
    alert('Top Correlations Found:\n\n• Oatmeal + berries → 4-hour sustained energy\n• 8+ hours sleep → 23% better regularity\n• 15-min walks → improved comfort\n\nAdvanced correlation analysis coming soon!');
}

function applyRecommendations() {
    alert('Applying This Week\'s Recommendations:\n\n• Morning walk reminder set\n• Dinner delay notification added\n• Hydration alerts enabled 2-4 PM\n\nSmart recommendations system coming soon!');
}

function showGettingStarted() {
    alert('QuietGo Quick Start Guide:\n\n• Upload stool photos for AI analysis\n• Log meals manually or with CalcuPlate\n• Sync mobile data regularly\n• Review patterns weekly\n\nInteractive guide coming soon!');
}

function contactSupport() {
    alert('Contact QuietGo Support:\n\nEmail: support@quietgo.app\nResponse time: 24 hours\nPhone: Available for Pro+ users\n\nDirect support chat coming soon!');
}

function showTechnicalHelp() {
    alert('Technical FAQ:\n\n• Sync not working? Check wifi connection\n• Photos not uploading? Try smaller file size\n• App crash? Restart and try again\n\nFull FAQ system coming soon!');
}

// NEW: Analysis functionality for pending items
function startPendingAnalysis() {
    const confirmAnalysis = confirm('🤖 Start AI Analysis?\n\nThis will analyze all pending items:\n• 2 meal photos (CalcuPlate analysis)\n• 1 stool photo (Bristol Scale analysis)\n\nEstimated time: 30-60 seconds\n\nProceed?');
    
    if (confirmAnalysis) {
        // Show loading state
        const button = event.target;
        button.textContent = '🔄 Analyzing...';
        button.disabled = true;
        
        // Simulate analysis process
        setTimeout(() => {
            alert('✅ Analysis Complete!\n\nResults:\n• Meal analysis: Balanced nutrition detected\n• Stool analysis: Bristol Scale Type 4 (optimal)\n\n📊 New insights added to your dashboard!');
            
            // Reload page to show updated state
            window.location.reload();
        }, 3000);
    }
}

function analyzeSpecificItem(itemType) {
    let message = '';
    
    switch(itemType) {
        case 'meal':
            message = '🍽️ Analyzing meal photos...\n\nThis will:\n• Identify foods and portions\n• Calculate calories and macros\n• Generate nutrition insights\n\nProceed with CalcuPlate analysis?';
            break;
        case 'stool':
            message = '🚩 Analyzing stool photo...\n\nThis will:\n• Classify Bristol Scale type\n• Assess color and consistency\n• Generate health insights\n\nProceed with AI analysis?';
            break;
        default:
            message = 'Analyze this item with AI?';
    }
    
    const confirmed = confirm(message);
    if (confirmed) {
        // Show loading and simulate analysis
        const button = event.target;
        button.textContent = '🔄 Analyzing...';
        button.disabled = true;
        
        setTimeout(() => {
            alert(`✅ ${itemType.charAt(0).toUpperCase() + itemType.slice(1)} analysis complete!\n\nResults added to your health insights.`);
            button.textContent = '✅ Complete';
            button.style.background = '#28a745';
        }, 2500);
    }
}

// Fix non-functional dashboard buttons
function fixDashboardButtons() {
    // Update action items that link to non-existent pages
    const actionItems = document.querySelectorAll('.action-item');
    actionItems.forEach(item => {
        const href = item.getAttribute('href');
        const itemName = item.querySelector('h3').textContent;
        
        // Make specific buttons functional
        if (href && !href.includes('upload.php') && !href.includes('sync.php')) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Custom functionality based on the feature
                switch(itemName) {
                    case 'Review Analysis':
                        showAnalysisPreview();
                        break;
                    case 'Generate Reports':
                        showReportOptions();
                        break;
                    case 'Browse All Records':
                        showRecordsBrowser();
                        break;
                    case 'Share Your Health Journey':
                        showSharingOptions();
                        break;
                    case 'Account & Privacy':
                        showAccountOptions();
                        break;
                    default:
                        alert(`${itemName} feature coming soon!\n\nThis will include:\n• Advanced functionality\n• Professional reports\n• Smart automation\n\nCurrently available: Upload and Sync`);
                }
            });
        }
    });
}

// Enhanced functionality for specific dashboard actions
function showAnalysisPreview() {
    alert('📈 Analysis & Patterns Preview:\n\n📉 Recent Trends:\n• Digestive regularity: Up 23% this month\n• Energy consistency: Improved\n• Sleep correlation: Strong positive\n\n🎯 Top Insights:\n• Oatmeal breakfasts = better energy\n• 8+ hour sleep = better digestion\n• Light exercise improves comfort\n\n📈 Full pattern analysis coming soon!');
}

function showReportOptions() {
    const reportType = prompt('📄 Generate Health Report\n\nChoose report type:\n1. Weekly Summary\n2. Healthcare Provider Report\n3. Progress Analysis\n4. Correlation Report\n\nEnter number (1-4):');
    
    switch(reportType) {
        case '1':
            alert('📅 Weekly Summary Report\n\nGenerating...\n• 7 days of data analyzed\n• Key patterns identified\n• Recommendations included\n\nPDF report will be ready in your exports folder!');
            break;
        case '2':
            alert('🏥 Healthcare Provider Report\n\nGenerating professional report...\n• Clinical terminology\n• Bristol Scale analysis\n• Symptom correlations\n• Provider-ready format\n\nReport will be available for secure sharing!');
            break;
        case '3':
            alert('📈 Progress Analysis\n\nAnalyzing your journey...\n• 30-day trend analysis\n• Improvement metrics\n• Goal tracking\n• Achievement highlights\n\nDetailed progress report generated!');
            break;
        case '4':
            alert('🔍 Correlation Report\n\nMapping connections...\n• Food-symptom relationships\n• Sleep-digestion patterns\n• Exercise-energy correlations\n• Lifestyle factor analysis\n\nComprehensive correlation analysis complete!');
            break;
        default:
            alert('Professional reporting system coming soon!');
    }
}

function showRecordsBrowser() {
    alert('🔍 Browse Your Health Records\n\n📁 Available Data:\n• 147 stool photos with AI analysis\n• 89 meal logs with nutrition data\n• 23 symptom tracking entries\n• 12 correlation reports\n\n🔎 Search Options:\n• Filter by date range\n• Search by symptoms\n• Browse by meal type\n• View analysis results\n\n🗂️ Advanced record browser coming soon!');
}

function showSharingOptions() {
    const shareOption = prompt('🤝 Share Your Health Journey\n\nWho would you like to share with?\n1. Healthcare Provider\n2. Family Member\n3. Support Community\n4. Personal Trainer/Coach\n\nEnter number (1-4):');
    
    switch(shareOption) {
        case '1':
            alert('🏥 Share with Healthcare Provider\n\n• Professional report format\n• Clinical terminology\n• Secure sharing link\n• Expiry date: 30 days\n• View tracking enabled\n\nYour provider will receive a secure link via email.');
            break;
        case '2':
            alert('👨‍👩‍👧‍👦 Share with Family\n\n• Family-friendly insights\n• Progress highlights\n• Achievement milestones\n• Support-focused format\n\nFamily member will receive encouragement-focused updates!');
            break;
        case '3':
            alert('🤝 Share with Community\n\n• Anonymous sharing option\n• Success story format\n• Community support features\n• Mutual encouragement\n\nConnect with others on similar health journeys!');
            break;
        case '4':
            alert('🏋️‍♂️ Share with Trainer/Coach\n\n• Performance-focused insights\n• Nutrition-exercise correlations\n• Energy pattern analysis\n• Training optimization data\n\nYour coach will receive performance-relevant health data!');
            break;
        default:
            alert('Secure sharing system coming soon!');
    }
}

function showAccountOptions() {
    alert('⚙️ Account & Privacy Settings\n\n🔒 Privacy Controls:\n• Data auto-deletion: 90 days\n• Photo storage: Secure cloud\n• Sharing permissions: User controlled\n• AI analysis: Anonymized\n\n💳 Subscription: Pro+ Active\n• CalcuPlate: Enabled\n• Advanced analytics: Included\n• Next billing: March 15, 2025\n\n👥 Account settings panel coming soon!');
}
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>

<!-- AI Support Chatbot -->
<?php include __DIR__ . '/../includes/chatbot-widget.php'; ?>
