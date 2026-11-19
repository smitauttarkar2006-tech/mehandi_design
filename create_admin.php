<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
$count = (int) $conn->query('SELECT COUNT(*) AS total FROM admin_users')->fetch_assoc()['total'];
$error = '';
if ($count === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $error = 'Choose a username with 3–50 letters, numbers, dots, dashes or underscores.';
    } elseif (strlen($password) < 12) {
        $error = 'Choose an admin password with at least 12 characters.';
    } elseif ($password !== $confirm) {
        $error = 'The passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO admin_users (username, password) VALUES (?, ?)');
        $stmt->bind_param('ss', $username, $hash);
        $stmt->execute();
        set_flash('success', 'Admin account created. For safety, remove admin/create_admin.php now.');
        header('Location: login.php');
        exit;
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create first admin | Mehndi Shop</title><link rel="stylesheet" href="../css/style.css"><script src="../js/script.js" defer></script></head><body>
<section class="auth-section"><div class="auth-card"><p class="eyebrow">One-time setup</p><h1>Create the admin account</h1>
<?php if ($count > 0): ?><div class="analyzer-notice">An admin account already exists. This setup page is now locked. <a href="login.php">Go to admin login</a>.</div>
<?php else: ?><p class="form-intro">Create the account used only to maintain design and category content. Use a unique, strong password.</p><?php if ($error): ?><div class="form-errors" role="alert"><?= h($error) ?></div><?php endif; ?>
<form method="post" data-validate><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><label for="username">Admin username</label><input id="username" name="username" required minlength="3" maxlength="50" autocomplete="username"><label for="password">Password (at least 12 characters)</label><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" required minlength="12" autocomplete="new-password"><button class="button button-green button-wide" type="submit">Create admin account</button></form><p class="form-intro">After creating the account, delete <code>admin/create_admin.php</code> from your project folder.</p><?php endif; ?>
</div></section></body></html>
