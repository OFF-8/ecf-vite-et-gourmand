<?php
require_once 'config/database.php';

// On valide l'id reçu dans l'URL : un entier, sinon 0 → page introuvable
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT m.*, t.nom_theme, r.nom_regime
     FROM menu m
     JOIN theme t ON t.id_theme = m.id_theme
     JOIN regime r ON r.id_regime = m.id_regime
     WHERE m.id_menu = :id AND m.actif = TRUE'
);
$stmt->execute(['id' => $id]);
$menu = $stmt->fetch();

if (!$menu) {
    http_response_code(404);
    $titrePage = 'Menu introuvable';
    require 'includes/header.php';
    echo '<h1>Menu introuvable</h1><p><a href="menus.php">Retour à nos menus</a></p>';
    require 'includes/footer.php';
    exit;
}

// Galerie d'images
$stmt = $pdo->prepare('SELECT url_image, alt_text FROM image WHERE id_menu = :id');
$stmt->execute(['id' => $id]);
$images = $stmt->fetchAll();

// Plats du menu avec leurs allergènes (regroupés en une seule colonne)
$stmt = $pdo->prepare(
    "SELECT p.nom_plat, p.description, p.type_plat,
            GROUP_CONCAT(a.nom_allergene SEPARATOR ', ') AS allergenes
     FROM plat p
     JOIN menu_plat mp ON mp.id_plat = p.id_plat
     LEFT JOIN plat_allergene pa ON pa.id_plat = p.id_plat
     LEFT JOIN allergene a ON a.id_allergene = pa.id_allergene
     WHERE mp.id_menu = :id
     GROUP BY p.id_plat
     ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert')"
);
$stmt->execute(['id' => $id]);
$plats = $stmt->fetchAll();

$titrePage = $menu['titre'] . ' — Vite & Gourmand';
require 'includes/header.php';
?>

<nav aria-label="Fil d'Ariane">
    <a href="menus.php">&larr; Retour à nos menus</a>
</nav>

<h1><?= htmlspecialchars($menu['titre']) ?></h1>
<p>
    <span class="badge bg-secondary"><?= htmlspecialchars($menu['nom_theme']) ?></span>
    <span class="badge bg-success"><?= htmlspecialchars($menu['nom_regime']) ?></span>
</p>

<?php if ($images): ?>
<div id="galerie" class="carousel slide mb-4" data-bs-ride="carousel" style="max-width: 600px;">
    <div class="carousel-inner">
        <?php foreach ($images as $i => $img): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <img src="<?= htmlspecialchars($img['url_image']) ?>"
                 alt="<?= htmlspecialchars($img['alt_text']) ?>" class="d-block w-100 rounded">
        </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#galerie" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Image précédente</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#galerie" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Image suivante</span>
    </button>
</div>
<?php endif; ?>

<p><?= nl2br(htmlspecialchars($menu['description'])) ?></p>

<ul>
    <li>Nombre de personnes minimum : <strong><?= $menu['nb_personnes_min'] ?></strong></li>
    <li>Prix pour <?= $menu['nb_personnes_min'] ?> personnes : <strong><?= $menu['prix_min'] ?> €</strong></li>
    <li>Stock : <strong><?= $menu['stock_disponible'] ?> commande(s) encore possible(s)</strong></li>
</ul>

<!-- Conditions mises en évidence : exigence forte du sujet -->
<div class="alert alert-warning border border-warning-subtle" role="alert">
    <h2 class="h5">⚠ Conditions de ce menu — à lire avant de commander</h2>
    <p class="mb-0"><?= nl2br(htmlspecialchars($menu['conditions'])) ?></p>
</div>

<h2>Composition du menu</h2>
<div class="row g-3 mb-4">
    <?php foreach ($plats as $plat): ?>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <p class="text-uppercase text-muted small mb-1"><?= htmlspecialchars($plat['type_plat']) ?></p>
                <h3 class="h6"><?= htmlspecialchars($plat['nom_plat']) ?></h3>
                <p class="small"><?= htmlspecialchars($plat['description'] ?? '') ?></p>
                <p class="small mb-0">
                    <strong>Allergènes :</strong>
                    <?= $plat['allergenes'] ? htmlspecialchars($plat['allergenes']) : 'aucun' ?>
                </p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<a class="btn btn-primary btn-lg" href="commande.php?menu=<?= $menu['id_menu'] ?>">Commander ce menu</a>

<?php require 'includes/footer.php'; ?>