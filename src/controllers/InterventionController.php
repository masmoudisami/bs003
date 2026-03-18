<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Intervention;

class InterventionController
{
    private Intervention $model;

    public function __construct()
    {
        AuthController::requireLogin();
        $this->model = new Intervention();
    }

    public function index(): void
    {
        $interventions = $this->model->getAll();
        include __DIR__ . '/../views/InterventionView.php';
    }

    public function create(): void
    {
        $interventions = $this->model->getAll();
        include __DIR__ . '/../views/InterventionView.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $libelle = htmlspecialchars(trim($_POST['libelle']));
            if ($libelle) {
                $this->model->create($libelle);
                $_SESSION['success'] = "Type d'intervention ajouté avec succès";
            } else {
                $_SESSION['error'] = "Le libellé est obligatoire";
            }
        }
        header('Location: index.php?controller=intervention&action=index');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->model->delete($id);
            $_SESSION['success'] = "Type d'intervention supprimé avec succès";
        }
        header('Location: index.php?controller=intervention&action=index');
        exit;
    }

    public function exportCsv(): void
    {
        $interventions = $this->model->getAll();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="interventions_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['ID', 'Libellé'], ';');
        foreach ($interventions as $i) {
            fputcsv($output, [$i['id'], $i['libelle']], ';');
        }
        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        $interventions = $this->model->getAll();
        include __DIR__ . '/../views/InterventionExportView.php';
        exit;
    }
}