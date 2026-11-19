<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$adminTitle = 'Add design';
$categories = $conn->query('SELECT id, category_name FROM categories ORDER BY category_name')->fetch_all(MYSQLI_ASSOC);
$values = ['design_name' => '', 'category_id' => '', 'style' => '', 'occasion' => '', 'complexity' => '', 'application_area' => '', 'design_type' => '', 'description' => '', 'price' => '1500'];
$errors = [];
$choices = [
    'style' => ['Bridal', 'Arabic', 'Traditional', 'Minimal', 'Floral', 'Geometric'],
    'occasion' => ['Wedding', 'Engagement', 'Festival', 'Party', 'Casual', 'Other'],
    'complexity' => ['Simple', 'Medium', 'Heavy'],
    'application_area' => ['Front Hand', 'Back Hand', 'Full Hand', 'Feet'],
    'design_type' => ['Floral', 'Mandala', 'Paisley', 'Geometric', 'Traditional', 'Mixed'],
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_) $values[$key] = trim((string) ($_POST[$key] ?? ''));
    if ($values['design_name'] === '' || mb_strlen($values['design_name']) > 150) $errors[] = 'Enter a design name (up to 150 characters).';
    if (!filter_var($values['category_id'], FILTER_VALIDATE_INT)) $errors[] = 'Choose a category.';
    foreach ($choices as $field => $allowed) if (!in_array($values[$field], $allowed, true)) $errors[] = 'Choose a valid ' . str_replace('_', ' ', $field) . '.';
    if ($values['description'] === '') $errors[] = 'Enter a short design description.';
    if (!is_numeric($values['price']) || (float) $values['price'] < 500 || (float) $values['price'] > 10000) $errors[] = 'Enter a numeric price between ₹500 and ₹10,000.';
    if (!$categories) $errors[] = 'Add a category before adding a design.';
    if (!$errors) {
        try {
            $image = upload_design_image($_FILES['image'] ?? []);
            $categoryId = (int) $values['category_id'];
            $price = (float) $values['price'];
            $stmt = $conn->prepare('INSERT INTO designs (design_name, category_id, image, style, occasion, complexity, application_area, design_type, description, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sisssssssd', $values['design_name'], $categoryId, $image, $values['style'], $values['occasion'], $values['complexity'], $values['application_area'], $values['design_type'], $values['description'], $price);
            $stmt->execute();
            set_flash('success', 'Design added to the gallery.');
            header('Location: dashboard.php');
            exit;
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}
require __DIR__ . '/_header.php';
?>
<section class="admin-page-heading"><div><p class="eyebrow">Design content</p><h1>Add a design</h1><p>All fields are used by the gallery or preference-matching recommender.</p></div><a class="text-link" href="dashboard.php">← Back to designs</a></section>
<?php if ($errors): ?><div class="form-errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="admin-form" method="post" enctype="multipart/form-data" data-validate><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
<label for="design_name">Design name</label><input id="design_name" name="design_name" maxlength="150" required value="<?= h($values['design_name']) ?>">
<label for="category_id">Category</label><select id="category_id" name="category_id" required><option value="">Choose a category</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string) $category['id'] === $values['category_id'] ? 'selected' : '' ?>><?= h($category['category_name']) ?></option><?php endforeach; ?></select>
<div class="admin-form-grid"><?php foreach ($choices as $field => $allowed): ?><div><label for="<?= h($field) ?>"><?= h(ucwords(str_replace('_', ' ', $field))) ?></label><select id="<?= h($field) ?>" name="<?= h($field) ?>" required><option value="">Choose one</option><?php foreach ($allowed as $choice): ?><option value="<?= h($choice) ?>" <?= $values[$field] === $choice ? 'selected' : '' ?>><?= h($choice) ?></option><?php endforeach; ?></select></div><?php endforeach; ?></div>
<label for="description">Description</label><textarea id="description" name="description" rows="4" required><?= h($values['description']) ?></textarea>
<label for="price">Price in rupees <span class="label-hint">(₹500–₹10,000)</span></label><input id="price" name="price" type="number" min="500" max="10000" step="0.01" required value="<?= h($values['price']) ?>">
<label for="image">Design image <span class="label-hint">(JPG, PNG or WebP; up to 5 MB)</span></label><input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" required data-image-input><img class="image-preview" data-image-preview alt="Selected image preview" hidden>
<button class="button button-green" type="submit">Save design</button></form>
<?php require __DIR__ . '/_footer.php'; ?>
