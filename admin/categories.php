<?php
$pageTitle = 'Categories';
$__adminPage = 'categories';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $slug = slugify($name);
            $stmt = db()->prepare('INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)');
            $stmt->execute([$name, $slug, (int) ($_POST['sort_order'] ?? 0)]);
        }
    } elseif ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
    }
    header('Location: ' . base_url('admin/categories.php'));
    exit;
}

$categories = db()->query(
    "SELECT c.*, (SELECT COUNT(*) FROM courses co WHERE co.category_id = c.id) AS course_count
     FROM categories c ORDER BY sort_order ASC, id ASC"
)->fetchAll();
?>

<div class="admin-topbar">
  <h1>Categories</h1>
</div>

<div class="table-wrap" style="margin-bottom:28px;">
  <table>
    <thead><tr><th>Name</th><th>Slug</th><th>Courses</th><th>Order</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($categories as $c): ?>
      <tr>
        <td><?= h($c['name']) ?></td>
        <td class="mono"><?= h($c['slug']) ?></td>
        <td><?= $c['course_count'] ?></td>
        <td class="mono"><?= $c['sort_order'] ?></td>
        <td>
          <form method="post" onsubmit="return confirm('Delete this category and all its courses?');" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<h3 style="font-size:15px; margin-bottom:14px;">Add category</h3>
<form method="post" style="max-width:420px;">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <input type="hidden" name="action" value="create">
  <div class="field"><label>Name</label><input type="text" name="name" required placeholder="e.g. Data Science"></div>
  <div class="field"><label>Sort order</label><input type="number" name="sort_order" value="0"></div>
  <button class="btn btn-primary" type="submit">Add category</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
