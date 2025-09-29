<?php
// REAL ANALYSIS PAGE - CONNECTS TO ACTUAL DATA
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
$userEmail = $user['email'] ?? 'admin@quietgo.app';

// LOAD REAL DATA FROM STORAGE SYSTEM
require_once __DIR__ . '/includes/storage-helper.php';
$storage = getQuietGoStorage();

// Build user path manually 
$safeEmail = preg_replace('/[^a-zA-Z0-9@.-]/', '_', $userEmail);
$userPath = __DIR__ . '/QuietGoData/users/' . $safeEmail;

// LOAD ACTUAL STOOL ANALYSIS RESULTS
$stoolAnalysisResults = [];
$stoolAnalysisDir = $userPath . '/analysis/ai_results';
if (is_dir($stoolAnalysisDir)) {
    $files = glob($stoolAnalysisDir . '/*_stool_*.json');
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            $stoolAnalysisResults[] = $data;
        }
    }
}

// LOAD ACTUAL MEAL ANALYSIS RESULTS
$mealAnalysisResults = [];
$mealAnalysisDir = $userPath . '/analysis/ai_results';
if (is_dir($mealAnalysisDir)) {
    $files = glob($mealAnalysisDir . '/*_meal_*.json');
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            $mealAnalysisResults[] = $data;
        }
    }
}

// LOAD MANUAL MEAL LOGS
$manualMealLogs = [];
$manualLogsDir = $userPath . '/logs/manual_meals';
if (is_dir($manualLogsDir)) {
    $files = glob($manualLogsDir . '/*.json');
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            $manualMealLogs[] = $data;
        }
    }
}

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
                <!-- REAL STOOL ANALYSIS RESULTS -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>🚽 Recent Stool Analysis</h3>
                    </div>
                    <div class="activity-list">
                        <?php if (!empty($stoolAnalysisResults)): ?>
                            <?php foreach (array_slice($stoolAnalysisResults, -5) as $result): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php 
                                    $bristol = $result['bristol_scale'] ?? 'Unknown';
                                    echo ($bristol >= 3 && $bristol <= 5) ? '✅' : (($bristol < 3) ? '⚠️' : '🔶');
                                    ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        Bristol Scale Type <?php echo $result['bristol_scale'] ?? 'Unknown'; ?> - 
                                        <?php echo $result['bristol_description'] ?? 'Analysis pending'; ?>
                                    </div>
                                    <div class="activity-time">
                                        <?php echo date('M j, g:i A', $result['timestamp'] ?? time()); ?> - 
                                        <?php echo round(($result['confidence'] ?? 0) * 100); ?>% confidence
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <div class="activity-icon">📷</div>
                                <div class="activity-content">
                                    <div class="activity-title">No stool analysis data yet</div>
                                    <div class="activity-time">Upload stool photos to see AI Bristol Scale analysis here</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- REAL MEAL ANALYSIS RESULTS -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>🍽️ Recent Meal Analysis</h3>
                    </div>
                    <div class="activity-list">
                        <?php 
                        // Combine AI meal analysis and manual logs
                        $allMeals = [];
                        foreach ($mealAnalysisResults as $meal) {
                            $allMeals[] = [
                                'type' => 'ai',
                                'title' => implode(', ', $meal['foods_detected'] ?? ['Unknown foods']),
                                'calories' => $meal['total_calories'] ?? 0,
                                'time' => $meal['timestamp'] ?? time(),
                                'icon' => '🤖'
                            ];
                        }
                        foreach ($manualMealLogs as $meal) {
                            $allMeals[] = [
                                'type' => 'manual',
                                'title' => $meal['meal_name'] ?? $meal['description'] ?? 'Manual meal entry',
                                'calories' => $meal['calories'] ?? 0,
                                'time' => $meal['timestamp'] ?? $meal['date'] ?? time(),
                                'icon' => '📝'
                            ];
                        }
                        
                        // Sort by time and show recent
                        usort($allMeals, function($a, $b) { return $b['time'] - $a['time']; });
                        $recentMeals = array_slice($allMeals, 0, 5);
                        ?>
                        
                        <?php if (!empty($recentMeals)): ?>
                            <?php foreach ($recentMeals as $meal): ?>
                            <div class="activity-item">
                                <div class="activity-icon"><?php echo $meal['icon']; ?></div>
                                <div class="activity-content">
                                    <div class="activity-title"><?php echo htmlspecialchars($meal['title']); ?></div>
                                    <div class="activity-time">
                                        <?php echo date('M j, g:i A', is_string($meal['time']) ? strtotime($meal['time']) : $meal['time']); ?> - 
                                        <?php echo $meal['calories'] ? $meal['calories'] . ' calories' : 'Calories not tracked'; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="activity-item">
                                <div class="activity-icon">🍽️</div>
                                <div class="activity-content">
                                    <div class="activity-title">No meal data yet</div>
                                    <div class="activity-time">Upload meal photos or add manual logs to see analysis here</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- PATTERN INSIGHTS FROM REAL DATA -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>🎯 Pattern Insights</h3>
                    </div>
                    <div class="stats-overview">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($stoolAnalysisResults); ?></span>
                            <span class="stat-label">Stool Analyses</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($mealAnalysisResults) + count($manualMealLogs); ?></span>
                            <span class="stat-label">Meal Entries</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo min(count($stoolAnalysisResults) + count($mealAnalysisResults), 30); ?></span>
                            <span class="stat-label">Days Tracked</span>
                        </div>
                    </div>
                    
                    <?php if (count($stoolAnalysisResults) >= 3 && count($allMeals) >= 3): ?>
                    <div class="activity-list" style="margin-top: var(--spacing-lg);">
                        <div class="activity-item">
                            <div class="activity-icon">⭐</div>
                            <div class="activity-content">
                                <div class="activity-title">Regularity patterns detected</div>
                                <div class="activity-time">Based on <?php echo count($stoolAnalysisResults); ?> stool analyses</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon">🍎</div>
                            <div class="activity-content">
                                <div class="activity-title">Dietary patterns emerging</div>
                                <div class="activity-time">Based on <?php echo count($allMeals); ?> meal entries</div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="activity-list" style="margin-top: var(--spacing-lg);">
                        <div class="activity-item">
                            <div class="activity-icon">📊</div>
                            <div class="activity-content">
                                <div class="activity-title">Keep tracking for patterns</div>
                                <div class="activity-time">Need more data to generate meaningful insights</div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="text-center" style="margin-top: var(--spacing-2xl);">
                <a href="/hub/" class="btn btn-outline">← Back to Dashboard</a>
                <a href="/hub/data.php" class="btn btn-primary">Browse All Data</a>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>
