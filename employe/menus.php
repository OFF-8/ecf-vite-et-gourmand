<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['employe', 'administrateur']);

// Suppression logique (actif = FALSE) pour ne pas casser les commandes existantes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $id = (int) ($_POST['id_menu'] ?? 0);
    $pdo->prepare('UPDATE menu SET actif = FALSE WHERE id_menu = :id')->execute(['id' => $id]);
    header('Location: menus.php?supprime=1');
    exit;
}

$menus = $pdo->query(
    'SELECT m.*, t.nom_theme, r.nom_regime
     FROM menu m
     JOIN theme t ON t.id_theme = m.id_theme
     JOIN regime r ON r.id_regime = m.id_regime
     WHERE m.actif = TRUE
     ORDER BY m.titre'
)->fetchAll();

$titrePage = 'Gestion des menus — Employé';
require __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des menus</h1>
<p>
    <a href="index.php">&larr; Retour</a>
    &nbsp;|&nbsp;
    <a class="btn btn-success btn-sm" href="menu-form.php">+ Ajouter un menu</a>
</p>

<?php if (isset($_GET['supprime'])): ?>
    <div class="alert alert-success" role="alert">Menu désactivé avec succès.</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Thème</th>
                <th>Régime</th>
                <th>Min pers.</th>
                <th>Prix min</th>
                <th>Stock</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($menus as $menu): ?>
            <tr>
                <td><?= htmlspecialchars($menu['titre']) ?></td>
                <td><?= htmlspecialchars($menu['nom_theme']) ?></td>
                <td><?= htmlspecialchars($menu['nom_regime']) ?></td>
                <td><?= $menu['nb_personnes_min'] ?></td>
                <td><?= number_format($menu['prix_min'], 2, ',', ' ') ?> €</td>
                <td><?= $menu['stock_disponible'] ?></td>
                <td>
                    <a class="btn btn-sm btn-primary" href="menu-form.php?id=<?= $menu['id_menu'] ?>">Modifier</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Désactiver ce menu ?');">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id_menu" value="<?= $menu['id_menu'] ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>