<?php
$pageTitle = 'Lessons';
$__adminPage = 'courses';
require_once __DIR__ . '/includes/header.php';

$moduleId = (int) ($_GET['module_id'] ?? 0);
$modStmt = db()->prepare('SELECT m.*, c.title AS course_title, c.id AS course_id FROM modules m JOIN courses c ON c.id = m.course_id WHERE m.id = ?');
$modStmt->execute([$moduleId]);
$module = $modStmt->fetch();
if (!$module) { die('Module not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $yt = trim($_POST['youtube_id'] ?? '');
        // Accept either a raw ID or a pasted YouTube URL
        if (preg_match('~(?:youtu\.be/|v=|embed/)([A-Za-z0-9_-]{11})~', $yt, $mMatch)) {
            $yt = $mMatch[1];
        }
        if ($title !== '' && $yt !== '') {
            $stmt = db()->prepare(
                'INSERT INTO lessons (module_id, title, youtube_id, duration_seconds, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$moduleId, $title, $yt, (int) ($_POST['duration_minutes'] ?? 0) * 60, trim($_POST['description'] ?? ''), (int) ($_POST['sort_order'] ?? 0)]);
        }
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM lessons WHERE id = ? AND module_id = ?');
        $stmt->execute([(int) $_POST['id'], $moduleId]);
    }
    header('Location: ' . base_url('admin/lessons.php?module_id=' . $moduleId));
    exit;
}

$lessons = db()->prepare('SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order ASC, id ASC');
$lessons->execute([$moduleId]);
$lessons = $lessons->fetchAll();
?>

<div class="admin-topbar">
  <h1><?= h($module['course_title']) ?> &mdash; <?= h($module['title']) ?></h1>
  <a class="btn btn-ghost btn-sm" href="<?= base_url('admin/modules.php?course_id=' . $module['course_id']) ?>">&larr; Modules</a>
</div>

<div class="table-wrap" style="margin-bottom:28px;">
  <table>
    <thead><tr><th>Title</th><th>YouTube ID</th><th>Duration</th><th>Order</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($lessons as $l): ?>
      <tr>
        <td><?= h($l['title']) ?></td>
        <td class="mono"><?= h($l['youtube_id']) ?></td>
        <td class="mono"><?= format_duration($l['duration_seconds']) ?></td>
        <td class="mono"><?= $l['sort_order'] ?></td>
        <td>
          <form method="post" onsubmit="return confirm('Delete this lesson?');" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $l['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$lessons): ?><tr><td colspan="5" style="color:var(--text-faint);">No lessons yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h3 style="font-size:15px; margin-bottom:14px;">Add lesson</h3>
<form method="post" style="max-width:460px;">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="action" value="create">
  <div class="field"><label>Title</label><input type="text" name="title" required></div>
  <div class="field"><label>YouTube URL or video ID</label><input type="text" name="youtube_id" required placeholder="https://youtu.be/xxxxxxxxxxx"></div>
  <div class="field-hint" style="margin-top:-10px;margin-bottom:16px;">Paste the unlisted video's link — it's converted automatically.</div>
  <div class="field"><label>Duration (minutes)</label><input type="number" name="duration_minutes" value="0"></div>
  <div class="field"><label>Description</label><textarea name="description" rows="2"></textarea></div>
  <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="0"></div>
  <button class="btn btn-primary" type="submit">Add lesson</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
