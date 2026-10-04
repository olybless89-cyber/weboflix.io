<?php
require_once __DIR__ . '/../includes/functions.php';

$u = current_user();
if ($u && $u['is_admin']) { header('Location: ' . base_url('admin/index.php')); exit; }

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, password_hash, is_admin FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();

    if ($row && password_verify($password, $row['password_hash']) && $row['is_admin']) {
        login_user((int) $row['id']);
        header('Location: ' . base_url('admin/index.php'));
        exit;
    }
    $error = 'Incorrect credentials, or this account is not an admin.';
}

$pageTitle = 'Admin login';
include __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h1>Admin login</h1>
    <p class="sub">Restricted access.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <div class="field"><label>Email</label><input type="email" name="email" required autofocus></div>
      <div class="field"><label>Password</label><input type="password" name="password" required></div>
      <button class="btn btn-primary btn-block" type="submit">Log in</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
