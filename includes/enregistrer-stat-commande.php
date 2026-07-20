<?php

function enregistrerStatCommande(int $idCommande, int $idMenu, string $menuTitre, float $montant): void
{
    if (class_exists('MongoDB\Driver\Manager')) {
        require_once __DIR__ . '/../config/mongodb.php';

        if ($mongoManager) {
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
                return;
            } catch (Throwable $e) {
                error_log('MongoDB write: ' . $e->getMessage());
            }
        }
    }

    // Fallback Vercel : API Node + driver officiel mongodb
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/mongo-bridge.php';
    mongoBridgeRequest('POST', [
        'id_commande' => $idCommande,
        'id_menu' => $idMenu,
        'menu_titre' => $menuTitre,
        'montant' => $montant,
    ]);
}
