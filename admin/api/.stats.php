<?php
// Admin stats endpoint
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

// Return dummy stats (replace with real data from your database)
http_response_code(200);
echo json_encode([
    'totalUsers' => 247,
    'recentUsers' => 23,
    'totalLogs' => 1543,
    'totalUploads' => 89,
    'activeUsers' => 156,
    'premiumUsers' => 45
]);
?>