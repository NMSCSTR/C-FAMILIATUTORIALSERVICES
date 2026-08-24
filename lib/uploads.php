<?php

const UPLOAD_MIME_MAP = [
    'jpg'  => ['image/jpeg', 'image/pjpeg'],
    'jpeg' => ['image/jpeg', 'image/pjpeg'],
    'png'  => ['image/png'],
    'gif'  => ['image/gif'],
    'webp' => ['image/webp'],
    'pdf'  => ['application/pdf'],
    'doc'  => ['application/msword'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'zip'  => ['application/zip', 'application/x-zip-compressed'],
    'rar'  => ['application/x-rar-compressed', 'application/vnd.rar'],
];

function store_uploaded_file(array $file, string $target_dir, array $allowed_ext, int $max_bytes = 5242880): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [false, null, 'Upload failed. Please try again.'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return [false, null, 'Invalid upload request.'];
    }

    if ($file['size'] <= 0 || $file['size'] > $max_bytes) {
        return [false, null, 'File is too large. Maximum size is ' . round($max_bytes / 1048576) . 'MB.'];
    }

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext, true)) {
        return [false, null, 'File type not allowed. Accepted: ' . strtoupper(implode(', ', $allowed_ext)) . '.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if (!in_array($mime, UPLOAD_MIME_MAP[$ext] ?? [], true)) {
        return [false, null, 'File content does not match its extension.'];
    }

    if (!is_dir($target_dir) && !mkdir($target_dir, 0775, true) && !is_dir($target_dir)) {
        return [false, null, 'Could not create the upload folder. Check permissions.'];
    }

    $stored = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest   = rtrim($target_dir, '/\\') . DIRECTORY_SEPARATOR . $stored;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return [false, null, 'Could not save the file. Check folder permissions.'];
    }

    return [true, $stored, null];
}
