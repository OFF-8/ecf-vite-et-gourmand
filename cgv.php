<?php
require_once 'config/database.php';
$titrePage = 'Conditions générales de vente — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Conditions générales de vente</h1>
<p class="text-muted">En vigueur au <?= date('d/m/Y') ?></p>

<h2 class="h5">Article 1 — Objet</h2>
<p>
    Les présentes Conditions Générales de Vente (CGV) régissent les relations contractuelles
    entre la société Vite &amp; Gourmand, traiteur situé à Bordeaux, et toute personne physique
    ou morale passant commande via le site internet. Toute commande implique l'acceptation
    sans réserve des présentes CGV.
</p>

<h2 class="h5">Article 2 — Commandes</h2>
<p>
    Les commandes sont passées exclusivement par les clients disposant d'un compte utilisateur.
    Chaque menu comporte des conditions spécifiques (délai de commande minimum, nombre de
    personnes minimum, stock disponible) clairement affichées sur la fiche du menu.
    Le client s'engage à respecter ces conditions avant validation de sa commande.
</p>
<p>
    Vite &amp; Gourmand se réserve le droit de refuser ou d'annuler toute commande en cas
    de stock insuffisant, d'informations erronées ou de non-respect des délais.
</p>

<h2 class="h5">Article 3 — Tarifs et paiement</h2>
<p>
    Les prix sont indiqués en euros TTC. Le prix d'un menu est calculé en fonction du nombre
    de personnes commandées, avec un minimum défini par menu. Une remise de 10 % est appliquée
    automatiquement pour toute commande comportant au moins 5 personnes de plus que le minimum
    requis par le menu.
</p>
<p>
    <strong>Frais de livraison :</strong> la livraison est gratuite dans la ville de Bordeaux.
    Pour toute livraison en dehors de Bordeaux, des frais de 5,00 € majorés de 0,59 € par
    kilomètre parcouru depuis Bordeaux sont appliqués.
</p>
<p>
    Le détail du prix (menu + livraison + remise éventuelle) est présenté au client avant
    validation définitive de la commande.
</p>

<h2 class="h5">Article 4 — Livraison et prestation</h2>
<p>
    La date, l'heure et l'adresse de livraison sont précisées par le client lors de la commande.
    Vite &amp; Gourmand s'engage à livrer dans les délais convenus, sous réserve du respect
    par le client des conditions de commande du menu choisi.
</p>
<p>
    En cas de retard imputable au client (accès impossible, absence, informations erronées),
    Vite &amp; Gourmand ne saurait être tenu responsable.
</p>

<h2 class="h5">Article 5 — Matériel prêté</h2>
<p>
    Pour certaines prestations, Vite &amp; Gourmand peut prêter du matériel au client
    (chauffe-plats, vaisselle, nappes, etc.). Lorsque du matériel est prêté, le client
    est informé lors de la commande et au moment de la livraison.
</p>
<p>
    <strong>Le client s'engage à restituer l'intégralité du matériel prêté dans un délai
    de 10 jours ouvrés</strong> suivant la prestation. À défaut de restitution dans ce délai,
    le client sera facturé d'une indemnité forfaitaire de <strong>600,00 €</strong>,
    correspondant à la valeur du matériel prêté.
</p>
<p>
    Pour restituer le matériel, le client doit prendre contact avec Vite &amp; Gourmand
    par téléphone ou par email à l'adresse contact@vite-et-gourmand.fr.
</p>

<h2 class="h5">Article 6 — Annulation et modification</h2>
<p>
    Le client peut annuler ou modifier sa commande tant que celle-ci n'a pas été acceptée
    par Vite &amp; Gourmand. Une fois la commande acceptée, toute annulation ou modification
    est soumise à l'accord préalable de Vite &amp; Gourmand.
</p>
<p>
    Vite &amp; Gourmand se réserve le droit d'annuler une commande après contact préalable
    avec le client (par téléphone ou email), en indiquant le motif de l'annulation.
</p>

<h2 class="h5">Article 7 — Allergènes et régimes alimentaires</h2>
<p>
    Les allergènes présents dans chaque plat sont indiqués sur la fiche détaillée du menu.
    Le client est responsable de vérifier ces informations avant de passer commande et doit
    informer Vite &amp; Gourmand de toute allergie ou intolérance particulière lors de la commande.
</p>

<h2 class="h5">Article 8 — Avis clients</h2>
<p>
    À l'issue d'une prestation terminée, le client peut laisser un avis (note de 1 à 5
    et commentaire) depuis son espace personnel. Les avis sont soumis à modération avant
    publication sur le site.
</p>

<h2 class="h5">Article 9 — Données personnelles</h2>
<p>
    Les données collectées lors de la commande et de la création de compte sont traitées
    conformément à notre politique de confidentialité et au RGPD. Pour plus d'informations,
    consultez nos <a href="mentions-legales.php">mentions légales</a>.
</p>

<h2 class="h5">Article 10 — Droit applicable et litiges</h2>
<p>
    Les présentes CGV sont soumises au droit français. En cas de litige, et à défaut de
    résolution amiable, compétence exclusive est attribuée aux tribunaux de Bordeaux.
</p>

<p><a href="index.php">&larr; Retour à l'accueil</a></p>

<?php require 'includes/footer.php'; ?>