<?php

/**
 * Router for PHP built-in server with document root set to public/
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// If the request is for an existing file, serve it directly
if ($uri !== '/' && file_exists(__DIR__.$uri)) {
    return false;
}

// Route everything else through index.php
require_once __DIR__.'/index.php';
