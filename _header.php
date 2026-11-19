<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$adminTitle = $adminTitle ?? 'Design content';
$flash = get_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= h($adminTitle) ?> | Mehndi Shop Admin</title><link rel="stylesheet" href="../css/style.css"><script src="../js/script.js" defer></script></head><body>
<header class="site-header"><a class="brand" href="../index.php"><span class="brand-mark">❋</span><span>Mehndi <em>Shop</em></span></a><nav class="main-nav admin-nav"><a href="dashboard.php">Designs</a><a href="categories.php">Categories</a><a href="../index.php">View website</a><a href="logout.php">Log out</a></nav></header><main class="admin-main">
<?php if ($flash): ?><div class="flash flash-<?= h($flash['type']) ?>" role="status"><?= h($flash['message']) ?></div><?php endif; ?>
