<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Method not allowed.'); }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid design.'); }
$stmt = $conn->prepare('DELETE FROM designs WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
set_flash($stmt->affected_rows ? 'success' : 'info', $stmt->affected_rows ? 'Design deleted.' : 'That design was already removed.');
header('Location: dashboard.php');
exit;
