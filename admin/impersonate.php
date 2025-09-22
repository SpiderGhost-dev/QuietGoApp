<?php 
include __DIR__ . '/includes/header-admin.php';
require_once __DIR__ . '/includes/impersonation.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  if ($email) {
    $_SESSION['impersonated_email'] = $email;
    header("Location: /hub/");
    exit;
  }
}
?>

<div class="admin-layout">
    <main class="admin-content">
        <div class="content-header">
            <h1 class="content-title">Impersonate User</h1>
            <p class="content-subtitle">Login as another user for support purposes</p>
        </div>
        
        <div class="data-section">
            <div class="section-content">
                <form method="post" class="admin-form">
                    <div class="form-group">
                        <label for="email">User Email:</label>
                        <input name="email" id="email" type="email" required placeholder="user@example.com" class="form-input">
                    </div>
                    <button type="submit" class="btn btn-primary">Start Impersonation</button>
                </form>
                
                <?php if (is_impersonating()): ?>
                <div class="alert alert-info" style="margin-top: 24px;">
                    <p><strong>Currently impersonating:</strong> <?php echo htmlspecialchars(impersonated_email()); ?></p>
                    <a href="/admin/stop-impersonation.php" class="btn btn-outline">Stop Impersonation</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer-admin.php'; ?>