<?php
require_once 'config/database.php';
$titrePage = 'Nos menus — Vite & Gourmand';

// Listes pour remplir les menus déroulants des filtres
$themes = $pdo->query('SELECT id_theme, nom_theme FROM theme ORDER BY nom_theme')->fetchAll();
$regimes = $pdo->query('SELECT id_regime, nom_regime FROM regime ORDER BY nom_regime')->fetchAll();

require 'includes/header.php';
?>

<h1>Nos menus</h1>

<form id="filtres" class="row g-3 mb-4" aria-label="Filtrer les menus">
    <div class="col-md-2">
        <label class="form-label" for="prix_min">Prix minimum (€)</label>
        <input class="form-control" type="number" id="prix_min" name="prix_min" min="0">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="prix_max">Prix maximum (€)</label>
        <input class="form-control" type="number" id="prix_max" name="prix_max" min="0">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="theme">Thème</label>
        <select class="form-select" id="theme" name="theme">
            <option value="">Tous</option>
            <?php foreach ($themes as $t): ?>
                <option value="<?= $t['id_theme'] ?>"><?= htmlspecialchars($t['nom_theme']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="regime">Régime</label>
        <select class="form-select" id="regime" name="regime">
            <option value="">Tous</option>
            <?php foreach ($regimes as $r): ?>
                <option value="<?= $r['id_regime'] ?>"><?= htmlspecialchars($r['nom_regime']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="nb_personnes">Personnes min.</label>
        <input class="form-control" type="number" id="nb_personnes" name="nb_personnes" min="1">
    </div>
</form>

<div id="liste-menus" class="row g-4" aria-live="polite"></div>

<script src="asset/menus.js"></script>
<?php require 'includes/footer.php'; ?>