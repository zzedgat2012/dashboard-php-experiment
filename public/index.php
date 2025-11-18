<?php

/**
 * Application front controller: bootstraps environment, serves static assets,
 * and routes incoming requests to the appropriate public view scripts.
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class)) {
    \Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

$requestUri    = $_SERVER['REQUEST_URI'] ?? '/';
$path          = parse_url($requestUri, PHP_URL_PATH) ?: '/';
$normalizedPath = trim($path, '/');

$assetPrefix = 'assets/';

if (strncmp($normalizedPath, $assetPrefix, strlen($assetPrefix)) === 0) {
    $assetRoot = realpath(__DIR__ . '/../src/assets');

    if ($assetRoot === false) {
        http_response_code(500);
        exit('Asset directory is missing.');
    }

    $assetRoot = rtrim($assetRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $relativeAssetPath = ltrim(substr($normalizedPath, strlen($assetPrefix)), '/\\');
    $candidatePath = realpath($assetRoot . $relativeAssetPath);

    if (
        $candidatePath === false
        || strpos($candidatePath, $assetRoot) !== 0
        || !is_file($candidatePath)
    ) {
        http_response_code(404);
        exit('Asset not found.');
    }

    $extension = strtolower(pathinfo($candidatePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'  => 'text/css; charset=UTF-8',
        'js'   => 'application/javascript; charset=UTF-8',
        'map'  => 'application/json; charset=UTF-8',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
    } else {
        header('Content-Type: application/octet-stream');
    }

    header('Cache-Control: public, max-age=31536000, immutable');
    readfile($candidatePath);
    exit;
}

switch ($normalizedPath) {
    case '':
    case 'login':
        require_once __DIR__ . '/login.view.php';
        break;

    case 'signup':
        require_once __DIR__ . '/signup.view.php';
        break;

    case 'dashboard':
        require_once __DIR__ . '/dashboard.view.php';
        break;

    case 'logout':
        require_once __DIR__ . '/logout.php';
        break;

    case 'forgot-password':
        require_once __DIR__ . '/forgot_password.view.php';
        break;

    case 'reset-password':
        require_once __DIR__ . '/reset_password.view.php';
        break;

    case 'auth/magic':
        require_once __DIR__ . '/auth_magic.view.php';
        break;

    default:
        http_response_code(404);
        echo '404 - Page not found!';
        break;
}
