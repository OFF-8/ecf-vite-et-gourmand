<?php
require_once 'config/database.php';
$titrePage = 'Accueil — Vite & Gourmand';

$avis = $pdo->query(
    "SELECT a.note, a.commentaire, a.date_avis, u.prenom
     FROM avis a
     JOIN commande c ON c.id_commande = a.id_commande
     JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
     WHERE a.statut = 'valide'
     ORDER BY a.date_avis DESC
     LIMIT 6"
)->fetchAll();

require 'includes/header.php';
?>

<h1>Vite &amp; Gourmand</h1>
<p>Traiteur à Bordeaux depuis 25 ans — Julie &amp; José vous régalent pour tous vos événements.</p>
<a class="btn btn-primary mb-5" href="menus.php">Découvrir nos menus</a>

<?php if ($avis): ?>
<h2 class="h4">Ce que disent nos clients</h2>
<div class="row g-3">
    <?php foreach ($avis as $a): ?>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="mb-1" aria-label="Note : <?= $a['note'] ?> sur 5">
                    <?= str_repeat('★', $a['note']) . str_repeat('☆', 5 - $a['note']) ?>
                </p>
                <p class="card-text">« <?= htmlspecialchars($a['commentaire']) ?> »</p>
                <p class="text-muted small mb-0">— <?= htmlspecialchars($a['prenom']) ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>