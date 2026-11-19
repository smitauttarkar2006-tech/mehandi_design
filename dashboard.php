<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$adminTitle = 'Manage designs';
$designs = $conn->query('SELECT d.id, d.design_name, d.image, d.style, d.occasion, d.price, c.category_name FROM designs d JOIN categories c ON c.id = d.category_id ORDER BY d.created_at DESC');
require __DIR__ . '/_header.php';
?>
<section class="admin-page-heading"><div><p class="eyebrow">Content workspace</p><h1>Mehndi designs</h1><p>Add, edit or remove design content shown in the gallery and recommendations.</p></div><a class="button button-green" href="add_design.php">＋ Add a design</a></section>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Design</th><th>Category</th><th>Style</th><th>Occasion</th><th>Price</th><th>Actions</th></tr></thead><tbody>
<?php while ($design = $designs->fetch_assoc()): ?><tr><td><div class="table-design"><img src="../<?= h(image_url($design['image'])) ?>" alt=""><strong><?= h($design['design_name']) ?></strong></div></td><td><?= h($design['category_name']) ?></td><td><?= h($design['style']) ?></td><td><?= h($design['occasion']) ?></td><td><?= h(format_price($design['price'])) ?></td><td><div class="table-actions"><a href="edit_design.php?id=<?= (int) $design['id'] ?>">Edit</a><form method="post" action="delete_design.php" data-confirm="Delete this design? Saved favorites that reference it will also be removed."><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $design['id'] ?>"><button class="danger-link" type="submit">Delete</button></form></div></td></tr><?php endwhile; ?>
</tbody></table><?php if ($designs->num_rows === 0): ?><div class="empty-state"><h2>No designs yet</h2><a class="button button-green" href="add_design.php">Add your first design</a></div><?php endif; ?></div>
<?php require __DIR__ . '/_footer.php'; ?>
