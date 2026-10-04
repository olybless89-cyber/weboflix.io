<?php
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$catStmt = db()->prepare('SELECT * FROM categories WHERE slug = ?');
$catStmt->execute([$slug]);
$category = $catStmt->fetch();
if (!$category) { http_response_code(404); die('Category not found.'); }

$courseStmt = db()->prepare(
    "SELECT c.*,
        (SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) AS lesson_count,
        (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id AND m.access_level = 'free') AS free_modules
     FROM courses c
     WHERE c.category_id = ? AND c.is_published = 1
     ORDER BY c.sort_order ASC, c.id DESC"
);
$courseStmt->execute([$category['id']]);
$courses = $courseStmt->fetchAll();

$pageTitle = $category['name'];
include __DIR__ . '/includes/header.php';
?>

<main class="container" style="padding: 44px 32px 60px;">
  <div class="eyebrow"><?= count($courses) ?> courses</div>
  <h1 style="font-size:30px; margin-bottom:32px;"><?= h($category['name']) ?></h1>

  <?php if (!$courses): ?>
    <p style="color:var(--text-dim);">No courses in this track yet — check back soon.</p>
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
