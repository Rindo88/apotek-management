<?php
// Router untuk PHP built-in server
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

// Jika file ada dan bukan directory
if (is_file($file)) {
    return false; // Serve file langsung
}

// Jika mencoba akses directory tanpa index.php
if (is_dir($file) && file_exists($file . '/index.php')) {
    include $file . '/index.php';
    return true;
}

// Jika file tidak ditemukan, tampilkan 404
include __DIR__ . '/404.php';
