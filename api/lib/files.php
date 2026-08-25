<?php

function api_upload_dir(string $subdir): string
{
    return rtrim(APP_ROOT . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . trim($subdir, '/\\'), '/\\');
}

function api_stream_upload_file(string $subdir, string $filename): void
{
    $dir = realpath(api_upload_dir($subdir));
    $path = realpath(api_upload_dir($subdir) . DIRECTORY_SEPARATOR . basename($filename));

    if ($dir === false || $path === false || strpos($path, $dir . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
        api_fail(404, 'not_found', 'File not found.');
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = UPLOAD_MIME_MAP[$ext][0] ?? null;

    if ($mime === null && function_exists('mime_content_type')) {
        $mime = mime_content_type($path);
    }

    header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
    header('Content-Length: ' . (string) filesize($path));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=0, no-cache');

    readfile($path);
    exit;
}
