<?php
/**
 * Admin Impersonation Functions
 * Allows admins to impersonate users for testing
 */

function is_impersonating() {
    return isset($_SESSION['impersonate_email']) && !empty($_SESSION['impersonate_email']);
}

function impersonated_email() {
    return $_SESSION['impersonate_email'] ?? null;
}

function start_impersonation($email) {
    $_SESSION['impersonate_email'] = $email;
}

function stop_impersonation() {
    unset($_SESSION['impersonate_email']);
}
