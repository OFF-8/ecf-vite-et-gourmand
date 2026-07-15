<?php

function isCloudEnvironment(): bool
{
    return env('VERCEL') === '1'
        || env('FLY_APP_NAME') !== null
        || env('RENDER') === 'true'
        || env('RAILWAY_ENVIRONMENT') !== null;
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

    $databaseUrl = env('DATABASE_URL')
        ?? env('MYSQL_PUBLIC_URL')
        ?? env('MYSQL_URL');

    $fromUrl = false;
    $proxyHost = null;

    // Variables séparées explicites (Vercel) : prioritaires sur DATABASE_URL
    $explicitHost = env('DB_HOST');
    $useDiscrete = isCloudEnvironment()
        && $explicitHost !== null
        && $explicitHost !== ''
        && $explicitHost !== 'localhost'
        && str_contains($explicitHost, 'proxy.rlwy.net');

    if (!$useDiscrete && $databaseUrl) {
        $parts = parse_url($databaseUrl);
        if (is_array($parts) && !empty($parts['host'])) {
            $host = $parts['host'];
            $port = (string) ($parts['port'] ?? $port);
            $user = isset($parts['user']) ? rawurldecode($parts['user']) : $user;
            $password = isset($parts['pass']) ? rawurldecode($parts['pass']) : $password;
            $urlDb = ltrim($parts['path'] ?? '', '/');
            if ($urlDb !== '') {
                $dbname = $urlDb;
            }
            $fromUrl = true;
        }
    }

    if (!$fromUrl) {
        $proxyHost = env('RAILWAY_TCP_PROXY_DOMAIN');
        $proxyPort = env('RAILWAY_TCP_PROXY_PORT');
        if ($proxyHost && $proxyPort) {
            $host = $proxyHost;
            $port = (string) $proxyPort;
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
    }

    if (isCloudEnvironment() && !$fromUrl && !$useDiscrete && !env('MYSQLHOST') && !$proxyHost) {
        throw new RuntimeException(
            'DATABASE_URL manquant. Railway → TCP Proxy activé → copiez MYSQL_PUBLIC_URL (proxy.rlwy.net + port type 18432).'
        );
    }

    // Sur Linux, "localhost" utilise un socket (erreur 2002) — forcer TCP
    if ($host === 'localhost') {
        $host = '127.0.0.1';
    }

    // Railway : une seule base "railway" — vite_et_gourmand n'existe pas en cloud
    if (isCloudEnvironment() && str_contains($host, 'proxy.rlwy.net') && $dbname === 'vite_et_gourmand') {
        $dbname = 'railway';
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
