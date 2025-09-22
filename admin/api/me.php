<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

echo json_encode([
    'username' => $_SESSION['admin_username'] ?? 'admin',
    'firstName' => 'Admin',
    'lastName' => 'User'
]);
?>