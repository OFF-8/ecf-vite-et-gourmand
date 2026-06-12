<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth-role.php';
requireRole(['administrateur']);

$erreurs = [];
$succes = '';

// Création d'un employé (PAS d'administrateur — règle du sujet)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'creer') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';

    if ($nom === '' || $prenom === '' || $email === '' || $gsm === '' || $adresse === '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Email invalide.';
    }
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/', $mdp)) {
        $erreurs[] = 'Mot de passe non conforme.';
    }

    if (!$erreurs) {
        $stmt = $pdo->prepare('SELECT 1 FROM utilisateur WHERE email = :email');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $erreurs[] = 'Cet email est déjà utilisé.';
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO utilisateur (nom, prenom, gsm, email, adresse, mot_de_passe, id_role)
                 VALUES (:nom, :prenom, :gsm, :email, :adresse, :mdp, 2)'
            );
            $stmt->execute([
                'nom' => $nom, 'prenom' => $prenom, 'gsm' => $gsm,
                'email' => $email, 'adresse' => $adresse,
                'mdp' => password_hash($mdp, PASSWORD_DEFAULT),
            ]);
            $succes = 'Compte employé créé. Un email de notification sera envoyé (sans le mot de passe).';
            // TODO : envoi mail de notification
        }
    }
}

// Désactivation d'un employé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'desactiver') {
    $id = (int) ($_POST['id_utilisateur'] ?? 0);
    $pdo->prepare(
        'UPDATE utilisateur SET actif = FALSE WHERE id_utilisateur = :id AND id_role = 2'
    )->execute(['id' => $id]);
    $succes = 'Compte employé désactivé.';
}

$employes = $pdo->query(
    "SELECT id_utilisateur, nom, prenom, email, gsm, actif
     FROM utilisateur WHERE id_role = 2 ORDER BY nom"
)->fetchAll();

$titrePage = 'Gestion des employés — Admin';
require __DIR__ . '/../includes/header.php';
?>

<h1>Gestion des employés</h1>
<p><a href="index.php">&larr; Retour</a></p>

<?php if ($succes): ?>
    <div class="alert alert-success" role="alert"><?= htmlspecialchars($succes) ?></div>
<?php endif; ?>
<?php foreach ($erreurs as $erreur): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>

<h2 class="h5">Créer un compte employé</h2>
<form method="post" class="row g-3 mb-4" style="max-width: 700px;">
    <input type="hidden" name="action" value="creer">
    <div class="col-md-6">
        <label class="form-label" for="nom">Nom</label>
        <input class="form-control" type="text" id="nom" name="nom" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="prenom">Prénom</label>
        <input class="form-control" type="text" id="prenom" name="prenom" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">Email (identifiant)</label>
        <input class="form-control" type="email" id="email" name="email" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="gsm">GSM</label>
        <input class="form-control" type="tel" id="gsm" name="gsm" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="adresse">Adresse</label>
        <input class="form-control" type="text" id="adresse" name="adresse" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="mot_de_passe">Mot de passe</label>
        <input class="form-control" type="password" id="mot_de_passe" name="mot_de_passe" required>
    </div>
    <div class="col-12">
        <button class="btn btn-primary" type="submit">Créer le compte employé</button>
    </div>
</form>

<h2 class="h5">Liste des employés</h2>
<table class="table table-striped">
    <thead>
        <tr><th>Nom</th><th>Email</th><th>GSM</th><th>Statut</th><th></th></tr>
    </thead>
    <tbody>
        <?php foreach ($employes as $emp): ?>
        <tr>
            <td><?= htmlspecialchars($emp['prenom'] . ' ' . $emp['nom']) ?></td>
            <td><?= htmlspecialchars($emp['email']) ?></td>
            <td><?= htmlspecialchars($emp['gsm']) ?></td>
            <td><?= $emp['actif'] ? 'Actif' : 'Désactivé' ?></td>
            <td>
                <?php if ($emp['actif']): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('Désactiver ce compte ?');">
                    <input type="hidden" name="action" value="desactiver">
                    <input type="hidden" name="id_utilisateur" value="<?= $emp['id_utilisateur'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Désactiver</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../includes/footer.php'; ?>