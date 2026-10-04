<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_actions.php';

if (current_user()) { header('Location: ' . base_url('dashboard.php')); exit; }

$error = null;
$next = $_GET['next'] ?? $_POST['next'] ?? base_url('dashboard.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $result = attempt_login($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['ok']) {
        login_user($result['user_id']);
        header('Location: ' . $next);
        exit;
    }
    $error = $result['error'];
}

$pageTitle = 'Log in';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-page">
  <div class="auth-visual">
    <a href="<?= base_url('index.php') ?>" class="logo"><img src="<?= base_url('assets/img/logo-mark.png') ?>" alt=""><span class="logo-text">web<span>oflix</span></span></a>
    <h2>Pick up right where you left off.</h2>
    <p>Log in to keep your progress and continue watching across every course.</p>
    <ul class="auth-visual-points">
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Free lessons on every course</li>
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Progress synced across devices</li>
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> One plan unlocks everything</li>
    </ul>
  </div>
  <div class="auth-panel">
    <div class="auth-card">
      <div class="auth-mobile-logo"><img src="<?= base_url('assets/img/logo-mark.png') ?>" alt=""> web<span>oflix</span></div>
      <h1>Welcome back</h1>
      <p class="sub">Log in to continue watching.</p>
      <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="next" value="<?= h($next) ?>">
        <div class="field"><label>Email</label><input type="email" name="email" required autofocus></div>
        <div class="field"><label>Password</label><input type="password" name="password" required></div>
        <button class="btn btn-primary btn-block" type="submit">Log in</button>
      </form>
      <div class="auth-foot">New to Weboflix? <a href="<?= base_url('register.php') . ($next !== base_url('dashboard.php') ? '?redirect=' . urlencode($next) : '') ?>">Create an account</a></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
