<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
$sub = get_active_subscription((int) $user['id']);

$stmt = db()->prepare(
    "SELECT c.*, MAX(lp.updated_at) AS last_activity
     FROM lesson_progress lp
     JOIN courses c ON c.id = lp.course_id
     WHERE lp.user_id = ?
     GROUP BY c.id
     ORDER BY last_activity DESC"
);
$stmt->execute([$user['id']]);
$myCourses = $stmt->fetchAll();

$pageTitle = 'My Learning';
$__page = 'dashboard';
include __DIR__ . '/includes/header.php';
?>

<div class="dash-header">
  <div class="container">
    <h1>My Learning</h1>
    <p class="sub">Welcome back, <?= h($user['name']) ?>.</p>
    <div style="margin-top:16px;">
      <?php if ($sub): ?>
        <span class="sub-status active"><?= h(ucfirst($sub['plan'])) ?> Premium &middot; renews <?= date('M j, Y', strtotime($sub['expires_at'])) ?></span>
      <?php else: ?>
        <span class="sub-status inactive">Free plan</span>
        <a href="<?= base_url('pricing.php') ?>" class="btn btn-sm btn-primary" style="margin-left:12px;">Upgrade to Premium</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<main class="container" style="padding: 32px 32px 60px;">
  <?php if (!$myCourses): ?>
    <div style="max-width:480px;">
      <h3 style="margin-bottom:8px;">You haven't started a course yet</h3>
      <p style="color:var(--text-dim); margin-bottom:20px;">Browse the catalog and press play on any free lesson to get going.</p>
      <a href="<?= base_url('index.php') ?>" class="btn btn-primary">Browse courses</a>
    </div>
  <?php else: ?>
    <div class="section-head"><h2>Your courses</h2></div>
    <div class="row-scroll">
      <?php foreach ($myCourses as $course):
          $pct = course_progress_percent((int) $user['id'], (int) $course['id']);
      ?>
      <a class="card" href="<?= base_url('course.php?slug=' . urlencode($course['slug'])) ?>">
        <div class="card-thumb">
          <?php if ($course['thumbnail_url']): ?><img src="<?= h($course['thumbnail_url']) ?>" alt=""><?php else: ?><svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" style="opacity:0.3;"><polygon points="6,4 20,12 6,20"/></svg><?php endif; ?>
        </div>
        <div class="card-body">
          <h3><?= h($course['title']) ?></h3>
          <div class="progress-track"><div class="progress-fill" style="width: <?= $pct ?>%"></div></div>
          <div class="card-meta" style="margin-top:8px;margin-bottom:0;"><?= $pct ?>% complete</div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
