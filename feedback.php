<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$designId = filter_input(INPUT_GET, 'design_id', FILTER_VALIDATE_INT);
if (!$designId && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedId = filter_input(INPUT_POST, 'design_id', FILTER_VALIDATE_INT);
    $designId = $postedId ?: null;
}
if (!$designId || $designId < 1) {
    http_response_code(404);
    exit('Design not found.');
}

$designStmt = $conn->prepare('SELECT id, design_name FROM designs WHERE id = ?');
$designStmt->bind_param('i', $designId);
$designStmt->execute();
$design = $designStmt->get_result()->fetch_assoc();
if (!$design) {
    http_response_code(404);
    exit('Design not found.');
}

$errors = [];
$rating = 0;
$comment = '';
if (is_logged_in()) {
    $existingStmt = $conn->prepare('SELECT rating, comment FROM design_feedback WHERE user_id = ? AND design_id = ?');
    $existingStmt->bind_param('ii', $_SESSION['user_id'], $designId);
    $existingStmt->execute();
    $existingFeedback = $existingStmt->get_result()->fetch_assoc();
    if ($existingFeedback) {
        $rating = (int) $existingFeedback['rating'];
        $comment = $existingFeedback['comment'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!is_logged_in()) {
        set_flash('info', 'Please log in to leave feedback.');
        header('Location: login.php');
        exit;
    }

    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT) ?: 0;
    $commentInput = $_POST['comment'] ?? '';
    $comment = is_string($commentInput) ? trim($commentInput) : '';
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Choose a rating from 1 to 5 stars.';
    }
    if (!is_string($commentInput)) {
        $errors[] = 'Enter a valid comment.';
    }
    if (mb_strlen($comment) > 1000) {
        $errors[] = 'Keep your comment to 1,000 characters or fewer.';
    }

    if (!$errors) {
        $userId = (int) $_SESSION['user_id'];
        $stmt = $conn->prepare(
            'INSERT INTO design_feedback (user_id, design_id, rating, comment)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP'
        );
        $stmt->bind_param('iiis', $userId, $designId, $rating, $comment);
        $stmt->execute();
        set_flash('success', 'Thank you. Your feedback has been saved.');
        header('Location: design_details.php?id=' . $designId);
        exit;
    }
}

$pageTitle = 'Feedback for ' . $design['design_name'];
$activePage = 'designs';
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section">
    <div class="auth-card feedback-card">
        <p class="eyebrow">Share your thoughts</p>
        <h1>Rate this design</h1>
        <p class="form-intro"><?= h($design['design_name']) ?> · Your review will appear on this design's details page.</p>
        <?php if ($errors): ?><div class="form-errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <?php if (is_logged_in()): ?>
        <form method="post" action="feedback.php?design_id=<?= (int) $designId ?>">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="design_id" value="<?= (int) $designId ?>">
            <fieldset class="rating-field">
                <legend>Rating</legend>
                <?php
                $ratingLabels = [1 => 'Poor', 2 => 'Average', 3 => 'Good', 4 => 'Very Good', 5 => 'Excellent'];
                foreach ($ratingLabels as $value => $label):
                ?>
                    <label class="rating-option" for="rating-<?= $value ?>">
                        <input id="rating-<?= $value ?>" type="radio" name="rating" value="<?= $value ?>" required <?= $rating === $value ? 'checked' : '' ?>>
                        <span><?= str_repeat('⭐', $value) ?> <?= $value ?> Star<?= $value === 1 ? '' : 's' ?> – <?= h($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </fieldset>
            <label for="comment">Comment <span class="label-hint">(optional, up to 1,000 characters)</span></label>
            <textarea id="comment" name="comment" maxlength="1000" rows="5" placeholder="What did you think of this design?"><?= h($comment) ?></textarea>
            <button class="button button-green button-wide" type="submit">Save feedback</button>
        </form>
        <?php else: ?>
            <p class="form-intro">Please log in to rate and review this design.</p>
            <a class="button button-green button-wide" href="login.php">Log in to leave feedback</a>
        <?php endif; ?>
        <p class="auth-switch"><a href="design_details.php?id=<?= (int) $designId ?>">← Back to design</a></p>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
