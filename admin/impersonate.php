<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/includes/impersonation.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  if ($email) {
    $_SESSION['impersonated_email'] = $email;
    header("Location: /hub.php");
    exit;
  }
}
?><!doctype html>
<html><head><meta charset="utf-8"><title>Impersonate User</title></head><body>
<h1>Impersonate User</h1>
<form method="post">
<input name="email" type="email" required placeholder="user@example.com">
<button type="submit">Impersonate</button>
</form>
<?php if (is_impersonating()): ?>
<p>Currently impersonating: <?php echo htmlspecialchars(impersonated_email()); ?></p>
<a href="/admin/stop-impersonation.php">Stop</a>
<?php endif; ?>
</body></html>