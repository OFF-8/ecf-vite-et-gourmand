<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['employe', 'administrateur']);

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    $id = (int) ($_POST['id_plat'] ?? 0);
    $pdo->prepare('DELETE FROM plat WHERE id_plat = :id')->execute(['id' => $id]);
    header('Location: plats.php?supprime=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'enregistrer') {
    $id = (int) ($_POST['id_plat'] ?? 0);
    $nom = trim($_POST['nom_plat'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type_plat'] ?? '';
    $allergenes = array_map('intval', $_POST['allergenes'] ?? []);

    if ($nom === '' || !in_array($type, ['entree', 'plat', 'dessert'], true)) {
        $erreurs[] = 'Nom et type obligatoires.';
    }

    if (!$erreurs) {
        if ($id > 0) {
            $pdo->prepare(
                'UPDATE plat SET nom_plat = :nom, description = :description, type_plat = :type WHERE id_plat = :id'
            )->execute(['nom' => $nom, 'description' => $description, 'type' => $type, 'id' => $id]);
            $idPlat = $id;
        } else {
            $pdo->prepare(
                'INSERT INTO plat (nom_plat, description, type_plat) VALUES (:nom, :description, :type)'
            )->execute(['nom' => $nom, 'description' => $description, 'type' => $type]);
            $idPlat = (int) $pdo->lastInsertId();
        }

        $pdo->prepare('DELETE FROM plat_allergene WHERE id_plat = :id')->execute(['id' => $idPlat]);
        $stmtA = $pdo->prepare('INSERT INTO plat_allergene (id_plat, id_allergene) VALUES (:id_plat, :id_allergene)');
        foreach ($allergenes as $idAllergene) {
            $stmtA->execute(['id_plat' => $idPlat, 'id_allergene' => $idAllergene]);
        }
        header('Location: plats.php?ok=1');
        exit;
    }
}

$plats = $pdo->query(
    'SELECT p.*, GROUP_CONCAT(a.nom_allergene SEPARATOR ", ") AS allergenes
     FROM plat p
     LEFT JOIN plat_allergene pa ON pa.id_plat = p.id_plat
     LEFT JOIN allergene a ON a.id_allergene = pa.id_allergene
     GROUP BY p.id_plat
     ORDER BY p.type_plat, p.nom_plat'
)->fetchAll();

$allergenes = $pdo->query('SELECT id_allergene, nom_allergene FROM allergene ORDER BY nom_allergene')->fetchAll();

$platEdit = null;
$allergenesEdit = [];
if (isset($_GET['edit'])) {
    $idEdit = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM plat WHERE id_plat = :id');
    $stmt->execute(['id' => $idEdit]);
    $platEdit = $stmt->fetch();
    $stmt = $pdo->prepare('SELECT id_allergene FROM plat_allergene WHERE id_plat = :id');
    $stmt->execute(['id' => $idEdit]);
    $allergenesEdit = array_column($stmt->fetchAll(), 'id_allergene');
}

$titrePage = 'Gestion des plats — Employé';
require __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des plats</h1>
<p><a href="index.php">&larr; Retour</a></p>

<?php if (isset($_GET['ok'])): ?>
    <div class="alert alert-success" role="alert">Plat enregistré.</div>
<?php endif; ?>
<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<h2 class="h5"><?= $platEdit ? 'Modifier' : 'Ajouter' ?> un plat</h2>
<form method="post" class="row g-3 mb-4">
    <input type="hidden" name="action" value="enregistrer">
    <input type="hidden" name="id_plat" value="<?= $platEdit['id_plat'] ?? 0 ?>">
    <div class="col-md-4">
        <label class="form-label" for="nom_plat">Nom</label>
        <input class="form-control" type="text" id="nom_plat" name="nom_plat" required
               value="<?= htmlspecialchars($platEdit['nom_plat'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="type_plat">Type</label>
        <select class="form-select" id="type_plat" name="type_plat" required>
            <?php foreach (['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= ($platEdit['type_plat'] ?? '') === $val ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="description">Description</label>
        <input class="form-control" type="text" id="description" name="description"
               value="<?= htmlspecialchars($platEdit['description'] ?? '') ?>">
    </div>
    <div class="col-12">
        <p class="form-label mb-1">Allergènes</p>
        <?php foreach ($allergenes as $a): ?>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="allergenes[]"
                   id="allergene_<?= $a['id_allergene'] ?>" value="<?= $a['id_allergene'] ?>"
                   <?= in_array($a['id_allergene'], $allergenesEdit) ? 'checked' : '' ?>>
            <label class="form-check-label" for="allergene_<?= $a['id_allergene'] ?>">
                <?= htmlspecialchars($a['nom_allergene']) ?>
            </label>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <?php if ($platEdit): ?>
            <a class="btn btn-secondary" href="plats.php">Annuler</a>
        <?php endif; ?>
    </div>
</form>

<h2 class="h5">Liste des plats</h2>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr><th>Type</th><th>Nom</th><th>Allergènes</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($plats as $plat): ?>
            <tr>
                <td><?= htmlspecialchars($plat['type_plat']) ?></td>
                <td><?= htmlspecialchars($plat['nom_plat']) ?></td>
                <td><?= htmlspecialchars($plat['allergenes'] ?? 'aucun') ?></td>
                <td>
                    <a class="btn btn-sm btn-primary" href="plats.php?edit=<?= $plat['id_plat'] ?>">Modifier</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce plat ?');">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id_plat" value="<?= $plat['id_plat'] ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>