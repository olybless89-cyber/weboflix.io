<?php
$pageTitle = 'Users';
$__adminPage = 'users';
require_once __DIR__ . '/includes/header.php';

$users = db()->query(
    "SELECT u.*,
        (SELECT COUNT(*) FROM subscriptions s WHERE s.user_id = u.id AND s.status='active' AND s.expires_at > NOW()) AS has_active_sub
     FROM users u WHERE u.is_admin = 0 ORDER BY u.created_at DESC"
)->fetchAll();
?>

<div class="admin-topbar"><h1>Users</h1></div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Plan</th><th>Joined</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= h($u['name']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td><span class="pill <?= $u['has_active_sub'] ? 'premium' : 'free' ?>"><?= $u['has_active_sub'] ? 'Premium' : 'Free' ?></span></td>
        <td class="mono"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$users): ?><tr><td colspan="4" style="color:var(--text-faint);">No users yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
