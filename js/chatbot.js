/* =================================================================
   TIER 3: AI CHATBOT WIDGET - JAVASCRIPT
   ================================================================= */

class QuietGoChatbot {
    constructor() {
        this.isOpen = false;
        this.conversationId = this.generateId();
        this.messageHistory = [];
        this.isTyping = false;
        this.userSession = this.getUserSession();
        
        this.init();
    }
    
    init() {
        this.createChatbotHTML();
        this.attachEventListeners();
        this.loadWelcomeMessage();
        
        console.log('🤖 QuietGo AI Chatbot initialized');
        console.log('📊 User session:', this.userSession);
    }
    
    getUserSession() {
        // Try to get user info from various sources
        const hubUser = window.hubUser || null; // From hub pages
        const userEmail = document.querySelector('meta[name="user-email"]')?.content;
        const userJourney = document.querySelector('meta[name="user-journey"]')?.content || 'best_life';
        
        return {
            email: hubUser?.email || userEmail || null,
            journey: hubUser?.journey || userJourney,
            subscription: hubUser?.subscription_plan || 'free',
            isLoggedIn: !!(hubUser || userEmail)
        };
    }
    
    createChatbotHTML() {
        const chatbotHTML = `
            <div class="quietgo-chatbot">
                <button class="chatbot-toggle" onclick="quietgoChatbot.toggle()">
                    💬
                </button>
                
                <div class="chatbot-window" id="chatbot-window">
                    <div class="chatbot-header">
                        <div class="chatbot-avatar">🤖</div>
                        <div class="chatbot-info">
                            <h4 class="chatbot-name">QuietGo Support</h4>
                            <p class="chatbot-status">Online • Typically replies instantly</p>
                        </div>
                        <button class="chatbot-close" onclick="quietgoChatbot.close()">&times;</button>
                    </div>
                    
                    <div class="chatbot-messages" id="chatbot-messages">
                        <!-- Messages will be inserted here -->
                    </div>
                    
                    <div class="chatbot-input">
                        <input type="text" 
                               class="chat-input" 
                               id="chat-input" 
                               placeholder="Type your message..."
                               onkeypress="quietgoChatbot.handleKeyPress(event)">
                        <button class="chat-send" 
                                id="chat-send"
                                onclick="quietgoChatbot.sendMessage()">
                            ➤
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }
    
    attachEventListeners() {
        // Close chatbot when clicking outside
        document.addEventListener('click', (e) => {
            const chatbot = document.querySelector('.quietgo-chatbot');
            if (this.isOpen && !chatbot.contains(e.target)) {
                this.close();
            }
        });
        
        // Auto-resize input
        const input = document.getElementById('chat-input');
        input.addEventListener('input', () => {
            this.adjustInputHeight();
        });
    }
    
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }
    
    open() {
        const window = document.getElementById('chatbot-window');
        const toggle = document.querySelector('.chatbot-toggle');
        
        window.classList.add('open');
        toggle.classList.add('open');
        toggle.innerHTML = '&times;';
        
        this.isOpen = true;
        
        // Focus input
        setTimeout(() => {
            document.getElementById('chat-input').focus();
        }, 300);
        
        // Track opening
        this.trackEvent('chatbot_opened');
    }
    
    close() {
        const window = document.getElementById('chatbot-window');
        const toggle = document.querySelector('.chatbot-toggle');
        
        window.classList.remove('open');
        toggle.classList.remove('open');
        toggle.innerHTML = '💬';
        
        this.isOpen = false;
        
        // Track closing
        this.trackEvent('chatbot_closed');
    }
    
    async sendMessage() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        
        if (!message || this.isTyping) return;
        
        // Add user message
        this.addMessage(message, 'user');
        input.value = '';
        this.adjustInputHeight();
        
        // Show typing indicator
        this.showTypingIndicator();
        
        try {
            // Get AI response
            const response = await this.getAIResponse(message);
            
            // Remove typing indicator
            this.removeTypingIndicator();
            
            // Add bot response
            this.addMessage(response.message, 'bot');
            
            // Add quick actions if provided
            if (response.quickActions) {
                this.addQuickActions(response.quickActions);
            }
            
            // Handle escalation if needed
            if (response.escalate) {
                this.handleEscalation(response.escalationReason);
            }
            
        } catch (error) {
            console.error('Chatbot error:', error);
            this.removeTypingIndicator();
            this.addMessage("I'm sorry, I'm having trouble connecting right now. Please try again in a moment or contact support directly.", 'bot');
        }
    }
    
    async getAIResponse(message) {
        // PLACEHOLDER: This would connect to actual AI service post-launch
        // For now, return intelligent mock responses based on keywords
        
        const lowerMessage = message.toLowerCase();
        
        // Simulate network delay
        await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 2000));
        
        // Journey-specific responses
        const journeyContext = this.getJourneyContext();
        
        // Common support topics with intelligent responses
        if (lowerMessage.includes('upload') || lowerMessage.includes('photo')) {
            return {
                message: `I can help with photo uploads! ${journeyContext}\\n\\nFor best results:\\n• Good lighting is key\\n• Fill the frame with your subject\\n• Hold your phone steady\\n\\nAre you having trouble with a specific step?`,
                quickActions: ['Upload troubleshooting', 'Photo quality tips', 'Contact human support']
            };
        }
        
        if (lowerMessage.includes('upgrade') || lowerMessage.includes('pro') || lowerMessage.includes('subscription')) {
            return {
                message: `Happy to help with QuietGo Pro! ${journeyContext}\\n\\nPro includes:\\n• AI stool analysis\\n• Advanced Pattern reports\\n• Priority support\\n\\nWould you like to upgrade or need help with billing?`,
                quickActions: ['Upgrade to Pro', 'Billing help', 'Feature comparison']
            };
        }
        
        if (lowerMessage.includes('pattern') || lowerMessage.includes('report')) {
            return {
                message: `Pattern reports are generated automatically:\\n\\n• Weekly reports every Sunday\\n• Monthly reports on the 1st\\n• You'll get notified when ready\\n\\n${journeyContext}\\n\\nNeed help interpreting your results?`,
                quickActions: ['Pattern help', 'Report not ready', 'Understanding insights']
            };
        }
        
        if (lowerMessage.includes('billing') || lowerMessage.includes('payment') || lowerMessage.includes('cancel')) {
            return {
                message: "I can help with billing questions! For account security, I'll connect you with our support team who can access your payment details safely.",
                quickActions: ['Contact billing support', 'Subscription help', 'Refund request'],
                escalate: true,
                escalationReason: 'billing_inquiry'
            };
        }
        
        if (lowerMessage.includes('bug') || lowerMessage.includes('error') || lowerMessage.includes('broken') || lowerMessage.includes('crash')) {
            return {
                message: "Sorry to hear you're experiencing issues! Let me help troubleshoot or connect you with our tech team.\\n\\nWhat specific problem are you encountering?",
                quickActions: ['Describe the problem', 'App troubleshooting', 'Contact tech support'],
                escalate: false
            };
        }
        
        if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
            return {
                message: `Hello! ${this.userSession.isLoggedIn ? "Nice to see you again!" : "Welcome to QuietGo!"} I'm here to help with any questions about your digestive health journey. What can I assist you with today?`,
                quickActions: ['Upload help', 'Account questions', 'Pattern reports', 'Billing support']
            };
        }
        
        if (lowerMessage.includes('human') || lowerMessage.includes('person') || lowerMessage.includes('agent')) {
            return {
                message: "I'd be happy to connect you with our human support team! They're available to help with more complex questions and account-specific issues.",
                quickActions: ['Contact human support', 'Schedule callback', 'Send email'],
                escalate: true,
                escalationReason: 'human_requested'
            };
        }
        
        // Default response with journey context
        return {
            message: `I'm here to help! ${journeyContext}\\n\\nI can assist with:\\n• Photo upload issues\\n• Account and billing questions\\n• Pattern report explanations\\n• Technical troubleshooting\\n\\nWhat would you like help with?`,
            quickActions: ['Upload help', 'Account questions', 'Technical support', 'Talk to human']
        };
    }
    
    getJourneyContext() {
        const journey = this.userSession.journey;
        const contexts = {
            'clinical': 'I see you\'re on the Clinical Focus journey - perfect for tracking digestive health patterns.',
            'performance': 'I see you\'re optimizing for Peak Performance - great for understanding how nutrition affects your goals.',
            'best_life': 'I see you\'re in Best Life Mode - excellent for overall wellness and lifestyle optimization.'
        };
        
        return this.userSession.isLoggedIn ? (contexts[journey] || contexts.best_life) : '';
    }
    
    addMessage(text, sender) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const messageElement = document.createElement('div');
        messageElement.className = `chat-message ${sender}`;
        messageElement.innerHTML = this.formatMessage(text);
        
        messagesContainer.appendChild(messageElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        // Store in history
        this.messageHistory.push({ text, sender, timestamp: Date.now() });
        
        // Track message
        this.trackEvent('message_sent', { sender, length: text.length });
    }
    
    addQuickActions(actions) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const actionsElement = document.createElement('div');
        actionsElement.className = 'quick-actions';
        
        actions.forEach(action => {
            const button = document.createElement('button');
            button.className = 'quick-action';
            button.textContent = action;
            button.onclick = () => this.handleQuickAction(action);
            actionsElement.appendChild(button);
        });
        
        messagesContainer.appendChild(actionsElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    handleQuickAction(action) {
        // Send the action as a user message
        this.addMessage(action, 'user');
        
        // Process the action
        setTimeout(() => {
            this.processQuickAction(action);
        }, 500);
    }
    
    async processQuickAction(action) {
        const lowerAction = action.toLowerCase();
        
        if (lowerAction.includes('human support') || lowerAction.includes('contact')) {
            this.handleEscalation('user_requested_human');
            return;
        }
        
        if (lowerAction.includes('upload') || lowerAction.includes('photo')) {
            this.addMessage("Here are some quick upload tips:\\n\\n📸 **Photo Quality:**\\n• Use good lighting\\n• Fill the frame\\n• Keep phone steady\\n\\n🔧 **Troubleshooting:**\\n• Check your internet connection\\n• Update the app if needed\\n• Try again in a few minutes\\n\\nNeed more specific help?", 'bot');
            this.addQuickActions(['Still having issues', 'Contact support', 'Photo tips']);
            return;
        }
        
        if (lowerAction.includes('billing') || lowerAction.includes('upgrade')) {
            this.handleEscalation('billing_request');
            return;
        }
        
        // Default response
        const response = await this.getAIResponse(action);
        this.addMessage(response.message, 'bot');
        
        if (response.quickActions) {
            this.addQuickActions(response.quickActions);
        }
    }
    
    handleEscalation(reason) {
        const escalationMessages = {
            'billing_inquiry': "I'm connecting you with our billing team who can securely access your account details.",
            'human_requested': "I'm connecting you with one of our human support specialists.",
            'billing_request': "For billing and subscription changes, I'll connect you with our account specialists.",
            'complex_issue': "This seems like a complex issue that would benefit from human expertise.",
            'user_requested_human': "Absolutely! I'm connecting you with our support team."
        };
        
        const message = escalationMessages[reason] || "I'm connecting you with our human support team.";
        
        this.addMessage(`${message}\\n\\n🔄 **Escalating to human support...**\\n\\nSomeone from our team will follow up with you shortly. You can also email us at support@quietgo.app or use the Contact form in your Hub.`, 'bot');
        
        // Send escalation to Communication System
        this.sendEscalation(reason);
        
        this.trackEvent('escalated_to_human', { reason });
    }
    
    async sendEscalation(reason) {
        // PLACEHOLDER: Send to Communication System API
        const escalationData = {
            user_email: this.userSession.email,
            conversation_id: this.conversationId,
            escalation_reason: reason,
            message_history: this.messageHistory,
            user_session: this.userSession,
            timestamp: Date.now()
        };
        
        try {
            // This would connect to the Communication System we built
            const response = await fetch('/admin/api/chatbot-escalation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(escalationData)
            });
            
            console.log('Escalation sent to Communication System:', escalationData);
        } catch (error) {
            console.error('Failed to send escalation:', error);
        }
    }
    
    showTypingIndicator() {
        if (this.isTyping) return;
        
        this.isTyping = true;
        const messagesContainer = document.getElementById('chatbot-messages');
        
        const typingElement = document.createElement('div');
        typingElement.className = 'chat-message bot typing';
        typingElement.id = 'typing-indicator';
        typingElement.innerHTML = `
            <span>QuietGo Support is typing</span>
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        `;
        
        messagesContainer.appendChild(typingElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    removeTypingIndicator() {
        this.isTyping = false;
        const typingElement = document.getElementById('typing-indicator');
        if (typingElement) {
            typingElement.remove();
        }
    }
    
    loadWelcomeMessage() {
        setTimeout(() => {
            const welcomeMessage = this.userSession.isLoggedIn 
                ? `Hi ${this.userSession.email?.split('@')[0] || 'there'}! 👋\\n\\nI'm here to help with any QuietGo questions. What can I assist you with today?`
                : "Hi there! 👋\\n\\nWelcome to QuietGo! I'm here to help answer questions about our digestive health platform. How can I assist you?";
                
            this.addMessage(welcomeMessage, 'bot');
            this.addQuickActions(['Upload help', 'How QuietGo works', 'Pricing info', 'Technical support']);
        }, 1000);
    }
    
    formatMessage(text) {
        // Basic formatting: convert newlines and simple markdown
        return text
            .replace(/\\n/g, '<br>')
            .replace(/\\*\\*(.*?)\\*\\*/g, '<strong>$1</strong>')
            .replace(/\\*(.*?)\\*/g, '<em>$1</em>');
    }
    
    handleKeyPress(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            this.sendMessage();
        }
    }
    
    adjustInputHeight() {
        const input = document.getElementById('chat-input');
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 80) + 'px';
    }
    
    trackEvent(event, data = {}) {
        // PLACEHOLDER: Track events for analytics
        const eventData = {
            event,
            conversation_id: this.conversationId,
            user_session: this.userSession,
            timestamp: Date.now(),
            ...data
        };
        
        console.log('📊 Chatbot event:', eventData);
        
        // This would send to analytics service
        // analytics.track('chatbot_' + event, eventData);
    }
    
    generateId() {
        return 'chat_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
    }
}

// Initialize chatbot when page loads
let quietgoChatbot;

document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if chatbot should be shown on this page
    if (shouldShowChatbot()) {
        quietgoChatbot = new QuietGoChatbot();
    }
});

function shouldShowChatbot() {
    // Show on all pages except admin pages
    if (window.location.pathname.startsWith('/admin/')) {
        return false;
    }
    
    // Hide on login/signup pages to avoid distraction
    if (window.location.pathname.includes('/login') || 
        window.location.pathname.includes('/signup') ||
        window.location.pathname.includes('/register')) {
        return false;
    }
    
    // Check if chatbot is disabled via meta tag
    const disableChatbot = document.querySelector('meta[name="disable-chatbot"]');
    if (disableChatbot && disableChatbot.content === 'true') {
        return false;
    }
    
    return true;
}

// Export for global access
window.QuietGoChatbot = QuietGoChatbot;
