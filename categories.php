<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$adminTitle = 'Manage categories';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $name = trim((string) ($_POST['category_name'] ?? ''));
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    try {
        if ($action === 'add' || $action === 'edit') {
            if ($name === '' || mb_strlen($name) > 100) throw new RuntimeException('Category name is required and must be 100 characters or fewer.');
            if ($action === 'add') {
                $stmt = $conn->prepare('INSERT INTO categories (category_name) VALUES (?)');
                $stmt->bind_param('s', $name);
                $stmt->execute();
                set_flash('success', 'Category added.');
            } elseif ($id) {
                $stmt = $conn->prepare('UPDATE categories SET category_name = ? WHERE id = ?');
                $stmt->bind_param('si', $name, $id);
                $stmt->execute();
                set_flash('success', 'Category updated.');
            } else throw new RuntimeException('Choose a valid category.');
        } elseif ($action === 'delete' && $id) {
            $stmt = $conn->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            set_flash('success', 'Category deleted.');
        } else throw new RuntimeException('Invalid category request.');
        header('Location: categories.php');
        exit;
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    } catch (mysqli_sql_exception $exception) {
        $error = 'This category is used by one or more designs. Reassign or delete those designs before deleting the category.';
    }
}
$categories = $conn->query('SELECT c.id, c.category_name, COUNT(d.id) AS design_count FROM categories c LEFT JOIN designs d ON d.category_id = c.id GROUP BY c.id ORDER BY c.category_name');
require __DIR__ . '/_header.php';
?>
<section class="admin-page-heading"><div><p class="eyebrow">Design content</p><h1>Categories</h1><p>Categories organize designs in the gallery. A category in use cannot be deleted.</p></div></section>
<?php if ($error): ?><div class="form-errors" role="alert"><?= h($error) ?></div><?php endif; ?>
<form class="category-add-form" method="post"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="add"><label class="sr-only" for="new-category">New category name</label><input id="new-category" name="category_name" maxlength="100" placeholder="New category name" required><button class="button button-green" type="submit">Add category</button></form>
<div class="category-admin-list"><?php while ($category = $categories->fetch_assoc()): ?><div class="category-admin-row"><form method="post" class="category-edit-form"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="edit"><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><label class="sr-only" for="category-<?= (int) $category['id'] ?>">Category name</label><input id="category-<?= (int) $category['id'] ?>" name="category_name" maxlength="100" required value="<?= h($category['category_name']) ?>"><span class="category-count"><?= (int) $category['design_count'] ?> designs</span><button class="small-action" type="submit">Save</button></form><form method="post" data-confirm="Delete this category? Categories used by designs cannot be deleted."><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><button class="danger-link" type="submit">Delete</button></form></div><?php endwhile; ?></div>
<?php require __DIR__ . '/_footer.php'; ?>
