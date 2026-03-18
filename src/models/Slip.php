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

    public function getAllFiltered(?string $date, ?int $doctor_id, ?int $patient_id, ?string $search): array
    {
        $sql = "SELECT s.*, p.nom as patient_nom, d.nom as doctor_nom 
                FROM slips s 
                JOIN patients p ON s.patient_id = p.id 
                JOIN doctors d ON s.doctor_id = d.id 
                WHERE 1=1";
        $params = [];

        if ($date) {
            $sql .= " AND s.date_soins = ?";
            $params[] = $date;
        }
        if ($doctor_id) {
            $sql .= " AND s.doctor_id = ?";
            $params[] = $doctor_id;
        }
        if ($patient_id) {
            $sql .= " AND s.patient_id = ?";
            $params[] = $patient_id;
        }
        if ($search) {
            $sql .= " AND (p.nom LIKE ? OR d.nom LIKE ? OR s.commentaire LIKE ? OR s.numero_bulletin LIKE ?)";
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

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

    public function getLines(int $slip_id): array
    {
        // === IMPORTANT : Inclure fichier_path dans le SELECT ===
        $stmt = $this->db->prepare("SELECT sl.*, it.libelle 
                                    FROM slip_lines sl 
                                    JOIN intervention_types it ON sl.intervention_type_id = it.id 
                                    WHERE sl.slip_id = ?
                                    ORDER BY sl.id");
        $stmt->execute([$slip_id]);
        return $stmt->fetchAll();
    }

    public function exists(string $numero_bulletin): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM slips WHERE numero_bulletin = ?");
        $stmt->execute([$numero_bulletin]);
        return (int)$stmt->fetchColumn() > 0;
    }

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

            // Insérer les nouvelles lignes avec les fichiers (conservés ou nouveaux)
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

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM slips WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
