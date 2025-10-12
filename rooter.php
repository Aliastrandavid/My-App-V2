<?php
/**
 * Router script for PHP built-in server
 * This enables URL rewriting for the built-in server
 */

// Get the requested URI
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the URI points to an actual file (CSS, JS, images, etc.), serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Let PHP's built-in server handle the file
}

// Otherwise, route through index.php
include __DIR__ . '/index.php';
