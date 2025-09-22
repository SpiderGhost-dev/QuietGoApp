<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (($username === 'spiderghost' && $password === 'TempAdmin2024') || 
    ($username === 'admin' && $password === 'admin123')) {
    
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    
    echo json_encode([
        'success' => true,
        'user' => ['username' => $username]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
}
?>