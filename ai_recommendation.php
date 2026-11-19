<?php
require_once __DIR__ . '/includes\db.php';
require_once __DIR__ . '/includes\functions.php';
$pageTitle = 'AI Design Recommendation';
$activePage = 'recommend';
$options = [
    'occasion' => ['Wedding', 'Engagement', 'Festival', 'Party', 'Casual', 'Other'],
    'style' => ['Bridal', 'Arabic', 'Traditional', 'Minimal', 'Floral', 'Geometric'],
    'complexity' => ['Simple', 'Medium', 'Heavy'],
    'application_area' => ['Front Hand', 'Back Hand', 'Full Hand', 'Feet'],
    'design_type' => ['Floral', 'Mandala', 'Paisley', 'Geometric', 'Traditional', 'Mixed'],
];
$budgetRanges = [
    '500-1000' => ['min' => 500, 'max' => 1000, 'label' => '₹500 – ₹1,000'],
    '1001-2500' => ['min' => 1001, 'max' => 2500, 'label' => '₹1,001 – ₹2,500'],
    '2501-5000' => ['min' => 2501, 'max' => 5000, 'label' => '₹2,501 – ₹5,000'],
    '5001-7500' => ['min' => 5001, 'max' => 7500, 'label' => '₹5,001 – ₹7,500'],
    '7501-10000' => ['min' => 7501, 'max' => 10000, 'label' => '₹7,501 – ₹10,000'],
];
$labels = ['occasion' => 'Occasion', 'style' => 'Design style', 'complexity' => 'Design complexity', 'application_area' => 'Application area', 'design_type' => 'Preferred design type'];
$preferences = [];
$errors = [];
$recommendations = [];
$budgetRange = (string) ($_POST['budget_range'] ?? '500-1000');
$customMax = trim((string) ($_POST['custom_max'] ?? ''));
$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';
if ($submitted) {
    verify_csrf();
    foreach ($options as $field => $allowed) {
        $value = (string) ($_POST[$field] ?? '');
        if ($value === '') {
            continue;
        }
        if (!in_array($value, $allowed, true)) {
            $errors[] = 'Please choose a valid ' . strtolower($labels[$field]) . '.';
        } else {
            $preferences[$field] = $value;
        }
    }
    if (!$preferences) {
        $errors[] = 'Please choose at least one design preference.';
    }
    if (!isset($budgetRanges[$budgetRange])) {
        $errors[] = 'Please choose one of the available budget ranges.';
    }
    if ($customMax !== '' && (!is_numeric($customMax) || (float) $customMax < 500 || (float) $customMax > 10000)) {
        $errors[] = 'Your custom maximum budget must be between ₹500 and ₹10,000.';
    }
    if (!$errors) {
        $minimumBudget = $customMax !== '' ? 500 : $budgetRanges[$budgetRange]['min'];
        $maximumBudget = $customMax !== '' ? (float) $customMax : $budgetRanges[$budgetRange]['max'];
        $result = $conn->query('
            SELECT d.*, c.category_name,
                   COALESCE(review_stats.average_rating, 0) AS average_rating,
                   COALESCE(review_stats.review_count, 0) AS review_count
            FROM designs d
            JOIN categories c ON c.id = d.category_id
            LEFT JOIN (
                SELECT design_id, AVG(rating) AS average_rating, COUNT(*) AS review_count
                FROM design_feedback
                GROUP BY design_id
            ) AS review_stats ON review_stats.design_id = d.id
        ');
        while ($design = $result->fetch_assoc()) {
            $score = 0;
            $matches = [];
            foreach ($preferences as $field => $value) {
                if (strcasecmp($design[$field], $value) === 0) {
                    $score += 2;
                    $matches[] = match ($field) {
                        'occasion' => $value . ' occasion',
                        'style' => $value . ' style',
                        'complexity' => $value . ' complexity',
                        'application_area' => $value . ' preference',
                        'design_type' => $value . ' design type',
                    };
                }
            }
            if ((float) $design['price'] >= $minimumBudget && (float) $design['price'] <= $maximumBudget) {
                $score += 3;
                $matches[] = 'selected budget';
            }
            if ($score > 0) {
                $design['score'] = $score;
                $design['priority_score'] = $score + ((float) $design['average_rating'] / 5 * 2);
                $design['matches'] = $matches;
                $recommendations[] = $design;
            }
        }
        usort($recommendations, static function (array $a, array $b): int {
            return ($b['priority_score'] <=> $a['priority_score'])
                ?: ($b['score'] <=> $a['score'])
                ?: ($b['average_rating'] <=> $a['average_rating'])
                ?: strcasecmp($a['design_name'], $b['design_name']);
        });
        $recommendations = array_slice($recommendations, 0, 6);
    }
}
require __DIR__ . '/includes\header.php';
?>
<section class="page-banner recommendation-banner"><p class="eyebrow">Your personal design finder</p><h1>A little magic,<br><span>made for you.</span></h1><p>Choose what matters to you. Our transparent, AI-inspired system will rank the closest matches in our design gallery.</p><span class="ai-note">✦ Rule-based matching · No external AI or machine-learning model</span></section>
<section class="section recommendation-section">
    <div class="questionnaire-intro"><span class="step-number">✿</span><div><p class="eyebrow">Tell us what you have in mind</p><h2>Your mehndi preferences</h2><p>Choose at least one preference. Each match earns two points, and an in-budget design earns three bonus points.</p></div></div>
    <?php if ($errors): ?><div class="form-errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" class="preference-form" data-validate><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <?php foreach ($options as $field => $values): ?>
        <fieldset class="preference-group"><legend><span><?= h($labels[$field]) ?></span></legend><div class="choice-list">
        <?php foreach ($values as $index => $value): $inputId = $field . '-' . $index; ?>
            <label class="choice-pill" for="<?= h($inputId) ?>"><input id="<?= h($inputId) ?>" type="radio" name="<?= h($field) ?>" value="<?= h($value) ?>" <?= ($preferences[$field] ?? '') === $value ? 'checked' : '' ?>><span><?= h($value) ?></span></label>
        <?php endforeach; ?>
        </div></fieldset>
        <?php endforeach; ?>
        <fieldset class="preference-group budget-group"><legend>Budget</legend><div class="budget-controls"><label for="budget_range">Choose a price range</label><select id="budget_range" name="budget_range" required><?php foreach ($budgetRanges as $key => $range): ?><option value="<?= h($key) ?>" <?= $budgetRange === $key ? 'selected' : '' ?>><?= h($range['label']) ?></option><?php endforeach; ?></select><label for="custom_max">Or set a custom maximum <span class="label-hint">(optional; from ₹500 to ₹10,000)</span></label><div class="custom-budget-input"><span>₹</span><input id="custom_max" name="custom_max" type="number" min="500" max="10000" step="1" value="<?= h($customMax) ?>" placeholder="e.g. 3500"></div><small>If you enter a custom maximum, designs from ₹500 up to that amount receive the budget match.</small></div></fieldset>
        <button class="button button-gold button-wide recommendation-submit" type="submit"><span>✦</span> Get AI Recommendations</button>
    </form>
</section>
<?php if ($submitted && !$errors): ?>
<script>document.addEventListener('DOMContentLoaded', () => document.getElementById('recommendations')?.scrollIntoView({ behavior: 'smooth' }));</script>
<section class="section results-section" id="recommendations"><div class="section-heading"><div><p class="eyebrow">Your matched collection</p><h2>Recommended for you</h2><p>Preference and budget matches determine fit; customer ratings help prioritize equally relevant designs.</p></div><span class="score-key">Up to <?= (count($preferences) * 2) + 3 ?> match points · rating boost up to 2</span></div>
<?php if ($recommendations): ?><div class="design-grid"><?php foreach ($recommendations as $design): $matchPercent = (int) round(($design['score'] / ((count($preferences) * 2) + 3)) * 100); ?><article class="design-card recommendation-card"><a class="card-image" href="design_details.php?id=<?= (int) $design['id'] ?>"><img src="<?= h(image_url($design['image'])) ?>" alt="<?= h($design['design_name']) ?>" loading="lazy"><span class="image-badge">AI Recommendation: <?= $matchPercent ?>% Match</span></a><div class="card-content"><p class="eyebrow"><?= h($design['category_name']) ?></p><h3><?= h($design['design_name']) ?></h3><p class="design-price"><?= h(format_price($design['price'])) ?></p><p class="recommendation-rating"><?= $design['review_count'] ? '⭐ ' . number_format((float) $design['average_rating'], 1) . '/5 · ' . (int) $design['review_count'] . ' review' . ((int) $design['review_count'] === 1 ? '' : 's') : 'No reviews yet' ?></p><p><?= h($design['style']) ?> · <?= h($design['complexity']) ?> · <?= h($design['application_area']) ?></p><p class="recommendation-reason">Recommended because it matches your <?= h(implode(', ', $design['matches'])) ?> preference<?= count($design['matches']) === 1 ? '' : 's' ?>.</p><a class="card-link" href="design_details.php?id=<?= (int) $design['id'] ?>">View design <span>↗</span></a></div></article><?php endforeach; ?></div>
<?php else: ?><div class="empty-state"><span>✿</span><h2>No close matches yet</h2><p>We couldn't find a design matching these preferences. Try the gallery or adjust a choice to see other designs.</p><a class="button button-green" href="designs.php">Browse all designs</a></div><?php endif; ?>
</section>
<?php endif; ?>
<?php require __DIR__ . '/includes\footer.php'; ?>
