<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['employe', 'administrateur']);

$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'UPDATE horaire SET heure_ouverture = :ouverture, heure_fermeture = :fermeture
         WHERE id_horaire = :id'
    );
    foreach ($_POST['horaires'] ?? [] as $idHoraire => $horaire) {
        $stmt->execute([
            'ouverture' => $horaire['ouverture'],
            'fermeture' => $horaire['fermeture'],
            'id' => (int) $idHoraire,
        ]);
    }
    $succes = true;
}

$horaires = $pdo->query(
    'SELECT * FROM horaire ORDER BY FIELD(jour, "lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche")'
)->fetchAll();

$titrePage = 'Gestion des horaires — Employé';
require __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des horaires</h1>
<p><a href="index.php">&larr; Retour</a></p>

<?php if ($succes): ?>
    <div class="alert alert-success" role="alert">Horaires mis à jour.</div>
<?php endif; ?>

<form method="post">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>Jour</th><th>Ouverture</th><th>Fermeture</th></tr>
            </thead>
            <tbody>
                <?php foreach ($horaires as $h): ?>
                <tr>
                    <td><?= ucfirst(htmlspecialchars($h['jour'])) ?></td>
                    <td>
                        <input class="form-control" type="time" required
                               name="horaires[<?= $h['id_horaire'] ?>][ouverture]"
                               value="<?= htmlspecialchars(substr($h['heure_ouverture'], 0, 5)) ?>">
                    </td>
                    <td>
                        <input class="form-control" type="time" required
                               name="horaires[<?= $h['id_horaire'] ?>][fermeture]"
                               value="<?= htmlspecialchars(substr($h['heure_fermeture'], 0, 5)) ?>">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button class="btn btn-primary" type="submit">Enregistrer les horaires</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>