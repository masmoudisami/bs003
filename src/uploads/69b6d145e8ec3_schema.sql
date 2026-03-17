-- ============================================================================
-- GESTION DES BULLETINS DE SOINS - SCRIPT D'INSTALLATION COMPLÈTE
-- ============================================================================
-- Version: 3.0
-- Date: 2024
-- Base de données: gestion_soins
-- Encodage: utf8mb4
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
-- 5. DONNÉES DE DÉMONSTRATION (OPTIONNEL)
-- ============================================================================

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

-- Bulletins de démonstration
INSERT INTO slips (numero_bulletin, patient_id, doctor_id, date_soins, date_remboursement, commentaire, total, montant_debourse, montant_rembourse) VALUES
    ('BS-2024-001', 1, 1, '2024-01-15', '2024-01-20', 'Consultation de routine', 50.000, 50.000, 40.000),
    ('BS-2024-002', 2, 2, '2024-01-18', '2024-01-25', 'Bilan cardiologique complet avec ECG', 120.000, 120.000, 100.000),
    ('BS-2024-003', 3, 3, '2024-01-20', NULL, 'Consultation orthopédique + Radiographie du genou', 180.000, 180.000, 0.000),
    ('BS-2024-004', 4, 1, '2024-01-22', '2024-01-28', 'Vaccination annuelle grippale', 35.000, 35.000, 30.000),
    ('BS-2024-005', 5, 4, '2024-01-25', '2024-02-01', 'Consultation dermatologique pour éruption cutanée', 60.000, 60.000, 50.000),
    ('BS-2024-006', 1, 2, '2024-02-01', '2024-02-10', 'Suivi cardiologique avec échographie', 200.000, 200.000, 180.000),
    ('BS-2024-007', 2, 3, '2024-02-05', NULL, 'Consultation orthopédique pour douleur épaule', 90.000, 90.000, 0.000),
    ('BS-2024-008', 3, 1, '2024-02-10', '2024-02-15', 'Consultation générale et analyse sanguine', 110.000, 110.000, 90.000);

-- Lignes d'intervention de démonstration
INSERT INTO slip_lines (slip_id, intervention_type_id, montant, fichier_path) VALUES
    (1, 1, 50.000, NULL),
    (2, 2, 80.000, NULL),
    (2, 6, 40.000, 'ecg_2024_002.pdf'),
    (3, 2, 80.000, NULL),
    (3, 3, 100.000, 'radio_genou_2024_003.jpg'),
    (4, 7, 35.000, NULL),
    (5, 2, 60.000, NULL),
    (6, 2, 120.000, NULL),
    (6, 4, 80.000, 'echo_coeur_2024_006.pdf'),
    (7, 2, 90.000, NULL),
    (8, 1, 50.000, NULL),
    (8, 5, 60.000, 'analyse_sang_2024_008.pdf');

-- ============================================================================
-- 6. VÉRIFICATION DE L'INSTALLATION
-- ============================================================================

SELECT '========================================' AS '';
SELECT 'INSTALLATION TERMINÉE AVEC SUCCÈS!' AS message;
SELECT '========================================' AS '';
SELECT 'Tables créées:' AS info;
SHOW TABLES;

SELECT 'Nombre de patients:' AS info, COUNT(*) AS count FROM patients;
SELECT 'Nombre de médecins:' AS info, COUNT(*) AS count FROM doctors;
SELECT 'Nombre de types d''intervention:' AS info, COUNT(*) AS count FROM intervention_types;
SELECT 'Nombre de bulletins:' AS info, COUNT(*) AS count FROM slips;
SELECT 'Nombre de lignes d''intervention:' AS info, COUNT(*) AS count FROM slip_lines;

SELECT '========================================' AS '';
SELECT 'Structure de la table slips:' AS info;
DESCRIBE slips;

SELECT '========================================' AS '';
SELECT 'Aperçu des bulletins:' AS info;
SELECT numero_bulletin, patient_id, doctor_id, date_soins, montant_debourse, montant_rembourse FROM slips LIMIT 5;

-- ============================================================================
-- FIN DU SCRIPT
-- ============================================================================