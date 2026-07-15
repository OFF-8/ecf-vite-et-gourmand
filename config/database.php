<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/app.php';
require_once __DIR__ . '/db-params.php';

session_start();

try {
    $params = getDatabaseParams();
    $pdo = new PDO(
        buildDatabaseDsn(),
        $params['user'],
        $params['password'],
        getDatabasePdoOptions()
    );
} catch (RuntimeException $e) {
    die($e->getMessage());
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'timed out')) {
        die('Erreur MySQL : timeout. Railway → activez TCP Proxy, URL avec proxy.rlwy.net');
    }
    if (str_contains($e->getMessage(), 'No such file or directory')) {
        die('Erreur MySQL : DATABASE_URL non configuré ou host invalide. Utilisez proxy.rlwy.net (pas localhost).');
    }
    die('Erreur de connexion à la base de données.');
}
