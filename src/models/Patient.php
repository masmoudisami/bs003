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

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM patients ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public function create(string $nom, string $date_naissance): bool
    {
        $stmt = $this->db->prepare("INSERT INTO patients (nom, date_naissance) VALUES (?, ?)");
        return $stmt->execute([$nom, $date_naissance]);
    }

    public function update(int $id, string $nom, string $date_naissance): bool
    {
        $stmt = $this->db->prepare("UPDATE patients SET nom = ?, date_naissance = ? WHERE id = ?");
        return $stmt->execute([$nom, $date_naissance, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM patients WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getPatientSlips(int $patient_id): array
    {
        $stmt = $this->db->prepare("SELECT s.*, d.nom as doctor_nom 
                                     FROM slips s 
                                     JOIN doctors d ON s.doctor_id = d.id 
                                     WHERE s.patient_id = ? 
                                     ORDER BY s.date_soins DESC");
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll();
    }

    public function getPatientSlipsWithLines(int $patient_id): array
    {
        $slips = $this->getPatientSlips($patient_id);
        
        foreach ($slips as &$slip) {
            $stmt = $this->db->prepare("SELECT sl.*, it.libelle 
                                        FROM slip_lines sl 
                                        JOIN intervention_types it ON sl.intervention_type_id = it.id 
                                        WHERE sl.slip_id = ?");
            $stmt->execute([$slip['id']]);
            $slip['lines'] = $stmt->fetchAll();
        }
        
        return $slips;
    }
}