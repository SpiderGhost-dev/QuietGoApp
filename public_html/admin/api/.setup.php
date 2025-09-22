<?php
// Admin setup endpoint - for creating new admin accounts
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

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Get request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

// Validate required fields
$required = ['username', 'email', 'firstName', 'lastName', 'password'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

// Simple validation
$username = trim($data['username']);
$email = trim($data['email']);
$firstName = trim($data['firstName']);
$lastName = trim($data['lastName']);
$password = $data['password'];

// Validate username (alphanumeric, 3-20 chars)
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    http_response_code(400);
    echo json_encode(['error' => 'Username must be 3-20 characters, alphanumeric and underscore only']);
    exit();
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit();
}

// Validate password strength
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit();
}

// Check if username already exists (in a real system, check database)
$existingUsers = ['admin', 'spiderghost']; // This would come from database
if (in_array($username, $existingUsers)) {
    http_response_code(409);
    echo json_encode(['error' => 'Username already exists']);
    exit();
}

// In a real system, you would:
// 1. Hash the password: password_hash($password, PASSWORD_BCRYPT)
// 2. Insert into database
// 3. Send welcome email
// For now, we'll just simulate success

$newAdmin = [
    'id' => rand(100, 999), // In real system, this would be from database
    'username' => $username,
    'email' => $email,
    'firstName' => $firstName,
    'lastName' => $lastName,
    'isActive' => true,
    'createdAt' => date('c'),
    'lastLoginAt' => null
];

// TODO: Actually save to database
// saveAdminToDatabase($newAdmin, $hashedPassword);

http_response_code(201);
echo json_encode([
    'success' => true,
    'message' => 'Admin account created successfully',
    'admin' => $newAdmin
]);
?>