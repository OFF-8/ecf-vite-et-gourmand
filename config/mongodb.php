<?php

require_once __DIR__ . '/env.php';

$mongoUri = env('MONGO_URI', 'mongodb://localhost:27017');
$mongoDatabase = env('MONGO_DATABASE', 'vite_et_gourmand');
$mongoCollection = env('MONGO_COLLECTION', 'commandes_stats');

try {
    $mongoManager = new MongoDB\Driver\Manager($mongoUri);
} catch (Exception $e) {
    $mongoManager = null;
}
