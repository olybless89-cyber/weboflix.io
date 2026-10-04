<?php
$pageTitle = 'Courses';
$__adminPage = 'courses';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        if ($title !== '') {
            $slug = slugify($title) . '-' . substr(bin2hex(random_bytes(2)), 0, 4);
            $stmt = db()->prepare(
                'INSERT INTO courses (category_id, title, slug, description, thumbnail_url, level, is_published, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                (int) $_POST['category_id'],
                $title,
                $slug,
                trim($_POST['description'] ?? ''),
                trim($_POST['thumbnail_url'] ?? ''),
                $_POST['level'] ?? 'beginner',
                isset($_POST['is_published']) ? 1 : 0,
                (int) ($_POST['sort_order'] ?? 0),
            ]);
        }
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
    } elseif ($action === 'toggle_publish') {
        $stmt = db()->prepare('UPDATE courses SET is_published = 1 - is_published WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
    } elseif ($action === 'toggle_featured') {
        $id = (int) $_POST['id'];
        $current = db()->prepare('SELECT is_featured FROM courses WHERE id = ?');
        $current->execute([$id]);
        $isCurrentlyFeatured = (bool) $current->fetchColumn();
        // Only one course is ever "featured" at a time — clear any existing one first
        db()->exec('UPDATE courses SET is_featured = 0');
        if (!$isCurrentlyFeatured) {
            $stmt = db()->prepare('UPDATE courses SET is_featured = 1 WHERE id = ?');
            $stmt->execute([$id]);
        }
    }
    header('Location: ' . base_url('admin/courses.php'));
    exit;
}

$categories = db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
$courses = db()->query(
    "SELECT c.*, cat.name AS category_name,
        (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS module_count
     FROM courses c JOIN categories cat ON cat.id = c.category_id
     ORDER BY c.id DESC"
)->fetchAll();
?>

<div class="admin-topbar"><h1>Courses</h1></div>

<div class="table-wrap" style="margin-bottom:28px;">
  <table>
    <thead><tr><th>Title</th><th>Category</th><th>Modules</th><th>Status</th><th>Hero video</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($courses as $c): ?>
      <tr>
        <td><?= h($c['title']) ?></td>
        <td><?= h($c['category_name']) ?></td>
        <td><?= $c['module_count'] ?></td>
        <td><span class="pill <?= $c['is_published'] ? 'free' : 'premium' ?>"><?= $c['is_published'] ? 'Published' : 'Draft' ?></span></td>
        <td><?php if ($c['is_featured']): ?><span class="pill premium">Featured</span><?php endif; ?></td>
        <td style="white-space:nowrap;">
          <a class="btn btn-ghost btn-sm" href="<?= base_url('admin/modules.php?course_id=' . $c['id']) ?>">Modules</a>
          <form method="post" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="toggle_featured">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit"><?= $c['is_featured'] ? 'Unfeature' : 'Feature on homepage' ?></button>
          </form>
          <form method="post" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="toggle_publish">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit"><?= $c['is_published'] ? 'Unpublish' : 'Publish' ?></button>
          </form>
          <form method="post" onsubmit="return confirm('Delete this course and everything in it?');" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$courses): ?><tr><td colspan="6" style="color:var(--text-faint);">No courses yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<p style="font-size:12px; color:var(--text-faint); margin-top:-18px; margin-bottom:28px;">
  Only one course can be featured at a time — its first lesson plays as the homepage hero video. If none is marked, the most recently added course is used automatically.
</p>

<h3 style="font-size:15px; margin-bottom:14px;">Add course</h3>
<form method="post" style="max-width:520px;">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="action" value="create">
  <div class="field"><label>Title</label><input type="text" name="title" required></div>
  <div class="field"><label>Category</label>
    <select name="category_id" required>
      <?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field"><label>Description</label><textarea name="description" rows="3"></textarea></div>
  <div class="field"><label>Thumbnail URL</label><input type="text" name="thumbnail_url" placeholder="https://..."></div>
  <div class="field"><label>Level</label>
    <select name="level"><option value="beginner">Beginner</option><option value="intermediate">Intermediate</option><option value="advanced">Advanced</option></select>
  </div>
  <div class="field"><label><input type="checkbox" name="is_published" checked style="width:auto; display:inline-block; margin-right:8px;">Published</label></div>
  <button class="btn btn-primary" type="submit">Add course</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
