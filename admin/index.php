<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['administrateur']);

$titrePage = 'Espace administrateur — Vite & Gourmand';
require __DIR__ . '/../includes/header.php';
?>

<h1>Espace administrateur</h1>
<p>Bienvenue <?= htmlspecialchars($_SESSION['prenom']) ?>.</p>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Comptes employés</h2>
                <p>Créer ou désactiver un compte employé.</p>
                <a class="btn btn-primary" href="employes.php">Gérer</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Statistiques</h2>
                <p>Commandes par menu et chiffre d'affaires (MongoDB).</p>
                <a class="btn btn-primary" href="stats.php">Voir les stats</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Espace employé</h2>
                <p>L'administrateur peut aussi faire tout ce qu'un employé fait.</p>
                <a class="btn btn-secondary" href="../employe/index.php">Accéder</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>