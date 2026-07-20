<?php

/**
 * Accès MongoDB via endpoint Node sur Vercel (quand l'extension PHP mongodb est absente).
 */
function mongoBridgeUrl(): string
{
    return rtrim(getBaseUrl(), '/') . '/api/mongo-stats';
}

function mongoBridgeKey(): string
{
    return env('INSTALL_KEY', 'vitegourmand2026');
}

/**
 * @param array<string, mixed> $payload
 */
function mongoBridgeRequest(string $method, array $payload = [], array $query = []): ?array
{
    $url = mongoBridgeUrl();
    if ($query) {
        $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
    }

    $headers = [
        'Content-Type: application/json',
        'x-mongo-key: ' . mongoBridgeKey(),
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $code >= 400) {
            return null;
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    $opts = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'timeout' => 20,
            'ignore_errors' => true,
        ],
    ];
    if ($method === 'POST') {
        $opts['http']['content'] = json_encode($payload);
    }
    $raw = @file_get_contents($url, false, stream_context_create($opts));
    if ($raw === false) {
        return null;
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}
