<?php
// Admin API Configuration
// Shared configuration and utility functions for admin endpoints

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration constants
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour
define('ADMIN_PASSWORD_MIN_LENGTH', 6);
define('ADMIN_USERNAME_MIN_LENGTH', 3);
define('ADMIN_USERNAME_MAX_LENGTH', 20);

// Admin credentials (in production, move to database)
$ADMIN_CREDENTIALS = [
    'admin' => [
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'firstName' => 'System',
        'lastName' => 'Administrator',
        'email' => 'admin@quietgo.app'
    ],
    'spiderghost' => [
        'password' => password_hash('TempAdmin2024', PASSWORD_BCRYPT), // Your actual password
        'firstName' => 'Spider',
        'lastName' => 'Ghost', 
        'email' => 'spiderghost@quietgo.app'
    ],
    'SpiderGhost' => [
        'password' => password_hash('TempAdmin2024', PASSWORD_BCRYPT), // Case variation
        'firstName' => 'Spider',
        'lastName' => 'Ghost', 
        'email' => 'spiderghost@quietgo.app'
    ]
];

/**
 * Set common headers for all admin API responses
 */
function setAdminHeaders() {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

/**
 * Handle OPTIONS preflight request
 */
function handleOptions() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

/**
 * Check if admin is authenticated
 */
function isAdminAuthenticated() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['admin_login_time'])) {
        $sessionAge = time() - $_SESSION['admin_login_time'];
        if ($sessionAge > ADMIN_SESSION_TIMEOUT) {
            // Session expired
            unset($_SESSION['admin_logged_in']);
            unset($_SESSION['admin_username']);
            unset($_SESSION['admin_login_time']);
            return false;
        }
    }
    
    return true;
}

/**
 * Require admin authentication
 */
function requireAdminAuth() {
    if (!isAdminAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit();
    }
}

/**
 * Validate admin credentials
 */
function validateAdminCredentials($username, $password) {
    global $ADMIN_CREDENTIALS;
    
    if (!isset($ADMIN_CREDENTIALS[$username])) {
        return false;
    }
    
    return password_verify($password, $ADMIN_CREDENTIALS[$username]['password']);
}

/**
 * Get admin user data
 */
function getAdminUser($username) {
    global $ADMIN_CREDENTIALS;
    
    if (!isset($ADMIN_CREDENTIALS[$username])) {
        return null;
    }
    
    return [
        'username' => $username,
        'firstName' => $ADMIN_CREDENTIALS[$username]['firstName'],
        'lastName' => $ADMIN_CREDENTIALS[$username]['lastName'],
        'email' => $ADMIN_CREDENTIALS[$username]['email'],
        'isActive' => true
    ];
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit();
}

/**
 * Send error response
 */
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse(['error' => $message], $statusCode);
}

/**
 * Get JSON input data
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendErrorResponse('Invalid JSON', 400);
    }
    
    return $data ?: [];
}

/**
 * Validate required fields
 */
function validateRequiredFields($data, $required) {
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            sendErrorResponse("Missing required field: $field", 400);
        }
    }
}

/**
 * Sanitize string input
 */
function sanitizeString($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Log admin action (for audit trail)
 */
function logAdminAction($action, $details = []) {
    $logEntry = [
        'timestamp' => date('c'),
        'admin' => $_SESSION['admin_username'] ?? 'unknown',
        'action' => $action,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    // In production, save to database or log file
    error_log("ADMIN_ACTION: " . json_encode($logEntry));
}

// Initialize
setAdminHeaders();
handleOptions();
?>