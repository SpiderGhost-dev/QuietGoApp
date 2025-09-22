<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/impersonation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/hub-auth.php';

$hubUser = hub_current_user();

if (is_impersonating()) {
  $impersonatedEmail = impersonated_email();
  $impersonatedRecord = $impersonatedEmail ? hub_find_subscriber($impersonatedEmail) : null;
  $hubUser = [
    'email' => $impersonatedEmail,
    'name' => $impersonatedRecord['name'] ?? ($impersonatedEmail ?: 'Impersonated user'),
    'subscription_status' => $impersonatedRecord['subscription_status'] ?? 'active',
    'subscription_plan' => $impersonatedRecord['subscription_plan'] ?? 'pro_monthly',
    'subscription_label' => $impersonatedRecord['subscription_label'] ?? 'Pro',
  ];
}

$navName = $hubUser['name'] ?? '';
$navEmail = $hubUser['email'] ?? '';
$navPlanLabel = $hubUser['subscription_label'] ?? '';
$planClass = 'status-free';

if (!empty($hubUser['subscription_plan']) && in_array($hubUser['subscription_plan'], ['pro_monthly', 'pro_yearly'], true)) {
  $planClass = 'status-pro';
}

$switchHref = hub_logged_in() || is_impersonating() ? '/hub/login.php?logout=1' : '/hub/login.php';
?>
<?php if (is_impersonating()): ?>
<div class="impersonation-banner">
  Impersonating <strong><?php echo htmlspecialchars(impersonated_email()); ?></strong>
  <a href="/admin/stop-impersonation.php" class="impersonation-stop">Stop</a>
</div>
<?php endif; ?>
<header>
  <nav class="navbar" aria-label="Hub navigation">
    <div class="container">
      <div class="nav-content">
        <a class="nav-brand" href="/hub/">
          <img src="/assets/images/logo-graphic.png" alt="QuietGo logo" width="40" height="40" loading="lazy">
          <div class="brand-stack">
            <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span>
            <span class="brand-subtitle">Hub</span>
          </div>
        </a>
        <div class="nav-links" role="navigation">
          <a href="/hub/" class="nav-link">Overview</a>
          <a href="/hub/#insights" class="nav-link">Insights</a>
          <a href="/hub/#exports" class="nav-link">Exports</a>
          <a href="/hub/#support" class="nav-link">Support</a>
        </div>
        <div class="nav-profile" id="userProfile">
          <img id="userAvatar" src="" alt="" aria-hidden="true">
          <div class="nav-profile-info">
            <span id="userName" class="muted"><?php echo htmlspecialchars($navName ?: $navEmail); ?></span>
            <?php if ($navPlanLabel): ?>
              <span id="userSubscription" class="status-badge <?php echo $planClass; ?>"><?php echo htmlspecialchars($navPlanLabel); ?></span>
            <?php endif; ?>
          </div>
        </div>
        <a class="btn btn-outline" href="<?php echo htmlspecialchars($switchHref); ?>"><?php echo hub_logged_in() || is_impersonating() ? 'Sign out' : 'Switch Account'; ?></a>
        <button class="mobile-menu-btn" type="button" aria-expanded="false" aria-controls="mobileMenu" onclick="toggleMobileMenu(this)">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <span class="sr-only">Toggle navigation</span>
        </button>
      </div>
      <div class="mobile-menu" id="mobileMenu">
        <a href="/hub/" class="nav-link">Overview</a>
        <a href="/hub/#insights" class="nav-link">Insights</a>
        <a href="/hub/#exports" class="nav-link">Exports</a>
        <a href="/hub/#support" class="nav-link">Support</a>
        <div class="nav-profile" id="userProfileMobile">
          <img id="userAvatarMobile" src="" alt="" aria-hidden="true">
          <div class="nav-profile-info">
            <span id="userNameMobile" class="muted"><?php echo htmlspecialchars($navName ?: $navEmail); ?></span>
            <?php if ($navPlanLabel): ?>
              <span id="userSubscriptionMobile" class="status-badge <?php echo $planClass; ?>"><?php echo htmlspecialchars($navPlanLabel); ?></span>
            <?php endif; ?>
          </div>
        </div>
        <a class="btn btn-outline" href="<?php echo htmlspecialchars($switchHref); ?>"><?php echo hub_logged_in() || is_impersonating() ? 'Sign out' : 'Switch Account'; ?></a>
      </div>
    </div>
  </nav>
</header>
