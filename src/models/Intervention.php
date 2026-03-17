<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Intervention
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM intervention_types ORDER BY libelle ASC");
        return $stmt->fetchAll();
    }

    public function create(string $libelle): bool
    {
        $stmt = $this->db->prepare("INSERT INTO intervention_types (libelle) VALUES (?)");
        return $stmt->execute([$libelle]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM intervention_types WHERE id = ?");
        return $stmt->execute([$id]);
    }
}