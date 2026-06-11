<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json; charset=utf-8');

$sql = 'SELECT m.id_menu, m.titre, m.description, m.nb_personnes_min,
               m.prix_min, m.stock_disponible, t.nom_theme, r.nom_regime
        FROM menu m
        JOIN theme t ON t.id_theme = m.id_theme
        JOIN regime r ON r.id_regime = m.id_regime
        WHERE m.actif = TRUE';
$params = [];

// Chaque filtre n'est ajouté à la requête QUE s'il est rempli,
// toujours via un paramètre préparé (jamais de valeur collée dans le SQL)
if (!empty($_GET['prix_min'])) {
    $sql .= ' AND m.prix_min >= :prix_min';
    $params['prix_min'] = (float) $_GET['prix_min'];
}
if (!empty($_GET['prix_max'])) {
    $sql .= ' AND m.prix_min <= :prix_max';
    $params['prix_max'] = (float) $_GET['prix_max'];
}
if (!empty($_GET['theme'])) {
    $sql .= ' AND m.id_theme = :theme';
    $params['theme'] = (int) $_GET['theme'];
}
if (!empty($_GET['regime'])) {
    $sql .= ' AND m.id_regime = :regime';
    $params['regime'] = (int) $_GET['regime'];
}
if (!empty($_GET['nb_personnes'])) {
    $sql .= ' AND m.nb_personnes_min >= :nb_personnes';
    $params['nb_personnes'] = (int) $_GET['nb_personnes'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll());