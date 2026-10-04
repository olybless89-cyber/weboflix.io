<?php
$pageTitle = 'Metrics';
$__adminPage = 'metrics';
require_once __DIR__ . '/includes/header.php';

$activeNow = (int) db()->query("SELECT COUNT(*) FROM users WHERE is_admin=0 AND last_active_at > (NOW() - INTERVAL 5 MINUTE)")->fetchColumn();
$activeToday = (int) db()->query("SELECT COUNT(*) FROM users WHERE is_admin=0 AND last_active_at > (NOW() - INTERVAL 1 DAY)")->fetchColumn();
$activeWeek = (int) db()->query("SELECT COUNT(*) FROM users WHERE is_admin=0 AND last_active_at > (NOW() - INTERVAL 7 DAY)")->fetchColumn();
$totalUsers = (int) db()->query("SELECT COUNT(*) FROM users WHERE is_admin=0")->fetchColumn();

// Signups per day, last 7 days
$signupRows = db()->query(
    "SELECT DATE(created_at) AS d, COUNT(*) AS c FROM users
     WHERE is_admin = 0 AND created_at > (NOW() - INTERVAL 7 DAY)
     GROUP BY DATE(created_at) ORDER BY d ASC"
)->fetchAll();
$signupByDay = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $signupByDay[$day] = 0;
}
foreach ($signupRows as $r) { $signupByDay[$r['d']] = (int) $r['c']; }
$maxSignups = max(1, max($signupByDay));

// Most recently active users
$recentActive = db()->query(
    "SELECT u.name, u.email, u.last_active_at, TIMESTAMPDIFF(SECOND, u.last_active_at, NOW()) AS seconds_ago,
        (SELECT COUNT(*) FROM subscriptions s WHERE s.user_id=u.id AND s.status='active' AND s.expires_at > NOW()) AS has_sub
     FROM users u
     WHERE u.is_admin = 0 AND u.last_active_at IS NOT NULL
     ORDER BY u.last_active_at DESC LIMIT 10"
)->fetchAll();

// Most-engaged courses (distinct learners with progress in last 30 days)
$topCourses = db()->query(
    "SELECT c.title, COUNT(DISTINCT lp.user_id) AS learners, COUNT(lp.id) AS lesson_opens
     FROM lesson_progress lp
     JOIN courses c ON c.id = lp.course_id
     WHERE lp.updated_at > (NOW() - INTERVAL 30 DAY)
     GROUP BY c.id ORDER BY learners DESC LIMIT 6"
)->fetchAll();

$neverActive = (int) db()->query("SELECT COUNT(*) FROM users WHERE is_admin=0 AND last_active_at IS NULL")->fetchColumn();
?>

<div class="admin-topbar"><h1>Metrics</h1></div>

<div class="stat-grid">
  <div class="stat-box"><div class="num mono"><?= $activeNow ?></div><div class="label">Active now (5 min)</div></div>
  <div class="stat-box"><div class="num mono"><?= $activeToday ?></div><div class="label">Active today</div></div>
  <div class="stat-box"><div class="num mono"><?= $activeWeek ?></div><div class="label">Active this week</div></div>
  <div class="stat-box"><div class="num mono"><?= $totalUsers ?></div><div class="label">Total users</div></div>
</div>

<h3 style="font-size:15px; margin-bottom:14px;">Signups, last 7 days</h3>
<div style="border:1px solid var(--border); border-radius:var(--radius-lg); padding:20px 24px; margin-bottom:32px; background:var(--bg-card);">
  <?php foreach ($signupByDay as $day => $count): $pct = (int) round(($count / $maxSignups) * 100); ?>
    <div style="display:flex; align-items:center; gap:14px; margin-bottom:10px;">
      <span class="mono" style="width:70px; font-size:12px; color:var(--text-faint); flex-shrink:0;"><?= date('M j', strtotime($day)) ?></span>
      <div style="flex:1; background:var(--border); border-radius:4px; height:10px; overflow:hidden;">
        <div style="width:<?= max($pct, $count > 0 ? 6 : 0) ?>%; background:var(--accent); height:100%;"></div>
      </div>
      <span class="mono" style="width:24px; text-align:right; font-size:12px; flex-shrink:0;"><?= $count ?></span>
    </div>
  <?php endforeach; ?>
</div>

<div style="display:grid; grid-template-columns:1.3fr 1fr; gap:24px; align-items:start;">
  <div>
    <h3 style="font-size:15px; margin-bottom:14px;">Recently active</h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Plan</th><th>Last active</th></tr></thead>
        <tbody>
          <?php foreach ($recentActive as $u): ?>
          <tr>
            <td><?= h($u['name']) ?><br><span style="color:var(--text-faint);font-size:12px;"><?= h($u['email']) ?></span></td>
            <td><span class="pill <?= $u['has_sub'] ? 'premium' : 'free' ?>"><?= $u['has_sub'] ? 'Premium' : 'Free' ?></span></td>
            <td class="mono"><?= time_ago($u['last_active_at'], (int) $u['seconds_ago']) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$recentActive): ?><tr><td colspan="3" style="color:var(--text-faint);">No activity recorded yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if ($neverActive > 0): ?>
      <p style="font-size:12px; color:var(--text-faint); margin-top:10px;"><?= $neverActive ?> registered user<?= $neverActive === 1 ? '' : 's' ?> ha<?= $neverActive === 1 ? 's' : 've' ?> never returned since signing up.</p>
    <?php endif; ?>
  </div>

  <div>
    <h3 style="font-size:15px; margin-bottom:14px;">Most engaged courses (30 days)</h3>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Course</th><th>Learners</th></tr></thead>
        <tbody>
          <?php foreach ($topCourses as $c): ?>
          <tr>
            <td><?= h($c['title']) ?></td>
            <td class="mono"><?= $c['learners'] ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$topCourses): ?><tr><td colspan="2" style="color:var(--text-faint);">No lesson activity yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<p style="font-size:12px; color:var(--text-faint); margin-top:24px;">
  "Active" is based on real page activity — any page load counts, and the watch page also pings every 2 minutes so long video sessions don't drop out of "active now."
</p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
