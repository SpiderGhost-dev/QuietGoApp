<?php
// Minimal impersonation helpers (dev shell).
// Starts session, sets/clears $_SESSION['impersonated_email'].
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function is_impersonating() {
  return isset($_SESSION['impersonated_email']) && !empty($_SESSION['impersonated_email']);
}
function impersonated_email() {
  return is_impersonating() ? $_SESSION['impersonated_email'] : null;
}
?>