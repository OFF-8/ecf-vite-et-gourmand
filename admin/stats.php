<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['administrateur']);

require_once __DIR__ . '/../config/mongodb.php';
require_once __DIR__ . '/../includes/mongo-bridge.php';

$filtreMenu = (int) ($_GET['menu'] ?? 0);
$filtreDebut = $_GET['debut'] ?? '';
$filtreFin = $_GET['fin'] ?? '';

$menus = $pdo->query('SELECT id_menu, titre FROM menu ORDER BY titre')->fetchAll();

$labels = [];
$counts = [];
$cas = [];
$caTotal = 0;
$erreurMongo = '';
$docs = [];

if ($mongoManager) {
    try {
        $filter = [];
        if ($filtreMenu > 0) {
            $filter['id_menu'] = $filtreMenu;
        }
        $query = new MongoDB\Driver\Query($filter);
        $cursor = $mongoManager->executeQuery("$mongoDatabase.$mongoCollection", $query);
        foreach ($cursor as $doc) {
            $docs[] = [
                'id_menu' => (int) $doc->id_menu,
                'menu_titre' => (string) $doc->menu_titre,
                'montant' => (float) $doc->montant,
                'date_commande' => $doc->date_commande->toDateTime()->format('Y-m-d'),
            ];
        }
    } catch (Throwable $e) {
        $erreurMongo = 'Erreur lecture MongoDB (extension) : ' . $e->getMessage();
    }
} else {
    $query = [];
    if ($filtreMenu > 0) {
        $query['menu'] = $filtreMenu;
    }
    $bridgeDocs = mongoBridgeRequest('GET', [], $query);
    if ($bridgeDocs === null) {
        $erreurMongo = 'MongoDB non disponible via le pont Vercel. Vérifiez MONGO_URI et /api/mongo-stats.';
    } else {
        $docs = $bridgeDocs;
    }
}

if (!$erreurMongo) {
    $aggregation = [];
    foreach ($docs as $doc) {
        $dateStr = $doc['date_commande'] ?? '';
        if ($filtreDebut !== '' && $dateStr !== '' && $dateStr < $filtreDebut) {
            continue;
        }
        if ($filtreFin !== '' && $dateStr !== '' && $dateStr > $filtreFin) {
            continue;
        }

        $key = (int) ($doc['id_menu'] ?? 0);
        if (!isset($aggregation[$key])) {
            $aggregation[$key] = [
                'titre' => (string) ($doc['menu_titre'] ?? 'Menu'),
                'count' => 0,
                'ca' => 0.0,
            ];
        }
        $aggregation[$key]['count']++;
        $aggregation[$key]['ca'] += (float) ($doc['montant'] ?? 0);
        $caTotal += (float) ($doc['montant'] ?? 0);
    }

    foreach ($aggregation as $stat) {
        $labels[] = $stat['titre'];
        $counts[] = $stat['count'];
        $cas[] = round($stat['ca'], 2);
    }
}

$titrePage = 'Statistiques — Admin';
require __DIR__ . '/../includes/header.php';
?>

<h1>Statistiques des commandes</h1>
<p><a href="index.php">&larr; Retour</a></p>

<?php if ($erreurMongo): ?>
    <div class="alert alert-warning" role="alert"><?= htmlspecialchars($erreurMongo) ?></div>
<?php endif; ?>

<form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label" for="menu">Menu</label>
        <select class="form-select" id="menu" name="menu">
            <option value="">Tous les menus</option>
            <?php foreach ($menus as $m): ?>
                <option value="<?= $m['id_menu'] ?>" <?= $filtreMenu == $m['id_menu'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="debut">Du</label>
        <input class="form-control" type="date" id="debut" name="debut" value="<?= htmlspecialchars($filtreDebut) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="fin">Au</label>
        <input class="form-control" type="date" id="fin" name="fin" value="<?= htmlspecialchars($filtreFin) ?>">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary" type="submit">Filtrer</button>
    </div>
</form>

<p class="fs-5">Chiffre d'affaires total : <strong><?= number_format($caTotal, 2, ',', ' ') ?> €</strong></p>

<?php if ($labels): ?>
<table class="table table-bordered mb-4">
    <thead>
        <tr><th>Menu</th><th>Nb commandes</th><th>CA (€)</th></tr>
    </thead>
    <tbody>
        <?php for ($i = 0; $i < count($labels); $i++): ?>
        <tr>
            <td><?= htmlspecialchars($labels[$i]) ?></td>
            <td><?= $counts[$i] ?></td>
            <td><?= number_format($cas[$i], 2, ',', ' ') ?></td>
        </tr>
        <?php endfor; ?>
    </tbody>
</table>

<canvas id="graphiqueCommandes" height="120" aria-label="Graphique des commandes par menu"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('graphiqueCommandes');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Nombre de commandes',
            data: <?= json_encode($counts) ?>,
            backgroundColor: 'rgba(107, 44, 62, 0.65)'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
<?php else: ?>
    <p class="text-muted">Aucune donnée pour ces filtres. Passez des commandes pour alimenter MongoDB.</p>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
