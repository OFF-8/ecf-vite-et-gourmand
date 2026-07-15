<?php

function getBasePath(): string
{
    if (env('VERCEL') === '1') {
        return '/';
    }

    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/ecf'));

    if (preg_match('#/(admin|employe)(/|$)#', $scriptDir)) {
        $scriptDir = dirname($scriptDir);
    }

    $base = rtrim($scriptDir, '/');

    return ($base === '' ? '' : $base) . '/';
}

function getBaseUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $protocol . '://' . $host . getBasePath();
}
