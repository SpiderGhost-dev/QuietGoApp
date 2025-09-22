<header>
  <nav class="navbar" aria-label="Primary">
    <div class="container">
      <div class="nav-content">
        <a class="nav-brand" href="/">
          <img src="/assets/images/logo-graphic.png" alt="QuietGo logo" width="40" height="40" loading="lazy">
          <div class="brand-stack">
            <span class="quietgo-brand">
              <span class="quiet">Quiet</span><span class="go">Go</span>
            </span>
            <span class="brand-subtitle muted">Plate to pattern</span>
          </div>
        </a>

        <div class="nav-links" role="navigation">
          <button class="nav-link" type="button" onclick="scrollToSection('features')">Features</button>
          <button class="nav-link" type="button" onclick="scrollToSection('download')">Download</button>
          <a href="/privacy.php" class="nav-link">Privacy</a>
          <button class="btn btn-outline" type="button" onclick="handleLogin()">
            <span class="quietgo-brand" style="font-size: 0.875rem;">
              <span class="quiet">Quiet</span><span class="go">Go</span>
            </span>
            <span class="btn-suffix">Hub</span>
          </button>
          <button class="btn btn-primary" type="button" onclick="handleGetStarted()">Get Started</button>
        </div>

        <button class="mobile-menu-btn" type="button" aria-expanded="false" aria-controls="mobileMenu" onclick="toggleMobileMenu(this)">
          <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
          <span class="sr-only">Toggle navigation</span>
        </button>
      </div>

      <div class="mobile-menu" id="mobileMenu">
        <button class="nav-link" type="button" onclick="scrollToSection('features')">Features</button>
        <button class="nav-link" type="button" onclick="scrollToSection('download')">Download</button>
        <a href="/privacy.php" class="nav-link">Privacy</a>
        <button class="btn btn-outline" type="button" onclick="handleLogin()" style="margin-top: 16px;">
          <span class="quietgo-brand">
            <span class="quiet">Quiet</span><span class="go">Go</span>
          </span>
          <span class="btn-suffix">Hub</span>
        </button>
        <button class="btn btn-primary" type="button" onclick="handleGetStarted()" style="margin-top: 8px;">Get Started</button>
      </div>
    </div>
  </nav>
</header>
