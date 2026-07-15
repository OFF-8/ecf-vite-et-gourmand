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

http_response_code(404);
echo 'Page non trouvée.';
