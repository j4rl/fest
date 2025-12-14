<?php
declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_url(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    return $protocol . $host . ($dir !== '' ? $dir . '/' : '/');
}

/**
 * Handle an uploaded image file and return a relative path or null.
 * Appends errors to the provided array.
 */
function handle_image_upload(string $field, array &$errors): ?string
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload failed.';
        return null;
    }
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        $errors[] = 'Image is too large (max 5MB).';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        $errors[] = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
        return null;
    }

    $ext = $allowed[$mime];
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }
    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $errors[] = 'Could not save uploaded image.';
        return null;
    }

    return 'uploads/' . $filename;
}
