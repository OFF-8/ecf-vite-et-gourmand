<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['employe', 'administrateur']);

$titrePage = 'Espace employé — Vite & Gourmand';
require __DIR__ . '/../includes/header.php';
?>

<h1>Espace employé</h1>
<p>Bienvenue <?= htmlspecialchars($_SESSION['prenom']) ?>.</p>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Commandes</h2>
                <p>Gérer les statuts et filtrer les commandes.</p>
                <a class="btn btn-primary" href="commandes.php">Accéder</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Menus &amp; plats</h2>
                <p>Modifier ou supprimer les menus et plats.</p>
                <a class="btn btn-primary" href="menus.php">Accéder</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Horaires</h2>
                <p>Modifier les horaires d'ouverture.</p>
                <a class="btn btn-primary" href="horaires.php">Accéder</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Avis clients</h2>
                <p>Valider ou refuser les avis reçus.</p>
                <a class="btn btn-primary" href="avis.php">Accéder</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>