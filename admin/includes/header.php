<?php
require_once __DIR__ . '/../../includes/functions.php';
require_admin();
$__admin = current_user();
$__adminPage = $__adminPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' — Admin' : 'Admin' ?> — Weboflix</title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<script>(function(){ var t = localStorage.getItem('wf-theme'); if (t) document.documentElement.setAttribute('data-theme', t); })();</script>
</head>
<body>
<div class="admin-mobile-bar">
  <div class="logo"><img src="<?= base_url('assets/img/logo-mark.png') ?>" alt=""><span class="logo-text">web<span>oflix</span></span></div>
  <button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Menu" aria-expanded="false">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
  </button>
</div>
<div class="admin-backdrop" id="adminBackdrop"></div>
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="logo" style="margin-bottom:28px;"><img src="<?= base_url('assets/img/logo-mark.png') ?>" alt=""><span class="logo-text">web<span>oflix</span></span></div>
    <nav class="admin-nav">
      <a href="<?= base_url('admin/index.php') ?>" class="<?= $__adminPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
      <a href="<?= base_url('admin/metrics.php') ?>" class="<?= $__adminPage === 'metrics' ? 'active' : '' ?>">Metrics</a>
      <a href="<?= base_url('admin/categories.php') ?>" class="<?= $__adminPage === 'categories' ? 'active' : '' ?>">Categories</a>
      <a href="<?= base_url('admin/courses.php') ?>" class="<?= $__adminPage === 'courses' ? 'active' : '' ?>">Courses</a>
      <a href="<?= base_url('admin/users.php') ?>" class="<?= $__adminPage === 'users' ? 'active' : '' ?>">Users</a>
      <a href="<?= base_url('admin/subscriptions.php') ?>" class="<?= $__adminPage === 'subscriptions' ? 'active' : '' ?>">Subscriptions</a>
      <a href="<?= base_url('index.php') ?>" style="margin-top:16px; display:block; border-top:1px solid var(--border-soft); padding-top:14px;">&larr; View site</a>
      <a href="<?= base_url('logout.php') ?>">Log out</a>
    </nav>
  </aside>
  <main class="admin-main">
