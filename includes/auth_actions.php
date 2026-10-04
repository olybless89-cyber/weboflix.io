<?php
require_once __DIR__ . '/functions.php';

/**
 * Shared by register.php and ajax_register.php so both stay in sync.
 * Returns ['ok' => bool, 'error' => ?string, 'user_id' => ?int].
 */
function attempt_register(string $name, string $email, string $password): array {
    $name = trim($name);
    $email = trim(strtolower($email));

    if (!$name || !$email || strlen($password) < 6) {
        return ['ok' => false, 'error' => 'Please fill in every field. Password must be at least 6 characters.', 'user_id' => null];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Enter a valid email address.', 'user_id' => null];
    }

    $chk = db()->prepare('SELECT id FROM users WHERE email = ?');
    $chk->execute([$email]);
    if ($chk->fetch()) {
        return ['ok' => false, 'error' => 'An account with that email already exists.', 'user_id' => null];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $colors = ['#4C6EF5', '#5FC9A8', '#E2685A', '#8AA0F8'];
    $ins = db()->prepare('INSERT INTO users (name, email, password_hash, avatar_color) VALUES (?, ?, ?, ?)');
    $ins->execute([$name, $email, $hash, $colors[array_rand($colors)]]);

    return ['ok' => true, 'error' => null, 'user_id' => (int) db()->lastInsertId()];
}

/**
 * Shared by login.php and ajax_login.php.
 */
function attempt_login(string $email, string $password): array {
    $email = trim(strtolower($email));
    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($password, $u['password_hash'])) {
        return ['ok' => true, 'error' => null, 'user_id' => (int) $u['id']];
    }
    return ['ok' => false, 'error' => 'Incorrect email or password.', 'user_id' => null];
}
