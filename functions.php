<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Your form expired or was invalid. Go back, refresh the page, and try again.');
    }
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash'] = ['type' => 'info', 'message' => 'Please log in to save and view favourites.'];
        header('Location: login.php');
        exit;
    }
}

function is_admin_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function image_url(string $image): string
{
    $relativePath = str_starts_with($image, 'uploads/')
        ? $image
        : 'images/' . basename($image);
    $diskPath = dirname(__DIR__) . DIRECTORY_SEPARATOR
        . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

    if (is_file($diskPath)) {
        return str_starts_with($image, 'uploads/')
            ? $relativePath
            : 'images/' . rawurlencode(basename($image));
    }

    $fallbackImages = [
        'popular-design-1.png',
        'popular-design-2.png',
        'popular-design-3.png',
        'popular-design-4.png',
        'popular-design-5.png',
        'popular-design-6.png',
    ];
    $fallbackIndex = array_sum(array_map('ord', str_split(basename($image)))) % count($fallbackImages);

    return 'images/' . $fallbackImages[$fallbackIndex];
}

function format_price(string|int|float $price): string
{
    $amount = (float) $price;
    $decimals = floor($amount) === $amount ? 0 : 2;
    return '₹' . number_format($amount, $decimals, '.', ',');
}

function upload_design_image(array $file, ?string $existing = null): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        if ($existing !== null) {
            return $existing;
        }
        throw new RuntimeException('Choose a design image to upload.');
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('The image upload did not complete. Please try again.');
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Image files must be 5 MB or smaller.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Use a JPG, PNG, or WebP image.');
    }

    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory) || !is_writable($directory)) {
        throw new RuntimeException('The uploads folder is missing or not writable.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The image could not be saved. Check the uploads folder permissions.');
    }

    return 'uploads/' . $filename;
}
