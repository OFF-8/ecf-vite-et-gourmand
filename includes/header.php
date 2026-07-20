<?php
$basePath = getBasePath();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titrePage ?? 'Vite & Gourmand' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>asset/index.css?v=5">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Navigation principale">
        <div class="container">
            <a class="navbar-brand" href="<?= $basePath ?>index.php">Vite &amp; Gourmand</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#menuPrincipal" aria-controls="menuPrincipal"
                    aria-expanded="false" aria-label="Ouvrir le menu de navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>menus.php">Nos menus</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>contact.php">Contact</a></li>
                    <?php if (($_SESSION['role'] ?? '') === 'administrateur'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>admin/index.php">Espace admin</a></li>
                    <?php endif; ?>
                    <?php if (in_array($_SESSION['role'] ?? '', ['employe', 'administrateur'], true)): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>employe/index.php">Espace employé</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['id_utilisateur'])): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>mon-espace.php">Bonjour <?= htmlspecialchars($_SESSION['prenom']) ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>deconnexion.php">Déconnexion</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $basePath ?>connexion.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="container py-4">
