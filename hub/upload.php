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

// Get user subscription status for feature access
$user = $_SESSION['hub_user'];
$subscriptionPlan = $user['subscription_plan'] ?? 'free';
$hasCalcuPlate = in_array($subscriptionPlan, ['pro_plus', 'calcuplate']);
$isProUser = in_array($subscriptionPlan, ['pro', 'pro_plus']);

// Free users shouldn't have Hub access - redirect them
if (!$isProUser) {
    header('Location: /hub/login.php?message=pro_required');
    exit;
}

// Handle individual file uploads
$uploadResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['health_item'])) {
    $uploadResult = handleIndividualUpload($_FILES['health_item'], $_POST);
}

function handleIndividualUpload($file, $postData) {
    $uploadDir = __DIR__ . '/uploads/individual/' . date('Y-m-d') . '/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'status' => 'success',
                'filename' => $filename,
                'filepath' => $filepath,
                'category' => $postData['category'] ?? 'unknown',
                'context' => $postData['context'] ?? '',
                'size' => $file['size']
            ];
        }
    }
    
    return ['status' => 'error', 'message' => 'Upload failed'];
}

include __DIR__ . '/includes/header-hub.php';
?>

<style>
/* 📤 INDIVIDUAL UPLOAD INTERFACE */
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
}

.upload-header {
    background: #1a1a1a;
    padding: 2rem 0;
    border-bottom: 1px solid var(--card-border);
    text-align: center;
}

.upload-title {
    color: var(--text-primary);
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.upload-subtitle {
    color: var(--text-secondary);
    font-size: 1.2rem;
    margin: 0 0 1rem 0;
}

.upload-description {
    color: var(--text-muted);
    font-size: 1rem;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.5;
}

/* 📂 UPLOAD CATEGORIES */
.upload-interface {
    padding: 3rem 0;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.category-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 2rem;
    transition: all 0.3s ease;
    cursor: pointer;
}

.category-card:hover {
    background: #333;
    border-color: var(--primary-blue);
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(70, 130, 180, 0.2);
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.category-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--primary-blue);
}

.category-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.badge-photos { background: var(--success-color); }
.badge-logs { background: var(--primary-blue); }
.badge-reports { background: var(--accent-teal); }
.badge-events { background: var(--logo-rose); }
.badge-files { background: var(--slate-blue); }

.category-card h3 {
    color: var(--text-primary);
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.75rem 0;
}

.category-description {
    color: var(--text-secondary);
    margin: 0 0 1.5rem 0;
    line-height: 1.5;
}

.category-items {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.category-item {
    color: var(--text-muted);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.category-item::before {
    content: '•';
    color: var(--primary-blue);
    font-weight: bold;
}

.category-actions {
    margin-top: 1.5rem;
    display: flex;
    gap: 1rem;
}

.category-btn {
    flex: 1;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary-cat {
    background: var(--primary-blue);
    color: white;
}

.btn-outline-cat {
    background: transparent;
    border: 1px solid var(--card-border);
    color: var(--text-secondary);
}

.category-btn:hover {
    transform: translateY(-1px);
}

.btn-primary-cat:hover {
    background: #3a5f8a;
}

.btn-outline-cat:hover {
    border-color: var(--primary-blue);
    color: var(--text-primary);
}

/* 🔍 MOBILE CONNECTION PLACEHOLDER */
.mobile-connection {
    background: var(--slate-blue);
    color: white;
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    margin: 2rem 0;
}

.mobile-connection h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
}

.mobile-connection p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

/* 📤 UPLOAD MODAL */
.upload-modal {
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

.modal-content {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 2.5rem;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    text-align: center;
    margin-bottom: 2rem;
}

.modal-header h2 {
    color: var(--text-primary);
    font-size: 1.75rem;
    margin: 0 0 0.5rem 0;
}

.modal-subtitle {
    color: var(--text-secondary);
    margin: 0;
}

.upload-area {
    border: 2px dashed var(--card-border);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    margin: 1.5rem 0;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: var(--primary-blue);
    background: rgba(70, 130, 180, 0.05);
}

.upload-area.drag-over {
    border-color: var(--success-color);
    background: rgba(108, 152, 95, 0.1);
}

.upload-icon {
    font-size: 3rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.upload-instructions {
    color: var(--text-secondary);
    margin: 1rem 0;
}

.context-form {
    margin: 1.5rem 0;
}

.form-group {
    margin: 1rem 0;
}

.form-group label {
    display: block;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    background: #333;
    border: 1px solid var(--card-border);
    color: var(--text-primary);
    padding: 0.75rem;
    border-radius: 8px;
    font-size: 0.95rem;
}

.form-group textarea {
    min-height: 80px;
    resize: vertical;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.modal-btn {
    flex: 1;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-modal-primary {
    background: var(--success-color);
    color: white;
}

.btn-modal-secondary {
    background: var(--slate-blue);
    color: white;
}

.btn-modal-cancel {
    background: transparent;
    border: 1px solid var(--card-border);
    color: var(--text-secondary);
}

.modal-btn:hover {
    transform: translateY(-1px);
}

/* 📱 RESPONSIVE */
@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .category-actions {
        flex-direction: column;
    }
    
    .modal-actions {
        flex-direction: column;
    }
}
</style>

<main class="hub-main">
    <!-- Upload Results -->
    <?php if ($uploadResult): ?>
    <section class="upload-results" style="background: var(--success-color); color: white; padding: 1rem 0; text-align: center;">
        <div class="container">
            <?php if ($uploadResult['status'] === 'success'): ?>
                ✅ Successfully uploaded <?php echo htmlspecialchars($uploadResult['filename']); ?>
            <?php else: ?>
                ❌ Upload failed: <?php echo htmlspecialchars($uploadResult['message']); ?>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Header Section -->
    <section class="upload-header">
        <div class="container">
            <h1 class="upload-title">📤 Upload Individual Items</h1>
            <p class="upload-subtitle">Add specific photos, logs, or reports from your mobile app</p>
            <p class="upload-description">
                <?php if ($hasCalcuPlate): ?>
                    Upload stool photos for AI Bristol Scale analysis and meal photos for instant CalcuPlate parsing with auto-logging.
                <?php else: ?>
                    Upload stool photos for AI Bristol Scale analysis and meal photos for manual nutrition logging. Upgrade to Pro+ for CalcuPlate automatic meal parsing.
                <?php endif; ?>
            </p>
        </div>
    </section>

    <!-- Mobile App Connection Status -->
    <section class="mobile-connection-section" style="padding: 1rem 0; background: #1a1a1a;">
        <div class="container">
            <div class="mobile-connection">
                <h4>📱 Mobile App Connection</h4>
                <p>Mobile app integration coming soon. Currently using local file uploads for development.</p>
            </div>
        </div>
    </section>

    <!-- Upload Categories -->
    <section class="upload-interface">
        <div class="container">
            <div class="categories-grid">
                <!-- Photos Category -->
                <article class="category-card" onclick="openUploadModal('photos')">
                    <div class="category-header">
                        <div class="category-icon">📸</div>
                        <span class="category-badge badge-photos">Most Popular</span>
                    </div>
                    <h3>Photos</h3>
                    <p class="category-description">
                        Upload stool photos for AI Bristol Scale analysis and meal photos for <?php echo $hasCalcuPlate ? 'instant CalcuPlate parsing' : 'manual nutrition logging'; ?>
                    </p>
                    <div class="category-items">
                        <div class="category-item">Stool photos with AI Bristol Scale analysis</div>
                        <div class="category-item">Meal photos <?php echo $hasCalcuPlate ? 'with CalcuPlate auto-parsing and logging' : 'for manual nutrition logging'; ?></div>
                        <div class="category-item">Symptom photos (skin, inflammation, etc.)</div>
                        <div class="category-item">Supplement/medication photos</div>
                        <div class="category-item">Progress comparison photos</div>
                        <?php if (!$hasCalcuPlate): ?>
                        <div class="category-item" style="color: var(--logo-rose); font-weight: 600;">✨ CalcuPlate: Upgrade to Pro+ for instant meal AI</div>
                        <?php endif; ?>
                    </div>
                    <div class="category-actions">
                        <button class="category-btn btn-primary-cat">📸 Upload Photo</button>
                        <button class="category-btn btn-outline-cat" onclick="event.stopPropagation(); showMobileItems('photos')">📱 From Mobile</button>
                    </div>
                </article>

                <!-- Logs & Entries Category -->
                <article class="category-card" onclick="openUploadModal('logs')">
                    <div class="category-header">
                        <div class="category-icon">📝</div>
                        <span class="category-badge badge-logs">Daily Tracking</span>
                    </div>
                    <h3>Logs & Entries</h3>
                    <p class="category-description">Import health logs, symptom tracking, medication records, and activity data</p>
                    <div class="category-items">
                        <div class="category-item">Symptom logs with severity ratings</div>
                        <div class="category-item">Medication & supplement tracking</div>
                        <div class="category-item">Sleep quality and duration logs</div>
                        <div class="category-item">Exercise and activity entries</div>
                        <div class="category-item">Mood and stress level tracking</div>
                    </div>
                    <div class="category-actions">
                        <button class="category-btn btn-primary-cat">📝 Upload Log</button>
                        <button class="category-btn btn-outline-cat" onclick="event.stopPropagation(); showMobileItems('logs')">📱 From Mobile</button>
                    </div>
                </article>

                <!-- Reports & Analysis Category -->
                <article class="category-card" onclick="openUploadModal('reports')">
                    <div class="category-header">
                        <div class="category-icon">📊</div>
                        <span class="category-badge badge-reports">AI Insights</span>
                    </div>
                    <h3>Reports & Analysis</h3>
                    <p class="category-description">Upload mobile-generated analysis reports, correlations, and health insights</p>
                    <div class="category-items">
                        <div class="category-item">Weekly pattern analysis reports</div>
                        <div class="category-item">Food-symptom correlation findings</div>
                        <div class="category-item">AI-generated health recommendations</div>
                        <div class="category-item">Trend analysis and predictions</div>
                        <div class="category-item">Comparative health metrics</div>
                    </div>
                    <div class="category-actions">
                        <button class="category-btn btn-primary-cat">📊 Upload Report</button>
                        <button class="category-btn btn-outline-cat" onclick="event.stopPropagation(); showMobileItems('reports')">📱 From Mobile</button>
                    </div>
                </article>

                <!-- Events & Tracking Category -->
                <article class="category-card" onclick="openUploadModal('events')">
                    <div class="category-header">
                        <div class="category-icon">📅</div>
                        <span class="category-badge badge-events">Scheduling</span>
                    </div>
                    <h3>Events & Tracking</h3>
                    <p class="category-description">Import calendar events, shared tracking data, and correlation timelines</p>
                    <div class="category-items">
                        <div class="category-item">Health-related calendar events</div>
                        <div class="category-item">Shared tracking with healthcare providers</div>
                        <div class="category-item">Appointment summaries and notes</div>
                        <div class="category-item">Treatment timeline data</div>
                        <div class="category-item">Goal tracking and milestones</div>
                    </div>
                    <div class="category-actions">
                        <button class="category-btn btn-primary-cat">📅 Upload Event</button>
                        <button class="category-btn btn-outline-cat" onclick="event.stopPropagation(); showMobileItems('events')">📱 From Mobile</button>
                    </div>
                </article>

                <!-- Other Health Files Category -->
                <article class="category-card" onclick="openUploadModal('files')">
                    <div class="category-header">
                        <div class="category-icon">📄</div>
                        <span class="category-badge badge-files">Any Format</span>
                    </div>
                    <h3>Other Health Files</h3>
                    <p class="category-description">Upload any health-related files in any format for storage and analysis</p>
                    <div class="category-items">
                        <div class="category-item">Lab results and test reports (PDF, images)</div>
                        <div class="category-item">Medical records and summaries</div>
                        <div class="category-item">Prescription and treatment plans</div>
                        <div class="category-item">Research articles and references</div>
                        <div class="category-item">Custom data exports from other apps</div>
                    </div>
                    <div class="category-actions">
                        <button class="category-btn btn-primary-cat">📄 Upload File</button>
                        <button class="category-btn btn-outline-cat" onclick="event.stopPropagation(); showMobileItems('files')">📱 From Mobile</button>
                    </div>
                </article>

                <!-- Pro+ Upgrade for Pro Users -->
                <?php if (!$hasCalcuPlate): ?>
                <article class="category-card" style="border: 2px solid var(--success-color); background: rgba(108, 152, 95, 0.05);">
                    <div class="category-header">
                        <div class="category-icon" style="color: var(--success-color);">🚀</div>
                        <span class="category-badge" style="background: var(--success-color);">Upgrade to Pro+</span>
                    </div>
                    <h3 style="color: var(--success-color);">Add CalcuPlate Meal AI</h3>
                    <p class="category-description">Upgrade to Pro+ to unlock instant meal photo parsing and automatic nutrition logging</p>
                    <div class="category-items">
                        <div class="category-item">Automatic food recognition from photos</div>
                        <div class="category-item">Instant portion size estimation</div>
                        <div class="category-item">Auto-calculated calories and macros</div>
                        <div class="category-item">Meal-to-symptom correlation analysis</div>
                        <div class="category-item">No more manual meal logging</div>
                    </div>
                    <div class="category-actions">
                        <button class="category-btn" style="background: var(--success-color); color: white;" onclick="upgradeToProPlusPage()">⚡ Upgrade to Pro+ (+$3.00/mo)</button>
                    </div>
                </article>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Upload Modal -->
    <div class="upload-modal" id="upload-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Upload Item</h2>
                <p class="modal-subtitle" id="modal-subtitle">Select and analyze your health data</p>
            </div>

            <form id="upload-form" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="upload-category" name="category" value="">
                
                <div class="upload-area" onclick="document.getElementById('file-input').click()">
                    <div class="upload-icon" id="upload-icon">📁</div>
                    <h4>Choose File</h4>
                    <p class="upload-instructions">Click here or drag and drop your file</p>
                    <input type="file" id="file-input" name="health_item" accept="*/*" hidden>
                </div>

                <div class="context-form" id="context-form">
                    <!-- Context fields will be populated based on category -->
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn btn-modal-cancel" onclick="closeUploadModal()">Cancel</button>
                    <button type="button" class="modal-btn btn-modal-secondary">🔍 Preview</button>
                    <button type="submit" class="modal-btn btn-modal-primary">🚀 Upload & Analyze</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
// 📤 INDIVIDUAL UPLOAD SYSTEM FOR PRO USERS
let currentCategory = '';
let selectedFile = null;
const hasCalcuPlate = <?php echo $hasCalcuPlate ? 'true' : 'false'; ?>;

const categoryConfigs = {
    photos: {
        title: '📸 Upload Photo',
        subtitle: 'Add stool, meal, or symptom photos for AI analysis',
        icon: '📸',
        accept: 'image/*',
        contexts: ['time', 'symptoms', 'notes']
    },
    logs: {
        title: '📝 Upload Health Log',
        subtitle: 'Import symptom tracking, medication, or activity data',
        icon: '📝',
        accept: '.json,.csv,.txt,.log',
        contexts: ['date_range', 'type', 'notes']
    },
    reports: {
        title: '📊 Upload Analysis Report',
        subtitle: 'Import mobile-generated insights and correlations',
        icon: '📊',
        accept: '.pdf,.json,.csv',
        contexts: ['report_type', 'date_generated', 'notes']
    },
    events: {
        title: '📅 Upload Health Event',
        subtitle: 'Import calendar events and tracking data',
        icon: '📅',
        accept: '.ics,.json,.csv',
        contexts: ['event_type', 'date', 'participants', 'notes']
    },
    files: {
        title: '📄 Upload Health File',
        subtitle: 'Upload any health-related file in any format',
        icon: '📄',
        accept: '*/*',
        contexts: ['file_type', 'source', 'date', 'notes']
    }
};

function openUploadModal(category) {
    currentCategory = category;
    const config = categoryConfigs[category];
    
    document.getElementById('modal-title').textContent = config.title;
    document.getElementById('modal-subtitle').textContent = config.subtitle;
    document.getElementById('upload-icon').textContent = config.icon;
    document.getElementById('upload-category').value = category;
    document.getElementById('file-input').accept = config.accept;
    
    // Build context form
    buildContextForm(config.contexts);
    
    document.getElementById('upload-modal').style.display = 'flex';
}

function buildContextForm(contexts) {
    const form = document.getElementById('context-form');
    form.innerHTML = '';
    
    contexts.forEach(context => {
        const group = document.createElement('div');
        group.className = 'form-group';
        
        switch(context) {
            case 'time':
                group.innerHTML = `
                    <label>When was this?</label>
                    <select name="context_time">
                        <option value="">Select time...</option>
                        <option value="morning">Morning</option>
                        <option value="afternoon">Afternoon</option>
                        <option value="evening">Evening</option>
                        <option value="night">Night</option>
                    </select>
                `;
                break;
            case 'symptoms':
                group.innerHTML = `
                    <label>Any symptoms?</label>
                    <input type="text" name="context_symptoms" placeholder="e.g., pain, urgency, bloating, none">
                `;
                break;
            case 'notes':
                group.innerHTML = `
                    <label>Additional notes</label>
                    <textarea name="context_notes" placeholder="Any additional context or details..."></textarea>
                `;
                break;
            case 'date_range':
                group.innerHTML = `
                    <label>Data date range</label>
                    <input type="text" name="context_date_range" placeholder="e.g., Sept 20-24, Last week">
                `;
                break;
            case 'type':
                group.innerHTML = `
                    <label>Log type</label>
                    <select name="context_type">
                        <option value="">Select type...</option>
                        <option value="symptoms">Symptom Log</option>
                        <option value="medication">Medication Log</option>
                        <option value="activity">Activity Log</option>
                        <option value="sleep">Sleep Log</option>
                        <option value="mood">Mood Log</option>
                    </select>
                `;
                break;
            case 'report_type':
                group.innerHTML = `
                    <label>Report type</label>
                    <select name="context_report_type">
                        <option value="">Select type...</option>
                        <option value="weekly">Weekly Analysis</option>
                        <option value="correlation">Correlation Report</option>
                        <option value="trends">Trend Analysis</option>
                        <option value="recommendations">AI Recommendations</option>
                    </select>
                `;
                break;
            case 'date_generated':
                group.innerHTML = `
                    <label>Date generated</label>
                    <input type="date" name="context_date_generated">
                `;
                break;
            case 'event_type':
                group.innerHTML = `
                    <label>Event type</label>
                    <select name="context_event_type">
                        <option value="">Select type...</option>
                        <option value="appointment">Medical Appointment</option>
                        <option value="treatment">Treatment Session</option>
                        <option value="milestone">Health Milestone</option>
                        <option value="shared_tracking">Shared Tracking</option>
                    </select>
                `;
                break;
            case 'date':
                group.innerHTML = `
                    <label>Date</label>
                    <input type="date" name="context_date">
                `;
                break;
            case 'participants':
                group.innerHTML = `
                    <label>Participants/Providers</label>
                    <input type="text" name="context_participants" placeholder="e.g., Dr. Smith, Nutritionist Jane">
                `;
                break;
            case 'file_type':
                group.innerHTML = `
                    <label>File type</label>
                    <select name="context_file_type">
                        <option value="">Select type...</option>
                        <option value="lab_results">Lab Results</option>
                        <option value="medical_records">Medical Records</option>
                        <option value="prescriptions">Prescriptions</option>
                        <option value="research">Research/Articles</option>
                        <option value="data_export">Data Export</option>
                    </select>
                `;
                break;
            case 'source':
                group.innerHTML = `
                    <label>Source</label>
                    <input type="text" name="context_source" placeholder="e.g., Lab Corp, Dr. Smith, MyFitnessPal">
                `;
                break;
        }
        
        form.appendChild(group);
    });
}

function closeUploadModal() {
    document.getElementById('upload-modal').style.display = 'none';
    document.getElementById('upload-form').reset();
    selectedFile = null;
    currentCategory = '';
}

function showMobileItems(category) {
    // This will connect to mobile app API in the future
    alert(`🚧 Mobile app connection coming soon!\n\nThis will show recent ${category} from your mobile app that are ready to upload.`);
}

function upgradeToProPlusPage() {
    window.location.href = '/hub/account.php?upgrade=pro_plus';
}

// File input handling
document.getElementById('file-input').addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        selectedFile = e.target.files[0];
        
        // Update upload area to show selected file
        const uploadArea = document.querySelector('.upload-area');
        const instructions = uploadArea.querySelector('.upload-instructions');
        instructions.textContent = `Selected: ${selectedFile.name} (${(selectedFile.size / 1024 / 1024).toFixed(1)}MB)`;
        uploadArea.style.background = 'rgba(108, 152, 95, 0.1)';
        uploadArea.style.borderColor = 'var(--success-color)';
    }
});

// Form submission
document.getElementById('upload-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!selectedFile) {
        alert('Please select a file first');
        return;
    }
    
    // In real app, this would upload to server with context
    const analysisText = currentCategory === 'photos' ? 
        (hasCalcuPlate ? 'AI analysis and CalcuPlate parsing' : 'AI stool analysis and manual meal logging') : 'processing';
    alert(`🚀 Uploading ${selectedFile.name} for ${analysisText}...\n\nThis will process and show results!`);
    
    // For demo, just close modal
    closeUploadModal();
});

// Drag and drop functionality
document.querySelector('.upload-area').addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('drag-over');
});

document.querySelector('.upload-area').addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
});

document.querySelector('.upload-area').addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        document.getElementById('file-input').files = files;
        document.getElementById('file-input').dispatchEvent(new Event('change'));
    }
});

console.log('📤 Pro User Individual Upload Interface loaded');
console.log('- Pro users: AI stool analysis + manual meal logging');
console.log('- Pro+ users: AI stool analysis + CalcuPlate meal parsing'); 
console.log('- Smart context forms based on upload type');
console.log('- Mobile app integration architecture ready');
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>