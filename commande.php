<?php
require_once 'config/database.php';

// Visiteur non connecté → connexion ou inscription
if (!isset($_SESSION['id_utilisateur'])) {
    $menuParam = isset($_GET['menu']) ? '?menu=' . (int) $_GET['menu'] : '';
    header('Location: connexion.php?redirect=' . urlencode('commande.php' . $menuParam));
    exit;
}

$idMenu = (int) ($_GET['menu'] ?? $_POST['id_menu'] ?? 0);
$erreurs = [];
$succes = false;

// Infos utilisateur connecté
$stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = :id');
$stmt->execute(['id' => $_SESSION['id_utilisateur']]);
$utilisateur = $stmt->fetch();

// Liste des menus actifs
$menus = $pdo->query(
    'SELECT id_menu, titre, nb_personnes_min, prix_min, stock_disponible
     FROM menu WHERE actif = TRUE ORDER BY titre'
)->fetchAll();

$menuSelectionne = null;
if ($idMenu > 0) {
    $stmt = $pdo->prepare(
        'SELECT id_menu, titre, nb_personnes_min, prix_min, stock_disponible
         FROM menu WHERE id_menu = :id AND actif = TRUE'
    );
    $stmt->execute(['id' => $idMenu]);
    $menuSelectionne = $stmt->fetch();
}

function calculerPrix(float $prixMin, int $nbMin, int $nbPersonnes): array
{
    $prixBase = $prixMin * ($nbPersonnes / $nbMin);
    $remise = 0;
    if ($nbPersonnes >= $nbMin + 5) {
        $remise = round($prixBase * 0.10, 2);
    }
    return [
        'prix_menu' => round($prixBase - $remise, 2),
        'remise' => $remise,
    ];
}

function calculerLivraison(string $ville, float $distanceKm): float
{
    if (mb_strtolower(trim($ville)) === 'bordeaux') {
        return 0;
    }
    return round(5 + ($distanceKm * 0.59), 2);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idMenuPost = (int) ($_POST['id_menu'] ?? 0);
    $nbPersonnes = (int) ($_POST['nb_personnes'] ?? 0);
    $datePrestation = $_POST['date_prestation'] ?? '';
    $heureLivraison = $_POST['heure_livraison'] ?? '';
    $adresseLivraison = trim($_POST['adresse_livraison'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $distanceKm = (float) ($_POST['distance_km'] ?? 0);
    $materielPrete = isset($_POST['materiel_prete']);

    $stmt = $pdo->prepare(
        'SELECT * FROM menu WHERE id_menu = :id AND actif = TRUE'
    );
    $stmt->execute(['id' => $idMenuPost]);
    $menu = $stmt->fetch();

    if (!$menu) {
        $erreurs[] = 'Menu invalide.';
    }
    if ($nbPersonnes < ($menu['nb_personnes_min'] ?? 1)) {
        $erreurs[] = 'Le nombre de personnes doit être au minimum ' . ($menu['nb_personnes_min'] ?? '?') . '.';
    }
    if ($menu && $menu['stock_disponible'] <= 0) {
        $erreurs[] = 'Ce menu n\'est plus disponible.';
    }
    if ($datePrestation === '' || $heureLivraison === '' || $adresseLivraison === '' || $ville === '') {
        $erreurs[] = 'Tous les champs de prestation sont obligatoires.';
    }
    if (mb_strtolower($ville) !== 'bordeaux' && $distanceKm <= 0) {
        $erreurs[] = 'Indiquez la distance en kilomètres pour une livraison hors Bordeaux.';
    }

    if (!$erreurs && $menu) {
        $prix = calculerPrix((float) $menu['prix_min'], (int) $menu['nb_personnes_min'], $nbPersonnes);
        $prixLivraison = calculerLivraison($ville, $distanceKm);
        $prixTotal = round($prix['prix_menu'] + $prixLivraison, 2);

        if (mb_strtolower($ville) === 'bordeaux') {
            $distanceKm = 0;
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO commande
                (date_prestation, heure_livraison, adresse_livraison, ville, distance_km,
                 nb_personnes, prix_menu, prix_livraison, remise, prix_total, materiel_prete,
                 id_utilisateur, id_menu)
                VALUES
                (:date_prestation, :heure_livraison, :adresse_livraison, :ville, :distance_km,
                 :nb_personnes, :prix_menu, :prix_livraison, :remise, :prix_total, :materiel_prete,
                 :id_utilisateur, :id_menu)'
            );
            $stmt->execute([
                'date_prestation' => $datePrestation,
                'heure_livraison' => $heureLivraison,
                'adresse_livraison' => $adresseLivraison,
                'ville' => $ville,
                'distance_km' => $distanceKm,
                'nb_personnes' => $nbPersonnes,
                'prix_menu' => $prix['prix_menu'],
                'prix_livraison' => $prixLivraison,
                'remise' => $prix['remise'],
                'prix_total' => $prixTotal,
                'materiel_prete' => $materielPrete ? 1 : 0,
                'id_utilisateur' => $_SESSION['id_utilisateur'],
                'id_menu' => $idMenuPost,
            ]);

            $idCommande = (int) $pdo->lastInsertId();

            // Premier statut : en attente
            $stmt = $pdo->prepare(
                'INSERT INTO historique_statut (id_commande, id_statut)
                 VALUES (:id_commande, 1)'
            );
            $stmt->execute(['id_commande' => $idCommande]);

            // Diminuer le stock
            $stmt = $pdo->prepare(
                'UPDATE menu SET stock_disponible = stock_disponible - 1
                 WHERE id_menu = :id AND stock_disponible > 0'
            );
            $stmt->execute(['id' => $idMenuPost]);

            $pdo->commit();
            $succes = true;
            // TODO : mail de confirmation
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreurs[] = 'Erreur lors de la commande. Veuillez réessayer.';
        }
    }
}

$titrePage = 'Commander — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Commander un menu</h1>

<?php if ($succes): ?>
    <div class="alert alert-success" role="alert">
        Votre commande a bien été enregistrée. Un email de confirmation vous sera envoyé.
    </div>
    <a class="btn btn-primary" href="menus.php">Retour aux menus</a>
<?php else: ?>

<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<form method="post" id="form-commande">
    <h2 class="h4">Informations client</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">Nom</label>
            <input class="form-control" type="text" value="<?= htmlspecialchars($utilisateur['nom']) ?>" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Prénom</label>
            <input class="form-control" type="text" value="<?= htmlspecialchars($utilisateur['prenom']) ?>" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input class="form-control" type="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">GSM</label>
            <input class="form-control" type="tel" value="<?= htmlspecialchars($utilisateur['gsm']) ?>" readonly>
        </div>
    </div>

    <h2 class="h4">Prestation</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label" for="adresse_livraison">Adresse de livraison</label>
            <input class="form-control" type="text" id="adresse_livraison" name="adresse_livraison" required
                   value="<?= htmlspecialchars($_POST['adresse_livraison'] ?? $utilisateur['adresse']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="ville">Ville</label>
            <input class="form-control" type="text" id="ville" name="ville" required
                   value="<?= htmlspecialchars($_POST['ville'] ?? 'Bordeaux') ?>">
        </div>
        <div class="col-md-3" id="bloc-distance">
            <label class="form-label" for="distance_km">Distance (km)</label>
            <input class="form-control" type="number" id="distance_km" name="distance_km" min="0" step="0.1"
                   value="<?= htmlspecialchars($_POST['distance_km'] ?? '0') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="date_prestation">Date de prestation</label>
            <input class="form-control" type="date" id="date_prestation" name="date_prestation" required
                   value="<?= htmlspecialchars($_POST['date_prestation'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="heure_livraison">Heure de livraison</label>
            <input class="form-control" type="time" id="heure_livraison" name="heure_livraison" required
                   value="<?= htmlspecialchars($_POST['heure_livraison'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" id="materiel_prete" name="materiel_prete">
                <label class="form-check-label" for="materiel_prete">Prêt de matériel</label>
            </div>
        </div>
    </div>

    <h2 class="h4">Menu choisi</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <label class="form-label" for="id_menu">Menu</label>
            <select class="form-select" id="id_menu" name="id_menu" required>
                <option value="">Sélectionnez un menu</option>
                <?php foreach ($menus as $m): ?>
                    <option value="<?= $m['id_menu'] ?>"
                        data-prix-min="<?= $m['prix_min'] ?>"
                        data-nb-min="<?= $m['nb_personnes_min'] ?>"
                        <?= ($menuSelectionne && $menuSelectionne['id_menu'] == $m['id_menu']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['titre']) ?> (min. <?= $m['nb_personnes_min'] ?> pers. — <?= $m['prix_min'] ?> €)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="nb_personnes">Nombre de personnes</label>
            <input class="form-control" type="number" id="nb_personnes" name="nb_personnes" min="1" required
                   value="<?= htmlspecialchars($_POST['nb_personnes'] ?? ($menuSelectionne['nb_personnes_min'] ?? '')) ?>">
            <p class="form-text" id="aide-nb-personnes"></p>
        </div>
    </div>

    <div class="card mb-4" aria-live="polite">
        <div class="card-body">
            <h2 class="h5">Récapitulatif du prix</h2>
            <p class="mb-1">Prix du menu : <strong id="recap-prix-menu">0,00 €</strong></p>
            <p class="mb-1">Remise (-10 %) : <strong id="recap-remise">0,00 €</strong></p>
            <p class="mb-1">Frais de livraison : <strong id="recap-livraison">0,00 €</strong></p>
            <p class="mb-0 fs-5">Total : <strong id="recap-total">0,00 €</strong></p>
        </div>
    </div>

    <button class="btn btn-primary btn-lg" type="submit">Valider la commande</button>
</form>

<script src="asset/commande.js"></script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>