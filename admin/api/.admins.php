<?php
// Admin accounts endpoint
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Return dummy admin data (replace with real data from your database)
$admins = [
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@quietgo.app',
        'firstName' => 'Admin',
        'lastName' => 'User',
        'isActive' => true,
        'lastLoginAt' => date('c'),
        'createdAt' => '2024-01-01T00:00:00Z'
    ],
    [
        'id' => 2,
        'username' => 'spiderghost',
        'email' => 'spiderghost@quietgo.app',
        'firstName' => 'Spider',
        'lastName' => 'Ghost',
        'isActive' => true,
        'lastLoginAt' => date('c'),
        'createdAt' => '2024-01-01T00:00:00Z'
    ]
];

http_response_code(200);
echo json_encode($admins);
?>
