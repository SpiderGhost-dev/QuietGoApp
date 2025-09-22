<?php
require_once __DIR__ . '/includes/impersonation.php';
unset($_SESSION['impersonated_email']);
header("Location: /admin/dashboard.php");
exit;