<?php

/**
 * Point d'entrée Vercel — route toutes les requêtes vers les fichiers PHP du projet.
 */
chdir(dirname(__DIR__));

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = rawurldecode(ltrim($uri, '/'));

if (str_contains($path, '..')) {
    http_response_code(400);
    exit('Requête invalide.');
}

if ($path === '' || $path === 'index.php') {
    require __DIR__ . '/../index.php';
    return;
}

if ($path === 'install.php') {
    require __DIR__ . '/install.php';
    return;
}

$target = __DIR__ . '/../' . $path;

if (is_dir($target)) {
    $target = rtrim($target, DIRECTORY_SEPARATOR) . '/index.php';
}

if (!pathinfo($target, PATHINFO_EXTENSION) && is_file($target . '.php')) {
    $target .= '.php';
}

if (is_file($target) && strtolower(pathinfo($target, PATHINFO_EXTENSION)) === 'php') {
    require $target;
    return;
}

$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'woff', 'woff2'];
$extension = strtolower(pathinfo($target, PATHINFO_EXTENSION));

if (is_file($target) && in_array($extension, $staticExtensions, true)) {
    $mimes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    header('Content-Type: ' . ($mimes[$extension] ?? 'application/octet-stream'));
    readfile($target);
    return;
}

// Vercel build : fichiers copiés dans public/asset/
$publicTarget = __DIR__ . '/../public/' . $path;
$publicExt = strtolower(pathinfo($publicTarget, PATHINFO_EXTENSION));
if (is_file($publicTarget) && in_array($publicExt, $staticExtensions, true)) {
    $mimes = [
        'css' => 'text/css; charset=UTF-8',
        'js' => 'application/javascript; charset=UTF-8',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
    ];
    header('Content-Type: ' . ($mimes[$publicExt] ?? 'application/octet-stream'));
    readfile($publicTarget);
    return;
}

http_response_code(404);
echo 'Page non trouvée.';
