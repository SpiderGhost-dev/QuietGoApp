<?php
/* =================================================================
   TIER 3: AI CHATBOT WIDGET INCLUDE
   ================================================================= */

// Only show chatbot on appropriate pages
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$showChatbot = true;

// Don't show on admin pages
if (strpos($currentPath, '/admin/') !== false) {
    $showChatbot = false;
}

// Don't show on auth pages to avoid distraction
if (strpos($currentPath, '/login') !== false || 
    strpos($currentPath, '/signup') !== false || 
    strpos($currentPath, '/register') !== false) {
    $showChatbot = false;
}

// Allow pages to disable chatbot with a flag
global $disable_chatbot;
if (isset($disable_chatbot) && $disable_chatbot === true) {
    $showChatbot = false;
}

if ($showChatbot):
?>
<!-- QuietGo AI Chatbot Widget -->
<link rel="stylesheet" href="/css/chatbot.css">

<!-- DEBUG: Remove after testing -->
<!-- <div style="position: fixed; top: 10px; left: 10px; background: red; color: white; padding: 5px; font-size: 12px; z-index: 99999;">CHATBOT LOADING: <?php echo $_SERVER['REQUEST_URI']; ?></div> -->

<script>
console.log('🤖 Chatbot widget script loading...');
// Pass user session data to chatbot if available
<?php if (isset($_SESSION['hub_user'])): ?>
window.hubUser = {
    email: '<?php echo addslashes($_SESSION['hub_user']['email'] ?? ''); ?>',
    journey: '<?php echo addslashes($_SESSION['hub_user']['journey'] ?? 'best_life'); ?>',
    subscription_plan: '<?php echo addslashes($_SESSION['hub_user']['subscription_plan'] ?? 'free'); ?>'
};
<?php endif; ?>

// Load chatbot script
document.addEventListener('DOMContentLoaded', function() {
    console.log('🤖 DOM loaded, initializing full chatbot...');
    
    // Create full chatbot interface immediately
    initializeQuietGoChatbot();
    
    // Also load external chatbot script for enhanced features
    const script = document.createElement('script');
    script.src = '/js/chatbot.js';
    script.async = true;
    script.onload = function() {
        console.log('🤖 Full chatbot script loaded and ready');
    };
    script.onerror = function() {
        console.error('❌ Failed to load chatbot script, using basic fallback');
    };
    document.head.appendChild(script);
});

// Basic chatbot initialization
function initializeQuietGoChatbot() {
    // Create main chatbot container
    const chatbot = document.createElement('div');
    chatbot.className = 'quietgo-chatbot';
    chatbot.innerHTML = `
        <div class="chatbot-toggle" id="chatbot-toggle">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
            </svg>
        </div>
        <div class="chatbot-window" id="chatbot-window">
            <div class="chatbot-header">
                <div class="chatbot-avatar">🤖</div>
                <div class="chatbot-info">
                    <h3 class="chatbot-name">QuietGo Support</h3>
                    <p class="chatbot-status">Online</p>
                </div>
                <button class="chatbot-close" id="chatbot-close">×</button>
            </div>
            <div class="chatbot-messages" id="chatbot-messages">
                <div class="chat-message bot">
                    Hi there! 👋<br><br>
                    I'm your QuietGo AI assistant. I can help you with:
                    <br><br>
                    • Photo upload questions<br>
                    • Account and billing support<br>
                    • Understanding your Pattern reports<br>
                    • Privacy and security questions<br><br>
                    How can I assist you today?
                </div>
            </div>
            <div class="chatbot-input">
                <input type="text" class="chat-input" id="chatbot-message-input" placeholder="Type your message...">
                <button class="chat-send" id="chatbot-send">➤</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(chatbot);
    
    // Add event listeners
    document.getElementById('chatbot-toggle').addEventListener('click', toggleChatbot);
    document.getElementById('chatbot-close').addEventListener('click', closeChatbot);
    document.getElementById('chatbot-send').addEventListener('click', sendMessage);
    document.getElementById('chatbot-message-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
    
    console.log('🤖 QuietGo chatbot initialized and ready');
}

function toggleChatbot() {
    const window = document.getElementById('chatbot-window');
    const isOpen = window.classList.contains('open');
    
    if (isOpen) {
        closeChatbot();
    } else {
        openChatbot();
    }
}

function openChatbot() {
    const window = document.getElementById('chatbot-window');
    window.style.display = 'flex';
    setTimeout(() => {
        window.classList.add('open');
    }, 10);
    console.log('💬 Chatbot opened');
}

function closeChatbot() {
    const window = document.getElementById('chatbot-window');
    window.classList.remove('open');
    setTimeout(() => {
        window.style.display = 'none';
    }, 300);
    console.log('💬 Chatbot closed');
}

function sendMessage() {
    const input = document.getElementById('chatbot-message-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message to chat
    addMessage(message, 'user');
    input.value = '';
    
    // Simulate AI response (replace with real AI later)
    setTimeout(() => {
        const responses = [
            "Thanks for your question! I can help you with that. For photo upload issues, make sure your device has good lighting and the photo is clear.",
            "I understand you need assistance. For account questions, you can visit the Account tab in your Hub or contact support at support@quietgo.app.",
            "Great question! Pattern reports show correlations between your meals and digestive health. Would you like me to explain a specific part?",
            "I'm here to help! For technical issues, try restarting the app first. If that doesn't work, I can escalate this to our technical team."
        ];
        const response = responses[Math.floor(Math.random() * responses.length)];
        addMessage(response, 'bot');
    }, 1000);
}

function addMessage(content, sender) {
    const messages = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${sender}`;
    messageDiv.innerHTML = content;
    
    messages.appendChild(messageDiv);
    messages.scrollTop = messages.scrollHeight;
}
</script>

<!-- Meta tags for chatbot configuration -->
<?php if (isset($user_email)): ?>
<meta name="user-email" content="<?php echo htmlspecialchars($user_email); ?>">
<?php endif; ?>

<?php if (isset($user_journey)): ?>
<meta name="user-journey" content="<?php echo htmlspecialchars($user_journey); ?>">
<?php endif; ?>

<?php endif; ?>
