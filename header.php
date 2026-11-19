<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'Mehndi Shop';
$activePage = $activePage ?? '';
$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#183b2c">
    <title><?= h($pageTitle) ?> | Mehndi Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php" aria-label="Mehndi Shop home">
        <span class="brand-mark">❋</span><span>Mehndi <em>Shop</em></span>
    </a>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-label="Open navigation">☰</button>
    <nav class="main-nav" aria-label="Main navigation">
        <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="index.php">Home</a>
        <a class="<?= $activePage === 'designs' ? 'active' : '' ?>" href="designs.php">Designs</a>
        <a class="<?= $activePage === 'recommend' ? 'active' : '' ?>" href="ai_recommendation.php">AI Recommendation</a>
        <a class="<?= $activePage === 'favorites' ? 'active' : '' ?>" href="favorites.php">Favorites</a>
        <?php if (is_logged_in()): ?>
            <a href="logout.php">Log out</a>
        <?php else: ?>
            <a class="<?= $activePage === 'account' ? 'active' : '' ?>" href="login.php">Login / Register</a>
        <?php endif; ?>
    </nav>
</header>
<main>
<?php if ($flash): ?>
    <div class="flash flash-<?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div>
<?php endif; ?>
