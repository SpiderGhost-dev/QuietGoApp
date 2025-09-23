<?php
/**
 * Admin User Management
 * Defines admin users with credentials and display names
 */

function get_admin_users() {
    return [
        'david' => [
            'password' => 'QuietGo2024!DB',
            'name' => 'David M. Baker',
            'role' => 'CTO'
        ],
        'savannah' => [
            'password' => 'QuietGo2024!SB',
            'name' => 'Savannah J. Baker',
            'role' => 'CEO'
        ],
        'stone' => [
            'password' => 'QuietGo2024!OS',
            'name' => 'O. Stone Baker',
            'role' => 'Lead Designer'
        ],
        'admin' => [
            'password' => 'admin123',
            'name' => 'System Admin',
            'role' => 'Administrator'
        ]
    ];
}

function authenticate_admin($username, $password) {
    $users = get_admin_users();
    
    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        return $users[$username];
    }
    
    return false;
}

function get_admin_user_info($username) {
    $users = get_admin_users();
    return $users[$username] ?? null;
}

function admin_current_user() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        return null;
    }
    
    $username = $_SESSION['admin_username'] ?? '';
    return get_admin_user_info($username);
}
?>
