<?php
require_once 'config/database.php';
$titrePage = 'Accueil — Vite & Gourmand';

$menus = $pdo->query(
    "SELECT m.id_menu, m.titre, m.description, m.prix_min, m.nb_personnes_min,
            t.nom_theme,
            (SELECT url_image FROM image WHERE id_menu = m.id_menu LIMIT 1) AS url_image,
            (SELECT alt_text FROM image WHERE id_menu = m.id_menu LIMIT 1) AS alt_text
     FROM menu m
     JOIN theme t ON t.id_theme = m.id_theme
     WHERE m.actif = TRUE
     ORDER BY m.id_menu
     LIMIT 3"
)->fetchAll();

$avis = $pdo->query(
    "SELECT a.note, a.commentaire, a.date_avis, u.prenom
     FROM avis a
     JOIN commande c ON c.id_commande = a.id_commande
     JOIN utilisateur u ON u.id_utilisateur = c.id_utilisateur
     WHERE a.statut = 'valide'
     ORDER BY a.date_avis DESC
     LIMIT 3"
)->fetchAll();

require 'includes/header.php';
$basePath = getBasePath();
require_once __DIR__ . '/includes/menu-image.php';
?>

<section class="hero-home" aria-labelledby="hero-titre">
    <div class="row align-items-center g-4">
        <div class="col-lg-7 hero-home__content">
            <span class="hero-badge">Traiteur à Bordeaux depuis 25 ans</span>
            <p class="hero-home__eyebrow">Julie &amp; José</p>
            <h1 id="hero-titre" class="hero-home__title">Vite &amp; <span>Gourmand</span></h1>
            <p class="hero-home__lead">
                Des menus raffinés et généreux pour vos réceptions :
                Noël, Pâques, anniversaires et événements professionnels.
            </p>
            <div class="hero-home__actions">
                <a class="btn btn-hero-primary btn-lg" href="menus.php">Découvrir nos menus</a>
                <a class="btn btn-hero-outline btn-lg" href="contact.php">Nous contacter</a>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="hero-home__panel">
                <p class="hero-home__panel-title">L'art de recevoir</p>
                <ul class="hero-home__stats list-unstyled mb-0">
                    <li>
                        <span class="hero-home__stat-value">25+</span>
                        <span class="hero-home__stat-label">ans d'expérience</span>
                    </li>
                    <li>
                        <span class="hero-home__stat-value">100%</span>
                        <span class="hero-home__stat-label">fait maison</span>
                    </li>
                    <li>
                        <span class="hero-home__stat-value">Bdx</span>
                        <span class="hero-home__stat-label">livraison locale</span>
                    </li>
                </ul>
                <p class="hero-home__panel-note mb-0">
                    Cuisine de saison, produits soigneusement sélectionnés,
                    présentation soignée pour chaque événement.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="mb-5" aria-labelledby="pourquoi-titre">
    <h2 id="pourquoi-titre" class="section-title h3">Pourquoi nous choisir ?</h2>
    <p class="section-subtitle">Une cuisine généreuse, locale et adaptée à chaque événement.</p>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card feature-card p-4">
                <div class="feature-icon" aria-hidden="true">🍽️</div>
                <h3 class="h5">Menus sur mesure</h3>
                <p class="mb-0 text-muted">Thèmes festifs, brunchs, options végétariennes — chaque menu est composé avec soin.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card p-4">
                <div class="feature-icon" aria-hidden="true">📍</div>
                <h3 class="h5">Livraison Bordeaux</h3>
                <p class="mb-0 text-muted">Livraison sur Bordeaux et environs. Tarif adapté selon la distance.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card feature-card p-4">
                <div class="feature-icon" aria-hidden="true">⭐</div>
                <h3 class="h5">25 ans d'expérience</h3>
                <p class="mb-0 text-muted">Julie &amp; José, votre duo traiteur de confiance pour tous vos moments importants.</p>
            </div>
        </div>
    </div>
</section>

<?php if ($menus): ?>
<section class="mb-5" aria-labelledby="menus-titre">
    <h2 id="menus-titre" class="section-title h3">Nos menus phares</h2>
    <p class="section-subtitle">Découvrez une sélection de nos créations les plus demandées.</p>
    <div class="row g-4">
        <?php foreach ($menus as $menu): ?>
        <div class="col-md-4">
            <article class="card menu-card-home h-100">
                <?php
                $imgSrc = menuImageUrl($menu['url_image'] ?? null, $basePath);
                $imgAlt = $menu['alt_text'] ?? $menu['titre'];
                ?>
                    <img class="card-img-top" src="<?= htmlspecialchars($imgSrc) ?>"
                         alt="<?= htmlspecialchars($imgAlt) ?>" loading="lazy">
                <div class="card-body d-flex flex-column">
                    <span class="badge text-bg-secondary mb-2 align-self-start"><?= htmlspecialchars($menu['nom_theme']) ?></span>
                    <h3 class="h5 card-title"><?= htmlspecialchars($menu['titre']) ?></h3>
                    <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars(mb_strimwidth($menu['description'], 0, 100, '…')) ?></p>
                    <p class="menu-price mb-2">À partir de <?= number_format($menu['prix_min'], 2, ',', ' ') ?> €</p>
                    <p class="small text-muted mb-3">Minimum <?= (int) $menu['nb_personnes_min'] ?> personnes</p>
                    <a class="btn btn-primary mt-auto" href="menu-detail.php?id=<?= (int) $menu['id_menu'] ?>">Voir le menu</a>
                </div>
            </article>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
        <a class="btn btn-outline-primary" href="menus.php">Voir tous les menus</a>
    </div>
</section>
<?php endif; ?>

<?php if ($avis): ?>
<section class="mb-5" aria-labelledby="avis-titre">
    <h2 id="avis-titre" class="section-title h3">Ce que disent nos clients</h2>
    <p class="section-subtitle">Avis vérifiés après commande.</p>
    <div class="row g-3">
        <?php foreach ($avis as $a): ?>
        <div class="col-md-4">
            <blockquote class="card avis-card h-100 p-3">
                <p class="avis-stars mb-2" aria-label="Note : <?= $a['note'] ?> sur 5">
                    <?= str_repeat('★', $a['note']) . str_repeat('☆', 5 - $a['note']) ?>
                </p>
                <p class="mb-2">« <?= htmlspecialchars($a['commentaire']) ?> »</p>
                <footer class="text-muted small mb-0">— <?= htmlspecialchars($a['prenom']) ?></footer>
            </blockquote>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="cta-band text-center" aria-labelledby="cta-titre">
    <h2 id="cta-titre">Un événement à organiser ?</h2>
    <p class="mb-4 opacity-75">Contactez-nous pour un devis personnalisé ou commandez directement en ligne.</p>
    <a class="btn btn-light btn-lg me-2" href="contact.php">Demander un devis</a>
    <a class="btn btn-outline-light btn-lg" href="inscription.php">Créer un compte</a>
</section>

<?php require 'includes/footer.php'; ?>
