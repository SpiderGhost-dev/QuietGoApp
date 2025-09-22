<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/impersonation.php';
unset($_SESSION['impersonated_email']);
header("Location: /admin/dashboard.php");
exit;
