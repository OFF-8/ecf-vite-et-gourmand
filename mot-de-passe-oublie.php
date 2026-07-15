<?php
require_once 'config/database.php';
require_once 'config/mail.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Message identique que l'email existe ou non (sécurité + RGPD)
    $message = 'Si un compte existe avec cet email, un lien de réinitialisation vous a été envoyé.';

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare(
            'SELECT id_utilisateur FROM utilisateur WHERE email = :email AND actif = TRUE'
        );
        $stmt->execute(['email' => $email]);
        $utilisateur = $stmt->fetch();

        if ($utilisateur) {
            // Supprimer les anciens tokens de cet utilisateur
            $pdo->prepare('DELETE FROM reset_password_token WHERE id_utilisateur = :id')
                ->execute(['id' => $utilisateur['id_utilisateur']]);

            $token = bin2hex(random_bytes(32));
            $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare(
                'INSERT INTO reset_password_token (token, date_expiration, id_utilisateur)
                 VALUES (:token, :expiration, :id)'
            );
            $stmt->execute([
                'token' => $token,
                'expiration' => $expiration,
                'id' => $utilisateur['id_utilisateur'],
            ]);

            $lien = getBaseUrl() . 'reinitialiser-mot-de-passe.php?token=' . $token;
            $sujet = 'Réinitialisation de votre mot de passe — Vite & Gourmand';
            $corps = "Bonjour,\n\nPour réinitialiser votre mot de passe, cliquez sur le lien suivant :\n$lien\n\nCe lien expire dans 1 heure.\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\nVite & Gourmand";

            envoyerMail($email, $sujet, $corps);
        }
    }
}

$titrePage = 'Mot de passe oublié — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Mot de passe oublié</h1>
<p>Saisissez votre adresse email. Un lien de réinitialisation vous sera envoyé.</p>

<?php if ($message): ?>
    <div class="alert alert-info" role="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post" style="max-width: 400px;">
    <div class="mb-3">
        <label class="form-label" for="email">Adresse email</label>
        <input class="form-control" type="email" id="email" name="email" required>
    </div>
    <button class="btn btn-primary" type="submit">Envoyer le lien</button>
</form>

<p class="mt-3"><a href="connexion.php">&larr; Retour à la connexion</a></p>

<?php require 'includes/footer.php'; ?>