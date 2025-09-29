<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/impersonation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hub/includes/hub-auth.php';

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuietGo Hub - Your Health Dashboard</title>
    <meta name="description" content="Access your QuietGo health tracking dashboard with AI-powered insights and pattern analysis.">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Hub Styles -->
    <link href="/hub/css/hub.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon_io/favicon.ico">
</head>
<body>
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
          <img src="/assets/images/logo-graphic.png" alt="QuietGo logo" width="48" height="48" loading="lazy">
          <div class="brand-stack">
            <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span>
            <span class="brand-tagline">Plate to Pattern</span>
          </div>
        </a>
        <div class="section-identifier">
          <span class="section-label">Hub</span>
        </div>
        <div class="nav-links" role="navigation">
          <a href="/" class="nav-link">Home</a>
          <a href="/hub/" class="nav-link">Dashboard</a>
          <a href="/hub/upload.php" class="nav-link">📸 Upload</a>
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
        <a href="/" class="nav-link">Home</a>
        <a href="/hub/" class="nav-link">Dashboard</a>
        <a href="/hub/upload.php" class="nav-link">📸 Upload</a>
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

<script>
// Mobile menu toggle
function toggleMobileMenu(button) {
    const mobileMenu = document.getElementById('mobileMenu');
    const isOpen = mobileMenu.classList.contains('open');
    
    if (isOpen) {
        mobileMenu.classList.remove('open');
        button.setAttribute('aria-expanded', 'false');
    } else {
        mobileMenu.classList.add('open');
        button.setAttribute('aria-expanded', 'true');
    }
}
</script>
