<?php
/* =================================================================
   TIER 3: CHATBOT ESCALATION API
   Handles escalations from AI chatbot to Communication System
   ================================================================= */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validate required fields
$required = ['conversation_id', 'escalation_reason', 'user_session'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: {$field}"]);
        exit;
    }
}

try {
    // Process the escalation
    $escalation = processEscalation($data);
    
    // Send success response
    echo json_encode([
        'success' => true,
        'escalation_id' => $escalation['id'],
        'message' => 'Escalation processed successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to process escalation',
        'debug' => $e->getMessage()
    ]);
}

function processEscalation($data) {
    $escalationId = 'esc_' . uniqid();
    $timestamp = date('Y-m-d H:i:s');
    
    // Extract escalation details
    $userEmail = $data['user_session']['email'] ?? 'anonymous@example.com';
    $journey = $data['user_session']['journey'] ?? 'unknown';
    $subscription = $data['user_session']['subscription'] ?? 'free';
    $reason = $data['escalation_reason'] ?? 'general';
    $conversationId = $data['conversation_id'];
    
    // Format message history for human agent
    $messageHistory = $data['message_history'] ?? [];
    $conversationSummary = formatConversationSummary($messageHistory);
    
    // Create escalation record
    $escalation = [
        'id' => $escalationId,
        'conversation_id' => $conversationId,
        'user_email' => $userEmail,
        'user_journey' => $journey,
        'user_subscription' => $subscription,
        'escalation_reason' => $reason,
        'conversation_summary' => $conversationSummary,
        'created_at' => $timestamp,
        'status' => 'pending',
        'priority' => determineEscalationPriority($reason, $subscription)
    ];
    
    // PLACEHOLDER: Save to database
    logEscalation($escalation);
    
    // Send notification to Communication System
    sendEscalationNotification($escalation);
    
    // Auto-create support ticket if needed
    if (shouldCreateSupportTicket($reason)) {
        createSupportTicket($escalation);
    }
    
    return $escalation;
}

function formatConversationSummary($messageHistory) {
    if (empty($messageHistory)) {
        return 'No conversation history available.';
    }
    
    $summary = "=== CHATBOT CONVERSATION SUMMARY ===\n\n";
    
    foreach ($messageHistory as $message) {
        $sender = $message['sender'] === 'user' ? 'USER' : 'AI BOT';
        $time = date('H:i:s', $message['timestamp'] / 1000);
        $text = strip_tags($message['text']);
        
        $summary .= "[{$time}] {$sender}: {$text}\n";
    }
    
    $summary .= "\n=== END CONVERSATION ===";
    
    return $summary;
}

function determineEscalationPriority($reason, $subscription) {
    // Pro users get higher priority
    if ($subscription === 'pro' || $subscription === 'pro_plus') {
        return 'high';
    }
    
    // Critical issues get high priority
    $highPriorityReasons = ['billing_inquiry', 'payment_failed', 'account_security'];
    if (in_array($reason, $highPriorityReasons)) {
        return 'high';
    }
    
    return 'medium';
}

function shouldCreateSupportTicket($reason) {
    // Auto-create tickets for these escalation types
    $ticketReasons = ['billing_inquiry', 'technical_issue', 'account_problem', 'human_requested'];
    return in_array($reason, $ticketReasons);
}

function logEscalation($escalation) {
    // PLACEHOLDER: Log to database/file
    $logEntry = date('Y-m-d H:i:s') . " - CHATBOT ESCALATION: " . json_encode($escalation) . "\n";
    error_log($logEntry, 3, '/tmp/chatbot_escalations.log');
}

function sendEscalationNotification($escalation) {
    // PLACEHOLDER: Send notification to Communication System
    // This would integrate with the email/messaging system we built
    
    $subject = "🤖 Chatbot Escalation: {$escalation['escalation_reason']}";
    $message = "
    A user conversation has been escalated from the AI chatbot:
    
    User: {$escalation['user_email']}
    Journey: {$escalation['user_journey']}  
    Subscription: {$escalation['user_subscription']}
    Reason: {$escalation['escalation_reason']}
    Priority: {$escalation['priority']}
    
    Conversation Summary:
    {$escalation['conversation_summary']}
    
    Please follow up with this user promptly.
    ";
    
    // This would use the Communication System we built
    // sendAdminNotification($subject, $message, $escalation['priority']);
    
    error_log("ESCALATION NOTIFICATION: {$subject}\n{$message}");
}

function createSupportTicket($escalation) {
    // PLACEHOLDER: Create support ticket using Communication System
    $ticketData = [
        'user_email' => $escalation['user_email'],
        'subject' => "Chatbot escalation: {$escalation['escalation_reason']}",
        'description' => $escalation['conversation_summary'],
        'priority' => $escalation['priority'],
        'source' => 'chatbot_escalation',
        'conversation_id' => $escalation['conversation_id']
    ];
    
    // This would integrate with the Communication System ticket creation
    // $ticketId = createSupportTicket($ticketData);
    
    error_log("SUPPORT TICKET CREATED: " . json_encode($ticketData));
}
?>
