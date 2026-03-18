<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Doctor;
use App\Config\Database;
use PDO;

class DoctorController
{
    private Doctor $model;
    private PDO $db;

    public function __construct()
    {
        AuthController::requireLogin();
        $this->model = new Doctor();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $doctors = $this->model->getAll();
        $doctor = null;
        include __DIR__ . '/../views/DoctorView.php';
    }

    public function create(): void
    {
        $doctors = $this->model->getAll();
        $doctor = null;
        include __DIR__ . '/../views/DoctorView.php';
    }

    public function viewData(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $doctor = $this->model->getById($id);
        
        if (!$doctor) {
            $_SESSION['error'] = "Médecin non trouvé";
            header('Location: index.php?controller=doctor&action=index');
            exit;
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as slip_count FROM slips WHERE doctor_id = ?");
        $stmt->execute([$id]);
        $stats = $stmt->fetch();
        $doctor['slip_count'] = $stats['slip_count'];
        
        $doctors = $this->model->getAll();
        include __DIR__ . '/../views/DoctorView.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => htmlspecialchars(trim($_POST['nom'])),
                'specialite' => htmlspecialchars(trim($_POST['specialite'])),
                'adresse' => htmlspecialchars(trim($_POST['adresse'])),
                'telephone' => htmlspecialchars(trim($_POST['telephone'])),
                'email' => htmlspecialchars(trim($_POST['email']))
            ];
            $this->model->create($data);
            $_SESSION['success'] = "Médecin ajouté avec succès";
        }
        header('Location: index.php?controller=doctor&action=index');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $doctor = $this->model->getById($id);
        
        if (!$doctor) {
            $_SESSION['error'] = "Médecin non trouvé";
            header('Location: index.php?controller=doctor&action=index');
            exit;
        }
        
        $doctors = $this->model->getAll();
        include __DIR__ . '/../views/DoctorView.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $data = [
                'nom' => htmlspecialchars(trim($_POST['nom'])),
                'specialite' => htmlspecialchars(trim($_POST['specialite'])),
                'adresse' => htmlspecialchars(trim($_POST['adresse'])),
                'telephone' => htmlspecialchars(trim($_POST['telephone'])),
                'email' => htmlspecialchars(trim($_POST['email']))
            ];
            $this->model->update($id, $data);
            $_SESSION['success'] = "Médecin modifié avec succès";
        }
        header('Location: index.php?controller=doctor&action=index');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $doctor = $this->model->getById($id);
            if ($doctor) {
                $this->model->delete($id);
                $_SESSION['success'] = "Médecin " . htmlspecialchars($doctor['nom']) . " supprimé avec succès";
            } else {
                $_SESSION['error'] = "Médecin non trouvé";
            }
        }
        header('Location: index.php?controller=doctor&action=index');
        exit;
    }

    public function exportCsv(): void
    {
        $doctors = $this->model->getAll();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="medecins_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['ID', 'Nom', 'Spécialité', 'Téléphone', 'Email'], ';');
        foreach ($doctors as $d) {
            fputcsv($output, [$d['id'], $d['nom'], $d['specialite'], $d['telephone'], $d['email']], ';');
        }
        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        $doctors = $this->model->getAll();
        include __DIR__ . '/../views/DoctorExportView.php';
        exit;
    }
}