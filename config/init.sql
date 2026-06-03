-- ============================================
-- GESTION DES LOCATIONS D'APPARTEMENTS
-- Script de création de la base de données
-- ============================================

CREATE DATABASE IF NOT EXISTS gestion_locations
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gestion_locations;

-- TABLE LOCATAIRES
CREATE TABLE IF NOT EXISTS locataires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    adresse TEXT,
    piece_identite VARCHAR(50) NOT NULL,   -- CIN ou Passport
    numero_locataire VARCHAR(20) NOT NULL UNIQUE,
    date_inscription DATE NOT NULL DEFAULT (CURDATE()),
    statut_locataire ENUM('actif','ancien','blackliste') NOT NULL DEFAULT 'actif',
    ALTER TABLE locataires CHANGE `blacklisté` `blackliste` TINYINT(1) NOT NULL DEFAULT 0;
) ENGINE=InnoDB;

-- TABLE APPARTEMENTS
CREATE TABLE IF NOT EXISTS appartements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_appartement VARCHAR(30) NOT NULL UNIQUE,
    designation VARCHAR(200) NOT NULL,
    lieu VARCHAR(150) NOT NULL,
    loyer_mensuel DECIMAL(10,2) NOT NULL,
    loyer_mensuel_contractuel DECIMAL(10,2),  -- figé au moment du contrat
    caution DECIMAL(10,2) NOT NULL,           -- ≤ 3 × loyer_mensuel
    charges_incluses ENUM('Oui','Non') DEFAULT 'Non',
    eau DECIMAL(10,2) DEFAULT 0,
    electricite DECIMAL(10,2) DEFAULT 0,
    gardiennage DECIMAL(10,2) DEFAULT 0,
    surface_m2 DECIMAL(8,2),
    nombre_pieces INT,
    description TEXT,
    statut ENUM('Libre','Occupé','En Travaux','Hors service') NOT NULL DEFAULT 'Libre'
) ENGINE=InnoDB;

-- TABLE CONTRATS
CREATE TABLE IF NOT EXISTS contrats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_contrat VARCHAR(30) NOT NULL UNIQUE,
    id_locataire INT NOT NULL,
    id_appartement INT NOT NULL,
    date_entree DATE NOT NULL,
    date_sortie_prevue DATE NOT NULL,
    date_sortie_reelle DATE,
    duree_mois INT NOT NULL,
    loyer_mensuel_contractuel DECIMAL(10,2) NOT NULL,
    caution_versee DECIMAL(10,2) NOT NULL,
    statut_contrat ENUM('En cours','Terminé','Résilié','Prolongé') NOT NULL DEFAULT 'En cours',
    date_creation DATETIME DEFAULT NOW(),
    FOREIGN KEY (id_locataire) REFERENCES locataires(id),
    FOREIGN KEY (id_appartement) REFERENCES appartements(id)
) ENGINE=InnoDB;

-- TABLE PAIEMENTS (quittances)
CREATE TABLE IF NOT EXISTS paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_quittance VARCHAR(30) NOT NULL UNIQUE,
    id_paiement VARCHAR(30) NOT NULL UNIQUE,
    id_contrat INT NOT NULL,
    mois_concerne DATE NOT NULL,           -- 1er du mois concerné (ex: 2024-03-01)
    montant_paye DECIMAL(10,2) NOT NULL,
    reste_a_payer DECIMAL(10,2) DEFAULT 0,
    date_paiement DATE NOT NULL,
    mode_paiement ENUM('Espèces','Virement','Chèque','Mobile Money') DEFAULT 'Espèces',
    statut_paiement ENUM('Payé','Partiel','Impayé') NOT NULL DEFAULT 'Payé',
    reference_transaction VARCHAR(100),
    date_generation DATETIME DEFAULT NOW(),
    fichier_pdf VARCHAR(255),
    FOREIGN KEY (id_contrat) REFERENCES contrats(id)
) ENGINE=InnoDB;
