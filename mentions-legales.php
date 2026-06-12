<?php
require_once 'config/database.php';
$titrePage = 'Mentions légales — Vite & Gourmand';
require 'includes/header.php';
?>

<h1>Mentions légales</h1>

<h2 class="h5">Éditeur du site</h2>
<p>
    <strong>Vite &amp; Gourmand</strong><br>
    Société de traiteur — forme juridique : [SARL / EURL / etc.]<br>
    Capital social : [montant] €<br>
    Siège social : 12 rue des Traiteurs, 33000 Bordeaux<br>
    SIRET : [numéro SIRET]<br>
    RCS Bordeaux : [numéro RCS]<br>
    Numéro TVA intracommunautaire : [numéro TVA]<br>
    Directeurs de la publication : José Dupont et Julie Martin<br>
    Email : contact@vite-et-gourmand.fr<br>
    Téléphone : 05 XX XX XX XX
</p>

<h2 class="h5">Hébergement</h2>
<p>
    Hébergeur : [nom de l'hébergeur — ex. OVH, Azure, fly.io]<br>
    Adresse : [adresse de l'hébergeur]<br>
    Site web : [URL de l'hébergeur]
</p>

<h2 class="h5">Propriété intellectuelle</h2>
<p>
    L'ensemble du contenu de ce site (textes, images, logos, structure) est la propriété
    exclusive de Vite &amp; Gourmand, sauf mention contraire. Toute reproduction, même partielle,
    est interdite sans autorisation écrite préalable.
</p>

<h2 class="h5">Données personnelles (RGPD)</h2>
<p>
    Vite &amp; Gourmand collecte des données personnelles uniquement dans le cadre de la gestion
    des comptes clients, des commandes et du contact. Les données collectées peuvent inclure :
    nom, prénom, adresse postale, adresse email, numéro de GSM et informations liées aux commandes.
</p>
<p>
    <strong>Finalités :</strong> gestion des commandes, suivi des prestations, communication avec
    le client, modération des avis, statistiques internes.
</p>
<p>
    <strong>Base légale :</strong> exécution du contrat (commande) et consentement (création de compte).
</p>
<p>
    <strong>Durée de conservation :</strong> les données sont conservées pendant la durée
    nécessaire à la gestion de la relation commerciale, puis archivées conformément aux
    obligations légales comptables.
</p>
<p>
    <strong>Vos droits :</strong> conformément au Règlement Général sur la Protection des Données
    (RGPD), vous disposez d'un droit d'accès, de rectification, de suppression, de limitation
    du traitement, d'opposition et de portabilité de vos données.
</p>
<p>
    Pour exercer vos droits, contactez-nous à :
    <a href="mailto:contact@vite-et-gourmand.fr">contact@vite-et-gourmand.fr</a>
    ou par courrier à l'adresse du siège social.
</p>
<p>
    En cas de litige, vous pouvez introduire une réclamation auprès de la
    <a href="https://www.cnil.fr" rel="noopener noreferrer">CNIL</a>.
</p>

<h2 class="h5">Cookies</h2>
<p>
    Ce site utilise des cookies strictement nécessaires au fonctionnement de la session
    utilisateur (connexion, panier de commande). Aucun cookie publicitaire n'est utilisé
    sans votre consentement.
</p>

<h2 class="h5">Crédits</h2>
<p>Site réalisé dans le cadre du projet Vite &amp; Gourmand — FastDev.</p>

<p><a href="index.php">&larr; Retour à l'accueil</a></p>

<?php require 'includes/footer.php'; ?>