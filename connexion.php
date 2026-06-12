<?php
require_once 'config/database.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT u.*, r.libelle_role FROM utilisateur u
         JOIN role r ON r.id_role = u.id_role
         WHERE u.email = :email AND u.actif = TRUE'
    );
    $stmt->execute(['email' => $email]);
    $utilisateur = $stmt->fetch();

    if ($utilisateur && password_verify($mdp, $utilisateur['mot_de_passe'])) {
        // On régénère l'id de session après connexion (protection contre la fixation de session)
        session_regenerate_id(true);
        $_SESSION['id_utilisateur'] = $utilisateur['id_utilisateur'];
        $_SESSION['prenom'] = $utilisateur['prenom'];
        $_SESSION['role'] = $utilisateur['libelle_role'];
        header('Location: index.php');
        exit;
    }
    // Message volontairement vague : ne pas révéler si l'email existe
    $erreur = 'Identifiants incorrects.';
}

$titrePage = 'Connexion — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Connexion</h1>

<?php if (isset($_GET['inscription'])): ?>
    <div class="alert alert-success" role="alert">Votre compte a été créé, vous pouvez vous connecter.</div>
<?php endif; ?>
<?php if ($erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>

<form method="post" style="max-width: 400px;">
    <div class="mb-3">
        <label class="form-label" for="email">Adresse email</label>
        <input class="form-control" type="email" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="mot_de_passe">Mot de passe</label>
        <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe" required>
    </div>
    <button class="btn btn-primary" type="submit">Se connecter</button>
</form>

<p class="mt-3">Pas encore de compte ? <a href="inscription.php">Créez-en un</a>.</p>

<?php require 'includes/footer.php'; ?>