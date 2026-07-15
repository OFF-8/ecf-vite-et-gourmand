<?php

function enregistrerStatCommande(int $idCommande, int $idMenu, string $menuTitre, float $montant): void
{
    if (!class_exists('MongoDB\Driver\Manager')) {
        return;
    }

    require_once __DIR__ . '/../config/mongodb.php';
    

    if (!$mongoManager) {
        return;
    }

    try {
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->insert([
            'id_commande' => $idCommande,
            'id_menu' => $idMenu,
            'menu_titre' => $menuTitre,
            'montant' => $montant,
            'date_commande' => new MongoDB\BSON\UTCDateTime(),
        ]);
        $mongoManager->executeBulkWrite("$mongoDatabase.$mongoCollection", $bulk);
    } catch (Exception $e) {
        // Ne pas bloquer la commande si MongoDB est indisponible
    }
}