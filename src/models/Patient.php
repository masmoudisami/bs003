<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Patient
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupérer tous les patients
     */
    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM patients ORDER BY nom ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupérer un patient par son ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Créer un nouveau patient
     */
    public function create(string $nom, string $date_naissance): int
    {
        $stmt = $this->db->prepare("INSERT INTO patients (nom, date_naissance) VALUES (?, ?)");
        $stmt->execute([$nom, $date_naissance]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Mettre à jour un patient
     */
    public function update(int $id, string $nom, string $date_naissance): bool
    {
        $stmt = $this->db->prepare("UPDATE patients SET nom = ?, date_naissance = ? WHERE id = ?");
        return $stmt->execute([$nom, $date_naissance, $id]);
    }

    /**
     * Supprimer un patient
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM patients WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Récupérer les bulletins d'un patient avec les lignes d'intervention
     * Avec filtres optionnels de date
     * 
     * @param int $patient_id ID du patient
     * @param string|null $date_debut Date de début (format YYYY-MM-DD)
     * @param string|null $date_fin Date de fin (format YYYY-MM-DD)
     * @return array Liste des bulletins avec leurs lignes
     */
    public function getPatientSlipsWithLines(int $patient_id, ?string $date_debut = null, ?string $date_fin = null): array
    {
        // Requête principale pour récupérer les bulletins
        $sql = "SELECT s.*, d.nom as doctor_nom 
                FROM slips s 
                JOIN doctors d ON s.doctor_id = d.id 
                WHERE s.patient_id = ?";
        $params = [$patient_id];
        
        // === AJOUT FILTRES DATE ===
        if (!empty($date_debut)) {
            $sql .= " AND s.date_soins >= ?";
            $params[] = $date_debut;
        }
        if (!empty($date_fin)) {
            $sql .= " AND s.date_soins <= ?";
            $params[] = $date_fin;
        }
        // === FIN FILTRES DATE ===
        
        $sql .= " ORDER BY s.date_soins DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $slips = $stmt->fetchAll();
        
        // Récupérer les lignes pour chaque bulletin
        foreach ($slips as &$slip) {
            $stmt_lines = $this->db->prepare("SELECT sl.*, it.libelle 
                                              FROM slip_lines sl 
                                              JOIN intervention_types it ON sl.intervention_type_id = it.id 
                                              WHERE sl.slip_id = ?
                                              ORDER BY sl.id");
            $stmt_lines->execute([$slip['id']]);
            $slip['lines'] = $stmt_lines->fetchAll();
        }
        
        return $slips;
    }

    /**
     * Compter le nombre de bulletins d'un patient
     */
    public function countSlips(int $patient_id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM slips WHERE patient_id = ?");
        $stmt->execute([$patient_id]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Compter le nombre total de déboursé pour un patient
     */
    public function totalDebourse(int $patient_id): float
    {
        $stmt = $this->db->prepare("SELECT SUM(montant_debourse) FROM slips WHERE patient_id = ?");
        $stmt->execute([$patient_id]);
        return (float)($stmt->fetchColumn() ?? 0);
    }

    /**
     * Compter le nombre total de remboursé pour un patient
     */
    public function totalRembourse(int $patient_id): float
    {
        $stmt = $this->db->prepare("SELECT SUM(montant_rembourse) FROM slips WHERE patient_id = ?");
        $stmt->execute([$patient_id]);
        return (float)($stmt->fetchColumn() ?? 0);
    }

    /**
     * Rechercher des patients par nom
     */
    public function searchByName(string $search): array
    {
        $search = "%$search%";
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE nom LIKE ? ORDER BY nom ASC");
        $stmt->execute([$search]);
        return $stmt->fetchAll();
    }

    /**
     * Vérifier si un patient existe par son nom et date de naissance
     */
    public function exists(string $nom, string $date_naissance, ?int $exclude_id = null): bool
    {
        $sql = "SELECT COUNT(*) FROM patients WHERE nom = ? AND date_naissance = ?";
        $params = [$nom, $date_naissance];
        
        if ($exclude_id !== null) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Récupérer les patients avec leurs statistiques
     */
    public function getAllWithStats(): array
    {
        $sql = "SELECT p.*, 
                       COUNT(s.id) as nb_bulletins,
                       COALESCE(SUM(s.montant_debourse), 0) as total_debourse,
                       COALESCE(SUM(s.montant_rembourse), 0) as total_rembourse
                FROM patients p
                LEFT JOIN slips s ON p.id = s.patient_id
                GROUP BY p.id
                ORDER BY p.nom ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupérer le dernier bulletin d'un patient
     */
    public function getLastSlip(int $patient_id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM slips WHERE patient_id = ? ORDER BY date_soins DESC LIMIT 1");
        $stmt->execute([$patient_id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Récupérer les bulletins d'un patient pour une période spécifique
     * (Méthode alternative avec plus de détails)
     */
    public function getSlipsByPeriod(int $patient_id, string $date_debut, string $date_fin): array
    {
        $sql = "SELECT s.*, d.nom as doctor_nom, p.nom as patient_nom
                FROM slips s
                JOIN doctors d ON s.doctor_id = d.id
                JOIN patients p ON s.patient_id = p.id
                WHERE s.patient_id = ?
                AND s.date_soins BETWEEN ? AND ?
                ORDER BY s.date_soins DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patient_id, $date_debut, $date_fin]);
        $slips = $stmt->fetchAll();
        
        // Ajouter les lignes à chaque bulletin
        foreach ($slips as &$slip) {
            $stmt_lines = $this->db->prepare("SELECT sl.*, it.libelle 
                                              FROM slip_lines sl 
                                              JOIN intervention_types it ON sl.intervention_type_id = it.id 
                                              WHERE sl.slip_id = ?");
            $stmt_lines->execute([$slip['id']]);
            $slip['lines'] = $stmt_lines->fetchAll();
        }
        
        return $slips;
    }
}