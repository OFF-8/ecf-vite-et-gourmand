<?php

/**
 * Retourne une URL d'image menu utilisable, avec fallback cohérent.
 */
function menuImagePath(?string $urlImage): string
{
    $default = 'asset/img/menu-default.jpg';
    $root = dirname(__DIR__);

    if ($urlImage) {
        $relative = ltrim(str_replace('\\', '/', $urlImage), '/');
        if (is_file($root . '/' . $relative)) {
            return $relative;
        }
    }

    return $default;
}

function menuImageUrl(?string $urlImage, ?string $basePath = null): string
{
    if ($basePath === null) {
        $basePath = function_exists('getBasePath') ? getBasePath() : '/';
    }

    return rtrim($basePath, '/') . '/' . menuImagePath($urlImage);
}
