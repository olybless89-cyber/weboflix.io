<?php
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');
$courses = [];
if ($q !== '') {
    $stmt = db()->prepare(
        "SELECT c.*,
            (SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) AS lesson_count,
            (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id AND m.access_level = 'free') AS free_modules
         FROM courses c
         WHERE c.is_published = 1 AND (c.title LIKE ? OR c.description LIKE ?)
         ORDER BY c.title ASC"
    );
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
    $courses = $stmt->fetchAll();
}

$pageTitle = 'Search';
include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding: 44px 32px 60px;">
  <div class="eyebrow"><?= count($courses) ?> results</div>
  <h1 style="font-size:26px; margin-bottom:32px;">Results for &ldquo;<?= h($q) ?>&rdquo;</h1>

  <?php if (!$courses): ?>
    <p style="color:var(--text-dim);">No courses matched that search. Try a different term.</p>
  <?php else: ?>
  <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px,1fr)); gap:18px;">
    <?php foreach ($courses as $course): $isFreeCourse = $course['free_modules'] > 0; ?>
    <a class="card" style="flex-basis:auto;" href="<?= base_url('course.php?slug=' . urlencode($course['slug'])) ?>">
      <div class="card-thumb">
        <?php if ($course['thumbnail_url']): ?><img src="<?= h($course['thumbnail_url']) ?>" alt=""><?php else: ?><svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" style="opacity:0.3;"><polygon points="6,4 20,12 6,20"/></svg><?php endif; ?>
        <span class="card-tag <?= $isFreeCourse ? 'free' : 'premium' ?>"><?= $isFreeCourse ? 'Free lessons' : 'Premium' ?></span>
      </div>
      <div class="card-body">
        <h3><?= h($course['title']) ?></h3>
        <div class="card-meta"><span class="mono"><?= $course['lesson_count'] ?> lessons</span><span>&middot;</span><span><?= ucfirst($course['level']) ?></span></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
