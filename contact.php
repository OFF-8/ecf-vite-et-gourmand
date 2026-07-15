<?php
require_once 'config/database.php';
require_once 'config/mail.php';

$erreurs = [];
$succes = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($titre === '' || $description === '' || $email === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Adresse email invalide.';
    }

    if (!$erreurs) {
        $destinataire = 'contact@vite-et-gourmand.fr';
        $sujet = 'Contact site : ' . $titre;
        $message = "Email client : $email\n\n$description";

        if (envoyerMail($destinataire, $sujet, $message, $email)) {
            $succes = true;
        } else {
            $erreurs[] = 'Erreur lors de l\'envoi. Réessayez plus tard.';
        }
    }
}

$titrePage = 'Contact — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Nous contacter</h1>

<?php if ($succes): ?>
    <div class="alert alert-success" role="alert">Votre message a été envoyé. Nous vous répondrons rapidement.</div>
<?php endif; ?>
<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<form method="post" class="row g-3" style="max-width: 600px;">
    <div class="col-12">
        <label class="form-label" for="titre">Titre</label>
        <input class="form-control" type="text" id="titre" name="titre" required
               value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>
    <div class="col-12">
        <label class="form-label" for="email">Votre email</label>
        <input class="form-control" type="email" id="email" name="email" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Envoyer</button>
    </div>
</form>

<?php require 'includes/footer.php'; ?>