<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();

$lessonId = (int) ($_GET['lesson'] ?? 0);
$stmt = db()->prepare(
    "SELECT l.*, m.access_level, m.title AS module_title, m.course_id,
            c.title AS course_title, c.slug AS course_slug
     FROM lessons l
     JOIN modules m ON m.id = l.module_id
     JOIN courses c ON c.id = m.course_id
     WHERE l.id = ?"
);
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();
if (!$lesson) { http_response_code(404); die('Lesson not found.'); }

$module = ['access_level' => $lesson['access_level']];
$unlocked = can_access_module($module, $user);

// Sibling lessons in the same module + full module list for the sidebar
$courseId = (int) $lesson['course_id'];
$modStmt = db()->prepare("SELECT * FROM modules WHERE course_id = ? ORDER BY sort_order ASC, id ASC");
$modStmt->execute([$courseId]);
$modules = $modStmt->fetchAll();
$lessonStmt = db()->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order ASC, id ASC");

$doneLessonIds = [];
if ($user) {
    $doneStmt = db()->prepare("SELECT lesson_id FROM lesson_progress WHERE user_id = ? AND course_id = ? AND completed = 1");
    $doneStmt->execute([$user['id'], $courseId]);
    $doneLessonIds = array_column($doneStmt->fetchAll(), 'lesson_id');
}
$isDone = in_array($lesson['id'], $doneLessonIds);

// Determine prev/next across the whole course (flattened)
$flat = [];
foreach ($modules as $m) {
    $lessonStmt->execute([$m['id']]);
    foreach ($lessonStmt->fetchAll() as $l) {
        $l['access_level'] = $m['access_level'];
        $flat[] = $l;
    }
}
$curIdx = null;
foreach ($flat as $i => $l) { if ((int) $l['id'] === $lessonId) { $curIdx = $i; break; } }
$prevLesson = $curIdx !== null && $curIdx > 0 ? $flat[$curIdx - 1] : null;
$nextLesson = $curIdx !== null && $curIdx < count($flat) - 1 ? $flat[$curIdx + 1] : null;

if ($user && $unlocked) {
    // record that they opened the lesson
    $up = db()->prepare(
        "INSERT INTO lesson_progress (user_id, lesson_id, course_id, completed)
         VALUES (?, ?, ?, 0)
         ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP"
    );
    $up->execute([$user['id'], $lesson['id'], $courseId]);
}

$pageTitle = $lesson['title'];
$__page = 'watch';
$__trackHeartbeat = (bool) $user;
include __DIR__ . '/includes/header.php';
?>

<div class="cinema-watch-container">
  <!-- Cinema Topbar -->
  <div class="cinema-topbar">
    <a href="<?= base_url('course.php?slug=' . urlencode($lesson['course_slug'])) ?>" class="cinema-back-btn">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      <span>Back to Overview</span>
    </a>
    <div class="cinema-course-name mono">
      <?= h($lesson['course_title']) ?> &bull; <?= h($lesson['module_title']) ?>
    </div>
  </div>

  <div class="cinema-layout">
    <!-- Cinema Player Column -->
    <div class="cinema-player-col">
      <div class="cinema-player-wrap">
        <?php if ($unlocked): ?>
          <iframe
            id="watchVideoFrame"
            src="https://www.youtube-nocookie.com/embed/<?= h($lesson['youtube_id']) ?>?rel=0&modestbranding=1&autoplay=1"
            title="<?= h($lesson['title']) ?>"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
          </iframe>
        <?php else: ?>
          <div class="lock-overlay">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            <h3>This Masterclass Lesson is Premium</h3>
            <p>Upgrade to Weboflix Premium to unlock this lesson and full course modules across the platform.</p>
            <a href="<?= base_url('pricing.php') ?>" class="btn btn-primary" style="padding:12px 28px;font-size:15px;">Unlock Premium Access</a>
          </div>
        <?php endif; ?>
      </div>

      <div class="cinema-video-info">
        <div class="hero-meta-row" style="margin-bottom:10px;">
          <span class="hero-match-score">98% Match</span>
          <span class="badge-quality">HD</span>
          <span class="badge-quality"><?= format_duration((int) $lesson['duration_seconds']) ?></span>
          <?php if ($unlocked): ?>
            <span class="badge-level" style="background:rgba(70, 211, 105, 0.15);color:var(--netflix-green);border:1px solid rgba(70,211,105,0.4);">UNLOCKED</span>
          <?php endif; ?>
        </div>

        <h1 class="cinema-lesson-title"><?= h($lesson['title']) ?></h1>

        <?php if ($lesson['description']): ?>
          <p class="cinema-lesson-desc"><?= nl2br(h($lesson['description'])) ?></p>
        <?php endif; ?>

        <!-- Watch Actions & Nav -->
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; margin-top:24px; padding-top:20px; border-top:1px solid var(--border);">
          <?php if ($user && $unlocked): ?>
            <button id="completeBtn" class="btn <?= $isDone ? 'btn-ghost' : 'btn-primary' ?>" <?= $isDone ? 'disabled' : '' ?> onclick="wfMarkComplete(<?= $lesson['id'] ?>, <?= $courseId ?>)">
              <?= $isDone ? '✓ Completed' : 'Mark Lesson as Complete' ?>
            </button>
          <?php else: ?>
            <div></div>
          <?php endif; ?>

          <div style="display:flex; gap:12px;">
            <?php if ($prevLesson): ?>
              <a class="btn btn-ghost" href="<?= base_url('watch.php?lesson=' . $prevLesson['id']) ?>">
                &larr; Previous Episode
              </a>
            <?php endif; ?>
            <?php if ($nextLesson): ?>
              <a class="btn btn-primary" href="<?= base_url('watch.php?lesson=' . $nextLesson['id']) ?>">
                Next Episode &rarr;
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Cinema Episodes Sidebar -->
    <div class="cinema-sidebar-col">
      <div class="cinema-sidebar-head">
        <span>Episodes &amp; Lessons</span>
        <span class="mono" style="font-size:12px;color:var(--text-dim);"><?= count($flat) ?> total</span>
      </div>

      <?php foreach ($modules as $m):
          $lessonStmt->execute([$m['id']]);
          $mLessons = $lessonStmt->fetchAll();
          $mUnlocked = can_access_module($m, $user);
      ?>
        <div class="cinema-module-header">
          <span><?= h($m['title']) ?></span>
          <span class="module-tag <?= $m['access_level'] ?>"><?= $m['access_level'] ?></span>
        </div>

        <?php foreach ($mLessons as $j => $l):
            $active = (int) $l['id'] === $lessonId;
            $done = in_array($l['id'], $doneLessonIds);
            $lThumb = 'https://i.ytimg.com/vi/' . $l['youtube_id'] . '/hqdefault.jpg';
        ?>
          <?php if ($mUnlocked): ?>
            <a class="cinema-lesson-item <?= $active ? 'active' : '' ?> <?= $done ? 'done' : '' ?>" href="<?= base_url('watch.php?lesson=' . $l['id']) ?>">
              <span class="cinema-lesson-num"><?= $done ? '✓' : $j + 1 ?></span>
              <img src="<?= h($lThumb) ?>" alt="" style="width:70px;height:42px;border-radius:3px;object-fit:cover;">
              <div style="flex:1;">
                <div style="font-size:13px;font-weight:600;line-height:1.2;margin-bottom:3px;"><?= h($l['title']) ?></div>
                <div class="mono" style="font-size:11px;color:var(--text-faint);"><?= format_duration((int) $l['duration_seconds']) ?></div>
              </div>
            </a>
          <?php else: ?>
            <div class="cinema-lesson-item" style="opacity:0.5;cursor:not-allowed;">
              <span class="cinema-lesson-num"><?= $j + 1 ?></span>
              <img src="<?= h($lThumb) ?>" alt="" style="width:70px;height:42px;border-radius:3px;object-fit:cover;filter:grayscale(1);">
              <div style="flex:1;">
                <div style="font-size:13px;font-weight:600;"><?= h($l['title']) ?></div>
                <div class="mono" style="font-size:11px;color:var(--text-faint);">Locked (Premium)</div>
              </div>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
