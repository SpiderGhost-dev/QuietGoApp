<?php 
require_once __DIR__ . '/includes/admin-auth.php';
$adminUser = require_admin_login();

// Set title for this page
$pageTitle = "User Inspector & Management";

// Handle user search and management actions
$searchResults = null;
$selectedUser = null;
$actionResult = null;
$activeImpersonation = null;

// Check if currently impersonating
if (isset($_SESSION['impersonated_user'])) {
    $activeImpersonation = $_SESSION['impersonated_user'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'search_user':
            $searchResults = handleUserSearch($_POST);
            break;
        case 'load_user':
            $selectedUser = loadUserProfile($_POST['user_email']);
            break;
        case 'update_subscription':
            $actionResult = updateUserSubscription($_POST);
            if ($actionResult['success']) {
                $selectedUser = loadUserProfile($_POST['user_email']); // Reload user data
            }
            break;
        case 'impersonate_user':
            handleUserImpersonation($_POST['user_email']);
            break;
        case 'send_message':
            $actionResult = sendUserMessage($_POST);
            break;
        case 'start_impersonation':
            $actionResult = startUserImpersonation($_POST);
            break;
        case 'stop_impersonation':
            $actionResult = stopUserImpersonation();
            break;
    }
}

// PLACEHOLDER FUNCTIONS - Will connect to real user database post-launch
function handleUserSearch($postData) {
    $query = trim($postData['search_query'] ?? '');
    if (empty($query)) return null;
    
    // PLACEHOLDER: Simulate user search results
    return [
        [
            'email' => 'john.doe@example.com',
            'name' => 'John Doe',
            'subscription' => 'pro_plus',
            'journey' => 'clinical',
            'created' => '2024-01-15',
            'last_active' => '2 hours ago',
            'status' => 'active'
        ],
        [
            'email' => 'jane.smith@example.com', 
            'name' => 'Jane Smith',
            'subscription' => 'pro',
            'journey' => 'performance',
            'created' => '2024-02-20',
            'last_active' => '1 day ago',
            'status' => 'active'
        ],
        [
            'email' => 'mike.johnson@example.com',
            'name' => 'Mike Johnson', 
            'subscription' => 'free',
            'journey' => 'best_life',
            'created' => '2024-03-10',
            'last_active' => '5 minutes ago',
            'status' => 'trial_expired'
        ]
    ];
}

function loadUserProfile($email) {
    // PLACEHOLDER: Load complete user profile
    return [
        'email' => $email,
        'name' => 'John Doe',
        'subscription' => 'pro_plus',
        'journey' => 'clinical',
        'created' => '2024-01-15',
        'last_active' => '2 hours ago',
        'status' => 'active',
        'subscription_history' => [
            ['date' => '2024-01-15', 'action' => 'Started Pro trial', 'amount' => '$0.00'],
            ['date' => '2024-01-18', 'action' => 'Upgraded to Pro', 'amount' => '$4.99'],
            ['date' => '2024-02-01', 'action' => 'Added CalcuPlate', 'amount' => '$2.99'],
            ['date' => '2024-03-01', 'action' => 'Payment successful', 'amount' => '$7.98']
        ],
        'usage_stats' => [
            'photos_uploaded' => 47,
            'meal_photos' => 28,
            'stool_photos' => 19,
            'reports_generated' => 6,
            'last_upload' => '1 hour ago',
            'avg_weekly_usage' => '8.2 photos'
        ],
        'support_history' => [
            ['date' => '2024-02-15', 'type' => 'Email', 'subject' => 'CalcuPlate not recognizing food', 'status' => 'Resolved'],
            ['date' => '2024-01-22', 'type' => 'In-app', 'subject' => 'How to share with doctor?', 'status' => 'Resolved']
        ],
        'device_info' => [
            'platform' => 'iOS',
            'app_version' => '1.2.3',
            'last_sync' => '2 hours ago',
            'device_model' => 'iPhone 14 Pro'
        ]
    ];
}

function updateUserSubscription($postData) {
    $email = $postData['user_email'];
    $newPlan = $postData['new_plan'];
    
    // PLACEHOLDER: Update subscription in database
    return [
        'success' => true,
        'message' => "Successfully updated {$email} to {$newPlan} plan"
    ];
}

function handleUserImpersonation($email) {
    // This will be handled by the enhanced modal in the frontend
    return [
        'success' => true,
        'message' => 'Opening impersonation modal for ' . $email
    ];
}

function startUserImpersonation($postData) {
    $email = $postData['user_email'] ?? '';
    $journey = $postData['journey'] ?? 'best_life';
    $subscription = $postData['subscription'] ?? 'pro';
    
    // PLACEHOLDER: Load real user data and create impersonation session
    $_SESSION['impersonated_user'] = [
        'email' => $email,
        'name' => getUserNameFromEmail($email),
        'journey' => $journey,
        'subscription' => $subscription,
        'started_at' => time(),
        'admin_user' => $GLOBALS['adminUser']['username'] ?? 'admin',
        'impersonation_id' => uniqid('imp_')
    ];
    
    // Also set hub_user session for seamless hub access
    $_SESSION['hub_user'] = [
        'email' => $email,
        'name' => getUserNameFromEmail($email),
        'journey' => $journey,
        'subscription_plan' => $subscription,
        'is_admin_impersonation' => true,
        'login_time' => time()
    ];
    
    return [
        'success' => true,
        'message' => "Started impersonating {$email} as {$journey} journey with {$subscription} subscription"
    ];
}

function stopUserImpersonation() {
    if (!isset($_SESSION['impersonated_user'])) {
        return ['success' => false, 'message' => 'No active impersonation'];
    }
    
    $email = $_SESSION['impersonated_user']['email'];
    unset($_SESSION['impersonated_user']);
    unset($_SESSION['hub_user']);
    
    return [
        'success' => true,
        'message' => "Stopped impersonating {$email} - back to admin view"
    ];
}

function getUserNameFromEmail($email) {
    // PLACEHOLDER: Get real user name from database
    $names = [
        'john.doe@example.com' => 'John Doe',
        'jane.smith@example.com' => 'Jane Smith',
        'mike.johnson@example.com' => 'Mike Johnson'
    ];
    return $names[$email] ?? 'Test User';
}

function sendUserMessage($postData) {
    $email = $postData['user_email'];
    $subject = $postData['message_subject'];
    $message = $postData['message_content'];
    
    // PLACEHOLDER: Send message via email/push notification
    return [
        'success' => true,
        'message' => "Message sent to {$email}"
    ];
}

include __DIR__ . '/includes/header-admin.php'; 
?>

<style>
/* =================================================================
   TIER 2: USER INSPECTOR & MANAGEMENT STYLES
   ================================================================= */

/* Search Interface */
.search-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.search-form {
    display: flex;
    gap: var(--spacing-md);
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.search-input {
    flex: 1;
    background: var(--bg-color);
    border: 1px solid var(--border-color);
    color: var(--text-color);
    padding: var(--spacing-md);
    border-radius: var(--border-radius);
    font-size: 1rem;
}

.search-input:focus {
    outline: none;
    border-color: var(--green-color);
}

.search-btn {
    background: var(--green-color);
    color: var(--bg-color);
    border: none;
    padding: var(--spacing-md) var(--spacing-lg);
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.search-btn:hover {
    background: #7a8a78;
    transform: translateY(-1px);
}

/* Search Results */
.search-results {
    margin-top: var(--spacing-lg);
}

.user-result {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-md);
    background: rgba(140, 157, 138, 0.05);
    border: 1px solid rgba(140, 157, 138, 0.1);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-sm);
    transition: all 0.2s;
    cursor: pointer;
}

.user-result:hover {
    background: rgba(140, 157, 138, 0.1);
    transform: translateY(-1px);
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: var(--heading-color);
    margin-bottom: var(--spacing-xs);
}

.user-email {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-bottom: var(--spacing-xs);
}

.user-meta {
    display: flex;
    gap: var(--spacing-md);
    font-size: 0.75rem;
    color: var(--text-muted);
}

.user-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.action-btn {
    background: var(--accent-dark);
    color: white;
    border: none;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: var(--accent-color);
    transform: translateY(-1px);
}

.action-btn.danger {
    background: #ff6b6b;
}

.action-btn.danger:hover {
    background: #ff5252;
}

/* User Profile Detail */
.user-profile {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
}

.profile-section {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
}

.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
}

.profile-title {
    color: var(--heading-color);
    font-size: 1.25rem;
    margin: 0;
}

.profile-actions {
    display: flex;
    gap: var(--spacing-sm);
}

/* Subscription Management */
.subscription-controls {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--spacing-md);
    margin: var(--spacing-md) 0;
}

.subscription-btn {
    padding: var(--spacing-sm) var(--spacing-md);
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.btn-grant {
    background: var(--green-color);
    color: var(--bg-color);
}

.btn-revoke {
    background: #ff6b6b;
    color: white;
}

.btn-grant:hover {
    background: #7a8a78;
    transform: translateY(-1px);
}

.btn-revoke:hover {
    background: #ff5252;
    transform: translateY(-1px);
}

/* Profile Details */
.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-sm) 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.detail-value {
    color: var(--text-color);
    font-weight: 500;
    font-size: 0.875rem;
}

/* Status Badges */
.status-active { background: var(--green-color); color: white; }
.status-trial_expired { background: #ff9800; color: white; }
.status-cancelled { background: #666; color: white; }

.plan-free { background: #666; color: white; }
.plan-pro { background: var(--accent-dark); color: white; }
.plan-pro_plus { background: var(--green-color); color: white; }

.journey-clinical { background: #4a90e2; color: white; }
.journey-performance { background: #7ed321; color: white; }
.journey-best_life { background: #f5a623; color: white; }

/* History Tables */
.history-table {
    width: 100%;
    margin-top: var(--spacing-md);
}

.history-table th {
    background: var(--bg-color);
    color: var(--text-secondary);
    padding: var(--spacing-sm);
    text-align: left;
    font-size: 0.75rem;
    border-bottom: 1px solid var(--border-color);
}

.history-table td {
    padding: var(--spacing-sm);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    font-size: 0.8rem;
}

/* Communication Panel */
.communication-form {
    margin-top: var(--spacing-md);
}

.form-group {
    margin-bottom: var(--spacing-md);
}

.form-group label {
    display: block;
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xs);
    font-weight: 500;
    font-size: 0.875rem;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    background: var(--bg-color);
    border: 1px solid var(--border-color);
    color: var(--text-color);
    padding: var(--spacing-sm);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--green-color);
}

/* Action Results */
.action-result {
    padding: var(--spacing-md);
    border-radius: var(--border-radius);
    margin-bottom: var(--spacing-lg);
    font-size: 0.875rem;
}

.action-result.success {
    background: rgba(108, 152, 95, 0.1);
    border: 1px solid var(--green-color);
    color: var(--green-color);
}

.action-result.error {
    background: rgba(255, 107, 107, 0.1);
    border: 1px solid #ff6b6b;
    color: #ff6b6b;
}

/* Responsive */
@media (max-width: 768px) {
    .user-profile {
        grid-template-columns: 1fr;
        gap: var(--spacing-lg);
    }
    
    .subscription-controls {
        grid-template-columns: 1fr;
    }
    
    .search-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .user-result {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .user-actions {
        width: 100%;
        justify-content: flex-end;
    }
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal.open {
    display: flex;
}

.modal-content {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    margin-bottom: var(--spacing-lg);
    text-align: center;
}

.modal-header h2 {
    color: var(--heading-color);
    font-size: 1.5rem;
    margin: 0 0 var(--spacing-sm) 0;
}

.modal-header p {
    color: var(--text-secondary);
    margin: 0;
    font-size: 0.875rem;
}

.modal-actions {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xl);
}

.modal-actions button {
    flex: 1;
    padding: var(--spacing-md) var(--spacing-lg);
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-color);
}

.btn-outline:hover {
    background: var(--border-color);
}

.btn-primary {
    background: var(--green-color);
    color: var(--bg-color);
    border: none;
}

.btn-primary:hover {
    background: #7a8a78;
    transform: translateY(-1px);
}
</style>

<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">User Support</div>
            <a href="#search" class="nav-item active" onclick="showSection('search')">
                <span class="nav-item-icon">🔍</span>
                User Search & Impersonation
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-title">Navigation</div>
            <a href="/admin/dashboard.php" class="nav-item">
                <span class="nav-item-icon">🏠</span>
                Main Dashboard
            </a>
            <a href="/admin/business-analytics.php" class="nav-item">
                <span class="nav-item-icon">🧠</span>
                Business Analytics
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-content">
        <!-- TIER 2: USER SEARCH & INSPECTION -->
        <div id="search" class="content-section active">
            <div class="content-header">
                <h1 class="content-title">🔍 User Inspector & Search</h1>
                <p class="content-subtitle">Search, inspect, and manage QuietGo user accounts and subscriptions</p>
                <div class="placeholder-notice">
                    🚀 <strong>POST-LAUNCH:</strong> Connect to user database, subscription APIs, and communication systems
                </div>
            </div>
            
            <!-- Action Results -->
            <?php if ($actionResult): ?>
            <div class="action-result <?php echo $actionResult['success'] ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($actionResult['message']); ?>
            </div>
            <?php endif; ?>
            
            <!-- Active Impersonation Status -->
            <?php if ($activeImpersonation): ?>
            <div class="action-result" style="background: rgba(255, 107, 107, 0.1); border: 2px solid #ff6b6b; color: #ff6b6b;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong>⚠️ CURRENTLY IMPERSONATING:</strong> <?php echo htmlspecialchars($activeImpersonation['name']); ?> 
                        (<?php echo htmlspecialchars($activeImpersonation['email']); ?>) • 
                        <?php echo ucfirst(str_replace('_', ' ', $activeImpersonation['journey'])); ?> • 
                        <?php echo ucfirst(str_replace('_', '+', $activeImpersonation['subscription'])); ?>
                    </div>
                    <div style="display: flex; gap: var(--spacing-sm);">
                        <a href="/hub/" target="_blank" class="btn-outline-small">🔗 Test as User</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="stop_impersonation">
                            <button type="submit" class="btn-primary-small" style="background: #ff6b6b;" onclick="return confirm('Stop impersonating this user?')">🛑 Stop</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- User Search -->
            <div class="search-section">
                <h3 style="color: var(--heading-color); margin-bottom: var(--spacing-md);">👥 Find User Account</h3>
                <form method="POST" class="search-form">
                    <input type="hidden" name="action" value="search_user">
                    <input type="text" name="search_query" class="search-input" 
                           placeholder="Search by email, name, or user ID..." 
                           value="<?php echo htmlspecialchars($_POST['search_query'] ?? ''); ?>" required>
                    <button type="submit" class="search-btn">🔍 Search Users</button>
                </form>
                
                <div style="font-size: 0.875rem; color: var(--text-muted);">
                    <strong>Search Tips:</strong> Use email for exact match, partial names for broader results, or subscription status filters
                </div>
                
                <!-- Search Results -->
                <?php if ($searchResults): ?>
                <div class="search-results">
                    <h4 style="color: var(--heading-color); margin-bottom: var(--spacing-md);">
                        Found <?php echo count($searchResults); ?> user(s)
                    </h4>
                    
                    <?php foreach ($searchResults as $user): ?>
                    <div class="user-result">
                        <div class="user-info">
                            <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="user-meta">
                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $user['status'])); ?>
                                </span>
                                <span class="status-badge plan-<?php echo $user['subscription']; ?>">
                                    <?php echo ucfirst(str_replace('_', '+', $user['subscription'])); ?>
                                </span>
                                <span class="status-badge journey-<?php echo $user['journey']; ?>">
                                    <?php 
                                    $journeyNames = [
                                        'clinical' => 'Clinical Focus',
                                        'performance' => 'Peak Performance', 
                                        'best_life' => 'Best Life Mode'
                                    ];
                                    echo $journeyNames[$user['journey']] ?? $user['journey'];
                                    ?>
                                </span>
                                <span>Last active: <?php echo htmlspecialchars($user['last_active']); ?></span>
                            </div>
                        </div>
                        <div class="user-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="load_user">
                                <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                <button type="submit" class="action-btn">👤 View Profile</button>
                            </form>
                            <button type="button" class="action-btn" onclick="openImpersonationModal('<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['journey']); ?>', '<?php echo htmlspecialchars($user['subscription']); ?>')">🎭 Impersonate</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- User Profile Detail -->
            <?php if ($selectedUser): ?>
            <div class="user-profile">
                <!-- Account Overview -->
                <div class="profile-section">
                    <div class="profile-header">
                        <h3 class="profile-title">👤 Account Overview</h3>
                        <div class="profile-actions">
                            <button type="button" class="action-btn" onclick="openImpersonationModal('<?php echo htmlspecialchars($selectedUser['email']); ?>', '<?php echo htmlspecialchars($selectedUser['journey'] ?? 'best_life'); ?>', '<?php echo htmlspecialchars($selectedUser['subscription']); ?>')">🎭 Impersonate User</button>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Account Status</span>
                        <span class="status-badge status-<?php echo $selectedUser['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $selectedUser['status'])); ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Account Created</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['created']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Last Active</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['last_active']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Journey Type</span>
                        <span class="status-badge journey-<?php echo $selectedUser['journey']; ?>">
                            <?php 
                            $journeyNames = [
                                'clinical' => 'Clinical Focus',
                                'performance' => 'Peak Performance',
                                'best_life' => 'Best Life Mode'
                            ];
                            echo $journeyNames[$selectedUser['journey']] ?? $selectedUser['journey'];
                            ?>
                        </span>
                    </div>
                    
                    <!-- Device Info -->
                    <div style="margin-top: var(--spacing-lg); padding-top: var(--spacing-md); border-top: 1px solid var(--border-color);">
                        <h4 style="color: var(--heading-color); margin-bottom: var(--spacing-sm);">📱 Device Info</h4>
                        <div class="detail-row">
                            <span class="detail-label">Platform</span>
                            <span class="detail-value"><?php echo htmlspecialchars($selectedUser['device_info']['platform']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">App Version</span>
                            <span class="detail-value"><?php echo htmlspecialchars($selectedUser['device_info']['app_version']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Device Model</span>
                            <span class="detail-value"><?php echo htmlspecialchars($selectedUser['device_info']['device_model']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Last Sync</span>
                            <span class="detail-value"><?php echo htmlspecialchars($selectedUser['device_info']['last_sync']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Subscription Management -->
                <div class="profile-section">
                    <div class="profile-header">
                        <h3 class="profile-title">💳 Subscription Management</h3>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Current Plan</span>
                        <span class="status-badge plan-<?php echo $selectedUser['subscription']; ?>">
                            <?php echo ucfirst(str_replace('_', '+', $selectedUser['subscription'])); ?>
                        </span>
                    </div>
                    
                    <!-- Quick Subscription Actions -->
                    <div style="margin: var(--spacing-lg) 0;">
                        <h4 style="color: var(--heading-color); margin-bottom: var(--spacing-md);">⚡ Quick Actions</h4>
                        <div class="subscription-controls">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_subscription">
                                <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($selectedUser['email']); ?>">
                                <input type="hidden" name="new_plan" value="pro">
                                <button type="submit" class="subscription-btn btn-grant">🎁 Grant Pro</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_subscription">
                                <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($selectedUser['email']); ?>">
                                <input type="hidden" name="new_plan" value="pro_plus">
                                <button type="submit" class="subscription-btn btn-grant">🚀 Grant Pro+</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_subscription">
                                <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($selectedUser['email']); ?>">
                                <input type="hidden" name="new_plan" value="free">
                                <button type="submit" class="subscription-btn btn-revoke">📉 Revoke to Free</button>
                            </form>
                            <button type="button" class="subscription-btn btn-revoke" onclick="refundSubscription('<?php echo htmlspecialchars($selectedUser['email']); ?>')">💰 Process Refund</button>
                        </div>
                    </div>
                    
                    <!-- Subscription History -->
                    <h4 style="color: var(--heading-color); margin-bottom: var(--spacing-sm);">📋 Payment History</h4>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selectedUser['subscription_history'] as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['date']); ?></td>
                                <td><?php echo htmlspecialchars($entry['action']); ?></td>
                                <td><?php echo htmlspecialchars($entry['amount']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Usage Statistics -->
                <div class="profile-section">
                    <div class="profile-header">
                        <h3 class="profile-title">📊 Usage Statistics</h3>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total Photos</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['usage_stats']['photos_uploaded']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Meal Photos</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['usage_stats']['meal_photos']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Stool Photos</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['usage_stats']['stool_photos']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Reports Generated</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['usage_stats']['reports_generated']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Last Upload</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['usage_stats']['last_upload']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Weekly Average</span>
                        <span class="detail-value"><?php echo htmlspecialchars($selectedUser['usage_stats']['avg_weekly_usage']); ?></span>
                    </div>
                    
                    <!-- Quick Analysis -->
                    <div style="margin-top: var(--spacing-lg); padding: var(--spacing-md); background: rgba(140, 157, 138, 0.1); border-radius: var(--border-radius);">
                        <h4 style="color: var(--green-color); margin-bottom: var(--spacing-sm);">🎯 Engagement Analysis</h4>
                        <ul style="margin: 0; padding-left: var(--spacing-lg); color: var(--text-color); font-size: 0.875rem;">
                            <li><strong>High engagement:</strong> Above average usage patterns</li>
                            <li><strong>Balanced tracking:</strong> Good meal/stool photo ratio</li>
                            <li><strong>Report generation:</strong> Actively using insights</li>
                            <li><strong>Recent activity:</strong> Still actively using app</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Support History & Communication -->
                <div class="profile-section">
                    <div class="profile-header">
                        <h3 class="profile-title">🎧 Support & Communication</h3>
                    </div>
                    
                    <h4 style="color: var(--heading-color); margin-bottom: var(--spacing-sm);">📋 Support History</h4>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($selectedUser['support_history'] as $ticket): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($ticket['date']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['type']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Send Message -->
                    <h4 style="color: var(--heading-color); margin: var(--spacing-lg) 0 var(--spacing-sm) 0;">📧 Send Message</h4>
                    <form method="POST" class="communication-form">
                        <input type="hidden" name="action" value="send_message">
                        <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($selectedUser['email']); ?>">
                        
                        <div class="form-group">
                            <label>Subject</label>
                            <select name="message_subject">
                                <option value="">Select message type...</option>
                                <option value="Welcome & Onboarding">Welcome & Onboarding</option>
                                <option value="Subscription Issue">Subscription Issue</option>
                                <option value="Technical Support">Technical Support</option>
                                <option value="Feature Update">Feature Update</option>
                                <option value="Account Status">Account Status</option>
                                <option value="Custom">Custom Message</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message_content" placeholder="Type your message to the user..."></textarea>
                        </div>
                        
                        <button type="submit" class="search-btn">📧 Send Message</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Impersonation Modal -->
<div id="impersonation-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>🎭 Start User Impersonation</h2>
            <p>Configure impersonation settings and start testing as this user</p>
        </div>
        <form method="POST" id="impersonation-form">
            <input type="hidden" name="action" value="start_impersonation">
            
            <div class="form-group">
                <label>User Email</label>
                <input type="email" name="user_email" id="impersonation-email" readonly 
                       style="background: #333; cursor: not-allowed;">
            </div>
            
            <div class="form-group">
                <label>Journey Experience</label>
                <select name="journey" id="impersonation-journey">
                    <option value="clinical">🏥 Clinical Focus</option>
                    <option value="performance">💪 Peak Performance</option>
                    <option value="best_life" selected>✨ Best Life Mode</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Subscription Level</label>
                <select name="subscription" id="impersonation-subscription">
                    <option value="free">Free</option>
                    <option value="pro" selected>Pro</option>
                    <option value="pro_plus">Pro+</option>
                </select>
            </div>
            
            <div style="background: rgba(255, 206, 84, 0.1); border: 1px solid #ffce54; border-radius: var(--border-radius); padding: var(--spacing-md); margin: var(--spacing-md) 0;">
                <p style="margin: 0; font-size: 0.875rem; color: var(--text-color);">
                    <strong>⚠️ Important:</strong> Impersonation will create a session that lets you experience 
                    the app exactly as this user would see it. You can test their journey, subscription features, 
                    and access level. Click "Test as User" links to open the Hub in their context.
                </p>
            </div>
            
            <div class="modal-actions">
                <button type="button" onclick="closeImpersonationModal()" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">🎭 Start Impersonation</button>
            </div>
        </form>
    </div>
</div>

<script>
// TIER 2: User Inspector & Management Functions

// Impersonation Modal Management
function openImpersonationModal(email, journey, subscription) {
    document.getElementById('impersonation-email').value = email;
    document.getElementById('impersonation-journey').value = journey || 'best_life';
    document.getElementById('impersonation-subscription').value = subscription || 'pro';
    document.getElementById('impersonation-modal').style.display = 'flex';
}

function closeImpersonationModal() {
    document.getElementById('impersonation-modal').style.display = 'none';
}

// Modal Management
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Modal click-outside-to-close
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

// Navigation and other functions
function refundSubscription(email) {
    if (confirm(`Process refund for ${email}?\n\nThis will:\n• Cancel their subscription\n• Process refund via payment processor\n• Send confirmation email\n• Update their account status`)) {
        alert(`🔄 PLACEHOLDER: Processing refund for ${email}\n\nWill connect to:\n• Stripe refund API\n• Subscription management\n• Email notification system\n• Account status updates`);
    }
}

function showSection(sectionName) {
    // Navigation function for future sections
    console.log(`Navigate to ${sectionName} section`);
}

// Enhanced user search with filters
function applySearchFilters() {
    const filters = {
        plan: document.querySelector('select[name="plan_filter"]')?.value || 'all',
        journey: document.querySelector('select[name="journey_filter"]')?.value || 'all', 
        status: document.querySelector('select[name="status_filter"]')?.value || 'all'
    };
    
    console.log('🔍 PLACEHOLDER: Apply search filters', filters);
}

// Real-time user validation
function validateUserEmail(email) {
    // PLACEHOLDER: Validate email exists in database
    console.log(`🔍 Validating user email: ${email}`);
}

// Load user analytics on profile view
function loadUserAnalytics(email) {
    console.log(`📊 PLACEHOLDER: Loading analytics for ${email}`);
    // Will connect to user analytics API
}

// Bulk user operations
function bulkUserAction(action, userEmails) {
    console.log(`🔧 PLACEHOLDER: Bulk ${action} for users:`, userEmails);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Tier 2 User Inspector & Management loaded');
    console.log('💡 Features: User search, profile inspection, subscription management, communication');
    console.log('🚀 Post-launch: Connect to user database, payment APIs, communication systems');
});
</script>

<?php include __DIR__ . '/includes/footer-admin.php'; ?>