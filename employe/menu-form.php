<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['employe', 'administrateur']);

$id = (int) ($_GET['id'] ?? 0);
$erreurs = [];
$menu = null;
$platsSelectionnes = [];

$themes = $pdo->query('SELECT id_theme, nom_theme FROM theme ORDER BY nom_theme')->fetchAll();
$regimes = $pdo->query('SELECT id_regime, nom_regime FROM regime ORDER BY nom_regime')->fetchAll();
$plats = $pdo->query('SELECT id_plat, nom_plat, type_plat FROM plat ORDER BY type_plat, nom_plat')->fetchAll();

if ($id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM menu WHERE id_menu = :id AND actif = TRUE');
    $stmt->execute(['id' => $id]);
    $menu = $stmt->fetch();
    if (!$menu) {
        header('Location: menus.php');
        exit;
    }
    $stmt = $pdo->prepare('SELECT id_plat FROM menu_plat WHERE id_menu = :id');
    $stmt->execute(['id' => $id]);
    $platsSelectionnes = array_column($stmt->fetchAll(), 'id_plat');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $conditions = trim($_POST['conditions'] ?? '');
    $nbMin = (int) ($_POST['nb_personnes_min'] ?? 0);
    $prixMin = (float) ($_POST['prix_min'] ?? 0);
    $stock = (int) ($_POST['stock_disponible'] ?? 0);
    $idTheme = (int) ($_POST['id_theme'] ?? 0);
    $idRegime = (int) ($_POST['id_regime'] ?? 0);
    $platsPost = array_map('intval', $_POST['plats'] ?? []);

    if ($titre === '' || $description === '' || $conditions === '' || $nbMin < 1 || $prixMin <= 0) {
        $erreurs[] = 'Veuillez remplir tous les champs obligatoires.';
    }

    if (!$erreurs) {
        if ($id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE menu SET titre = :titre, description = :description, conditions = :conditions,
                 nb_personnes_min = :nb_min, prix_min = :prix_min, stock_disponible = :stock,
                 id_theme = :id_theme, id_regime = :id_regime WHERE id_menu = :id'
            );
            $stmt->execute([
                'titre' => $titre, 'description' => $description, 'conditions' => $conditions,
                'nb_min' => $nbMin, 'prix_min' => $prixMin, 'stock' => $stock,
                'id_theme' => $idTheme, 'id_regime' => $idRegime, 'id' => $id,
            ]);
            $idMenu = $id;
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO menu (titre, description, conditions, nb_personnes_min, prix_min,
                 stock_disponible, id_theme, id_regime)
                 VALUES (:titre, :description, :conditions, :nb_min, :prix_min, :stock, :id_theme, :id_regime)'
            );
            $stmt->execute([
                'titre' => $titre, 'description' => $description, 'conditions' => $conditions,
                'nb_min' => $nbMin, 'prix_min' => $prixMin, 'stock' => $stock,
                'id_theme' => $idTheme, 'id_regime' => $idRegime,
            ]);
            $idMenu = (int) $pdo->lastInsertId();
        }

        // Synchroniser les plats du menu (relation N,N)
        $pdo->prepare('DELETE FROM menu_plat WHERE id_menu = :id')->execute(['id' => $idMenu]);
        $stmtPlat = $pdo->prepare('INSERT INTO menu_plat (id_menu, id_plat) VALUES (:id_menu, :id_plat)');
        foreach ($platsPost as $idPlat) {
            $stmtPlat->execute(['id_menu' => $idMenu, 'id_plat' => $idPlat]);
        }

        header('Location: menus.php');
        exit;
    }
}

$titrePage = ($id > 0 ? 'Modifier' : 'Ajouter') . ' un menu — Employé';
require __DIR__ . '/../includes/header.php';

$valeurs = $menu ?? [
    'titre' => $_POST['titre'] ?? '',
    'description' => $_POST['description'] ?? '',
    'conditions' => $_POST['conditions'] ?? '',
    'nb_personnes_min' => $_POST['nb_personnes_min'] ?? '',
    'prix_min' => $_POST['prix_min'] ?? '',
    'stock_disponible' => $_POST['stock_disponible'] ?? '',
    'id_theme' => $_POST['id_theme'] ?? '',
    'id_regime' => $_POST['id_regime'] ?? '',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $platsSelectionnes = array_map('intval', $_POST['plats'] ?? []);
}
?>

<h1><?= $id > 0 ? 'Modifier' : 'Ajouter' ?> un menu</h1>
<p><a href="menus.php">&larr; Retour à la liste</a></p>

<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<form method="post" class="row g-3">
    <div class="col-md-8">
        <label class="form-label" for="titre">Titre</label>
        <input class="form-control" type="text" id="titre" name="titre" required
               value="<?= htmlspecialchars($valeurs['titre']) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="stock_disponible">Stock disponible</label>
        <input class="form-control" type="number" id="stock_disponible" name="stock_disponible" min="0" required
               value="<?= htmlspecialchars($valeurs['stock_disponible']) ?>">
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($valeurs['description']) ?></textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="conditions">Conditions</label>
        <textarea class="form-control" id="conditions" name="conditions" rows="3" required><?= htmlspecialchars($valeurs['conditions']) ?></textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="nb_personnes_min">Personnes minimum</label>
        <input class="form-control" type="number" id="nb_personnes_min" name="nb_personnes_min" min="1" required
               value="<?= htmlspecialchars($valeurs['nb_personnes_min']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="prix_min">Prix minimum (€)</label>
        <input class="form-control" type="number" id="prix_min" name="prix_min" min="0" step="0.01" required
               value="<?= htmlspecialchars($valeurs['prix_min']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="id_theme">Thème</label>
        <select class="form-select" id="id_theme" name="id_theme" required>
            <?php foreach ($themes as $t): ?>
                <option value="<?= $t['id_theme'] ?>" <?= ($valeurs['id_theme'] ?? '') == $t['id_theme'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['nom_theme']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="id_regime">Régime</label>
        <select class="form-select" id="id_regime" name="id_regime" required>
            <?php foreach ($regimes as $r): ?>
                <option value="<?= $r['id_regime'] ?>" <?= ($valeurs['id_regime'] ?? '') == $r['id_regime'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['nom_regime']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12">
        <fieldset>
            <legend class="form-label">Plats composant le menu</legend>
            <?php foreach ($plats as $plat): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="plats[]"
                       id="plat_<?= $plat['id_plat'] ?>" value="<?= $plat['id_plat'] ?>"
                       <?= in_array($plat['id_plat'], $platsSelectionnes) ? 'checked' : '' ?>>
                <label class="form-check-label" for="plat_<?= $plat['id_plat'] ?>">
                    [<?= htmlspecialchars($plat['type_plat']) ?>] <?= htmlspecialchars($plat['nom_plat']) ?>
                </label>
            </div>
            <?php endforeach; ?>
        </fieldset>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
    </div>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>