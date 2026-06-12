<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['employe', 'administrateur']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idAvis = (int) ($_POST['id_avis'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'valider') {
        $pdo->prepare('UPDATE avis SET statut = \'valide\' WHERE id_avis = :id')
            ->execute(['id' => $idAvis]);
    } elseif ($action === 'refuser') {
        $pdo->prepare('UPDATE avis SET statut = \'refuse\' WHERE id_avis = :id')
            ->execute(['id' => $idAvis]);
    }
}

$avis = $pdo->query(
    'SELECT a.*, u.prenom, u.nom, m.titre
     FROM avis a
     JOIN commande c ON c.id_commande = a.id_commande
     JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
     JOIN menu m ON m.id_menu = c.id_menu
     WHERE a.statut = \'en_attente\'
     ORDER BY a.date_avis DESC'
)->fetchAll();

$titrePage = 'Modération des avis — Employé';
require __DIR__ . '/../includes/header.php';
?>

<h1>Avis en attente de modération</h1>
<p><a href="index.php">&larr; Retour</a></p>

<?php if (!$avis): ?>
    <p class="text-muted">Aucun avis à modérer.</p>
<?php else: ?>
    <?php foreach ($avis as $a): ?>
    <div class="card mb-3">
        <div class="card-body">
            <p><strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
               — Menu : <?= htmlspecialchars($a['titre']) ?></p>
            <p>Note : <?= $a['note'] ?>/5</p>
            <p><?= htmlspecialchars($a['commentaire']) ?></p>
            <p class="text-muted"><?= date('d/m/Y H:i', strtotime($a['date_avis'])) ?></p>
            <form method="post" class="d-inline">
                <input type="hidden" name="id_avis" value="<?= $a['id_avis'] ?>">
                <button class="btn btn-success btn-sm" name="action" value="valider">Valider</button>
                <button class="btn btn-danger btn-sm" name="action" value="refuser">Refuser</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>