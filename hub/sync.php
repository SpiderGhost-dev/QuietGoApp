<?php
// Hub authentication check
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

// If admin but no hub session, create one for demo purposes
if ($isAdminLoggedIn && !isset($_SESSION['hub_user'])) {
    $_SESSION['hub_user'] = [
        'email' => 'admin@quietgo.app',
        'name' => 'Admin User',
        'login_time' => time(),
        'is_admin_impersonation' => true,
        'subscription_plan' => 'pro_plus'
    ];
}

$user = $_SESSION['hub_user'];
$userName = $user['name'] ?? 'User';
$subscriptionPlan = $user['subscription_plan'] ?? 'free';

// Simulate last sync time (in real app, get from database)
$lastSync = time() - (6 * 3600); // 6 hours ago
$lastSyncFormatted = date('M j, Y g:i A', $lastSync);

include __DIR__ . '/includes/header-hub.php';
?>

<style>
/* 🔄 SYNC INTERFACE WITH CONFLICT RESOLUTION */
:root {
    --success-color: #6c985f;
    --primary-blue: #4682b4;
    --accent-teal: #3c9d9b;
    --logo-rose: #d4a799;
    --slate-blue: #6a7ba2;
    --midnight-blue: #191970;
    --card-bg: #2a2a2a;
    --card-border: #404040;
    --text-primary: #ffffff;
    --text-secondary: #b0b0b0;
    --text-muted: #808080;
    --warning-bg: #8b5a2b;
    --danger-bg: #8b2635;
}

.sync-header {
    background: #1a1a1a;
    padding: 2rem 0;
    border-bottom: 1px solid var(--card-border);
    text-align: center;
}

.sync-title {
    color: var(--text-primary);
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.sync-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
    margin: 0;
}

.last-sync-info {
    background: var(--slate-blue);
    color: white;
    padding: 1rem 0;
    text-align: center;
    font-size: 1rem;
}

/* 🚀 SYNC PHASES */
.sync-phase {
    padding: 2rem 0;
    display: none;
}

.sync-phase.active {
    display: block;
}

.sync-phase-1 { background: #1a1a1a; }
.sync-phase-2 { background: #1a1a1a; }
.sync-phase-3 { background: var(--success-color); }

/* 🎯 PHASE 1: START SYNC */
.start-sync-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 3rem;
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

.sync-icon {
    font-size: 4rem;
    margin-bottom: 1.5rem;
    color: var(--primary-blue);
}

.start-sync-card h2 {
    color: var(--text-primary);
    font-size: 1.75rem;
    margin: 0 0 1rem 0;
}

.sync-description {
    color: var(--text-secondary);
    margin: 0 0 2rem 0;
    line-height: 1.6;
}

.sync-details {
    background: #333;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 1.5rem 0;
    text-align: left;
}

.sync-details h4 {
    color: var(--text-primary);
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
}

.sync-item {
    display: flex;
    justify-content: space-between;
    margin: 0.5rem 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.start-sync-btn {
    background: var(--primary-blue);
    color: white;
    padding: 1.25rem 3rem;
    border: none;
    border-radius: 12px;
    font-size: 1.2rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 6px 16px rgba(70, 130, 180, 0.3);
}

.start-sync-btn:hover {
    background: #3a5f8a;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(70, 130, 180, 0.4);
}

/* 📊 PHASE 2: UPLOAD PROGRESS */
.upload-progress-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 2.5rem;
    max-width: 800px;
    margin: 0 auto;
}

.progress-header {
    text-align: center;
    margin-bottom: 2rem;
}

.progress-header h2 {
    color: var(--text-primary);
    font-size: 1.75rem;
    margin: 0 0 0.5rem 0;
}

.progress-stats {
    color: var(--text-secondary);
    font-size: 1.1rem;
    margin: 0;
}

.progress-bar-container {
    background: #333;
    height: 12px;
    border-radius: 6px;
    overflow: hidden;
    margin: 1.5rem 0;
}

.progress-bar-fill {
    background: var(--primary-blue);
    height: 100%;
    transition: width 0.5s ease;
    border-radius: 6px;
}

.upload-items {
    display: grid;
    gap: 1rem;
    margin-top: 2rem;
}

.upload-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #333;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    border-left: 4px solid var(--success-color);
}

.upload-item.processing {
    border-left-color: var(--primary-blue);
}

.upload-item.complete {
    border-left-color: var(--success-color);
}

.item-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.item-icon {
    font-size: 1.5rem;
}

.item-details h4 {
    color: var(--text-primary);
    margin: 0;
    font-size: 1rem;
}

.item-details p {
    color: var(--text-secondary);
    margin: 0.25rem 0 0 0;
    font-size: 0.85rem;
}

.item-status {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* ⚠️ PHASE 3: CONFLICT RESOLUTION */
.conflict-resolution-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 2.5rem;
    max-width: 1000px;
    margin: 0 auto;
}

.resolution-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--card-border);
}

.resolution-header h2 {
    color: var(--text-primary);
    font-size: 1.75rem;
    margin: 0 0 0.5rem 0;
}

.resolution-summary {
    color: var(--text-secondary);
    margin: 0.5rem 0;
}

.auto-added-summary {
    background: rgba(108, 152, 95, 0.1);
    color: var(--success-color);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    border-left: 4px solid var(--success-color);
    margin: 1rem 0;
}

.conflicts-list {
    margin-top: 2rem;
}

.conflict-item {
    background: rgba(139, 90, 43, 0.1);
    border: 1px solid var(--warning-bg);
    border-radius: 12px;
    padding: 1.5rem;
    margin: 1rem 0;
}

.conflict-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.conflict-type {
    background: var(--warning-bg);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.conflict-comparison {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin: 1rem 0;
}

.comparison-side {
    background: #333;
    border-radius: 8px;
    padding: 1.5rem;
}

.comparison-side h4 {
    color: var(--text-primary);
    margin: 0 0 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.comparison-details {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.comparison-details p {
    margin: 0.5rem 0;
}

.conflict-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-top: 1.5rem;
}

.conflict-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-keep-existing {
    background: var(--slate-blue);
    color: white;
}

.btn-use-new {
    background: var(--success-color);
    color: white;
}

.btn-merge {
    background: var(--accent-teal);
    color: white;
}

.conflict-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* ✅ COMPLETE SYNC BUTTON */
.complete-sync-section {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--card-border);
}

.complete-sync-btn {
    background: var(--success-color);
    color: white;
    padding: 1.25rem 3rem;
    border: none;
    border-radius: 12px;
    font-size: 1.3rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(108, 152, 95, 0.3);
    opacity: 0.5;
    pointer-events: none;
}

.complete-sync-btn.ready {
    opacity: 1;
    pointer-events: auto;
    animation: pulse 2s infinite;
}

.complete-sync-btn.ready:hover {
    background: #5a7d4f;
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(108, 152, 95, 0.5);
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 8px 20px rgba(108, 152, 95, 0.3); }
    50% { box-shadow: 0 8px 30px rgba(108, 152, 95, 0.6); }
}

/* 🎉 SUCCESS PHASE */
.success-card {
    background: var(--success-color);
    color: white;
    border-radius: 16px;
    padding: 3rem;
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.success-card h2 {
    font-size: 2rem;
    margin: 0 0 1rem 0;
}

.success-summary {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0 0 2rem 0;
}

.success-actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.success-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.success-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-1px);
}

/* 📱 RESPONSIVE */
@media (max-width: 768px) {
    .conflict-comparison {
        grid-template-columns: 1fr;
    }
    
    .conflict-actions {
        flex-direction: column;
    }
    
    .success-actions {
        flex-direction: column;
    }
}
</style>

<main class="hub-main">
    <!-- Last Sync Info Bar -->
    <section class="last-sync-info">
        <div class="container">
            📱 Last sync: <?php echo $lastSyncFormatted; ?> • Ready to pull latest data from mobile app
        </div>
    </section>

    <!-- PHASE 1: START SYNC -->
    <section class="sync-phase sync-phase-1 active" id="phase-1">
        <section class="sync-header">
            <div class="container">
                <h1 class="sync-title">🔄 Full Sync</h1>
                <p class="sync-subtitle">Pull all data from your mobile app with smart conflict resolution</p>
            </div>
        </section>

        <section class="sync-interface" style="padding: 2rem 0;">
            <div class="container">
                <div class="start-sync-card">
                    <div class="sync-icon">📱</div>
                    <h2>Ready to Sync</h2>
                    <p class="sync-description">
                        This will pull all photos, logs, reports, and analysis from your mobile app. 
                        Any conflicts with existing data will be presented for your review.
                    </p>
                    
                    <div class="sync-details">
                        <h4>📋 What gets synced:</h4>
                        <div class="sync-item">
                            <span>📸 New stool & meal photos</span>
                            <span style="color: var(--success-color);">~15 items</span>
                        </div>
                        <div class="sync-item">
                            <span>📝 Health logs & entries</span>
                            <span style="color: var(--success-color);">~8 items</span>
                        </div>
                        <div class="sync-item">
                            <span>📊 Mobile app analysis reports</span>
                            <span style="color: var(--success-color);">~3 items</span>
                        </div>
                        <div class="sync-item">
                            <span>🔗 Shared events & correlations</span>
                            <span style="color: var(--success-color);">~2 items</span>
                        </div>
                    </div>
                    
                    <button class="start-sync-btn" onclick="startSyncProcess()">
                        🚀 Start Full Sync
                    </button>
                </div>
            </div>
        </section>
    </section>

    <!-- PHASE 2: UPLOAD PROGRESS -->
    <section class="sync-phase sync-phase-2" id="phase-2">
        <section class="sync-interface" style="padding: 2rem 0;">
            <div class="container">
                <div class="upload-progress-card">
                    <div class="progress-header">
                        <h2>📤 Uploading Data</h2>
                        <p class="progress-stats">
                            <span id="upload-current">0</span> of <span id="upload-total">28</span> items processed
                        </p>
                    </div>
                    
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" id="upload-progress-fill" style="width: 0%"></div>
                    </div>
                    
                    <div class="upload-items" id="upload-items">
                        <!-- Upload items will be populated dynamically -->
                    </div>
                </div>
            </div>
        </section>
    </section>

    <!-- PHASE 3: CONFLICT RESOLUTION -->
    <section class="sync-phase sync-phase-3" id="phase-3">
        <section class="sync-interface" style="padding: 2rem 0;">
            <div class="container">
                <div class="conflict-resolution-card">
                    <div class="resolution-header">
                        <h2>⚖️ Review Conflicts</h2>
                        <p class="resolution-summary" id="conflict-summary">
                            Found 3 items that need your decision
                        </p>
                        <div class="auto-added-summary">
                            ✅ 25 new items automatically added to your health history
                        </div>
                    </div>
                    
                    <div class="conflicts-list" id="conflicts-list">
                        <!-- Conflicts will be populated dynamically -->
                    </div>
                    
                    <div class="complete-sync-section">
                        <button class="complete-sync-btn" id="complete-sync-btn" onclick="completeSync()">
                            ✨ Complete Sync
                        </button>
                        <p style="color: var(--text-secondary); margin-top: 1rem;">
                            Review all conflicts above before completing sync
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </section>
</main>

<script>
// 🔄 FULL SYNC WORKFLOW WITH CONFLICT RESOLUTION
let currentPhase = 1;
let uploadQueue = [];
let conflicts = [];
let resolvedConflicts = 0;

// Mock data for demo
const mockUploadItems = [
    { type: 'stool_photo', name: 'morning_stool_sept24.jpg', size: '2.1MB', status: 'pending' },
    { type: 'meal_photo', name: 'lunch_salad_sept24.jpg', size: '1.8MB', status: 'pending' },
    { type: 'health_log', name: 'symptom_log_sept24.json', size: '15KB', status: 'pending' },
    { type: 'mobile_report', name: 'weekly_analysis_sept23.pdf', size: '450KB', status: 'pending' },
    { type: 'stool_photo', name: 'evening_stool_sept23.jpg', size: '1.9MB', status: 'pending' },
];

const mockConflicts = [
    {
        type: 'duplicate_photo',
        description: 'Same stool photo with different analysis results',
        existing: {
            name: 'morning_stool_sept24.jpg',
            date: 'Sept 24, 8:30 AM',
            analysis: 'Bristol Type 4, Normal color',
            confidence: '87%'
        },
        new: {
            name: 'morning_stool_sept24_mobile.jpg', 
            date: 'Sept 24, 8:30 AM',
            analysis: 'Bristol Type 3, Slightly darker',
            confidence: '91%'
        }
    },
    {
        type: 'updated_report',
        description: 'Weekly analysis report has been updated on mobile',
        existing: {
            name: 'weekly_analysis_sept23.pdf',
            date: 'Sept 23, 11:45 PM',
            version: '1.0',
            insights: '12 correlations found'
        },
        new: {
            name: 'weekly_analysis_sept23_v2.pdf',
            date: 'Sept 24, 9:15 AM', 
            version: '2.1',
            insights: '15 correlations found, 3 new recommendations'
        }
    }
];

function startSyncProcess() {
    // Move to Phase 2: Upload Progress
    currentPhase = 2;
    showPhase(2);
    
    // Initialize upload queue
    uploadQueue = [...mockUploadItems];
    
    // Start upload simulation
    simulateUploadProcess();
}

function showPhase(phaseNum) {
    document.querySelectorAll('.sync-phase').forEach(phase => phase.classList.remove('active'));
    document.getElementById(`phase-${phaseNum}`).classList.add('active');
}

async function simulateUploadProcess() {
    const uploadContainer = document.getElementById('upload-items');
    const progressFill = document.getElementById('upload-progress-fill');
    const currentCounter = document.getElementById('upload-current');
    
    // Create upload item elements
    uploadQueue.forEach((item, index) => {
        const itemEl = createUploadItemElement(item, index);
        uploadContainer.appendChild(itemEl);
    });
    
    // Process each item
    for (let i = 0; i < uploadQueue.length; i++) {
        const item = uploadQueue[i];
        const itemEl = document.querySelector(`[data-index="${i}"]`);
        
        // Update item status to processing
        itemEl.classList.add('processing');
        itemEl.querySelector('.item-status').textContent = '🔄 Processing...';
        
        // Wait for realistic processing time
        await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 1500));
        
        // Update item to complete
        itemEl.classList.remove('processing');
        itemEl.classList.add('complete');
        itemEl.querySelector('.item-status').textContent = '✅ Complete';
        
        // Update progress
        const progress = ((i + 1) / uploadQueue.length) * 100;
        progressFill.style.width = `${progress}%`;
        currentCounter.textContent = i + 1;
    }
    
    // Wait a moment then move to conflict resolution
    setTimeout(() => {
        currentPhase = 3;
        showPhase(3);
        loadConflictResolution();
    }, 1500);
}

function createUploadItemElement(item, index) {
    const itemEl = document.createElement('div');
    itemEl.className = 'upload-item';
    itemEl.setAttribute('data-index', index);
    
    const iconMap = {
        'stool_photo': '💧',
        'meal_photo': '🍽️', 
        'health_log': '📝',
        'mobile_report': '📊'
    };
    
    itemEl.innerHTML = `
        <div class="item-info">
            <div class="item-icon">${iconMap[item.type] || '📄'}</div>
            <div class="item-details">
                <h4>${item.name}</h4>
                <p>${item.size} • ${item.type.replace('_', ' ')}</p>
            </div>
        </div>
        <div class="item-status">⏳ Pending</div>
    `;
    
    return itemEl;
}

function loadConflictResolution() {
    conflicts = [...mockConflicts];
    const conflictsContainer = document.getElementById('conflicts-list');
    const conflictSummary = document.getElementById('conflict-summary');
    
    conflictSummary.textContent = `Found ${conflicts.length} items that need your decision`;
    
    conflicts.forEach((conflict, index) => {
        const conflictEl = createConflictElement(conflict, index);
        conflictsContainer.appendChild(conflictEl);
    });
    
    updateCompleteButton();
}

function createConflictElement(conflict, index) {
    const conflictEl = document.createElement('div');
    conflictEl.className = 'conflict-item';
    conflictEl.setAttribute('data-conflict-index', index);
    
    conflictEl.innerHTML = `
        <div class="conflict-header">
            <h3 style="color: var(--text-primary); margin: 0;">${conflict.description}</h3>
            <span class="conflict-type">${conflict.type.replace('_', ' ')}</span>
        </div>
        
        <div class="conflict-comparison">
            <div class="comparison-side">
                <h4>🏠 Current (Hub)</h4>
                <div class="comparison-details">
                    <p><strong>File:</strong> ${conflict.existing.name}</p>
                    <p><strong>Date:</strong> ${conflict.existing.date}</p>
                    ${conflict.existing.analysis ? `<p><strong>Analysis:</strong> ${conflict.existing.analysis}</p>` : ''}
                    ${conflict.existing.version ? `<p><strong>Version:</strong> ${conflict.existing.version}</p>` : ''}
                    ${conflict.existing.confidence ? `<p><strong>Confidence:</strong> ${conflict.existing.confidence}</p>` : ''}
                </div>
            </div>
            
            <div class="comparison-side">
                <h4>📱 New (Mobile)</h4>
                <div class="comparison-details">
                    <p><strong>File:</strong> ${conflict.new.name}</p>
                    <p><strong>Date:</strong> ${conflict.new.date}</p>
                    ${conflict.new.analysis ? `<p><strong>Analysis:</strong> ${conflict.new.analysis}</p>` : ''}
                    ${conflict.new.version ? `<p><strong>Version:</strong> ${conflict.new.version}</p>` : ''}
                    ${conflict.new.confidence ? `<p><strong>Confidence:</strong> ${conflict.new.confidence}</p>` : ''}
                </div>
            </div>
        </div>
        
        <div class="conflict-actions">
            <button class="conflict-btn btn-keep-existing" onclick="resolveConflict(${index}, 'keep')">
                🏠 Keep Current
            </button>
            <button class="conflict-btn btn-use-new" onclick="resolveConflict(${index}, 'new')">
                📱 Use New
            </button>
            ${conflict.type === 'duplicate_photo' ? '' : `
                <button class="conflict-btn btn-merge" onclick="resolveConflict(${index}, 'merge')">
                    🔗 Merge Both
                </button>
            `}
        </div>
    `;
    
    return conflictEl;
}

function resolveConflict(conflictIndex, decision) {
    const conflictEl = document.querySelector(`[data-conflict-index="${conflictIndex}"]`);
    
    // Disable all buttons in this conflict
    conflictEl.querySelectorAll('.conflict-btn').forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
    });
    
    // Highlight the chosen decision
    const chosenBtn = conflictEl.querySelector(
        decision === 'keep' ? '.btn-keep-existing' :
        decision === 'new' ? '.btn-use-new' : '.btn-merge'
    );
    chosenBtn.style.opacity = '1';
    chosenBtn.style.background = var(--success-color);
    chosenBtn.innerHTML = '✅ ' + chosenBtn.textContent;
    
    // Add visual indicator
    conflictEl.style.background = 'rgba(108, 152, 95, 0.05)';
    conflictEl.style.borderColor = 'var(--success-color)';
    
    resolvedConflicts++;
    updateCompleteButton();
}

function updateCompleteButton() {
    const completeBtn = document.getElementById('complete-sync-btn');
    
    if (resolvedConflicts >= conflicts.length) {
        completeBtn.classList.add('ready');
        completeBtn.innerHTML = '🎉 Complete Sync - All Conflicts Resolved';
    } else {
        completeBtn.classList.remove('ready');
        completeBtn.innerHTML = `⏳ Complete Sync (${resolvedConflicts}/${conflicts.length} resolved)`;
    }
}

function completeSync() {
    if (resolvedConflicts < conflicts.length) {
        alert('Please resolve all conflicts before completing sync');
        return;
    }
    
    // Show completion message
    document.querySelector('.sync-phase-3').innerHTML = `
        <section class="sync-interface" style="padding: 2rem 0;">
            <div class="container">
                <div class="success-card">
                    <div class="success-icon">🎉</div>
                    <h2>Sync Complete!</h2>
                    <p class="success-summary">
                        Successfully synced 28 items with ${conflicts.length} conflicts resolved. 
                        Your health data is now up to date.
                    </p>
                    <div class="success-actions">
                        <button class="success-btn" onclick="window.location.href='/hub/'">
                            🏠 Return to Dashboard
                        </button>
                        <button class="success-btn" onclick="window.location.href='/hub/patterns.php'">
                            📊 View Analysis
                        </button>
                        <button class="success-btn" onclick="window.location.href='/hub/reports.php'">
                            📄 Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </section>
    `;
}

// Initialize
console.log('🔄 Full Sync with Conflict Resolution loaded');
console.log('- Phase 1: Start sync with data preview');
console.log('- Phase 2: Upload progress with real-time status');  
console.log('- Phase 3: Smart conflict resolution');
console.log('- Phase 4: Completion with next steps');
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>