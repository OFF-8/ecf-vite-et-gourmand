USE vite_et_gourmand;

-- ===== ROLES =====
INSERT INTO role (id_role, libelle_role) VALUES
(1, 'utilisateur'),
(2, 'employe'),
(3, 'administrateur');

-- ===== STATUTS DE COMMANDE =====
INSERT INTO statut_commande (id_statut, libelle_statut) VALUES
(1, 'en attente'),
(2, 'acceptee'),
(3, 'en preparation'),
(4, 'en cours de livraison'),
(5, 'livree'),
(6, 'en attente du retour de materiel'),
(7, 'terminee'),
(8, 'annulee');

-- ===== COMPTES =====
-- Compte admin de José : créé en base car le sujet interdit
-- la création d'un compte administrateur depuis l'application.
-- Remplace COLLE_TON_HASH_ICI par le hash généré avec hash.php
INSERT INTO utilisateur (id_utilisateur, nom, prenom, gsm, email, adresse, mot_de_passe, actif, id_role) VALUES
(1, 'Dupont', 'Jose', '0612345678', 'jose@vite-et-gourmand.fr', '12 rue des Traiteurs, 33000 Bordeaux', '$2y$10$HICErbcZ3oWysMuw5crJZOgWyfMFXDqWQreDNfddHov1Py3udtjdO', TRUE, 3),
(2, 'Martin', 'Julie', '0623456789', 'julie@vite-et-gourmand.fr', '12 rue des Traiteurs, 33000 Bordeaux', '$2y$10$xT5a/wS6hCqCin91UiEDqOIc4biZC2ItGcGlqDmgvUy04gdGVAIzm', TRUE, 2),
(3, 'Test', 'Client', '0634567890', 'client@test.fr', '5 avenue de Test, 33000 Bordeaux', '$2y$10$F3NbK2lPWPvSotSDntmiGuLc3yj/nKUbzdQQGzHsFS9Dm.OXZIKWW', TRUE, 1);

-- ===== HORAIRES =====
INSERT INTO horaire (jour, heure_ouverture, heure_fermeture) VALUES
('lundi', '09:00', '18:00'),
('mardi', '09:00', '18:00'),
('mercredi', '09:00', '18:00'),
('jeudi', '09:00', '18:00'),
('vendredi', '09:00', '19:00'),
('samedi', '10:00', '19:00'),
('dimanche', '10:00', '13:00');

-- ===== THEMES =====
INSERT INTO theme (id_theme, nom_theme) VALUES
(1, 'Noel'),
(2, 'Paques'),
(3, 'Classique'),
(4, 'Evenement');

-- ===== REGIMES =====
INSERT INTO regime (id_regime, nom_regime) VALUES
(1, 'Classique'),
(2, 'Vegetarien'),
(3, 'Vegan');

-- ===== ALLERGENES =====
INSERT INTO allergene (id_allergene, nom_allergene) VALUES
(1, 'Gluten'),
(2, 'Lactose'),
(3, 'Arachide'),
(4, 'Fruits a coque'),
(5, 'Crustaces'),
(6, 'Oeuf'),
(7, 'Poisson'),
(8, 'Soja');

-- ===== PLATS =====
INSERT INTO plat (id_plat, nom_plat, description, type_plat) VALUES
(1, 'Foie gras maison', 'Foie gras mi-cuit et son chutney de figues', 'entree'),
(2, 'Veloute de potimarron', 'Veloute onctueux aux eclats de chataignes', 'entree'),
(3, 'Oeufs mimosa printaniers', 'Oeufs mimosa revisites aux herbes fraiches', 'entree'),
(4, 'Chapon farci', 'Chapon farci aux marrons, jus corse', 'plat'),
(5, 'Agneau de Pauillac', 'Epaule d agneau confite, legumes de saison', 'plat'),
(6, 'Risotto aux asperges', 'Risotto cremeux aux asperges vertes', 'plat'),
(7, 'Buche chocolat-praline', 'Buche patissiere chocolat et coeur praline', 'dessert'),
(8, 'Nid de Paques', 'Entremets chocolat-noisette facon nid', 'dessert'),
(9, 'Tarte aux fruits de saison', 'Pate sablee et fruits frais du marche', 'dessert');

-- ===== MENUS =====
INSERT INTO menu (id_menu, titre, description, nb_personnes_min, prix_min, stock_disponible, conditions, actif, id_theme, id_regime) VALUES
(1, 'Menu Reveillon de Noel', 'Un menu festif et genereux pour celebrer Noel en famille.', 8, 320.00, 5,
 'A commander au minimum 2 semaines avant la prestation. Conservation au refrigerateur entre 0 et 4 degres, a consommer sous 48h.', TRUE, 1, 1),
(2, 'Menu Brunch de Paques', 'Un menu printanier et convivial pour le dimanche de Paques.', 6, 180.00, 8,
 'A commander au minimum 1 semaine avant la prestation. Conservation au refrigerateur, a consommer le jour meme.', TRUE, 2, 1),
(3, 'Menu Vegetarien Gourmand', 'Une selection raffinee 100% vegetarienne pour tous vos evenements.', 4, 120.00, 10,
 'A commander au minimum 5 jours avant la prestation. Conservation au refrigerateur entre 0 et 4 degres.', TRUE, 3, 2);

-- ===== IMAGES (galerie de chaque menu) =====
INSERT INTO image (url_image, alt_text, id_menu) VALUES
('asset/img/menu-noel-1.jpg', 'Table de reveillon avec chapon farci et foie gras', 1),
('asset/img/menu-noel-2.jpg', 'Buche chocolat praline du menu de Noel', 1),
('asset/img/menu-paques-1.jpg', 'Brunch de Paques avec agneau et entremets chocolat', 2),
('asset/img/menu-vege-1.jpg', 'Risotto aux asperges du menu vegetarien', 3);

-- ===== COMPOSITION DES MENUS (relation N,N) =====
-- Le foie gras (1) et la tarte (9) sont dans PLUSIEURS menus : demonstration de la relation N,N
INSERT INTO menu_plat (id_menu, id_plat) VALUES
(1, 1), (1, 4), (1, 7),        -- Noel : foie gras, chapon, buche
(2, 1), (2, 5), (2, 8),        -- Paques : foie gras (partage !), agneau, nid
(3, 2), (3, 6), (3, 9),        -- Vegetarien : veloute, risotto, tarte
(2, 9);                        -- la tarte aussi dans le menu de Paques

-- ===== ALLERGENES DES PLATS (relation N,N) =====
INSERT INTO plat_allergene (id_plat, id_allergene) VALUES
(2, 2),            -- veloute : lactose
(3, 6),            -- oeufs mimosa : oeuf
(4, 1),            -- chapon farci : gluten
(6, 2),            -- risotto : lactose
(7, 1), (7, 2), (7, 4),   -- buche : gluten, lactose, fruits a coque
(8, 2), (8, 4), (8, 8),   -- nid de Paques : lactose, fruits a coque, soja
(9, 1), (9, 6);           -- tarte : gluten, oeuf