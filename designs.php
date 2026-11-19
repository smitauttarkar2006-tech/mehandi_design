<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Design Gallery';
$activePage = 'designs';
$search = trim((string) ($_GET['q'] ?? ''));
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;
$categories = $conn->query('SELECT id, category_name FROM categories ORDER BY category_name');
$sql = 'SELECT d.*, c.category_name,
               COALESCE(review_stats.average_rating, 0) AS average_rating,
               COALESCE(review_stats.review_count, 0) AS review_count
        FROM designs d
        JOIN categories c ON c.id = d.category_id
        LEFT JOIN (
            SELECT design_id, AVG(rating) AS average_rating, COUNT(*) AS review_count
            FROM design_feedback
            GROUP BY design_id
        ) AS review_stats ON review_stats.design_id = d.id
        WHERE (? = "" OR d.design_name LIKE CONCAT("%", ?, "%") OR c.category_name LIKE CONCAT("%", ?, "%") OR d.style LIKE CONCAT("%", ?, "%") OR d.occasion LIKE CONCAT("%", ?, "%"))
          AND (? = 0 OR d.category_id = ?)
        ORDER BY d.design_name';
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssssii', $search, $search, $search, $search, $search, $categoryId, $categoryId);
$stmt->execute();
$designs = $stmt->get_result();
require __DIR__ . '/includes/header.php';
?>
<section class="page-banner"><p class="eyebrow">The design gallery</p><h1>Patterns for every<br><span>moment &amp; mood.</span></h1><p>Search by design name, category, style or occasion. Your next favourite is waiting.</p></section>
<section class="section gallery-section">
    <form class="search-bar" method="get" action="designs.php">
        <label class="sr-only" for="search">Search designs</label><input id="search" name="q" type="search" placeholder="Search name, style, category or occasion…" value="<?= h($search) ?>">
        <label class="sr-only" for="category">Category</label><select id="category" name="category"><option value="0">All categories</option><?php while ($category = $categories->fetch_assoc()): ?><option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= h($category['category_name']) ?></option><?php endwhile; ?></select>
        <button class="button button-green" type="submit">Search</button>
    </form>
    <p class="result-count"><?= $designs->num_rows ?> design<?= $designs->num_rows === 1 ? '' : 's' ?> found</p>
    <?php if ($designs->num_rows): ?><div class="design-grid">
        <?php while ($design = $designs->fetch_assoc()): ?>
        <article class="design-card" data-search="<?= h(strtolower($design['design_name'] . ' ' . $design['category_name'] . ' ' . $design['style'] . ' ' . $design['occasion'])) ?>">
            <a class="card-image" href="design_details.php?id=<?= (int) $design['id'] ?>"><img src="<?= h(image_url($design['image'])) ?>" alt="<?= h($design['design_name']) ?>" loading="lazy"><span class="image-badge"><?= h($design['category_name']) ?></span></a>
            <div class="card-content"><h3><?= h($design['design_name']) ?></h3><p class="design-price"><?= h(format_price($design['price'])) ?></p><p class="recommendation-rating"><?= (int) $design['review_count'] > 0 ? '⭐ ' . number_format((float) $design['average_rating'], 1) . '/5 · ' . (int) $design['review_count'] . ' review' . ((int) $design['review_count'] === 1 ? '' : 's') : 'No reviews yet' ?></p><p><?= h($design['style']) ?> · <?= h($design['complexity']) ?> · <?= h($design['occasion']) ?></p><div class="card-actions"><a class="card-link" href="design_details.php?id=<?= (int) $design['id'] ?>">View details <span>↗</span></a><a class="card-link" href="design_details.php?id=<?= (int) $design['id'] ?>#reviews">Reviews <span>↓</span></a><?php if (is_logged_in()): ?><form method="post" action="favorite_action.php"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="design_id" value="<?= (int) $design['id'] ?>"><input type="hidden" name="action" value="add"><button class="icon-button" type="submit" aria-label="Add to favorites">♡</button></form><?php else: ?><a class="icon-button" href="login.php" aria-label="Log in to add favorites">♡</a><?php endif; ?></div></div>
        </article>
        <?php endwhile; ?>
    </div><?php else: ?><div class="empty-state"><span>❋</span><h2>No designs found just yet</h2><p>Try another search, or choose a different category.</p><a class="button button-green" href="designs.php">Show all designs</a></div><?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
