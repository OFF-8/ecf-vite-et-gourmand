<?php
require_once 'config/database.php';

if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php?redirect=' . urlencode('profil.php'));
    exit;
}

$erreurs = [];
$succes = false;

$stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE id_utilisateur = :id');
$stmt->execute(['id' => $_SESSION['id_utilisateur']]);
$utilisateur = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    if ($nom === '' || $prenom === '' || $gsm === '' || $adresse === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }

    if ($mdp !== '' && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/', $mdp)) {
        $erreurs[] = 'Le nouveau mot de passe ne respecte pas les règles de sécurité.';
    }

    if (!$erreurs) {
        if ($mdp !== '') {
            $stmt = $pdo->prepare(
                'UPDATE utilisateur SET nom = :nom, prenom = :prenom, gsm = :gsm,
                 adresse = :adresse, mot_de_passe = :mdp WHERE id_utilisateur = :id'
            );
            $params = [
                'nom' => $nom, 'prenom' => $prenom, 'gsm' => $gsm,
                'adresse' => $adresse, 'mdp' => password_hash($mdp, PASSWORD_DEFAULT),
                'id' => $_SESSION['id_utilisateur'],
            ];
        } else {
            $stmt = $pdo->prepare(
                'UPDATE utilisateur SET nom = :nom, prenom = :prenom, gsm = :gsm,
                 adresse = :adresse WHERE id_utilisateur = :id'
            );
            $params = [
                'nom' => $nom, 'prenom' => $prenom, 'gsm' => $gsm,
                'adresse' => $adresse, 'id' => $_SESSION['id_utilisateur'],
            ];
        }
        $stmt->execute($params);
        $_SESSION['prenom'] = $prenom;
        $succes = true;
        $utilisateur = array_merge($utilisateur, $params);
    }
}

$titrePage = 'Mon profil — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Modifier mes informations</h1>
<p><a href="mon-espace.php">&larr; Retour à mon espace</a></p>

<?php if ($succes): ?>
    <div class="alert alert-success" role="alert">Vos informations ont été mises à jour.</div>
<?php endif; ?>
<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<form method="post" class="row g-3" style="max-width: 600px;">
    <div class="col-md-6">
        <label class="form-label" for="nom">Nom</label>
        <input class="form-control" type="text" id="nom" name="nom" required
               value="<?= htmlspecialchars($utilisateur['nom']) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="prenom">Prénom</label>
        <input class="form-control" type="text" id="prenom" name="prenom" required
               value="<?= htmlspecialchars($utilisateur['prenom']) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" value="<?= htmlspecialchars($utilisateur['email']) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="gsm">GSM</label>
        <input class="form-control" type="tel" id="gsm" name="gsm" required
               value="<?= htmlspecialchars($utilisateur['gsm']) ?>">
    </div>
    <div class="col-12">
        <label class="form-label" for="adresse">Adresse postale</label>
        <input class="form-control" type="text" id="adresse" name="adresse" required
               value="<?= htmlspecialchars($utilisateur['adresse']) ?>">
    </div>
    <div class="col-12">
        <label class="form-label" for="mot_de_passe">Nouveau mot de passe (optionnel)</label>
        <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe">
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Enregistrer</button>
    </div>
</form>

<?php require 'includes/footer.php'; ?>