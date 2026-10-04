<?php
$pageTitle = 'Dashboard';
$__adminPage = 'dashboard';
require_once __DIR__ . '/includes/header.php';

$totalUsers = (int) db()->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$activeSubs = (int) db()->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND expires_at > NOW()")->fetchColumn();
$totalCourses = (int) db()->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalLessons = (int) db()->query("SELECT COUNT(*) FROM lessons")->fetchColumn();

$recentUsers = db()->query("SELECT name, email, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC LIMIT 6")->fetchAll();
?>

<div class="admin-topbar">
  <h1>Dashboard</h1>
</div>

<div class="stat-grid">
  <div class="stat-box"><div class="num mono"><?= $totalUsers ?></div><div class="label">Registered users</div></div>
  <div class="stat-box"><div class="num mono"><?= $activeSubs ?></div><div class="label">Active subscriptions</div></div>
  <div class="stat-box"><div class="num mono"><?= $totalCourses ?></div><div class="label">Courses</div></div>
  <div class="stat-box"><div class="num mono"><?= $totalLessons ?></div><div class="label">Lessons</div></div>
</div>

<h3 style="font-size:15px; margin-bottom:14px;">Recent signups</h3>
<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
    <tbody>
      <?php foreach ($recentUsers as $u): ?>
      <tr>
        <td><?= h($u['name']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td class="mono"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$recentUsers): ?><tr><td colspan="3" style="color:var(--text-faint);">No users yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
