<?php
/**
 * Flutterwave redirects here after checkout. We re-verify the transaction
 * server-side (never trust query params alone) before activating access.
 * Docs: https://developer.flutterwave.com/docs/verifications
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$status = $_GET['status'] ?? '';
$txId = $_GET['transaction_id'] ?? '';
$pending = $_SESSION['pending_tx'] ?? null;

$pageTitle = 'Payment status';
$__page = 'pricing';

function render_result(string $title, string $message, bool $success): void {
    global $pageTitle;
    include __DIR__ . '/includes/header.php';
    ?>
    <div class="auth-wrap">
      <div class="auth-card" style="text-align:center;">
        <h1><?= h($title) ?></h1>
        <p class="sub"><?= h($message) ?></p>
        <a class="btn btn-primary btn-block" href="<?= base_url($success ? 'dashboard.php' : 'pricing.php') ?>"><?= $success ? 'Go to dashboard' : 'Back to pricing' ?></a>
      </div>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

if ($status !== 'successful' || !$txId || !$pending || (int) $pending['user_id'] !== (int) $user['id']) {
    render_result('Payment not completed', 'Your payment was cancelled or did not go through. No charge was made.', false);
}

// Verify with Flutterwave directly
$ch = curl_init("https://api.flutterwave.com/v3/transactions/{$txId}/verify");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . FLW_SECRET_KEY],
]);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response ?: '', true);

$verified = $data['status'] ?? '';
$txData = $data['data'] ?? [];
$expectedAmount = $pending['plan'] === 'yearly' ? PRICE_YEARLY : PRICE_MONTHLY;

if (
    $verified === 'success'
    && ($txData['status'] ?? '') === 'successful'
    && ($txData['tx_ref'] ?? '') === $pending['ref']
    && (float) ($txData['amount'] ?? 0) >= $expectedAmount
    && ($txData['currency'] ?? '') === 'NGN'
) {
    $plan = $pending['plan'];
    $expiresInterval = $plan === 'yearly' ? '+1 year' : '+1 month';
    $expiresAt = date('Y-m-d H:i:s', strtotime($expiresInterval));

    $ins = db()->prepare(
        "INSERT INTO subscriptions (user_id, plan, status, amount_kobo, flutterwave_ref, flutterwave_tx_id, expires_at)
         VALUES (?, ?, 'active', ?, ?, ?, ?)"
    );
    $ins->execute([$user['id'], $plan, (int) ($txData['amount'] ?? 0) * 100, $pending['ref'], $txId, $expiresAt]);

    unset($_SESSION['pending_tx']);
    render_result('You\'re Premium now', 'Every locked module on Weboflix is unlocked. Enjoy the courses.', true);
} else {
    render_result('We could not verify this payment', 'If you were charged, contact support with your reference: ' . h($pending['ref']), false);
}
