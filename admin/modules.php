<?php
$pageTitle = 'Modules';
$__adminPage = 'courses';
require_once __DIR__ . '/includes/header.php';

$courseId = (int) ($_GET['course_id'] ?? 0);
$courseStmt = db()->prepare('SELECT * FROM courses WHERE id = ?');
$courseStmt->execute([$courseId]);
$course = $courseStmt->fetch();
if (!$course) { die('Course not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        if ($title !== '') {
            $stmt = db()->prepare(
                'INSERT INTO modules (course_id, title, description, access_level, sort_order) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$courseId, $title, trim($_POST['description'] ?? ''), $_POST['access_level'] ?? 'premium', (int) ($_POST['sort_order'] ?? 0)]);
        }
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM modules WHERE id = ? AND course_id = ?');
        $stmt->execute([(int) $_POST['id'], $courseId]);
    } elseif ($action === 'toggle_access') {
        $stmt = db()->prepare("UPDATE modules SET access_level = IF(access_level='free','premium','free') WHERE id = ? AND course_id = ?");
        $stmt->execute([(int) $_POST['id'], $courseId]);
    }
    header('Location: ' . base_url('admin/modules.php?course_id=' . $courseId));
    exit;
}

$modules = db()->prepare('SELECT m.*, (SELECT COUNT(*) FROM lessons l WHERE l.module_id = m.id) AS lesson_count FROM modules m WHERE m.course_id = ? ORDER BY sort_order ASC, id ASC');
$modules->execute([$courseId]);
$modules = $modules->fetchAll();
?>

<div class="admin-topbar">
  <h1><?= h($course['title']) ?> &mdash; Modules</h1>
  <a class="btn btn-ghost btn-sm" href="<?= base_url('admin/courses.php') ?>">&larr; All courses</a>
</div>

<div class="table-wrap" style="margin-bottom:28px;">
  <table>
    <thead><tr><th>Title</th><th>Access</th><th>Lessons</th><th>Order</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($modules as $m): ?>
      <tr>
        <td><?= h($m['title']) ?></td>
        <td><span class="pill <?= $m['access_level'] ?>"><?= $m['access_level'] ?></span></td>
        <td><?= $m['lesson_count'] ?></td>
        <td class="mono"><?= $m['sort_order'] ?></td>
        <td style="white-space:nowrap;">
          <a class="btn btn-ghost btn-sm" href="<?= base_url('admin/lessons.php?module_id=' . $m['id']) ?>">Lessons</a>
          <form method="post" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="toggle_access">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit">Make <?= $m['access_level'] === 'free' ? 'Premium' : 'Free' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Delete this module and its lessons?');" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $m['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$modules): ?><tr><td colspan="5" style="color:var(--text-faint);">No modules yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<h3 style="font-size:15px; margin-bottom:14px;">Add module</h3>
<form method="post" style="max-width:460px;">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="action" value="create">
  <div class="field"><label>Title</label><input type="text" name="title" required placeholder="e.g. Getting Started"></div>
  <div class="field"><label>Description</label><textarea name="description" rows="2"></textarea></div>
  <div class="field"><label>Access level</label>
    <select name="access_level"><option value="free">Free</option><option value="premium" selected>Premium</option></select>
  </div>
  <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="0"></div>
  <button class="btn btn-primary" type="submit">Add module</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
