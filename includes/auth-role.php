<?php

require_once __DIR__ . '/../config/app.php';

function requireLogin(): void
{
    if (!isset($_SESSION['id_utilisateur'])) {
        header('Location: ' . getBasePath() . 'connexion.php');
        exit;
    }
}

function requireRole(array $rolesAutorises): void
{
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $rolesAutorises, true)) {
        http_response_code(403);
        die('Accès refusé.');
    }
}