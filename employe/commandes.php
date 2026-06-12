<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['employe', 'administrateur']);

$succes = '';
$erreurs = [];

function getStatutActuel(PDO $pdo, int $idCommande): ?array
{
    $stmt = $pdo->prepare(
        'SELECT sc.id_statut, sc.libelle_statut
         FROM historique_statut hs
         JOIN statut_commande sc ON sc.id_statut = hs.id_statut
         WHERE hs.id_commande = :id
         ORDER BY hs.date_heure_modif DESC LIMIT 1'
    );
    $stmt->execute(['id' => $idCommande]);
    return $stmt->fetch() ?: null;
}

// --- Changement de statut ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'statut') {
    $idCommande = (int) ($_POST['id_commande'] ?? 0);
    $idStatut = (int) ($_POST['id_statut'] ?? 0);

    $statutsValides = [2, 3, 4, 5, 6, 7];
    if (!in_array($idStatut, $statutsValides, true)) {
        $erreurs[] = 'Statut invalide.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO historique_statut (id_commande, id_statut, id_utilisateur)
             VALUES (:id_commande, :id_statut, :id_utilisateur)'
        );
        $stmt->execute([
            'id_commande' => $idCommande,
            'id_statut' => $idStatut,
            'id_utilisateur' => $_SESSION['id_utilisateur'],
        ]);
        $succes = 'Statut mis à jour.';
        // TODO : envoi mail si statut 6 (retour matériel) ou 7 (terminée → invitation avis)
    }
}

// --- Annulation employé (motif + mode de contact obligatoires) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'annuler') {
    $idCommande = (int) ($_POST['id_commande'] ?? 0);
    $motif = trim($_POST['motif_annulation'] ?? '');
    $modeContact = $_POST['mode_contact'] ?? '';

    if ($motif === '' || !in_array($modeContact, ['gsm', 'mail'], true)) {
        $erreurs[] = 'Motif et mode de contact (gsm ou mail) obligatoires pour annuler.';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE commande SET motif_annulation = :motif, mode_contact = :mode
             WHERE id_commande = :id'
        );
        $stmt->execute(['motif' => $motif, 'mode' => $modeContact, 'id' => $idCommande]);

        $stmt = $pdo->prepare(
            'INSERT INTO historique_statut (id_commande, id_statut, id_utilisateur)
             VALUES (:id, 8, :id_utilisateur)'
        );
        $stmt->execute(['id' => $idCommande, 'id_utilisateur' => $_SESSION['id_utilisateur']]);

        $stmt = $pdo->prepare(
            'UPDATE menu SET stock_disponible = stock_disponible + 1
             WHERE id_menu = (SELECT id_menu FROM commande WHERE id_commande = :id)'
        );
        $stmt->execute(['id' => $idCommande]);

        $succes = 'Commande annulée.';
    }
}

// --- Filtres ---
$filtreStatut = (int) ($_GET['statut'] ?? 0);
$filtreClient = trim($_GET['client'] ?? '');

$sql = 'SELECT c.id_commande, c.date_commande, c.date_prestation, c.prix_total,
               u.nom, u.prenom, u.email, m.titre,
               (
                   SELECT sc.libelle_statut FROM historique_statut hs
                   JOIN statut_commande sc ON sc.id_statut = hs.id_statut
                   WHERE hs.id_commande = c.id_commande
                   ORDER BY hs.date_heure_modif DESC LIMIT 1
               ) AS statut_actuel,
               (
                   SELECT hs.id_statut FROM historique_statut hs
                   WHERE hs.id_commande = c.id_commande
                   ORDER BY hs.date_heure_modif DESC LIMIT 1
               ) AS id_statut_actuel
        FROM commande c
        JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
        JOIN menu m ON m.id_menu = c.id_menu
        WHERE 1=1';
$params = [];

if ($filtreStatut > 0) {
    $sql .= ' AND (
        SELECT hs.id_statut FROM historique_statut hs
        WHERE hs.id_commande = c.id_commande
        ORDER BY hs.date_heure_modif DESC LIMIT 1
    ) = :statut';
    $params['statut'] = $filtreStatut;
}
if ($filtreClient !== '') {
    $sql .= ' AND (u.nom LIKE :client OR u.prenom LIKE :client OR u.email LIKE :client)';
    $params['client'] = '%' . $filtreClient . '%';
}

$sql .= ' ORDER BY c.date_commande DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll();

$statuts = $pdo->query('SELECT id_statut, libelle_statut FROM statut_commande ORDER BY id_statut')->fetchAll();

$titrePage = 'Gestion des commandes — Employé';
require __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des commandes</h1>
<p><a href="index.php">&larr; Retour à l'espace employé</a></p>

<?php if ($succes): ?>
    <div class="alert alert-success" role="alert"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>
<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
        <label class="form-label" for="statut">Filtrer par statut</label>
        <select class="form-select" id="statut" name="statut">
            <option value="">Tous</option>
            <?php foreach ($statuts as $s): ?>
                <option value="<?= $s['id_statut'] ?>" <?= $filtreStatut == $s['id_statut'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['libelle_statut']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="client">Filtrer par client</label>
        <input class="form-control" type="text" id="client" name="client"
               placeholder="Nom, prénom ou email"
               value="<?= htmlspecialchars($filtreClient) ?>">
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary" type="submit">Filtrer</button>
    </div>
</form>

<?php if (!$commandes): ?>
    <p class="text-muted">Aucune commande trouvée.</p>
<?php else: ?>
    <?php foreach ($commandes as $cmd): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h2 class="h6">Commande n°<?= $cmd['id_commande'] ?> — <?= htmlspecialchars($cmd['titre']) ?></h2>
            <p class="mb-1">
                Client : <?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?>
                (<?= htmlspecialchars($cmd['email']) ?>)
            </p>
            <p class="mb-1">
                Prestation : <?= date('d/m/Y', strtotime($cmd['date_prestation'])) ?>
                — Total : <?= number_format($cmd['prix_total'], 2, ',', ' ') ?> €
            </p>
            <p class="mb-2"><strong>Statut :</strong> <?= htmlspecialchars($cmd['statut_actuel'] ?? '—') ?></p>

            <?php if ((int) $cmd['id_statut_actuel'] !== 8): ?>
            <form method="post" class="row g-2 align-items-end mb-2">
                <input type="hidden" name="action" value="statut">
                <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                <div class="col-md-6">
                    <label class="form-label">Changer le statut</label>
                    <select class="form-select" name="id_statut" required>
                        <option value="2">acceptée</option>
                        <option value="3">en préparation</option>
                        <option value="4">en cours de livraison</option>
                        <option value="5">livrée</option>
                        <option value="6">en attente du retour de matériel</option>
                        <option value="7">terminée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-warning" type="submit">Mettre à jour</button>
                </div>
            </form>

            <details>
                <summary class="text-danger">Annuler cette commande</summary>
                <form method="post" class="mt-2">
                    <input type="hidden" name="action" value="annuler">
                    <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
                    <div class="mb-2">
                        <label class="form-label">Motif d'annulation</label>
                        <textarea class="form-control" name="motif_annulation" rows="2" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mode de contact client</label>
                        <select class="form-select" name="mode_contact" required>
                            <option value="gsm">Appel GSM</option>
                            <option value="mail">Email</option>
                        </select>
                    </div>
                    <button class="btn btn-danger btn-sm" type="submit"
                            onclick="return confirm('Confirmer l\'annulation ?');">
                        Annuler la commande
                    </button>
                </form>
            </details>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>