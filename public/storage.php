<?php

$path = $_GET['path'] ?? '';

// Try multiple common shared hosting structures
$possibleBases = [
    __DIR__ . '/../laravel_core/storage/app/public',      // cPanel: laravel_core outside public_html
    __DIR__ . '/../../laravel_core/storage/app/public',   // cPanel: laravel_core sibling of public_html
    __DIR__ . '/../storage/app/public',                   // Standard Laravel (symlink or same level)
    __DIR__ . '/../../storage/app/public',                // Laravel in subfolder
];

$basePath = null;
foreach ($possibleBases as $base) {
    $real = realpath($base);
    if ($real && is_dir($real)) {
        $basePath = $real;
        break;
    }
}

if (!$basePath) {
    http_response_code(500);
    exit('Storage path not configured');
}

$file = $basePath . DIRECTORY_SEPARATOR . $path;

if (!file_exists($file) || is_dir($file)) {
    http_response_code(404);
    exit('File not found');
}

$mime = mime_content_type($file);
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($file));
header('Cache-Control: public, max-age=2592000');
header('ETag: "' . md5_file($file) . '"');

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . md5_file($file) . '"') {
    http_response_code(304);
    exit;
}

readfile($file);