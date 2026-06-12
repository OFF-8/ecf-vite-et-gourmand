<?php

$mongoUri = 'mongodb://localhost:27017';
$mongoDatabase = 'vite_et_gourmand';
$mongoCollection = 'commandes_stats';

try {
    $mongoManager = new MongoDB\Driver\Manager($mongoUri);
} catch (Exception $e) {
    $mongoManager = null;
}