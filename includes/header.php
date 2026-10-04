<?php
require_once __DIR__ . '/functions.php';
$__user = current_user();
$__page = $__page ?? '';
$__activeSub = $__user ? get_active_subscription((int) $__user['id']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' — ' . SITE_NAME : SITE_NAME . ' — Stream Tech Courses & Master Real Skills' ?></title>
<meta name="description" content="Weboflix is a Netflix-style streaming platform for practical tech & web development courses — build AI stores, WordPress platforms, and cloud projects with bite-sized video lessons.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body<?= !empty($__trackHeartbeat) ? ' data-track="1"' : '' ?>>

<nav class="nav" id="mainNav">
  <div class="nav-inner">
    <a href="<?= base_url('index.php') ?>" class="logo">
      <span class="logo-badge">W</span>
      <span class="logo-text">WEBO<span>FLIX</span></span>
    </a>
    
    <div class="nav-links">
      <a href="<?= base_url('index.php') ?>" class="<?= $__page === 'home' ? 'active' : '' ?>">Home</a>
      <a href="<?= base_url('index.php#browse') ?>">Courses</a>
      <a href="<?= base_url('index.php#top10') ?>">Top 10</a>
      <a href="<?= base_url('pricing.php') ?>" class="<?= $__page === 'pricing' ? 'active' : '' ?>">Premium</a>
      <?php if ($__user): ?>
      <a href="<?= base_url('dashboard.php') ?>" class="<?= $__page === 'dashboard' ? 'active' : '' ?>">My List</a>
      <?php endif; ?>
    </div>
    
    <div class="nav-right">
      <form class="nav-search" id="navSearchForm" action="<?= base_url('search.php') ?>" method="get">
        <button type="button" class="search-trigger-btn" id="searchTriggerBtn" aria-label="Search">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
        <input type="text" name="q" id="navSearchInput" placeholder="Titles, skills, categories..." value="<?= h($_GET['q'] ?? '') ?>" autocomplete="off">
        <button type="button" class="search-close-btn" id="searchCloseBtn" aria-label="Clear search">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </form>

      <!-- Notifications dropdown -->
      <div class="nav-notify" id="navNotify">
        <button class="notify-btn" id="notifyBtn" aria-label="Notifications">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <span class="notify-dot"></span>
        </button>
        <div class="notify-dropdown" id="notifyDropdown">
          <div class="notify-header">New on Weboflix</div>
          <a class="notify-item" href="<?= base_url('course.php?slug=build-ecommerce-website-with-ai') ?>">
            <img src="https://i.ytimg.com/vi/EUfyzKqmZbk/default.jpg" alt="" class="notify-thumb">
            <div class="notify-info">
              <span class="notify-title">How to Build an E-Commerce Website With AI in 20 Minutes</span>
              <span class="notify-time">New Masterclass added</span>
            </div>
          </a>
          <a class="notify-item" href="<?= base_url('course.php?slug=create-courier-logistics-website') ?>">
            <img src="https://i.ytimg.com/vi/6xbGLvcMRjQ/default.jpg" alt="" class="notify-thumb">
            <div class="notify-info">
              <span class="notify-title">Courier & Logistics Website with WordPress</span>
              <span class="notify-time">Featured Tutorial</span>
            </div>
          </a>
          <a class="notify-item" href="<?= base_url('course.php?slug=build-charity-donation-website') ?>">
            <img src="https://i.ytimg.com/vi/mykCOktgKZk/default.jpg" alt="" class="notify-thumb">
            <div class="notify-info">
              <span class="notify-title">Professional Charity & Donation Website</span>
              <span class="notify-time">Full Course</span>
            </div>
          </a>
        </div>
      </div>

      <div class="nav-actions">
        <?php if ($__user): ?>
          <div class="user-profile-menu" id="userProfileMenu">
            <button class="profile-btn" id="profileBtn" aria-label="Account Menu">
              <span class="profile-avatar" style="background-color: <?= h($__user['avatar_color'] ?? '#00C853') ?>;">
                <?= strtoupper(substr($__user['name'], 0, 1)) ?>
              </span>
              <svg class="profile-caret" width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><polygon points="12,16 6,8 18,8"/></svg>
            </button>
            <div class="profile-dropdown" id="profileDropdown">
              <div class="profile-dropdown-header">
                <div class="profile-dropdown-user">
                  <span class="profile-avatar-sm" style="background-color: <?= h($__user['avatar_color'] ?? '#00C853') ?>;">
                    <?= strtoupper(substr($__user['name'], 0, 1)) ?>
                  </span>
                  <div>
                    <div class="profile-name"><?= h($__user['name']) ?></div>
                    <div class="profile-email"><?= h($__user['email']) ?></div>
                  </div>
                </div>
                <?php if ($__activeSub): ?>
                  <span class="badge-plan-active">Premium <?= ucfirst($__activeSub['plan']) ?></span>
                <?php else: ?>
                  <a href="<?= base_url('pricing.php') ?>" class="badge-plan-free">Upgrade to Premium &rarr;</a>
                <?php endif; ?>
              </div>
              <div class="profile-dropdown-divider"></div>
              <a href="<?= base_url('dashboard.php') ?>" class="profile-dropdown-link">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                My Learning & Progress
              </a>
              <a href="<?= base_url('pricing.php') ?>" class="profile-dropdown-link">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Plans & Subscription
              </a>
              <?php if (!empty($__user['is_admin'])): ?>
              <a href="<?= base_url('admin/index.php') ?>" class="profile-dropdown-link">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Admin Panel
              </a>
              <?php endif; ?>
              <div class="profile-dropdown-divider"></div>
              <a href="<?= base_url('logout.php') ?>" class="profile-dropdown-link profile-logout">
                Sign out of Weboflix
              </a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= base_url('login.php') ?>" class="btn btn-netflix-signin">Sign In</a>
        <?php endif; ?>

        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu" aria-expanded="false">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
      </div>
    </div>
  </div>

  <div class="mobile-menu" id="mobileMenu">
    <form class="mobile-search" action="<?= base_url('search.php') ?>" method="get">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" placeholder="Search courses, topics..." value="<?= h($_GET['q'] ?? '') ?>">
    </form>
    <a href="<?= base_url('index.php') ?>" class="<?= $__page === 'home' ? 'active' : '' ?>">Home</a>
    <a href="<?= base_url('index.php#browse') ?>">Courses</a>
    <a href="<?= base_url('index.php#top10') ?>">Top 10 Today</a>
    <a href="<?= base_url('pricing.php') ?>" class="<?= $__page === 'pricing' ? 'active' : '' ?>">Premium Plans</a>
    <?php if ($__user): ?>
      <a href="<?= base_url('dashboard.php') ?>" class="<?= $__page === 'dashboard' ? 'active' : '' ?>">My Learning List</a>
    <?php endif; ?>
    <div class="mobile-menu-divider"></div>
    <?php if ($__user): ?>
      <div class="mobile-user-info">
        <span class="profile-avatar-sm" style="background-color: <?= h($__user['avatar_color'] ?? '#00C853') ?>;">
          <?= strtoupper(substr($__user['name'], 0, 1)) ?>
        </span>
        <span><?= h($__user['name']) ?></span>
      </div>
      <?php if (!empty($__user['is_admin'])): ?>
      <a href="<?= base_url('admin/index.php') ?>">Admin Panel</a>
      <?php endif; ?>
      <a href="<?= base_url('logout.php') ?>" class="mobile-logout">Sign out</a>
    <?php else: ?>
      <a href="<?= base_url('login.php') ?>" class="mobile-cta">Sign In</a>
      <a href="<?= base_url('register.php') ?>" class="mobile-signup">Get Started</a>
    <?php endif; ?>
  </div>
</nav>
