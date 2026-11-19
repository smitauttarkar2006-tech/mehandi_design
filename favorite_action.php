<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();
$designId = filter_input(INPUT_POST, 'design_id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
if (!$designId || !in_array($action, ['add', 'remove'], true)) {
    http_response_code(400);
    exit('Invalid favourite request.');
}
$exists = $conn->prepare('SELECT id FROM designs WHERE id = ?');
$exists->bind_param('i', $designId);
$exists->execute();
if (!$exists->get_result()->fetch_assoc()) {
    http_response_code(404);
    exit('That design could not be found.');
}
if ($action === 'add') {
    $stmt = $conn->prepare('INSERT IGNORE INTO favorites (user_id, design_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $_SESSION['user_id'], $designId);
    $stmt->execute();
    set_flash('success', 'Design added to your favorites.');
} else {
    $stmt = $conn->prepare('DELETE FROM favorites WHERE user_id = ? AND design_id = ?');
    $stmt->bind_param('ii', $_SESSION['user_id'], $designId);
    $stmt->execute();
    set_flash('success', 'Design removed from your favorites.');
}
$returnTo = $_SERVER['HTTP_REFERER'] ?? 'favorites.php';
$path = parse_url($returnTo, PHP_URL_PATH) ?: '';
$allowed = ['designs.php', 'design_details.php', 'favorites.php'];
$safeReturn = in_array(basename($path), $allowed, true) ? basename($path) . (parse_url($returnTo, PHP_URL_QUERY) ? '?' . parse_url($returnTo, PHP_URL_QUERY) : '') : 'favorites.php';
header('Location: ' . $safeReturn);
exit;
