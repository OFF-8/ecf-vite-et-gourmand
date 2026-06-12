<?php
require_once 'config/database.php';

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    // Validation côté serveur (obligatoire : le client peut contourner le HTML)
    if ($nom === '' || $prenom === '' || $gsm === '' || $adresse === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Adresse email invalide.';
    }
    // Règle du sujet : 10 caractères min, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/', $mdp)) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }

    // L'email doit être unique
    if (!$erreurs) {
        $stmt = $pdo->prepare('SELECT 1 FROM utilisateur WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $erreurs[] = 'Un compte existe déjà avec cet email.';
        }
    }

    if (!$erreurs) {
        $stmt = $pdo->prepare(
            'INSERT INTO utilisateur (nom, prenom, gsm, email, adresse, mot_de_passe, id_role)
             VALUES (:nom, :prenom, :gsm, :email, :adresse, :mdp, 1)'  // 1 = role utilisateur
        );
        $stmt->execute([
            'nom' => $nom, 'prenom' => $prenom, 'gsm' => $gsm,
            'email' => $email, 'adresse' => $adresse,
            'mdp' => password_hash($mdp, PASSWORD_DEFAULT),
        ]);
        // TODO : envoi du mail de bienvenue (sera branché avec PHPMailer)
        header('Location: connexion.php?inscription=ok');
        exit;
    }
}

$titrePage = 'Créer un compte — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Créer un compte</h1>

<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<form method="post" class="row g-3" style="max-width: 600px;">
    <div class="col-md-6">
        <label class="form-label" for="nom">Nom</label>
        <input class="form-control" type="text" id="nom" name="nom" required
               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="prenom">Prénom</label>
        <input class="form-control" type="text" id="prenom" name="prenom" required
               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="gsm">Numéro de GSM</label>
        <input class="form-control" type="tel" id="gsm" name="gsm" required
               value="<?= htmlspecialchars($_POST['gsm'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">Adresse email</label>
        <input class="form-control" type="email" id="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="col-12">
        <label class="form-label" for="adresse">Adresse postale</label>
        <input class="form-control" type="text" id="adresse" name="adresse" required
               value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
    </div>
    <div class="col-12">
        <label class="form-label" for="mot_de_passe">Mot de passe</label>
        <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe" required
               aria-describedby="aide-mdp">
        <p id="aide-mdp" class="form-text">Au moins 10 caractères, avec une majuscule, une minuscule, un chiffre et un caractère spécial.</p>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Créer mon compte</button>
    </div>
</form>

<?php require 'includes/footer.php'; ?>