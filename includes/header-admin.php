<?php
$adminHeaderMode = $adminHeaderMode ?? 'dashboard';
$showAdminActions = $adminHeaderMode !== 'login';
?>
<header class="site-header">
  <div class="container">
    <div class="header-content">
      <a class="header-brand" href="/admin/dashboard.php">
        <img src="/assets/images/logo-graphic.png" alt="QuietGo logo" width="36" height="36" loading="lazy">
        <div class="brand-text">
          <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span>
          <span class="hub-text">Hub • Admin</span>
        </div>
      </a>
      <nav class="header-nav" aria-label="Admin navigation">
        <?php if ($showAdminActions): ?>
          <a href="/hub/" class="header-nav-link">Hub</a>
          <button type="button" class="header-nav-link active" aria-current="page">Admin</button>
          <a href="/" class="header-nav-link">Main Site</a>
          <div class="admin-user-info">
            <span id="admin-info">Admin User</span>
            <button class="logout-btn" type="button" onclick="logout()">Logout</button>
          </div>
        <?php else: ?>
          <a href="/" class="header-nav-link">Return to QuietGo</a>
        <?php endif; ?>
      </nav>
      <button class="mobile-menu-btn" type="button" aria-expanded="false" aria-controls="mobileMenu" onclick="toggleMobileMenu(this)">
        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </svg>
        <span class="sr-only">Toggle navigation</span>
      </button>
    </div>
    <div class="mobile-menu" id="mobileMenu">
      <?php if ($showAdminActions): ?>
        <a href="/hub/" class="header-nav-link">Hub</a>
        <button type="button" class="header-nav-link active" aria-current="page">Admin</button>
        <a href="/" class="header-nav-link">Main Site</a>
        <div class="mobile-menu-actions">
          <button class="logout-btn" type="button" onclick="logout()">Logout</button>
        </div>
      <?php else: ?>
        <a href="/" class="header-nav-link">Return to QuietGo</a>
      <?php endif; ?>
    </div>
  </div>
</header>
