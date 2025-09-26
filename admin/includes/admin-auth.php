<?php
/**
 * Admin Authentication Guard
 * Include this file to protect admin pages
 */

// Only start session if one hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/admin-users.php';

function require_admin_login() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        header('Location: /admin/login.php');
        exit();
    }

    // Verify the session is still valid
    $username = $_SESSION['admin_username'] ?? '';
    $user = get_admin_user_info($username);

    if (!$user) {
        // Invalid session, redirect to login
        session_destroy();
        header('Location: /admin/login.php');
        exit();
    }

    return $user;
}

function admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
}
?>
