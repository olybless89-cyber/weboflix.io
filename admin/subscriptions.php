<?php
$pageTitle = 'Subscriptions';
$__adminPage = 'subscriptions';
require_once __DIR__ . '/includes/header.php';

$subs = db()->query(
    "SELECT s.*, u.name, u.email FROM subscriptions s JOIN users u ON u.id = s.user_id ORDER BY s.created_at DESC LIMIT 100"
)->fetchAll();

$mrr = (int) db()->query(
    "SELECT COALESCE(SUM(CASE WHEN plan='monthly' THEN amount_kobo/100 ELSE amount_kobo/100/12 END),0)
     FROM subscriptions WHERE status='active' AND expires_at > NOW()"
)->fetchColumn();
?>

<div class="admin-topbar"><h1>Subscriptions</h1></div>

<div class="stat-grid" style="grid-template-columns:repeat(2,1fr); max-width:520px;">
  <div class="stat-box"><div class="num mono">&#8358;<?= number_format($mrr) ?></div><div class="label">Est. monthly recurring revenue</div></div>
  <div class="stat-box"><div class="num mono"><?= count(array_filter($subs, fn($s) => $s['status']==='active' && strtotime($s['expires_at']) > time())) ?></div><div class="label">Active subscriptions</div></div>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>User</th><th>Plan</th><th>Amount</th><th>Status</th><th>Expires</th><th>Ref</th></tr></thead>
    <tbody>
      <?php foreach ($subs as $s): $isLive = $s['status']==='active' && strtotime($s['expires_at']) > time(); ?>
      <tr>
        <td><?= h($s['name']) ?><br><span style="color:var(--text-faint);font-size:12px;"><?= h($s['email']) ?></span></td>
        <td><?= h(ucfirst($s['plan'])) ?></td>
        <td class="mono">&#8358;<?= number_format($s['amount_kobo'] / 100) ?></td>
        <td><span class="pill <?= $isLive ? 'free' : 'premium' ?>"><?= $isLive ? 'Active' : ucfirst($s['status']) ?></span></td>
        <td class="mono"><?= date('M j, Y', strtotime($s['expires_at'])) ?></td>
        <td class="mono" style="font-size:12px;"><?= h($s['flutterwave_ref']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$subs): ?><tr><td colspan="6" style="color:var(--text-faint);">No subscriptions yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
