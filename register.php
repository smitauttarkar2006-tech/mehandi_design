<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Create an Account';
$activePage = 'account';
$errors = [];
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter your name (up to 100 characters).';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Your password must be at least 8 characters long.';
    if ($password !== $confirm) $errors[] = 'The passwords do not match.';
    if (!$errors) {
        $check = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->fetch_assoc()) {
            $errors[] = 'An account already uses that email. Please log in instead.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $hash);
            $stmt->execute();
            set_flash('success', 'Your account is ready. Please log in.');
            header('Location: login.php');
            exit;
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section"><div class="auth-card"><p class="eyebrow">Join the community</p><h1>Create your account</h1><p class="form-intro">Save favourite designs and return to the patterns you love.</p>
<?php if ($errors): ?><div class="form-errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" data-validate>
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
    <label for="name">Full name</label><input id="name" name="name" type="text" maxlength="100" autocomplete="name" required value="<?= h($name) ?>">
    <label for="email">Email address</label><input id="email" name="email" type="email" maxlength="190" autocomplete="email" required value="<?= h($email) ?>">
    <label for="password">Password <span class="label-hint">(at least 8 characters)</span></label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
    <label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required>
    <button class="button button-green button-wide" type="submit">Create account</button>
</form><p class="auth-switch">Already registered? <a href="login.php">Log in</a></p></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
