<?php
// Reset rate limiting and clear sessions
session_start();

// Clear all admin-related session data
$keysToRemove = [];
foreach ($_SESSION as $key => $value) {
    if (strpos($key, 'admin') !== false || strpos($key, 'login_attempts') !== false) {
        $keysToRemove[] = $key;
    }
}

foreach ($keysToRemove as $key) {
    unset($_SESSION[$key]);
}

// Clear the entire session to be safe
session_destroy();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Rate limiting and sessions cleared',
    'cleared_keys' => $keysToRemove,
    'timestamp' => date('c')
]);
?>