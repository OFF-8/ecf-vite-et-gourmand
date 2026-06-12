<?php
require_once 'config/database.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php?redirect=' . urlencode('mon-espace.php'));
    exit;
}

$stmt = $pdo->prepare(
    'SELECT c.id_commande, c.date_commande, c.date_prestation, c.prix_total,
            m.titre,
            (
                SELECT sc.libelle_statut
                FROM historique_statut hs
                JOIN statut_commande sc ON sc.id_statut = hs.id_statut
                WHERE hs.id_commande = c.id_commande
                ORDER BY hs.date_heure_modif DESC
                LIMIT 1
            ) AS statut_actuel
     FROM commande c
     JOIN menu m ON m.id_menu = c.id_menu
     WHERE c.id_utilisateur = :id
     ORDER BY c.date_commande DESC'
);
$stmt->execute(['id' => $_SESSION['id_utilisateur']]);
$commandes = $stmt->fetchAll();

$titrePage = 'Mon espace — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Mon espace</h1>

<p><a class="btn btn-outline-primary" href="profil.php">Modifier mes informations</a></p>

<h2 class="h4 mt-4">Mes commandes</h2>

<?php if (!$commandes): ?>
    <p class="text-muted">Vous n'avez pas encore passé de commande.</p>
    <a class="btn btn-primary" href="menus.php">Voir nos menus</a>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Menu</th>
                    <th>Date commande</th>
                    <th>Prestation</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $commande): ?>
                <tr>
                    <td><?= $commande['id_commande'] ?></td>
                    <td><?= htmlspecialchars($commande['titre']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($commande['date_prestation'])) ?></td>
                    <td><?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</td>
                    <td><?= htmlspecialchars($commande['statut_actuel'] ?? '—') ?></td>
                    <td>
                        <a class="btn btn-sm btn-primary"
                           href="commande-detail.php?id=<?= $commande['id_commande'] ?>">
                            Voir le détail
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>