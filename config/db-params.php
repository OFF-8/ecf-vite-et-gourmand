<?php

function isCloudEnvironment(): bool
{
    return env('VERCEL') === '1'
        || env('FLY_APP_NAME') !== null
        || env('RENDER') === 'true';
}

/**
 * @return array{host: string, port: string, dbname: string, user: string, password: string}
 */
function getDatabaseParams(): array
{
    $host = env('DB_HOST', 'localhost');
    $port = env('DB_PORT', '3306');
    $dbname = env('DB_NAME', 'vite_et_gourmand');
    $user = env('DB_USER', 'root');
    $password = env('DB_PASSWORD', '');

    $databaseUrl = env('DATABASE_URL') ?? env('MYSQL_PUBLIC_URL') ?? env('MYSQL_URL');

    if ($databaseUrl) {
        $parts = parse_url($databaseUrl);
        if (is_array($parts)) {
            $host = $parts['host'] ?? $host;
            $port = (string) ($parts['port'] ?? $port);
            $user = isset($parts['user']) ? rawurldecode($parts['user']) : $user;
            $password = isset($parts['pass']) ? rawurldecode($parts['pass']) : $password;
            $urlDb = ltrim($parts['path'] ?? '', '/');
            if ($urlDb !== '') {
                $dbname = $urlDb;
            }
        }
    }

    if ($railwayHost = env('MYSQLHOST') ?? env('MYSQL_HOST')) {
        $host = $railwayHost;
    }
    if ($railwayPort = env('MYSQLPORT') ?? env('MYSQL_PORT')) {
        $port = (string) $railwayPort;
    }
    if ($railwayUser = env('MYSQLUSER') ?? env('MYSQL_USER')) {
        $user = $railwayUser;
    }
    if ($railwayPass = env('MYSQLPASSWORD') ?? env('MYSQL_PASSWORD')) {
        $password = $railwayPass;
    }
    if ($railwayDb = env('MYSQLDATABASE') ?? env('MYSQL_DATABASE')) {
        $dbname = $railwayDb;
    }

    if (isCloudEnvironment() && $host === 'localhost' && !$databaseUrl && !env('MYSQLHOST')) {
        throw new RuntimeException(
            'DATABASE_URL manquant. Fly.io : fly secrets set DATABASE_URL="mysql://root:PASS@xxx.proxy.rlwy.net:PORT/railway"'
        );
    }

    // Sur Linux, "localhost" utilise un socket (erreur 2002) — forcer TCP
    if ($host === 'localhost') {
        $host = '127.0.0.1';
    }

    return compact('host', 'port', 'dbname', 'user', 'password');
}

function getDatabasePdoOptions(): array
{
    $params = getDatabaseParams();
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $useSsl = env('DB_SSL', '0');
    $isLocal = in_array($params['host'], ['127.0.0.1', 'db'], true);

    if ($useSsl === '1' && !$isLocal) {
        if (PHP_VERSION_ID >= 80400) {
            $options[\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = false;
        } elseif (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
    }

    return $options;
}

function buildDatabaseDsn(): string
{
    $params = getDatabaseParams();
    $dsn = "mysql:host={$params['host']};port={$params['port']};dbname={$params['dbname']};charset=utf8mb4";

    if (!in_array($params['host'], ['127.0.0.1', 'db'], true)) {
        $dsn .= ';allowPublicKeyRetrieval=true';
    }

    return $dsn;
}
