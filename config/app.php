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
    $forwarded = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    $protocol = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $forwarded === 'https'
        || env('VERCEL') === '1'
    ) ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $protocol . '://' . $host . getBasePath();
}
