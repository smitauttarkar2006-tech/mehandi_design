<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $stmt = $conn->prepare('SELECT id, username, password FROM admin_users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Username or password was not correct.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin login | Mehndi Shop</title><link rel="stylesheet" href="../css/style.css"><script src="../js/script.js" defer></script></head><body>
<section class="auth-section"><div class="auth-card"><p class="eyebrow">Design content only</p><h1>Admin log in</h1><?php $flash = get_flash(); if ($flash): ?><div class="flash flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div><?php endif; ?><?php if ($error): ?><div class="form-errors" role="alert"><?= h($error) ?></div><?php endif; ?><form method="post" data-validate><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><label for="username">Username</label><input id="username" name="username" required autocomplete="username"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"><button class="button button-green button-wide" type="submit">Log in</button></form><p class="auth-switch"><a href="../index.php">← Back to website</a></p></div></section></body></html>
