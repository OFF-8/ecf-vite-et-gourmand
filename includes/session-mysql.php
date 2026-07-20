<?php

/**
 * Handler de sessions PHP en MySQL — indispensable sur Vercel (filesystem éphémère).
 */
class MysqlSessionHandler implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo)
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS php_session (
                id VARCHAR(128) NOT NULL PRIMARY KEY,
                data MEDIUMTEXT NOT NULL,
                last_activity INT UNSIGNED NOT NULL,
                INDEX idx_session_activity (last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT data FROM php_session WHERE id = :id AND last_activity > :min'
        );
        $stmt->execute([
            'id' => $id,
            'min' => time() - (int) ini_get('session.gc_maxlifetime'),
        ]);
        $data = $stmt->fetchColumn();

        return $data === false ? '' : (string) $data;
    }

    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'REPLACE INTO php_session (id, data, last_activity) VALUES (:id, :data, :ts)'
        );

        return $stmt->execute([
            'id' => $id,
            'data' => $data,
            'ts' => time(),
        ]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM php_session WHERE id = :id');

        return $stmt->execute(['id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare('DELETE FROM php_session WHERE last_activity < :min');
        $stmt->execute(['min' => time() - $max_lifetime]);

        return $stmt->rowCount();
    }
}

function startAppSession(PDO $pdo): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = env('VERCEL') === '1'
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.gc_maxlifetime', '86400');

    session_set_save_handler(new MysqlSessionHandler($pdo), true);
    session_start();
}
