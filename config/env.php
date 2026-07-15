<?php

function env(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value !== false && $value !== '') {
        return $value;
    }

    return $default;
}

if (file_exists(__DIR__ . '/local.php')) {
    require __DIR__ . '/local.php';
}
