CREATE DATABASE IF NOT EXISTS vite_et_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vite_et_gourmand;

CREATE TABLE role (
    id_role INT AUTO_INCREMENT PRIMARY KEY,
    libelle_role VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    gsm VARCHAR(20) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    adresse VARCHAR(255) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,        -- hash (password_hash PHP)
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    id_role INT NOT NULL,
    CONSTRAINT fk_utilisateur_role FOREIGN KEY (id_role) REFERENCES role(id_role)
) ENGINE=InnoDB;

CREATE TABLE reset_password_token (
    id_token INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(255) NOT NULL UNIQUE,
    date_expiration DATETIME NOT NULL,
    id_utilisateur INT NOT NULL,
    CONSTRAINT fk_token_utilisateur FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE theme (
    id_theme INT AUTO_INCREMENT PRIMARY KEY,
    nom_theme VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE regime (
    id_regime INT AUTO_INCREMENT PRIMARY KEY,
    nom_regime VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE menu (
    id_menu INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    nb_personnes_min INT NOT NULL,
    prix_min DECIMAL(10,2) NOT NULL,           -- prix pour le nb de personnes minimum
    stock_disponible INT NOT NULL DEFAULT 0,   -- nb de commandes restantes
    conditions TEXT NOT NULL,                  -- délai de commande, stockage...
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    id_theme INT NOT NULL,
    id_regime INT NOT NULL,
    CONSTRAINT fk_menu_theme FOREIGN KEY (id_theme) REFERENCES theme(id_theme),
    CONSTRAINT fk_menu_regime FOREIGN KEY (id_regime) REFERENCES regime(id_regime)
) ENGINE=InnoDB;

CREATE TABLE image (
    id_image INT AUTO_INCREMENT PRIMARY KEY,
    url_image VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) NOT NULL,            -- RGAA
    id_menu INT NOT NULL,
    CONSTRAINT fk_image_menu FOREIGN KEY (id_menu)
        REFERENCES menu(id_menu) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plat (
    id_plat INT AUTO_INCREMENT PRIMARY KEY,
    nom_plat VARCHAR(100) NOT NULL,
    description TEXT,
    type_plat ENUM('entree','plat','dessert') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE allergene (
    id_allergene INT AUTO_INCREMENT PRIMARY KEY,
    nom_allergene VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Association N,N : un plat peut appartenir à plusieurs menus
CREATE TABLE menu_plat (
    id_menu INT NOT NULL,
    id_plat INT NOT NULL,
    PRIMARY KEY (id_menu, id_plat),
    CONSTRAINT fk_mp_menu FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE CASCADE,
    CONSTRAINT fk_mp_plat FOREIGN KEY (id_plat) REFERENCES plat(id_plat) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Association N,N : allergènes d'un plat
CREATE TABLE plat_allergene (
    id_plat INT NOT NULL,
    id_allergene INT NOT NULL,
    PRIMARY KEY (id_plat, id_allergene),
    CONSTRAINT fk_pa_plat FOREIGN KEY (id_plat) REFERENCES plat(id_plat) ON DELETE CASCADE,
    CONSTRAINT fk_pa_allergene FOREIGN KEY (id_allergene) REFERENCES allergene(id_allergene) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE statut_commande (
    id_statut INT AUTO_INCREMENT PRIMARY KEY,
    libelle_statut VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE commande (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    date_commande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_prestation DATE NOT NULL,
    heure_livraison TIME NOT NULL,
    adresse_livraison VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    distance_km DECIMAL(6,2) NOT NULL DEFAULT 0,   -- 0 si Bordeaux
    nb_personnes INT NOT NULL,
    prix_menu DECIMAL(10,2) NOT NULL,              -- prix figé à la commande
    prix_livraison DECIMAL(10,2) NOT NULL DEFAULT 0,
    remise DECIMAL(10,2) NOT NULL DEFAULT 0,       -- -10% si nb >= min + 5
    prix_total DECIMAL(10,2) NOT NULL,
    materiel_prete BOOLEAN NOT NULL DEFAULT FALSE,
    motif_annulation TEXT NULL,
    mode_contact ENUM('gsm','mail') NULL,          -- renseigné si annulation employé
    id_utilisateur INT NOT NULL,
    id_menu INT NOT NULL,
    CONSTRAINT fk_commande_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur),
    CONSTRAINT fk_commande_menu FOREIGN KEY (id_menu) REFERENCES menu(id_menu)
) ENGINE=InnoDB;

-- Historique horodaté des statuts (suivi de commande)
CREATE TABLE historique_statut (
    id_historique INT AUTO_INCREMENT PRIMARY KEY,
    date_heure_modif DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_commande INT NOT NULL,
    id_statut INT NOT NULL,
    id_utilisateur INT NULL,                       -- employé auteur du changement
    CONSTRAINT fk_hs_commande FOREIGN KEY (id_commande) REFERENCES commande(id_commande) ON DELETE CASCADE,
    CONSTRAINT fk_hs_statut FOREIGN KEY (id_statut) REFERENCES statut_commande(id_statut),
    CONSTRAINT fk_hs_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
) ENGINE=InnoDB;

CREATE TABLE avis (
    id_avis INT AUTO_INCREMENT PRIMARY KEY,
    note INT NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT NOT NULL,
    date_avis DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
    id_commande INT NOT NULL UNIQUE,               -- un seul avis par commande
    CONSTRAINT fk_avis_commande FOREIGN KEY (id_commande) REFERENCES commande(id_commande) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE horaire (
    id_horaire INT AUTO_INCREMENT PRIMARY KEY,
    jour ENUM('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL UNIQUE,
    heure_ouverture TIME NOT NULL,
    heure_fermeture TIME NOT NULL
) ENGINE=InnoDB;