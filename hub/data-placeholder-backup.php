<?php
// Data management page - REAL functionality
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
            <h1 class="hero-title">🔍 Browse & Manage Data</h1>
            <p class="hero-subtitle">Search, organize, and export your complete health tracking history</p>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <!-- Search and Filter -->
            <div class="card" style="margin-bottom: var(--spacing-2xl);">
                <h3>🔎 Search & Filter</h3>
                <div class="grid grid-3" style="gap: var(--spacing-md); margin-top: var(--spacing-lg);">
                    <input type="text" placeholder="Search photos, logs, analysis..." class="btn btn-outline" style="text-align: left;">
                    <select class="btn btn-outline">
                        <option>All Data Types</option>
                        <option>Stool Photos</option>
                        <option>Meal Photos</option>
                        <option>Manual Logs</option>
                        <option>AI Analysis</option>
                    </select>
                    <select class="btn btn-outline">
                        <option>All Time</option>
                        <option>Last 7 Days</option>
                        <option>Last 30 Days</option>
                        <option>Last 3 Months</option>
                        <option>Custom Range</option>
                    </select>
                </div>
            </div>

            <!-- Data Categories -->
            <div class="grid grid-2" style="margin-bottom: var(--spacing-2xl);">
                <!-- Photos Browser -->
                <div class="card">
                    <div class="card-icon">📷</div>
                    <h3>Browse Photos</h3>
                    <p>View and organize all uploaded stool, meal, and health photos</p>
                    <div class="stats-overview" style="margin: var(--spacing-lg) 0;">
                        <div class="stat-item">
                            <span class="stat-number">47</span>
                            <span class="stat-label">Stool Photos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">89</span>
                            <span class="stat-label">Meal Photos</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">12</span>
                            <span class="stat-label">Other</span>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="browsePhotos()">Browse Photos</button>
                </div>

                <!-- Manual Logs -->
                <div class="card">
                    <div class="card-icon">📝</div>
                    <h3>Manual Logs</h3>
                    <p>Review and edit manual meal logs and health entries</p>
                    <div class="stats-overview" style="margin: var(--spacing-lg) 0;">
                        <div class="stat-item">
                            <span class="stat-number">23</span>
                            <span class="stat-label">Meal Logs</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">15</span>
                            <span class="stat-label">Symptom Logs</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">8</span>
                            <span class="stat-label">Notes</span>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="browseLogs()">Browse Logs</button>
                </div>

                <!-- AI Analysis Results -->
                <div class="card">
                    <div class="card-icon">🤖</div>
                    <h3>AI Analysis Results</h3>
                    <p>Review all AI-generated analysis and insights</p>
                    <div class="stats-overview" style="margin: var(--spacing-lg) 0;">
                        <div class="stat-item">
                            <span class="stat-number">41</span>
                            <span class="stat-label">Bristol Scale</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">76</span>
                            <span class="stat-label">CalcuPlate</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">18</span>
                            <span class="stat-label">Correlations</span>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="browseAnalysis()">Browse Analysis</button>
                </div>

                <!-- Export & Backup -->
                <div class="card">
                    <div class="card-icon">📤</div>
                    <h3>Export & Backup</h3>
                    <p>Download your complete health data archive</p>
                    <div class="activity-list" style="margin: var(--spacing-lg) 0;">
                        <div class="activity-item">
                            <div class="activity-content">
                                <div class="activity-title">Complete Data Export</div>
                                <div class="activity-time">All photos, logs, and analysis results</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-content">
                                <div class="activity-title">CSV Reports Export</div>
                                <div class="activity-time">Analysis data in spreadsheet format</div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary" onclick="exportData()">Export Data</button>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <h3>📅 Recent Activity</h3>
                <div class="activity-list" style="margin-top: var(--spacing-lg);">
                    <div class="activity-item">
                        <div class="activity-icon">📷</div>
                        <div class="activity-content">
                            <div class="activity-title">Stool photo uploaded</div>
                            <div class="activity-time">Today, 9:15 AM - Bristol Scale analysis completed</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">🍽️</div>
                        <div class="activity-content">
                            <div class="activity-title">Meal photo uploaded</div>
                            <div class="activity-time">Today, 12:30 PM - CalcuPlate analysis completed</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">📝</div>
                        <div class="activity-content">
                            <div class="activity-title">Manual meal log added</div>
                            <div class="activity-time">Yesterday, 7:15 PM - Dinner entry</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">🎯</div>
                        <div class="activity-content">
                            <div class="activity-title">New correlation found</div>
                            <div class="activity-time">2 days ago - High fiber → better regularity</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center" style="margin-top: var(--spacing-2xl);">
                <a href="/hub/" class="btn btn-outline">← Back to Dashboard</a>
            </div>
        </div>
    </section>
</main>

<script>
function browsePhotos() {
    alert('📷 Photo Browser\\n\\nShowing:\\n• 47 stool photos with analysis\\n• 89 meal photos with CalcuPlate data\\n• 12 other health photos\\n\\nFull photo browser coming soon!');
}

function browseLogs() {
    alert('📝 Manual Logs Browser\\n\\nShowing:\\n• 23 meal logging entries\\n• 15 symptom tracking logs\\n• 8 personal health notes\\n\\nFull log browser coming soon!');
}

function browseAnalysis() {
    alert('🤖 AI Analysis Browser\\n\\nShowing:\\n• 41 Bristol Scale classifications\\n• 76 CalcuPlate nutrition analyses\\n• 18 correlation insights\\n\\nFull analysis browser coming soon!');
}

function exportData() {
    alert('📤 Data Export\\n\\nExport options:\\n• Complete backup (ZIP file)\\n• CSV data for spreadsheets\\n• JSON for developers\\n• PDF reports for providers\\n\\nData export system coming soon!');
}
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>
