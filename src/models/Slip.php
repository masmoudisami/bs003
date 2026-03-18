<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Slip
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Récupérer tous les bulletins avec filtres
     * 
     * @param string|null $date_debut Date de début (format YYYY-MM-DD)
     * @param string|null $date_fin Date de fin (format YYYY-MM-DD)
     * @param int|null $doctor_id ID du médecin
     * @param int|null $patient_id ID du patient
     * @param string|null $search Terme de recherche
     * @param bool $non_rembourses Filtre non remboursés uniquement
     * @return array Liste des bulletins
     */
    public function getAllFiltered(?string $date_debut, ?string $date_fin, ?int $doctor_id, ?int $patient_id, ?string $search, bool $non_rembourses = false): array
    {
        $sql = "SELECT s.*, p.nom as patient_nom, d.nom as doctor_nom 
                FROM slips s 
                JOIN patients p ON s.patient_id = p.id 
                JOIN doctors d ON s.doctor_id = d.id 
                WHERE 1=1";
        $params = [];

        // === FILTRE PLAGE DE DATE ===
        if (!empty($date_debut)) {
            $sql .= " AND s.date_soins >= ?";
            $params[] = $date_debut;
        }
        if (!empty($date_fin)) {
            $sql .= " AND s.date_soins <= ?";
            $params[] = $date_fin;
        }
        
        // === FILTRE MÉDECIN ===
        if ($doctor_id) {
            $sql .= " AND s.doctor_id = ?";
            $params[] = $doctor_id;
        }
        
        // === FILTRE PATIENT ===
        if ($patient_id) {
            $sql .= " AND s.patient_id = ?";
            $params[] = $patient_id;
        }
        
        // === FILTRE RECHERCHE ===
        if ($search) {
            $sql .= " AND (p.nom LIKE ? OR d.nom LIKE ? OR s.commentaire LIKE ? OR s.numero_bulletin LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
        
        // === FILTRE NON REMBOURSÉS ===
        if ($non_rembourses) {
            $sql .= " AND (s.montant_rembourse = 0 OR s.montant_rembourse IS NULL)";
        }

        $sql .= " ORDER BY s.date_soins DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer un bulletin par son ID
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.nom as patient_nom, d.nom as doctor_nom 
                                     FROM slips s 
                                     JOIN patients p ON s.patient_id = p.id 
                                     JOIN doctors d ON s.doctor_id = d.id 
                                     WHERE s.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Récupérer un bulletin par son numéro
     */
    public function getByNumero(string $numero): ?array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.nom as patient_nom, d.nom as doctor_nom 
                                     FROM slips s 
                                     JOIN patients p ON s.patient_id = p.id 
                                     JOIN doctors d ON s.doctor_id = d.id 
                                     WHERE s.numero_bulletin = ?");
        $stmt->execute([$numero]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Récupérer les lignes d'un bulletin
     */
    public function getLines(int $slip_id): array
    {
        $stmt = $this->db->prepare("SELECT sl.*, it.libelle 
                                    FROM slip_lines sl 
                                    JOIN intervention_types it ON sl.intervention_type_id = it.id 
                                    WHERE sl.slip_id = ?
                                    ORDER BY sl.id");
        $stmt->execute([$slip_id]);
        return $stmt->fetchAll();
    }

    /**
     * Vérifier si un bulletin existe par son numéro
     */
    public function exists(string $numero_bulletin): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM slips WHERE numero_bulletin = ?");
        $stmt->execute([$numero_bulletin]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Créer un nouveau bulletin avec ses lignes
     */
    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO slips (numero_bulletin, patient_id, doctor_id, date_soins, date_remboursement, commentaire, total, montant_debourse, montant_rembourse) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['numero_bulletin'], 
                $data['patient_id'], 
                $data['doctor_id'], 
                $data['date_soins'], 
                $data['date_remboursement'], 
                $data['commentaire'], 
                $data['total'], 
                $data['montant_debourse'], 
                $data['montant_rembourse']
            ]);
            $slip_id = (int)$this->db->lastInsertId();

            // Insertion des lignes avec les fichiers
            foreach ($data['lines'] as $line) {
                $stmt_line = $this->db->prepare("INSERT INTO slip_lines (slip_id, intervention_type_id, montant, fichier_path) VALUES (?, ?, ?, ?)");
                $stmt_line->execute([
                    $slip_id, 
                    $line['intervention_type_id'], 
                    $line['montant'], 
                    $line['fichier_path']
                ]);
            }

            $this->db->commit();
            return $slip_id;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour un bulletin avec ses lignes
     */
    public function update(int $id, array $data): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE slips SET numero_bulletin = ?, patient_id = ?, doctor_id = ?, date_soins = ?, date_remboursement = ?, commentaire = ?, total = ?, montant_debourse = ?, montant_rembourse = ? WHERE id = ?");
            $stmt->execute([
                $data['numero_bulletin'], 
                $data['patient_id'], 
                $data['doctor_id'], 
                $data['date_soins'], 
                $data['date_remboursement'], 
                $data['commentaire'], 
                $data['total'], 
                $data['montant_debourse'], 
                $data['montant_rembourse'], 
                $id
            ]);

            // Supprimer les anciennes lignes
            $stmt_del = $this->db->prepare("DELETE FROM slip_lines WHERE slip_id = ?");
            $stmt_del->execute([$id]);

            // Insérer les nouvelles lignes avec les fichiers
            foreach ($data['lines'] as $line) {
                $stmt_line = $this->db->prepare("INSERT INTO slip_lines (slip_id, intervention_type_id, montant, fichier_path) VALUES (?, ?, ?, ?)");
                $stmt_line->execute([
                    $id, 
                    $line['intervention_type_id'], 
                    $line['montant'], 
                    $line['fichier_path']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Supprimer un bulletin
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM slips WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Compter le nombre total de bulletins
     */
    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM slips");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Compter le nombre de bulletins non remboursés
     */
    public function countNonRembourses(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM slips WHERE montant_rembourse = 0 OR montant_rembourse IS NULL");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Calculer le total déboursé (tous bulletins)
     */
    public function totalDebourse(): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(montant_debourse), 0) FROM slips");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    /**
     * Calculer le total remboursé (tous bulletins)
     */
    public function totalRembourse(): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(montant_rembourse), 0) FROM slips");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }

    /**
     * Récupérer les bulletins d'un patient (sans filtres de date)
     */
    public function getByPatient(int $patient_id): array
    {
        $stmt = $this->db->prepare("SELECT s.*, d.nom as doctor_nom 
                                    FROM slips s 
                                    JOIN doctors d ON s.doctor_id = d.id 
                                    WHERE s.patient_id = ?
                                    ORDER BY s.date_soins DESC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer les bulletins d'un médecin
     */
    public function getByDoctor(int $doctor_id): array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.nom as patient_nom 
                                    FROM slips s 
                                    JOIN patients p ON s.patient_id = p.id 
                                    WHERE s.doctor_id = ?
                                    ORDER BY s.date_soins DESC");
        $stmt->execute([$doctor_id]);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer les bulletins par période
     */
    public function getByPeriod(string $date_debut, string $date_fin): array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.nom as patient_nom, d.nom as doctor_nom 
                                    FROM slips s 
                                    JOIN patients p ON s.patient_id = p.id 
                                    JOIN doctors d ON s.doctor_id = d.id 
                                    WHERE s.date_soins BETWEEN ? AND ?
                                    ORDER BY s.date_soins DESC");
        $stmt->execute([$date_debut, $date_fin]);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer les derniers bulletins créés
     */
    public function getLatest(int $limit = 10): array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.nom as patient_nom, d.nom as doctor_nom 
                                    FROM slips s 
                                    JOIN patients p ON s.patient_id = p.id 
                                    JOIN doctors d ON s.doctor_id = d.id 
                                    ORDER BY s.created_at DESC 
                                    LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer les bulletins avec un solde positif (non remboursé)
     */
    public function getWithPositiveBalance(): array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.nom as patient_nom, d.nom as doctor_nom 
                                    FROM slips s 
                                    JOIN patients p ON s.patient_id = p.id 
                                    JOIN doctors d ON s.doctor_id = d.id 
                                    WHERE s.montant_debourse > s.montant_rembourse
                                    ORDER BY s.date_soins DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Vérifier si un bulletin a des lignes d'intervention
     */
    public function hasLines(int $slip_id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM slip_lines WHERE slip_id = ?");
        $stmt->execute([$slip_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Supprimer les lignes d'un bulletin (pour modification)
     */
    public function deleteLines(int $slip_id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM slip_lines WHERE slip_id = ?");
        return $stmt->execute([$slip_id]);
    }

    /**
     * Ajouter une ligne à un bulletin
     */
    public function addLine(int $slip_id, int $intervention_type_id, float $montant, ?string $fichier_path): int
    {
        $stmt = $this->db->prepare("INSERT INTO slip_lines (slip_id, intervention_type_id, montant, fichier_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$slip_id, $intervention_type_id, $montant, $fichier_path]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Mettre à jour une ligne d'intervention
     */
    public function updateLine(int $line_id, int $intervention_type_id, float $montant, ?string $fichier_path): bool
    {
        $stmt = $this->db->prepare("UPDATE slip_lines SET intervention_type_id = ?, montant = ?, fichier_path = ? WHERE id = ?");
        return $stmt->execute([$intervention_type_id, $montant, $fichier_path, $line_id]);
    }

    /**
     * Supprimer une ligne d'intervention
     */
    public function deleteLine(int $line_id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM slip_lines WHERE id = ?");
        return $stmt->execute([$line_id]);
    }

    /**
     * Récupérer une ligne d'intervention spécifique
     */
    public function getLine(int $line_id): ?array
    {
        $stmt = $this->db->prepare("SELECT sl.*, it.libelle 
                                    FROM slip_lines sl 
                                    JOIN intervention_types it ON sl.intervention_type_id = it.id 
                                    WHERE sl.id = ?");
        $stmt->execute([$line_id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Calculer le total des interventions pour un bulletin
     */
    public function calculateTotal(int $slip_id): float
    {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(montant), 0) FROM slip_lines WHERE slip_id = ?");
        $stmt->execute([$slip_id]);
        return (float)$stmt->fetchColumn();
    }

    /**
     * Exporter tous les bulletins pour rapport
     */
    public function exportAll(): array
    {
        $stmt = $this->db->prepare("SELECT s.*, p.nom as patient_nom, d.nom as doctor_nom, 
                                           (SELECT COUNT(*) FROM slip_lines WHERE slip_id = s.id) as nb_lignes
                                    FROM slips s 
                                    JOIN patients p ON s.patient_id = p.id 
                                    JOIN doctors d ON s.doctor_id = d.id 
                                    ORDER BY s.date_soins DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}