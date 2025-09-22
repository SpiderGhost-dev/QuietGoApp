<?php
// Admin logout handler
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Clear admin session
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_login_time']);

// Destroy session
session_destroy();

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
?>