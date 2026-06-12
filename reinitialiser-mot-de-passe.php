<?php
require_once 'config/database.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$erreurs = [];
$succes = false;

// Vérifier que le token est valide et non expiré
$stmt = $pdo->prepare(
    'SELECT t.*, u.email FROM reset_password_token t
     JOIN utilisateur u ON u.id_utilisateur = t.id_utilisateur
     WHERE t.token = :token AND t.date_expiration > NOW() AND u.actif = TRUE'
);
$stmt->execute(['token' => $token]);
$tokenData = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenData) {
    $mdp = $_POST['mot_de_passe'] ?? '';
    $mdpConfirm = $_POST['mot_de_passe_confirm'] ?? '';

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/', $mdp)) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }
    if ($mdp !== $mdpConfirm) {
        $erreurs[] = 'Les mots de passe ne correspondent pas.';
    }

    if (!$erreurs) {
        $pdo->prepare(
            'UPDATE utilisateur SET mot_de_passe = :mdp WHERE id_utilisateur = :id'
        )->execute([
            'mdp' => password_hash($mdp, PASSWORD_DEFAULT),
            'id' => $tokenData['id_utilisateur'],
        ]);

        // Token à usage unique : supprimer après utilisation
        $pdo->prepare('DELETE FROM reset_password_token WHERE id_token = :id')
            ->execute(['id' => $tokenData['id_token']]);

        $succes = true;
    }
}

$titrePage = 'Réinitialiser le mot de passe — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Réinitialiser le mot de passe</h1>

<?php if ($succes): ?>
    <div class="alert alert-success" role="alert">
        Votre mot de passe a été modifié. <a href="connexion.php">Connectez-vous</a>.
    </div>
<?php elseif (!$tokenData): ?>
    <div class="alert alert-danger" role="alert">
        Ce lien est invalide ou a expiré. <a href="mot-de-passe-oublie.php">Demander un nouveau lien</a>.
    </div>
<?php else: ?>
    <p>Compte : <?= htmlspecialchars($tokenData['email']) ?></p>
    <?php foreach ($erreurs as $erreur): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
    <?php endforeach; ?>
    <form method="post" style="max-width: 400px;">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <div class="mb-3">
            <label class="form-label" for="mot_de_passe">Nouveau mot de passe</label>
            <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe" required
                   aria-describedby="aide-mdp">
            <p id="aide-mdp" class="form-text">10 caractères min., majuscule, minuscule, chiffre, caractère spécial.</p>
        </div>
        <div class="mb-3">
            <label class="form-label" for="mot_de_passe_confirm">Confirmer le mot de passe</label>
            <input class="form-control" type="password" id="mot_de_passe_confirm" name="mot_de_passe_confirm" required>
        </div>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
    </form>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>