<?php
require_once 'config/database.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php?redirect=' . urlencode('mon-espace.php'));
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$erreurs = [];
$succes = '';

function calculerPrix(float $prixMin, int $nbMin, int $nbPersonnes): array
{
    $prixBase = $prixMin * ($nbPersonnes / $nbMin);
    $remise = 0;
    if ($nbPersonnes >= $nbMin + 5) {
        $remise = round($prixBase * 0.10, 2);
    }
    return ['prix_menu' => round($prixBase - $remise, 2), 'remise' => $remise];
}

function calculerLivraison(string $ville, float $distanceKm): float
{
    if (mb_strtolower(trim($ville)) === 'bordeaux') {
        return 0;
    }
    return round(5 + ($distanceKm * 0.59), 2);
}

function getStatutActuel(PDO $pdo, int $idCommande): ?array
{
    $stmt = $pdo->prepare(
        'SELECT sc.id_statut, sc.libelle_statut
         FROM historique_statut hs
         JOIN statut_commande sc ON sc.id_statut = hs.id_statut
         WHERE hs.id_commande = :id
         ORDER BY hs.date_heure_modif DESC
         LIMIT 1'
    );
    $stmt->execute(['id' => $idCommande]);
    return $stmt->fetch() ?: null;
}

// Récupérer la commande (uniquement celle de l'utilisateur connecté)
$stmt = $pdo->prepare(
    'SELECT c.*, m.titre, m.nb_personnes_min, m.prix_min
     FROM commande c
     JOIN menu m ON m.id_menu = c.id_menu
     WHERE c.id_commande = :id AND c.id_utilisateur = :id_utilisateur'
);
$stmt->execute(['id' => $id, 'id_utilisateur' => $_SESSION['id_utilisateur']]);
$commande = $stmt->fetch();

if (!$commande) {
    http_response_code(404);
    $titrePage = 'Commande introuvable';
    require 'includes/header.php';
    echo '<h1>Commande introuvable</h1><p><a href="mon-espace.php">Retour</a></p>';
    require 'includes/footer.php';
    exit;
}

$statutActuel = getStatutActuel($pdo, $id);
$peutModifier = ($statutActuel['id_statut'] ?? 0) === 1; // en attente

// --- Annulation ---
if (isset($_POST['action']) && $_POST['action'] === 'annuler' && $peutModifier) {
    $stmt = $pdo->prepare(
        'INSERT INTO historique_statut (id_commande, id_statut) VALUES (:id, 8)'
    );
    $stmt->execute(['id' => $id]);

    $stmt = $pdo->prepare(
        'UPDATE menu SET stock_disponible = stock_disponible + 1 WHERE id_menu = :id'
    );
    $stmt->execute(['id' => $commande['id_menu']]);

    $succes = 'Votre commande a été annulée.';
    $statutActuel = getStatutActuel($pdo, $id);
    $peutModifier = false;
}

// --- Modification ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier' && $peutModifier) {
    $nbPersonnes = (int) ($_POST['nb_personnes'] ?? 0);
    $datePrestation = $_POST['date_prestation'] ?? '';
    $heureLivraison = $_POST['heure_livraison'] ?? '';
    $adresseLivraison = trim($_POST['adresse_livraison'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $distanceKm = (float) ($_POST['distance_km'] ?? 0);

    if ($nbPersonnes < $commande['nb_personnes_min']) {
        $erreurs[] = 'Minimum ' . $commande['nb_personnes_min'] . ' personnes.';
    }
    if ($datePrestation === '' || $heureLivraison === '' || $adresseLivraison === '' || $ville === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }

    if (!$erreurs) {
        if (mb_strtolower($ville) === 'bordeaux') {
            $distanceKm = 0;
        }
        $prix = calculerPrix((float) $commande['prix_min'], (int) $commande['nb_personnes_min'], $nbPersonnes);
        $prixLivraison = calculerLivraison($ville, $distanceKm);
        $prixTotal = round($prix['prix_menu'] + $prixLivraison, 2);

        $stmt = $pdo->prepare(
            'UPDATE commande SET date_prestation = :date, heure_livraison = :heure,
             adresse_livraison = :adresse, ville = :ville, distance_km = :distance,
             nb_personnes = :nb, prix_menu = :prix_menu, prix_livraison = :prix_livraison,
             remise = :remise, prix_total = :prix_total
             WHERE id_commande = :id'
        );
        $stmt->execute([
            'date' => $datePrestation, 'heure' => $heureLivraison,
            'adresse' => $adresseLivraison, 'ville' => $ville,
            'distance' => $distanceKm, 'nb' => $nbPersonnes,
            'prix_menu' => $prix['prix_menu'], 'prix_livraison' => $prixLivraison,
            'remise' => $prix['remise'], 'prix_total' => $prixTotal, 'id' => $id,
        ]);
        $succes = 'Commande modifiée avec succès.';

        // Re-fetch commande
        $stmt = $pdo->prepare(
            'SELECT c.*, m.titre, m.nb_personnes_min, m.prix_min
             FROM commande c JOIN menu m ON m.id_menu = c.id_menu
             WHERE c.id_commande = :id AND c.id_utilisateur = :id_utilisateur'
        );
        $stmt->execute(['id' => $id, 'id_utilisateur' => $_SESSION['id_utilisateur']]);
        $commande = $stmt->fetch();
    }
}

// --- Avis ---
$stmt = $pdo->prepare('SELECT * FROM avis WHERE id_commande = :id');
$stmt->execute(['id' => $id]);
$avisExistant = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'avis') {
    if (($statutActuel['id_statut'] ?? 0) !== 7) {
        $erreurs[] = 'Vous ne pouvez laisser un avis que pour une commande terminée.';
    } elseif ($avisExistant) {
        $erreurs[] = 'Vous avez déjà laissé un avis pour cette commande.';
    } else {
        $note = (int) ($_POST['note'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');
        if ($note < 1 || $note > 5 || $commentaire === '') {
            $erreurs[] = 'Note (1 à 5) et commentaire obligatoires.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO avis (note, commentaire, id_commande) VALUES (:note, :commentaire, :id)'
            );
            $stmt->execute(['note' => $note, 'commentaire' => $commentaire, 'id' => $id]);
            $succes = 'Votre avis a été enregistré. Il sera visible après modération.';
            $avisExistant = ['note' => $note, 'commentaire' => $commentaire, 'statut' => 'en_attente'];
        }
    }
}

// Historique du suivi
$stmt = $pdo->prepare(
    'SELECT sc.libelle_statut, hs.date_heure_modif
     FROM historique_statut hs
     JOIN statut_commande sc ON sc.id_statut = hs.id_statut
     WHERE hs.id_commande = :id
     ORDER BY hs.date_heure_modif ASC'
);
$stmt->execute(['id' => $id]);
$historique = $stmt->fetchAll();

$titrePage = 'Commande n°' . $id . ' — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Commande n°<?= $id ?></h1>
<p><a href="mon-espace.php">&larr; Retour à mon espace</a></p>

<?php if ($succes): ?>
    <div class="alert alert-success" role="alert"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>
<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<h2 class="h4">Détail</h2>
<ul>
    <li><strong>Menu :</strong> <?= htmlspecialchars($commande['titre']) ?> (non modifiable)</li>
    <li><strong>Date commande :</strong> <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></li>
    <li><strong>Prestation :</strong> <?= date('d/m/Y', strtotime($commande['date_prestation'])) ?> à <?= substr($commande['heure_livraison'], 0, 5) ?></li>
    <li><strong>Adresse :</strong> <?= htmlspecialchars($commande['adresse_livraison']) ?>, <?= htmlspecialchars($commande['ville']) ?></li>
    <li><strong>Personnes :</strong> <?= $commande['nb_personnes'] ?></li>
    <li><strong>Prix menu :</strong> <?= number_format($commande['prix_menu'], 2, ',', ' ') ?> €</li>
    <li><strong>Livraison :</strong> <?= number_format($commande['prix_livraison'], 2, ',', ' ') ?> €</li>
    <?php if ($commande['remise'] > 0): ?>
        <li><strong>Remise :</strong> -<?= number_format($commande['remise'], 2, ',', ' ') ?> €</li>
    <?php endif; ?>
    <li><strong>Total :</strong> <?= number_format($commande['prix_total'], 2, ',', ' ') ?> €</li>
    <li><strong>Statut actuel :</strong> <?= htmlspecialchars($statutActuel['libelle_statut'] ?? '—') ?></li>
</ul>

<h2 class="h4">Suivi de la commande</h2>
<ol class="list-group list-group-numbered mb-4">
    <?php foreach ($historique as $etape): ?>
        <li class="list-group-item d-flex justify-content-between">
            <span><?= htmlspecialchars($etape['libelle_statut']) ?></span>
            <span class="text-muted"><?= date('d/m/Y H:i', strtotime($etape['date_heure_modif'])) ?></span>
        </li>
    <?php endforeach; ?>
</ol>

<?php if ($peutModifier): ?>
<h2 class="h4">Modifier ma commande</h2>
<form method="post" class="row g-3 mb-4">
    <input type="hidden" name="action" value="modifier">
    <div class="col-md-4">
        <label class="form-label" for="date_prestation">Date</label>
        <input class="form-control" type="date" id="date_prestation" name="date_prestation" required
               value="<?= htmlspecialchars($commande['date_prestation']) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="heure_livraison">Heure</label>
        <input class="form-control" type="time" id="heure_livraison" name="heure_livraison" required
               value="<?= htmlspecialchars($commande['heure_livraison']) ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="nb_personnes">Personnes</label>
        <input class="form-control" type="number" id="nb_personnes" name="nb_personnes" required
               min="<?= $commande['nb_personnes_min'] ?>"
               value="<?= $commande['nb_personnes'] ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="adresse_livraison">Adresse</label>
        <input class="form-control" type="text" id="adresse_livraison" name="adresse_livraison" required
               value="<?= htmlspecialchars($commande['adresse_livraison']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="ville">Ville</label>
        <input class="form-control" type="text" id="ville" name="ville" required
               value="<?= htmlspecialchars($commande['ville']) ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="distance_km">Distance (km)</label>
        <input class="form-control" type="number" id="distance_km" name="distance_km" step="0.1"
               value="<?= htmlspecialchars($commande['distance_km']) ?>">
    </div>
    <div class="col-12">
        <button class="btn btn-warning" type="submit">Enregistrer les modifications</button>
    </div>
</form>

<form method="post" onsubmit="return confirm('Confirmer l\'annulation ?');">
    <input type="hidden" name="action" value="annuler">
    <button class="btn btn-danger" type="submit">Annuler la commande</button>
</form>
<?php endif; ?>

<?php if (($statutActuel['id_statut'] ?? 0) === 7 && !$avisExistant): ?>
<h2 class="h4 mt-4">Laisser un avis</h2>
<form method="post" style="max-width: 500px;">
    <input type="hidden" name="action" value="avis">
    <div class="mb-3">
        <label class="form-label" for="note">Note (1 à 5)</label>
        <select class="form-select" id="note" name="note" required>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> étoile<?= $i > 1 ? 's' : '' ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label" for="commentaire">Commentaire</label>
        <textarea class="form-control" id="commentaire" name="commentaire" rows="4" required></textarea>
    </div>
    <button class="btn btn-primary" type="submit">Envoyer mon avis</button>
</form>
<?php elseif ($avisExistant): ?>
<h2 class="h4 mt-4">Mon avis</h2>
<p>Note : <?= $avisExistant['note'] ?>/5 — <?= htmlspecialchars($avisExistant['commentaire']) ?></p>
<p class="text-muted">Statut : <?= htmlspecialchars($avisExistant['statut']) ?></p>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>