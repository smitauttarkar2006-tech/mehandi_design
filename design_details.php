<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(404);
    exit('Design not found.');
}
$stmt = $conn->prepare('SELECT d.*, c.category_name FROM designs d JOIN categories c ON c.id = d.category_id WHERE d.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$design = $stmt->get_result()->fetch_assoc();
if (!$design) {
    http_response_code(404);
    exit('Design not found.');
}
$pageTitle = $design['design_name'];
$activePage = 'designs';
$isFavorite = false;
if (is_logged_in()) {
    $favoriteStmt = $conn->prepare('SELECT id FROM favorites WHERE user_id = ? AND design_id = ?');
    $favoriteStmt->bind_param('ii', $_SESSION['user_id'], $id);
    $favoriteStmt->execute();
    $isFavorite = (bool) $favoriteStmt->get_result()->fetch_assoc();
}
$ratingStmt = $conn->prepare('SELECT COUNT(*) AS review_count, AVG(rating) AS average_rating FROM design_feedback WHERE design_id = ?');
$ratingStmt->bind_param('i', $id);
$ratingStmt->execute();
$ratingSummary = $ratingStmt->get_result()->fetch_assoc();
$reviewStmt = $conn->prepare(
    'SELECT f.rating, f.comment, f.created_at, u.name
     FROM design_feedback f
     JOIN users u ON u.id = f.user_id
     WHERE f.design_id = ?
     ORDER BY f.created_at DESC
     LIMIT 50'
);
$reviewStmt->bind_param('i', $id);
$reviewStmt->execute();
$reviews = $reviewStmt->get_result();
require __DIR__ . '/includes/header.php';
?>
<section class="section detail-section">
    <a class="back-link" href="designs.php">← Back to designs</a>
    <div class="detail-layout">
        <div class="detail-image"><img src="<?= h(image_url($design['image'])) ?>" alt="<?= h($design['design_name']) ?>"></div>
        <div class="detail-copy"><p class="eyebrow"><?= h($design['category_name']) ?></p><h1><?= h($design['design_name']) ?></h1><p class="detail-description"><?= h($design['description']) ?></p>
            <p class="detail-price"><?= h(format_price($design['price'])) ?></p>
            <p class="detail-rating"><?php if ((int) $ratingSummary['review_count'] > 0): ?>⭐ <?= number_format((float) $ratingSummary['average_rating'], 1) ?>/5 based on <?= (int) $ratingSummary['review_count'] ?> review<?= (int) $ratingSummary['review_count'] === 1 ? '' : 's' ?><?php else: ?>No reviews yet<?php endif; ?></p>
            <dl class="detail-facts"><div><dt>Style</dt><dd><?= h($design['style']) ?></dd></div><div><dt>Occasion</dt><dd><?= h($design['occasion']) ?></dd></div><div><dt>Complexity</dt><dd><?= h($design['complexity']) ?></dd></div><div><dt>Application area</dt><dd><?= h($design['application_area']) ?></dd></div><div><dt>Design type</dt><dd><?= h($design['design_type']) ?></dd></div></dl>
            <?php if (is_logged_in()): ?><form method="post" action="favorite_action.php"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="design_id" value="<?= (int) $design['id'] ?>"><input type="hidden" name="action" value="<?= $isFavorite ? 'remove' : 'add' ?>"><button class="button button-green" type="submit"><?= $isFavorite ? '♥ Remove from favorites' : '♡ Add to favorites' ?></button></form><?php else: ?><a class="button button-green" href="login.php">Log in to save this design</a><?php endif; ?>
        </div>
    </div>
</section>
<section class="section reviews-section" id="reviews">
    <div class="section-heading"><div><p class="eyebrow">Community feedback</p><h2>Reviews</h2><?php if ((int) $ratingSummary['review_count'] > 0): ?><p>⭐ <?= number_format((float) $ratingSummary['average_rating'], 1) ?>/5 based on <?= (int) $ratingSummary['review_count'] ?> review<?= (int) $ratingSummary['review_count'] === 1 ? '' : 's' ?></p><?php else: ?><p>Be the first to share your thoughts on this design.</p><?php endif; ?></div><a class="button button-gold" href="feedback.php?design_id=<?= (int) $id ?>">Write a review</a></div>
    <?php if ($reviews->num_rows): ?><div class="review-list">
        <?php while ($review = $reviews->fetch_assoc()): ?>
            <article class="review-card">
                <div class="review-heading"><strong><?= h($review['name']) ?></strong><time datetime="<?= h(date(DATE_ATOM, strtotime($review['created_at']))) ?>"><?= h(date('M j, Y', strtotime($review['created_at']))) ?></time></div>
                <p class="review-stars" aria-label="<?= (int) $review['rating'] ?> out of 5 stars"><?= str_repeat('⭐', (int) $review['rating']) ?> <span><?= (int) $review['rating'] ?>/5</span></p>
                <?php if ($review['comment'] !== ''): ?><p class="review-comment"><?= nl2br(h($review['comment'])) ?></p><?php endif; ?>
            </article>
        <?php endwhile; ?>
    </div><?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
