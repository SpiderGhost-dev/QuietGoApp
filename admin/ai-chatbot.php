<?php 
require_once __DIR__ . '/includes/admin-auth.php';
$adminUser = require_admin_login();

// Set title for this page
$pageTitle = "AI Support Chatbot";

// Handle chatbot management actions
$actionResult = null;
$chatbotStats = [];
$knowledgeBase = [];
$chatLogs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'update_chatbot_settings':
            $actionResult = updateChatbotSettings($_POST);
            break;
        case 'add_knowledge_item':
            $actionResult = addKnowledgeItem($_POST);
            break;
        case 'train_ai_model':
            $actionResult = trainAIModel($_POST);
            break;
        case 'toggle_chatbot':
            $actionResult = toggleChatbot($_POST);
            break;
        case 'export_chat_logs':
            $actionResult = exportChatLogs();
            break;
    }
}

// Load chatbot data
$chatbotStats = getChatbotStats();
$knowledgeBase = getKnowledgeBase();
$chatLogs = getRecentChatLogs();

// PLACEHOLDER FUNCTIONS - Will connect to real AI service post-launch
function updateChatbotSettings($postData) {
    $enabled = $postData['chatbot_enabled'] ?? 'off';
    $greeting = $postData['chatbot_greeting'] ?? '';
    $aiModel = $postData['ai_model'] ?? 'gpt-4';
    $escalationThreshold = $postData['escalation_threshold'] ?? 3;
    
    return [
        'success' => true,
        'message' => "Chatbot settings updated successfully. Status: " . ($enabled === 'on' ? 'Enabled' : 'Disabled')
    ];
}

function addKnowledgeItem($postData) {
    $category = $postData['kb_category'] ?? 'general';
    $question = $postData['kb_question'] ?? '';
    $answer = $postData['kb_answer'] ?? '';
    $journey = $postData['kb_journey'] ?? 'all';
    
    return [
        'success' => true,
        'message' => "Knowledge base item added: \"{$question}\""
    ];
}

function trainAIModel($postData) {
    $trainingData = $postData['training_data'] ?? '';
    
    return [
        'success' => true,
        'message' => "AI model training started. This process may take 10-15 minutes to complete."
    ];
}

function toggleChatbot($postData) {
    $enabled = $postData['enabled'] ?? false;
    $status = $enabled ? 'enabled' : 'disabled';
    
    return [
        'success' => true,
        'message' => "Chatbot {$status} across all pages"
    ];
}

function exportChatLogs() {
    return [
        'success' => true,
        'message' => "Chat logs exported to CSV. Download will begin automatically."
    ];
}

function getChatbotStats() {
    return [
        'total_conversations' => 1847,
        'conversations_today' => 142,
        'ai_resolution_rate' => 73.2,
        'avg_response_time' => 2.3,
        'escalation_rate' => 18.7,
        'user_satisfaction' => 4.2,
        'common_topics' => [
            'Upload Issues' => 234,
            'Account & Billing' => 189,
            'Pattern Reports' => 156,
            'Subscription Help' => 143,
            'Technical Problems' => 98
        ]
    ];
}

function getKnowledgeBase() {
    return [
        [
            'id' => 'kb_001',
            'category' => 'upload',
            'question' => 'How do I upload photos to QuietGo?',
            'answer' => 'To upload photos: 1) Go to the Upload tab in your Hub 2) Tap the camera button 3) Choose "Take Photo" or "Choose from Gallery" 4) Follow the on-screen guides for optimal photo quality.',
            'journey' => 'all',
            'usage_count' => 89,
            'last_updated' => time() - 86400
        ],
        [
            'id' => 'kb_002', 
            'category' => 'billing',
            'question' => 'How do I upgrade to QuietGo Pro?',
            'answer' => 'To upgrade to Pro: 1) Go to Account tab in your Hub 2) Tap "Upgrade to Pro" 3) Choose monthly ($4.99) or yearly ($39.99) 4) Complete payment. Pro includes AI stool analysis and advanced Pattern reports.',
            'journey' => 'all',
            'usage_count' => 67,
            'last_updated' => time() - 172800
        ],
        [
            'id' => 'kb_003',
            'category' => 'patterns',
            'question' => 'When will my Pattern report be ready?',
            'answer' => 'Pattern reports generate automatically: Weekly reports every Sunday, Monthly reports on the 1st of each month. You\'ll receive a notification when your report is ready to view.',
            'journey' => 'all',
            'usage_count' => 45,
            'last_updated' => time() - 259200
        ],
        [
            'id' => 'kb_004',
            'category' => 'clinical',
            'question' => 'Can QuietGo help track my IBS symptoms?',
            'answer' => 'Yes! QuietGo\'s Clinical Focus journey is designed specifically for digestive health tracking. Our AI analyzes patterns to help identify potential triggers and correlations with your symptoms.',
            'journey' => 'clinical',
            'usage_count' => 38,
            'last_updated' => time() - 345600
        ]
    ];
}

function getRecentChatLogs() {
    return [
        [
            'id' => 'chat_001',
            'user_email' => 'john.doe@example.com',
            'started_at' => time() - 1800,
            'ended_at' => time() - 1650,
            'messages_count' => 4,
            'resolution_type' => 'ai_resolved',
            'satisfaction_rating' => 5,
            'topic' => 'Upload troubleshooting'
        ],
        [
            'id' => 'chat_002',
            'user_email' => 'jane.smith@example.com', 
            'started_at' => time() - 3600,
            'ended_at' => time() - 3300,
            'messages_count' => 7,
            'resolution_type' => 'escalated_human',
            'satisfaction_rating' => 4,
            'topic' => 'Billing issue'
        ],
        [
            'id' => 'chat_003',
            'user_email' => 'mike.johnson@example.com',
            'started_at' => time() - 7200,
            'ended_at' => time() - 6900,
            'messages_count' => 3,
            'resolution_type' => 'ai_resolved',
            'satisfaction_rating' => 5,
            'topic' => 'Pattern report questions'
        ]
    ];
}

include __DIR__ . '/includes/header-admin.php'; 
?>

<style>
/* Compact chatbot admin styles */
.chatbot-dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-xl);
}

.chatbot-card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    transition: all 0.3s ease;
}

.chatbot-card.featured {
    border: 2px solid var(--green-color);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-md);
}

.action-buttons {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.btn-primary, .btn-outline {
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

.btn-outline {
    background: transparent;
    border: 1px solid var(--border-color);
    color: var(--text-color);
}
</style>

<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">AI Support</div>
            <a href="#dashboard" class="nav-item active" onclick="showSection('dashboard')">
                <span class="nav-item-icon">🤖</span>
                Chatbot Dashboard
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-title">Navigation</div>
            <a href="/admin/dashboard.php" class="nav-item">
                <span class="nav-item-icon">🏠</span>
                Main Dashboard
            </a>
            <a href="/admin/communication-system.php" class="nav-item">
                <span class="nav-item-icon">📧</span>
                Communication System
            </a>
            <a href="/admin/user-inspector.php" class="nav-item">
                <span class="nav-item-icon">🔍</span>
                User Inspector
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-content">
        <!-- Chatbot Dashboard -->
        <div id="dashboard" class="content-section active">
            <div class="content-header">
                <h1 class="content-title" style="text-align: center;">🤖 AI Support Chatbot</h1>
                <p class="content-subtitle" style="text-align: center;">24/7 intelligent user support with seamless human escalation</p>
                <div class="placeholder-notice">
                    🚀 <strong>POST-LAUNCH:</strong> Integration with OpenAI/Claude API, real-time chat infrastructure, and advanced NLP
                </div>
            </div>
            
            <!-- Action Results -->
            <?php if ($actionResult): ?>
            <div class="action-result <?php echo $actionResult['success'] ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($actionResult['message']); ?>
            </div>
            <?php endif; ?>
            
            <!-- Performance Dashboard -->
            <div class="chatbot-dashboard">
                <div class="chatbot-card featured">
                    <h3 style="margin: 0 0 var(--spacing-md) 0; color: var(--text-color);">🤖 AI Performance</h3>
                    <div style="display: flex; justify-content: space-between;">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-color);"><?php echo number_format($chatbotStats['ai_resolution_rate'], 1); ?>%</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">AI Resolution</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-color);"><?php echo $chatbotStats['avg_response_time']; ?>s</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Avg Response</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-color);"><?php echo $chatbotStats['user_satisfaction']; ?>/5</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">User Rating</div>
                        </div>
                    </div>
                </div>
                
                <div class="chatbot-card">
                    <h3 style="margin: 0 0 var(--spacing-md) 0; color: var(--text-color);">💬 Conversations</h3>
                    <div style="display: flex; justify-content: space-between;">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-color);"><?php echo number_format($chatbotStats['conversations_today']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Today</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-color);"><?php echo number_format($chatbotStats['total_conversations']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">All Time</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 600; color: var(--text-color);"><?php echo number_format($chatbotStats['escalation_rate'], 1); ?>%</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Escalated</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: var(--spacing-lg);">
                <h3 style="margin: 0 0 var(--spacing-md) 0; color: var(--text-color);">⚡ Quick Actions</h3>
                <div class="action-buttons">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="toggle_chatbot">
                        <input type="hidden" name="enabled" value="true">
                        <button type="submit" class="btn-primary">🟢 Enable Chatbot</button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="toggle_chatbot">
                        <input type="hidden" name="enabled" value="false">
                        <button type="submit" class="btn-outline">🔴 Disable Chatbot</button>
                    </form>
                    <button type="button" class="btn-outline" onclick="testChatbot()">🧪 Test Chatbot</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="export_chat_logs">
                        <button type="submit" class="btn-outline">📊 Export Logs</button>
                    </form>
                </div>
            </div>
            
            <!-- Recent Chat Logs -->
            <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: var(--spacing-lg); margin-top: var(--spacing-lg);">
                <h3 style="margin: 0 0 var(--spacing-md) 0; color: var(--text-color);">💬 Recent Conversations</h3>
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php foreach ($chatLogs as $log): ?>
                    <div style="padding: var(--spacing-md); background: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--border-radius); margin-bottom: var(--spacing-sm);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xs);">
                            <span style="color: var(--text-color); font-weight: 600;"><?php echo htmlspecialchars($log['user_email']); ?></span>
                            <span style="color: var(--text-muted); font-size: 0.75rem;"><?php echo date('M j, H:i', $log['started_at']); ?></span>
                        </div>
                        <div style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: var(--spacing-xs);"><?php echo htmlspecialchars($log['topic']); ?></div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted); font-size: 0.875rem;">
                                <?php echo $log['messages_count']; ?> messages • 
                                <?php echo gmdate('i:s', $log['ended_at'] - $log['started_at']); ?> duration
                            </span>
                            <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                                <span style="padding: 2px 8px; background: rgba(108, 152, 95, 0.2); color: var(--green-color); border-radius: 12px; font-size: 0.625rem; font-weight: 600; text-transform: uppercase;">
                                    <?php echo ucfirst(str_replace('_', ' ', $log['resolution_type'])); ?>
                                </span>
                                <?php if ($log['satisfaction_rating']): ?>
                                <span style="color: var(--text-muted); font-size: 0.875rem;">
                                    <?php echo str_repeat('⭐', $log['satisfaction_rating']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// AI Chatbot Management Functions
function showSection(sectionName) {
    console.log('Showing section:', sectionName);
}

function testChatbot() {
    console.log('Testing chatbot...');
    alert('🧪 Testing chatbot functionality - this would open a test conversation window.');
}

// Initialize chatbot management dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('🤖 AI Chatbot Management Dashboard loaded');
    console.log('✨ Features: Dashboard, Settings, Knowledge Base, Training, Analytics');
    console.log('🔗 Integration: Connected to Communication System for escalations');
});
</script>

<?php include __DIR__ . '/includes/footer-admin.php'; ?>
