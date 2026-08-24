<?php

function thumb_url(string $relative_path, int $width): string
{
    $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');

    if (!preg_match('#^uploads/#', $relative_path)) {
        return $relative_path;
    }

    if (!function_exists('imagecreatetruecolor') || !function_exists('getimagesize')) {
        return $relative_path;
    }

    $root   = dirname(__DIR__);
    $source = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);

    if (!is_file($source)) {
        return $relative_path;
    }

    $info = @getimagesize($source);
    if (!$info || empty($info[0]) || (int) $info[0] <= $width) {
        return $relative_path;
    }

    $ext       = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    $loaders   = ['jpg' => 'imagecreatefromjpeg', 'jpeg' => 'imagecreatefromjpeg', 'png' => 'imagecreatefrompng', 'webp' => 'imagecreatefromwebp'];
    $painters  = ['jpg' => 'imagejpeg', 'jpeg' => 'imagejpeg', 'png' => 'imagepng', 'webp' => 'imagewebp'];

    if (!isset($loaders[$ext]) || !function_exists($loaders[$ext])) {
        return $relative_path;
    }

    $out_ext = $ext === 'png' ? 'png' : (function_exists('imagewebp') ? 'webp' : $ext);

    $rel_after = substr($relative_path, strlen('uploads/'));
    $cache_rel = 'uploads/cache/' . $width . '/'
        . preg_replace('/\.' . preg_quote($ext, '/') . '$/', '.' . $out_ext, $rel_after);
    $cache_abs = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cache_rel);

    if (!is_file($cache_abs) || @filemtime($cache_abs) < @filemtime($source)) {
        $src_img = @$loaders[$ext]($source);
        if (!$src_img) {
            return $relative_path;
        }

        $scaled = function_exists('imagescale') ? imagescale($src_img, $width) : null;
        imagedestroy($src_img);
        if (!$scaled) {
            return $relative_path;
        }

        $dir = dirname($cache_abs);
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            imagedestroy($scaled);
            return $relative_path;
        }

        $ok = false;
        if ($out_ext === 'webp') {
            $ok = imagewebp($scaled, $cache_abs, 82);
        } elseif ($out_ext === 'png') {
            $ok = imagepng($scaled, $cache_abs, 6);
        } else {
            $ok = imagejpeg($scaled, $cache_abs, 82);
        }
        imagedestroy($scaled);

        if (!$ok) {
            return $relative_path;
        }
    }

    return $cache_rel . '?v=' . @filemtime($source);
}
