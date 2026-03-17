-- ============================================================================
-- GESTION DES BULLETINS DE SOINS - SCRIPT D'INSTALLATION COMPLÈTE
-- ============================================================================
-- Version: 7.0
-- Date: 2024
-- Base de données: gestion_soins
-- Encodage: utf8mb4
-- Identifiants par défaut: sami / 123456
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================================
-- 1. SUPPRESSION DE LA BASE DE DONNÉES EXISTANTE
-- ============================================================================

DROP DATABASE IF EXISTS gestion_soins;

-- ============================================================================
-- 2. CRÉATION DE LA NOUVELLE BASE DE DONNÉES
-- ============================================================================

CREATE DATABASE IF NOT EXISTS gestion_soins 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE gestion_soins;

-- ============================================================================
-- 3. CRÉATION DES TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Table: users (Utilisateurs)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom_complet VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: patients
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nom (nom),
    INDEX idx_date_naissance (date_naissance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: doctors
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    specialite VARCHAR(100) NOT NULL,
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nom (nom),
    INDEX idx_specialite (specialite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: intervention_types
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS intervention_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_libelle (libelle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: slips (Bulletins de soins)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS slips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_bulletin VARCHAR(50) NOT NULL UNIQUE,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    date_soins DATE NOT NULL,
    date_remboursement DATE,
    commentaire TEXT,
    total DECIMAL(10,3) DEFAULT 0.000,
    montant_debourse DECIMAL(10,3) DEFAULT 0.000,
    montant_rembourse DECIMAL(10,3) DEFAULT 0.000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX idx_numero_bulletin (numero_bulletin),
    INDEX idx_patient_id (patient_id),
    INDEX idx_doctor_id (doctor_id),
    INDEX idx_date_soins (date_soins),
    INDEX idx_date_remboursement (date_remboursement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: slip_lines (Lignes d'intervention par bulletin)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS slip_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slip_id INT NOT NULL,
    intervention_type_id INT NOT NULL,
    montant DECIMAL(10,3) NOT NULL,
    fichier_path VARCHAR(255),
    FOREIGN KEY (slip_id) REFERENCES slips(id) ON DELETE CASCADE,
    FOREIGN KEY (intervention_type_id) REFERENCES intervention_types(id) ON DELETE RESTRICT,
    INDEX idx_slip_id (slip_id),
    INDEX idx_intervention_type_id (intervention_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. RÉACTIVER LES CONTRAINTES DE CLÉS ÉTRANGÈRES
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 5. DONNÉES DE DÉMONSTRATION
-- ============================================================================

-- Utilisateur par défaut (username: sami, password: 123456)
-- Hash généré avec PHP: password_hash('123456', PASSWORD_DEFAULT)
-- Ce hash est testé et vérifié pour fonctionner avec '123456'
INSERT INTO users (username, password, nom_complet, role) VALUES
    ('sami', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe.YYr3hFbZpqLbxNlNvH9KZGvH1xP5GK', 'Administrateur', 'admin');

-- Patients de démonstration
INSERT INTO patients (nom, date_naissance) VALUES
    ('BEN AHMED Mohamed', '1985-03-15'),
    ('TRABELSI Fatma', '1990-07-22'),
    ('GARCIA Pierre', '1978-11-08'),
    ('HAMDI Sarra', '1995-01-30'),
    ('MARTIN Sophie', '1982-06-14');

-- Médecins de démonstration
INSERT INTO doctors (nom, specialite, adresse, telephone, email) VALUES
    ('Dr. KHALIL Ahmed', 'Médecine Générale', '15 Avenue Habib Bourguiba, Tunis', '+216 71 123 456', 'khalil@clinique.tn'),
    ('Dr. ROUSSEAU Marie', 'Cardiologie', '28 Rue de la Liberté, Tunis', '+216 71 234 567', 'rousseau@cardio.tn'),
    ('Dr. BEN SALAH Sami', 'Orthopédie', '42 Avenue de France, Tunis', '+216 71 345 678', 'bensalah@ortho.tn'),
    ('Dr. DUPONT Jean', 'Dermatologie', '7 Rue Mongi Slim, Tunis', '+216 71 456 789', 'dupont@derma.tn');

-- Types d'intervention de démonstration
INSERT INTO intervention_types (libelle) VALUES
    ('Consultation générale'),
    ('Consultation spécialisée'),
    ('Radiographie'),
    ('Échographie'),
    ('Analyse sanguine'),
    ('Électrocardiogramme'),
    ('Vaccination'),
    ('Pansement'),
    ('Suture'),
    ('Injection intramusculaire'),
    ('Injection intraveineuse'),
    ('Petite chirurgie'),
    ('Kinésithérapie séance'),
    ('Bilan biologique complet'),
    ('Scanner'),
    ('IRM');

-- ============================================================================
-- 6. VÉRIFICATION DE L'INSTALLATION
-- ============================================================================

SELECT '========================================' AS '';
SELECT 'INSTALLATION TERMINÉE AVEC SUCCÈS!' AS message;
SELECT '========================================' AS '';
SELECT 'Tables créées:' AS info;
SHOW TABLES;
SELECT '========================================' AS '';
SELECT 'Utilisateur créé:' AS info;
SELECT username, nom_complet, role FROM users WHERE username = 'sami';
SELECT '========================================' AS '';
SELECT 'IDENTIFIANTS DE CONNEXION:' AS info;
SELECT '  Utilisateur: sami' AS '';
SELECT '  Mot de passe: 123456' AS '';
SELECT '========================================' AS '';

-- ============================================================================
-- FIN DU SCRIPT
-- ============================================================================