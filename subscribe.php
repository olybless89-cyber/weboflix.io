<?php
/**
 * Initiates a Flutterwave Standard checkout for the chosen subscription plan.
 * Docs: https://developer.flutterwave.com/docs/collecting-payments/standard
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$plan = $_GET['plan'] ?? 'monthly';
if (!in_array($plan, ['monthly', 'yearly'], true)) { $plan = 'monthly'; }

$amount = $plan === 'yearly' ? PRICE_YEARLY : PRICE_MONTHLY;
$txRef = 'WFX-' . strtoupper($plan) . '-' . $user['id'] . '-' . time();

// Store the pending reference in session so the callback can verify it belongs to this user
$_SESSION['pending_tx'] = ['ref' => $txRef, 'plan' => $plan, 'user_id' => $user['id']];

$payload = [
    'tx_ref' => $txRef,
    'amount' => $amount,
    'currency' => 'NGN',
    'redirect_url' => base_url('flutterwave-callback.php'),
    'customer' => [
        'email' => $user['email'],
        'name' => $user['name'],
    ],
    'customizations' => [
        'title' => 'Weboflix Premium',
        'description' => ucfirst($plan) . ' subscription',
    ],
];

$ch = curl_init('https://api.flutterwave.com/v3/payments');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . FLW_SECRET_KEY,
    ],
]);
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

$data = json_decode($response ?: '', true);

if ($err || empty($data['data']['link'])) {
    flash_set('error', 'Could not start checkout. Please try again in a moment.');
    header('Location: ' . base_url('pricing.php'));
    exit;
}

header('Location: ' . $data['data']['link']);
exit;
