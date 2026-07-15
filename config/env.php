<?php

function env(string $key, ?string $default = null): ?string
{
    $candidates = [
        getenv($key),
        $_ENV[$key] ?? null,
        $_SERVER[$key] ?? null,
    ];

    foreach ($candidates as $value) {
        if ($value !== false && $value !== null && $value !== '') {
            return (string) $value;
        }
    }

    return $default;
}

if (file_exists(__DIR__ . '/local.php')) {
    require __DIR__ . '/local.php';
}
