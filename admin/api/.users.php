<?php
// Admin users endpoint
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

// Return dummy user data (replace with real data from your database)
$users = [
    [
        'id' => 1,
        'email' => 'user1@example.com',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'subscriptionPlan' => 'pro',
        'mealAiAddon' => true,
        'createdAt' => '2024-01-15T10:30:00Z'
    ],
    [
        'id' => 2,
        'email' => 'user2@example.com',
        'firstName' => 'Jane',
        'lastName' => 'Smith',
        'subscriptionPlan' => 'free',
        'mealAiAddon' => false,
        'createdAt' => '2024-02-20T14:15:00Z'
    ],
    [
        'id' => 3,
        'email' => 'user3@example.com',
        'firstName' => 'Mike',
        'lastName' => 'Johnson',
        'subscriptionPlan' => 'pro',
        'mealAiAddon' => true,
        'createdAt' => '2024-03-10T09:45:00Z'
    ]
];

http_response_code(200);
echo json_encode($users);
?>