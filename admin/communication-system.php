<?php 
require_once __DIR__ . '/includes/admin-auth.php';
$adminUser = require_admin_login();

// Set title for this page
$pageTitle = "Communication System";

// Handle communication actions
$actionResult = null;
$messageHistory = [];
$templates = [];
$prefilledUser = null;

// Check for pre-filled user from URL (coming from User Inspector)
if (isset($_GET['user'])) {
    $prefilledUser = $_GET['user'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'send_direct_message':
            $actionResult = sendDirectMessage($_POST);
            break;
        case 'send_bulk_message':
            $actionResult = sendBulkMessage($_POST);
            break;
        case 'save_template':
            $actionResult = saveMessageTemplate($_POST);
            break;
        case 'send_sms':
            $actionResult = sendSMSMessage($_POST);
            break;
        case 'create_ticket':
            $actionResult = createSupportTicket($_POST);
            break;
        case 'load_template':
            $actionResult = loadMessageTemplate($_POST);
            break;
    }
}

// Load message history and templates
$messageHistory = getRecentMessages();
$templates = getMessageTemplates();

// PLACEHOLDER FUNCTIONS - Will connect to real communication systems post-launch
function sendDirectMessage($postData) {
    $recipient = $postData['recipient_email'] ?? '';
    $subject = $postData['message_subject'] ?? '';
    $message = $postData['message_content'] ?? '';
    $messageType = $postData['message_type'] ?? 'email';
    $journey = $postData['recipient_journey'] ?? 'best_life';
    
    return [
        'success' => true,
        'message' => "Direct {$messageType} sent to {$recipient}: \"{$subject}\""
    ];
}

function sendBulkMessage($postData) {
    $subject = $postData['bulk_subject'] ?? '';
    $message = $postData['bulk_content'] ?? '';
    $targetAudience = $postData['target_audience'] ?? 'all_users';
    $messageType = $postData['bulk_type'] ?? 'email';
    
    // PLACEHOLDER: Calculate recipient count based on targeting
    $recipientCounts = [
        'all_users' => 1247,
        'free_users' => 613,
        'pro_users' => 634,
        'clinical_journey' => 349,
        'performance_journey' => 437,
        'bestlife_journey' => 461,
        'recent_signups' => 89,
        'inactive_users' => 156
    ];
    
    $recipientCount = $recipientCounts[$targetAudience] ?? 0;
    
    return [
        'success' => true,
        'message' => "Bulk {$messageType} campaign started: {$recipientCount} recipients targeted with \"{$subject}\""
    ];
}

function saveMessageTemplate($postData) {
    $templateName = $postData['template_name'] ?? '';
    $templateType = $postData['template_type'] ?? 'support';
    $templateSubject = $postData['template_subject'] ?? '';
    $templateContent = $postData['template_content'] ?? '';
    $journey = $postData['template_journey'] ?? 'all';
    
    return [
        'success' => true,
        'message' => "Template \"{$templateName}\" saved successfully"
    ];
}

function sendSMSMessage($postData) {
    $recipient = $postData['sms_recipient'] ?? '';
    $message = $postData['sms_content'] ?? '';
    
    return [
        'success' => true,
        'message' => "SMS sent to {$recipient}: \"" . substr($message, 0, 50) . "...\""
    ];
}

function createSupportTicket($postData) {
    $userEmail = $postData['ticket_user_email'] ?? '';
    $subject = $postData['ticket_subject'] ?? '';
    $description = $postData['ticket_description'] ?? '';
    $priority = $postData['ticket_priority'] ?? 'medium';
    
    $ticketId = 'QG-' . strtoupper(substr(uniqid(), -6));
    
    return [
        'success' => true,
        'message' => "Support ticket {$ticketId} created for {$userEmail}"
    ];
}

function loadMessageTemplate($postData) {
    $templateId = $postData['template_id'] ?? '';
    $templates = getMessageTemplates();
    
    foreach ($templates as $template) {
        if ($template['id'] === $templateId) {
            return [
                'success' => true,
                'template' => $template
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Template not found'
    ];
}

function getRecentMessages() {
    return [
        [
            'id' => 'msg_001',
            'type' => 'email',
            'recipient' => 'john.doe@example.com',
            'subject' => 'Welcome to QuietGo Pro!',
            'sent_at' => time() - 1200,
            'status' => 'delivered'
        ],
        [
            'id' => 'msg_002', 
            'type' => 'in_app',
            'recipient' => 'jane.smith@example.com',
            'subject' => 'Your Pattern Report is Ready',
            'sent_at' => time() - 3600,
            'status' => 'read'
        ],
        [
            'id' => 'camp_001',
            'type' => 'bulk_email',
            'recipient' => 'Clinical Journey Users (349)',
            'subject' => 'New AI Analysis Features',
            'sent_at' => time() - 7200,
            'status' => 'completed'
        ]
    ];
}

function getMessageTemplates() {
    return [
        [
            'id' => 'tpl_welcome_clinical',
            'name' => 'Welcome - Clinical Focus',
            'type' => 'onboarding',
            'journey' => 'clinical',
            'subject' => 'Welcome to Your Clinical Journey',
            'content' => 'Hi {{name}}, Welcome to QuietGo! We\'re here to support your digestive health journey with clinical-grade insights...'
        ],
        [
            'id' => 'tpl_upgrade_prompt',
            'name' => 'Pro Upgrade Prompt',
            'type' => 'conversion',
            'journey' => 'all',
            'subject' => 'Unlock Advanced Pattern Analysis',
            'content' => 'Hi {{name}}, Ready to dive deeper into your digestive patterns? QuietGo Pro offers AI-powered stool analysis...'
        ],
        [
            'id' => 'tpl_support_upload',
            'name' => 'Upload Troubleshooting',
            'type' => 'support',
            'journey' => 'all',
            'subject' => 'Help with Photo Uploads',
            'content' => 'Hi {{name}}, I see you\'re having trouble with photo uploads. Here are a few quick solutions...'
        ],
        [
            'id' => 'tpl_billing_issue',
            'name' => 'Billing Issue Resolution',
            'type' => 'billing',
            'journey' => 'all',
            'subject' => 'Billing Update Required',
            'content' => 'Hi {{name}}, We need to update your payment information to continue your QuietGo Pro subscription...'
        ]
    ];
}

include __DIR__ . '/includes/header-admin.php'; 
?>

<style>
/* Communication System Styles */
.comm-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    transition: all 0.3s ease;
}

.comm-card:hover {
    background: rgba(140, 157, 138, 0.05);
    border-color: var(--green-color);
    transform: translateY(-2px);
}

.message-composer {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
}

.composer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
    padding-bottom: var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-md);
}

.form-group label {
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 0.875rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    background: var(--bg-color);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-md);
    color: var(--text-color);
    font-size: 0.875rem;
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
    font-family: inherit;
    line-height: 1.5;
}

.action-buttons {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.btn-primary,
.btn-secondary,
.btn-outline,
.btn-danger {
    padding: var(--spacing-md) var(--spacing-xl);
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    font-size: 0.875rem;
}

.btn-primary {
    background: var(--green-color);
    color: var(--bg-color);
}

.btn-primary:hover {
    background: #7a8a78;
    transform: translateY(-1px);
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-color);
}

.btn-outline:hover {
    background: var(--border-color);
}
</style>

<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Communication</div>
            <a href="#messaging" class="nav-item active" onclick="showSection('messaging')">
                <span class="nav-item-icon">📧</span>
                Direct Messaging
            </a>
            <a href="#templates" class="nav-item" onclick="showSection('templates')">
                <span class="nav-item-icon">📝</span>
                Message Templates
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-title">Navigation</div>
            <a href="/admin/dashboard.php" class="nav-item">
                <span class="nav-item-icon">🏠</span>
                Main Dashboard
            </a>
            <a href="/admin/user-inspector.php" class="nav-item">
                <span class="nav-item-icon">🔍</span>
                User Inspector
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-content">
        <div id="messaging" class="content-section active">
            <div class="content-header">
                <h1 class="content-title" style="text-align: center;">📧 Communication System</h1>
                <p class="content-subtitle" style="text-align: center;">Send targeted messages and manage user communications</p>
                <div class="placeholder-notice">
                    🚀 <strong>POST-LAUNCH:</strong> Integration with email service, SMS gateway, and in-app messaging
                </div>
            </div>
            
            <!-- Action Results -->
            <?php if ($actionResult): ?>
            <div class="action-result <?php echo $actionResult['success'] ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($actionResult['message']); ?>
            </div>
            <?php endif; ?>
            
            <!-- Message Composer -->
            <div class="message-composer">
                <div class="composer-header">
                    <h3 style="margin: 0; color: var(--text-color);">✉️ Compose Message</h3>
                    <div>
                        <button type="button" class="btn-outline">📋 Load from User Inspector</button>
                        <button type="button" class="btn-outline">📝 Use Template</button>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="send_direct_message">
                    
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: var(--spacing-md);">
                        <div class="form-group">
                            <label>Recipient Email *</label>
                            <input type="email" name="recipient_email" placeholder="user@example.com" value="<?php echo $prefilledUser ? htmlspecialchars($prefilledUser) : ''; ?>" required>
                            <?php if ($prefilledUser): ?>
                            <small style="color: var(--green-color); font-size: 0.75rem;">✓ Pre-filled from User Inspector</small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Message Type</label>
                            <select name="message_type" style="max-width: 180px;">
                                <option value="email">📧 Email</option>
                                <option value="in_app">💬 In-App</option>
                                <option value="sms">📱 SMS</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Subject Line *</label>
                        <input type="text" name="message_subject" placeholder="Enter message subject..." required>
                    </div>
                    
                    <div class="form-group">
                        <label>Message Content *</label>
                        <textarea name="message_content" placeholder="Write your personalized message here..." required></textarea>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn-primary">📤 Send Message</button>
                        <button type="button" class="btn-outline">💾 Save Draft</button>
                        <button type="button" class="btn-outline">👁️ Preview</button>
                    </div>
                </form>
            </div>
            
            <!-- Bulk Campaign -->
            <div class="message-composer">
                <div class="composer-header">
                    <h3 style="margin: 0; color: var(--text-color);">📢 Bulk Campaign</h3>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="send_bulk_message">
                    
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--spacing-sm); margin-bottom: var(--spacing-md);">
                        <div style="text-align: center; padding: var(--spacing-md); background: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); cursor: pointer;">
                            <input type="radio" name="target_audience" value="all_users" checked style="display: none;">
                            <div style="font-weight: 600; font-size: 1.25rem;">1,247</div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary);">All Users</div>
                        </div>
                        <div style="text-align: center; padding: var(--spacing-md); background: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); cursor: pointer;">
                            <input type="radio" name="target_audience" value="free_users" style="display: none;">
                            <div style="font-weight: 600; font-size: 1.25rem;">613</div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary);">Free Users</div>
                        </div>
                        <div style="text-align: center; padding: var(--spacing-md); background: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); cursor: pointer;">
                            <input type="radio" name="target_audience" value="pro_users" style="display: none;">
                            <div style="font-weight: 600; font-size: 1.25rem;">634</div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary);">Pro Users</div>
                        </div>
                        <div style="text-align: center; padding: var(--spacing-md); background: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); cursor: pointer;">
                            <input type="radio" name="target_audience" value="clinical_journey" style="display: none;">
                            <div style="font-weight: 600; font-size: 1.25rem;">349</div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary);">Clinical Focus</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Campaign Subject *</label>
                        <input type="text" name="bulk_subject" placeholder="Enter campaign subject..." required>
                    </div>
                    
                    <div class="form-group">
                        <label>Campaign Message *</label>
                        <textarea name="bulk_content" placeholder="Write your campaign message here..." required></textarea>
                    </div>
                    
                    <div class="action-buttons">
                        <button type="submit" class="btn-primary">📢 Launch Campaign</button>
                        <button type="button" class="btn-outline">⏰ Schedule</button>
                        <button type="button" class="btn-outline">🧪 Test Send</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Templates Section -->
        <div id="templates" class="content-section">
            <div class="content-header">
                <h1 class="content-title" style="text-align: center;">📝 Message Templates</h1>
                <p class="content-subtitle" style="text-align: center;">Create and manage reusable message templates</p>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-xl);">
                <!-- Create Template -->
                <div class="message-composer">
                    <div class="composer-header">
                        <h3 style="margin: 0; color: var(--text-color);">✨ Create Template</h3>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="save_template">
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                            <div class="form-group">
                                <label>Template Name *</label>
                                <input type="text" name="template_name" placeholder="e.g. Welcome Email" required>
                            </div>
                            <div class="form-group">
                                <label>Template Type</label>
                                <select name="template_type">
                                    <option value="support">Support Response</option>
                                    <option value="onboarding">Onboarding</option>
                                    <option value="billing">Billing</option>
                                    <option value="announcement">Announcement</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Subject Line *</label>
                            <input type="text" name="template_subject" placeholder="Use {{name}} for personalization" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Message Content *</label>
                            <textarea name="template_content" placeholder="Hi {{name}}, \n\nYour template content here..." required></textarea>
                        </div>
                        
                        <div class="action-buttons">
                            <button type="submit" class="btn-primary">💾 Save Template</button>
                            <button type="button" class="btn-outline">👁️ Preview</button>
                        </div>
                    </form>
                </div>
                
                <!-- Existing Templates -->
                <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: var(--spacing-lg);">
                    <h4 style="margin: 0 0 var(--spacing-md) 0; color: var(--text-color);">📝 Saved Templates</h4>
                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php foreach ($templates as $template): ?>
                        <div style="padding: var(--spacing-md); background: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); margin-bottom: var(--spacing-sm); cursor: pointer;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div>
                                    <h5 style="margin: 0; color: var(--text-color);"><?php echo htmlspecialchars($template['name']); ?></h5>
                                    <p style="margin: var(--spacing-xs) 0 0 0; font-size: 0.75rem; color: var(--text-secondary);"><?php echo htmlspecialchars($template['subject']); ?></p>
                                </div>
                                <div>
                                    <button class="btn-outline" style="padding: 4px 8px; font-size: 0.75rem;">Use</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Communication System Functions
function showSection(sectionName) {
    // Remove active class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked nav item
    event.target.classList.add('active');
    
    // Hide all content sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected content section
    document.getElementById(sectionName).classList.add('active');
}

// Template functions
function loadTemplate(templateId) {
    console.log('Loading template:', templateId);
    // PLACEHOLDER: Load template into composer
}

function useTemplate(templateId) {
    console.log('Using template:', templateId);
    // PLACEHOLDER: Pre-fill message composer with template
}

// Audience selection for bulk campaigns
document.addEventListener('DOMContentLoaded', function() {
    const audienceOptions = document.querySelectorAll('[style*="cursor: pointer"]');
    audienceOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove active state from all options
            audienceOptions.forEach(opt => {
                opt.style.background = 'var(--bg-color)';
                opt.style.borderColor = 'var(--border-color)';
            });
            
            // Add active state to clicked option
            this.style.background = 'var(--green-color)';
            this.style.borderColor = 'var(--green-color)';
            this.style.color = 'var(--bg-color)';
            
            // Check the radio button
            const radio = this.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });
    
    console.log('🚀 Communication System loaded');
    console.log('✨ Features: Direct messaging, bulk campaigns, message templates');
    console.log('🔗 Integration: Works with User Inspector for targeted communications');
});
</script>

<?php include __DIR__ . '/includes/footer-admin.php'; ?>
