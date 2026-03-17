<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Doctor
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM doctors ORDER BY nom ASC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO doctors (nom, specialite, adresse, telephone, email) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$data['nom'], $data['specialite'], $data['adresse'], $data['telephone'], $data['email']]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE doctors SET nom = ?, specialite = ?, adresse = ?, telephone = ?, email = ? WHERE id = ?");
        return $stmt->execute([$data['nom'], $data['specialite'], $data['adresse'], $data['telephone'], $data['email'], $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM doctors WHERE id = ?");
        return $stmt->execute([$id]);
    }
}