<?php

require_once __DIR__ . '/env.php';

$mongoUri = env('MONGO_URI', 'mongodb://localhost:27017');
$mongoDatabase = env('MONGO_DATABASE', 'vite_et_gourmand');
$mongoCollection = env('MONGO_COLLECTION', 'commandes_stats');
$mongoManager = null;

if (class_exists('MongoDB\\Driver\\Manager')) {
    try {
        $mongoManager = new MongoDB\Driver\Manager($mongoUri);
    } catch (Throwable $e) {
        error_log('MongoDB connexion: ' . $e->getMessage());
        $mongoManager = null;
    }
}
