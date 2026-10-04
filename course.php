<?php
require_once __DIR__ . '/includes/functions.php';
$user = current_user();

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare("SELECT c.*, cat.name AS category_name FROM courses c JOIN categories cat ON cat.id = c.category_id WHERE c.slug = ? AND c.is_published = 1");
$stmt->execute([$slug]);
$course = $stmt->fetch();
if (!$course) { http_response_code(404); die('Course not found.'); }

$modStmt = db()->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order ASC, id ASC");
$modStmt->execute([$course['id']]);
$modules = $modStmt->fetchAll();

$lessonStmt = db()->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order ASC, id ASC");

$doneLessonIds = [];
if ($user) {
    $doneStmt = db()->prepare("SELECT lesson_id FROM lesson_progress WHERE user_id = ? AND course_id = ? AND completed = 1");
    $doneStmt->execute([$user['id'], $course['id']]);
    $doneLessonIds = array_column($doneStmt->fetchAll(), 'lesson_id');
}

$lessonCount = course_lesson_count((int) $course['id']);
$totalDuration = course_total_duration((int) $course['id']);
$progressPct = $user ? course_progress_percent((int) $user['id'], (int) $course['id']) : 0;
$lastWatched = $user ? get_last_watched_lesson((int) $user['id'], (int) $course['id']) : null;

$bannerImg = $course['banner_url'] ?: $course['thumbnail_url'] ?: 'https://i.ytimg.com/vi/EUfyzKqmZbk/maxresdefault.jpg';

$pageTitle = $course['title'];
$__page = 'course';
include __DIR__ . '/includes/header.php';
?>

<div class="course-banner" style="background-image: url('<?= h($bannerImg) ?>');">
  <div class="container" style="max-width:960px;">
    <div class="netflix-badge-pill">
      <span class="n-logo">W</span>
      <span class="series-text">SERIES &bull; <?= strtoupper(h($course['category_name'])) ?></span>
    </div>
    <h1><?= h($course['title']) ?></h1>

    <div class="hero-meta-row" style="margin-bottom:16px;">
      <span class="hero-match-score">98% Match</span>
      <span class="badge-quality">4K ULTRA HD</span>
      <span class="badge-level"><?= ucfirst($course['level']) ?></span>
      <span class="badge-quality"><?= $lessonCount ?> Lessons</span>
      <span class="badge-quality"><?= format_duration($totalDuration) ?></span>
    </div>

    <p><?= nl2br(h($course['description'])) ?></p>

    <?php if ($user && $progressPct > 0): ?>
    <div style="max-width:400px; margin-bottom:20px;">
      <div class="progress-bar-container">
        <div class="progress-bar-fill" style="width: <?= $progressPct ?>%"></div>
      </div>
      <div class="mono" style="font-size:12px; color:var(--text-dim); margin-top:6px;"><?= $progressPct ?>% complete</div>
    </div>
    <?php endif; ?>

    <div class="hero-actions">
      <?php if ($lastWatched): ?>
        <a class="btn-netflix-play" href="<?= base_url('watch.php?lesson=' . $lastWatched['lesson_id']) ?>">
          <svg width="20" height="20" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"/></svg>
          Resume: <?= h($lastWatched['lesson_title']) ?>
        </a>
      <?php elseif (!empty($modules)): ?>
        <?php
          $lessonStmt->execute([$modules[0]['id']]);
          $firstLesson = $lessonStmt->fetch();
        ?>
        <?php if ($firstLesson): ?>
        <a class="btn-netflix-play" href="<?= base_url('watch.php?lesson=' . $firstLesson['id']) ?>" <?= $user ? '' : 'data-auth-gate' ?>>
          <svg width="20" height="20" viewBox="0 0 24 24"><polygon points="6,4 20,12 6,20"/></svg>
          Start Course
        </a>
        <?php endif; ?>
      <?php endif; ?>

      <button type="button" class="btn-netflix-info" onclick="wfToggleMyList(<?= (int) $course['id'] ?>, this)">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <span>Add to My List</span>
      </button>
    </div>
  </div>
</div>

<main class="container" style="max-width:960px; padding: 40px clamp(20px, 4vw, 32px) 80px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <h2 style="font-size:24px;">Episodes &amp; Curriculum</h2>
    <span class="mono" style="color:var(--text-dim);font-size:13px;">Instructor: Web Oracle</span>
  </div>

  <?php foreach ($modules as $i => $module):
      $lessonStmt->execute([$module['id']]);
      $lessons = $lessonStmt->fetchAll();
      $unlocked = can_access_module($module, $user);
  ?>
  <div class="module">
    <div class="module-head">
      <span class="idx mono" style="color:var(--accent);font-weight:700;"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
      <h4><?= h($module['title']) ?></h4>
      <span class="module-tag <?= $module['access_level'] ?>"><?= $module['access_level'] ?></span>
    </div>
    <div class="lesson-list">
      <?php foreach ($lessons as $j => $lesson):
          $isDone = in_array($lesson['id'], $doneLessonIds);
          $lThumb = 'https://i.ytimg.com/vi/' . $lesson['youtube_id'] . '/hqdefault.jpg';
      ?>
        <?php if ($unlocked): ?>
        <a class="lesson <?= $isDone ? 'done' : '' ?>" href="<?= base_url('watch.php?lesson=' . $lesson['id']) ?>" <?= $user ? '' : 'data-auth-gate' ?>>
          <span class="lesson-num mono"><?= $isDone ? '✓' : $j + 1 ?></span>
          <img src="<?= h($lThumb) ?>" alt="" style="width:84px;height:50px;border-radius:4px;object-fit:cover;">
          <div style="flex:1;">
            <div class="lesson-title"><?= h($lesson['title']) ?></div>
            <?php if ($lesson['description']): ?>
            <div style="font-size:12px;color:var(--text-dim);line-height:1.4;margin-top:2px;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;">
              <?= h($lesson['description']) ?>
            </div>
            <?php endif; ?>
          </div>
          <span class="lesson-duration mono"><?= format_duration((int) $lesson['duration_seconds']) ?></span>
        </a>
        <?php else: ?>
        <div class="lesson locked">
          <span class="lesson-num mono"><?= $j + 1 ?></span>
          <img src="<?= h($lThumb) ?>" alt="" style="width:84px;height:50px;border-radius:4px;object-fit:cover;filter:grayscale(1);">
          <div style="flex:1;">
            <div class="lesson-title"><?= h($lesson['title']) ?></div>
            <div style="font-size:12px;color:var(--text-faint);">Locked &bull; Premium Subscription Required</div>
          </div>
          <span class="lesson-lock" title="Premium subscription required">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          </span>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (!$user): ?>
    <div class="alert alert-error" style="margin-top:32px;">
      <a href="<?= base_url('register.php') ?>" style="color:inherit;font-weight:700;">Create a free account</a> to track your progress and unlock all premium modules with a subscription.
    </div>
  <?php elseif (!has_active_subscription((int) $user['id'])): ?>
    <div class="alert" style="margin-top:32px; border-color: var(--accent); color: #fff; background: var(--accent-soft);">
      Some modules in this masterclass are locked. <a href="<?= base_url('pricing.php') ?>" style="color:var(--accent);font-weight:700;">Go Premium</a> to unlock every course on Weboflix.
    </div>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
