<?php
session_start();
header('Content-Type: application/json');

require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/admin-users.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Verify the session is still valid with actual user data
$username = $_SESSION['admin_username'] ?? '';
$user = get_admin_user_info($username);

if (!$user) {
    // Invalid user, clear session
    session_destroy();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid session']);
    exit();
}

echo json_encode([
    'success' => true,
    'user' => [
        'username' => $username,
        'name' => $user['name'],
        'role' => $user['role']
    ]
]);
?>