<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Your Favorites';
$activePage = 'favorites';
$stmt = $conn->prepare('SELECT d.id, d.design_name, d.image, d.style, d.complexity, d.price, c.category_name FROM favorites f JOIN designs d ON d.id = f.design_id JOIN categories c ON c.id = d.category_id WHERE f.user_id = ? ORDER BY f.created_at DESC');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$designs = $stmt->get_result();
require __DIR__ . '/includes/header.php';
?>
<section class="page-banner"><p class="eyebrow">Your collection</p><h1>Saved with <span>love.</span></h1><p>All the designs you have kept close, together in one place.</p></section>
<section class="section gallery-section"><?php if ($designs->num_rows): ?><div class="design-grid">
<?php while ($design = $designs->fetch_assoc()): ?><article class="design-card"><a class="card-image" href="design_details.php?id=<?= (int) $design['id'] ?>"><img src="<?= h(image_url($design['image'])) ?>" alt="<?= h($design['design_name']) ?>" loading="lazy"><span class="image-badge"><?= h($design['category_name']) ?></span></a><div class="card-content"><h3><?= h($design['design_name']) ?></h3><p class="design-price"><?= h(format_price($design['price'])) ?></p><p><?= h($design['style']) ?> · <?= h($design['complexity']) ?></p><div class="card-actions"><a class="card-link" href="design_details.php?id=<?= (int) $design['id'] ?>">View details <span>↗</span></a><form method="post" action="favorite_action.php" data-confirm="Remove this design from your favorites?"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="design_id" value="<?= (int) $design['id'] ?>"><input type="hidden" name="action" value="remove"><button class="icon-button is-favorite" type="submit" aria-label="Remove from favorites">♥</button></form></div></div></article><?php endwhile; ?>
</div><?php else: ?><div class="empty-state"><span>♡</span><h2>Your collection is waiting</h2><p>Browse designs and save the ones you love.</p><a class="button button-green" href="designs.php">Explore designs</a></div><?php endif; ?></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
