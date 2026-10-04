<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_actions.php';

if (current_user()) { header('Location: ' . base_url('dashboard.php')); exit; }

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $result = attempt_register($_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($result['ok']) {
        login_user($result['user_id']);
        $redirect = $_POST['redirect'] ?? '';
        header('Location: ' . ($redirect !== '' ? $redirect : base_url('dashboard.php')));
        exit;
    }
    $error = $result['error'];
}

$pageTitle = 'Create your account';
include __DIR__ . '/includes/header.php';
?>
<div class="auth-page">
  <div class="auth-visual">
    <a href="<?= base_url('index.php') ?>" class="logo"><img src="<?= base_url('assets/img/logo-mark.png') ?>" alt=""><span class="logo-text">web<span>oflix</span></span></a>
    <h2>Learn to build. One lesson at a time.</h2>
    <p>Join Weboflix to track your progress and unlock every course as you go.</p>
    <ul class="auth-visual-points">
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Free lessons on every course</li>
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Progress synced across devices</li>
      <li><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> One plan unlocks everything</li>
    </ul>
  </div>
  <div class="auth-panel">
    <div class="auth-card">
      <div class="auth-mobile-logo"><img src="<?= base_url('assets/img/logo-mark.png') ?>" alt=""> web<span>oflix</span></div>
      <h1>Create your account</h1>
      <p class="sub">Start with free courses today. Upgrade anytime.</p>
      <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="hidden" name="redirect" value="<?= h($_GET['redirect'] ?? '') ?>">
        <div class="field"><label>Full name</label><input type="text" name="name" value="<?= h($_POST['name'] ?? '') ?>" required></div>
        <div class="field"><label>Email</label><input type="email" name="email" value="<?= h($_POST['email'] ?? $_GET['email'] ?? '') ?>" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" minlength="6" required></div>
        <button class="btn btn-primary btn-block" type="submit">Create account</button>
      </form>
      <div class="auth-foot">Already have an account? <a href="<?= base_url('login.php') ?>">Log in</a></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
