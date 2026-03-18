<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Patient;

class PatientController
{
    private Patient $model;

    public function __construct()
    {
        AuthController::requireLogin();
        $this->model = new Patient();
    }

    public function index(): void
    {
        $patients = $this->model->getAll();
        $patient = null;
        include __DIR__ . '/../views/PatientView.php';
    }

    public function create(): void
    {
        $patients = $this->model->getAll();
        $patient = null;
        include __DIR__ . '/../views/PatientView.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = htmlspecialchars(trim($_POST['nom']));
            $date_naissance = $_POST['date_naissance'];
            if ($nom && $date_naissance) {
                $this->model->create($nom, $date_naissance);
                $_SESSION['success'] = "Patient ajouté avec succès";
            } else {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
            }
        }
        header('Location: index.php?controller=patient&action=index');
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $patient = $this->model->getById($id);
        
        if (!$patient) {
            $_SESSION['error'] = "Patient non trouvé";
            header('Location: index.php?controller=patient&action=index');
            exit;
        }
        
        $patients = $this->model->getAll();
        include __DIR__ . '/../views/PatientView.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $nom = htmlspecialchars(trim($_POST['nom']));
            $date_naissance = $_POST['date_naissance'];
            if ($id && $nom && $date_naissance) {
                $this->model->update($id, $nom, $date_naissance);
                $_SESSION['success'] = "Patient modifié avec succès";
            } else {
                $_SESSION['error'] = "Tous les champs sont obligatoires";
            }
        }
        header('Location: index.php?controller=patient&action=index');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $patient = $this->model->getById($id);
            if ($patient) {
                $this->model->delete($id);
                $_SESSION['success'] = "Patient " . htmlspecialchars($patient['nom']) . " supprimé avec succès";
            } else {
                $_SESSION['error'] = "Patient non trouvé";
            }
        }
        header('Location: index.php?controller=patient&action=index');
        exit;
    }

    public function exportCsv(): void
    {
        AuthController::requireLogin();
        
        $patients = $this->model->getAll();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="patients_' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['ID', 'Nom', 'Date de Naissance'], ';');
        foreach ($patients as $p) {
            $date_naissance = !empty($p['date_naissance']) ? date('d/m/Y', strtotime($p['date_naissance'])) : '';
            fputcsv($output, [$p['id'], $p['nom'], $date_naissance], ';');
        }
        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        AuthController::requireLogin();
        
        $patients = $this->model->getAll();
        include __DIR__ . '/../views/PatientExportView.php';
        exit;
    }

    public function history(): void
    {
        AuthController::requireLogin();
        
        $id = (int)($_GET['id'] ?? 0);
        $patient = $this->model->getById($id);
        
        if ($patient) {
            // Récupérer les filtres de date
            $date_debut = $_GET['date_debut'] ?? null;
            $date_fin = $_GET['date_fin'] ?? null;
            
            // Récupérer les bulletins avec lignes et filtres
            $slips = $this->model->getPatientSlipsWithLines($id, $date_debut, $date_fin);
            
            include __DIR__ . '/../views/PatientHistoryView.php';
        } else {
            $_SESSION['error'] = "Patient non trouvé";
            header('Location: index.php?controller=patient&action=index');
            exit;
        }
    }

    public function historyExportCsv(): void
    {
        AuthController::requireLogin();
        
        $id = (int)($_GET['id'] ?? 0);
        $patient = $this->model->getById($id);
        
        if ($patient) {
            // === RÉCUPÉRER LES FILTRES DE DATE ===
            $date_debut = $_GET['date_debut'] ?? null;
            $date_fin = $_GET['date_fin'] ?? null;
            
            // Récupérer les bulletins avec lignes et filtres
            $slips = $this->model->getPatientSlipsWithLines($id, $date_debut, $date_fin);
            
            // Nom du fichier
            $filename = 'historique_' . preg_replace('/[^a-zA-Z0-9]/', '_', $patient['nom']) . '_' . date('Y-m-d') . '.csv';
            
            // En-têtes HTTP
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
            
            // BOM pour Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // En-têtes de colonnes
            fputcsv($output, [
                'N° Bulletin',
                'Date Soins',
                'Date Remboursement',
                'Médecin',
                'Type Intervention',
                'Montant Intervention (TND)',
                'Pièce Jointe',
                'Total Interventions (TND)',
                'Montant Déboursé (TND)',
                'Montant Remboursé (TND)',
                'Différence (TND)',
                'Commentaire'
            ], ';');

            // Données
            foreach ($slips as $s) {
                $difference = (float)$s['montant_debourse'] - (float)$s['montant_rembourse'];
                
                $date_soins = !empty($s['date_soins']) ? date('d/m/Y', strtotime($s['date_soins'])) : '';
                $date_remboursement = !empty($s['date_remboursement']) ? date('d/m/Y', strtotime($s['date_remboursement'])) : '';
                
                $commentaire = str_replace(["\r\n", "\r", "\n"], ' ', $s['commentaire'] ?? '');
                $commentaire = str_replace([';', '"'], ['', ''], $commentaire);
                
                if (empty($s['lines'])) {
                    fputcsv($output, [
                        $s['numero_bulletin'],
                        $date_soins,
                        $date_remboursement,
                        $s['doctor_nom'],
                        '-',
                        str_replace('.', ',', number_format(0, 3, '.', '')),
                        'Non',
                        str_replace('.', ',', number_format((float)$s['total'], 3, '.', '')),
                        str_replace('.', ',', number_format((float)$s['montant_debourse'], 3, '.', '')),
                        str_replace('.', ',', number_format((float)$s['montant_rembourse'], 3, '.', '')),
                        str_replace('.', ',', number_format($difference, 3, '.', '')),
                        $commentaire
                    ], ';');
                } else {
                    $first_line = true;
                    foreach ($s['lines'] as $line) {
                        fputcsv($output, [
                            $first_line ? $s['numero_bulletin'] : '',
                            $first_line ? $date_soins : '',
                            $first_line ? $date_remboursement : '',
                            $first_line ? $s['doctor_nom'] : '',
                            $line['libelle'],
                            str_replace('.', ',', number_format((float)$line['montant'], 3, '.', '')),
                            $line['fichier_path'] ? 'Oui' : 'Non',
                            $first_line ? str_replace('.', ',', number_format((float)$s['total'], 3, '.', '')) : '',
                            $first_line ? str_replace('.', ',', number_format((float)$s['montant_debourse'], 3, '.', '')) : '',
                            $first_line ? str_replace('.', ',', number_format((float)$s['montant_rembourse'], 3, '.', '')) : '',
                            $first_line ? str_replace('.', ',', number_format($difference, 3, '.', '')) : '',
                            $first_line ? $commentaire : ''
                        ], ';');
                        $first_line = false;
                    }
                }
            }
            
            fclose($output);
        }
        exit;
    }

    public function historyExportPdf(): void
    {
        AuthController::requireLogin();
        
        $id = (int)($_GET['id'] ?? 0);
        $patient = $this->model->getById($id);
        if ($patient) {
            $date_debut = $_GET['date_debut'] ?? null;
            $date_fin = $_GET['date_fin'] ?? null;
            
            $slips = $this->model->getPatientSlipsWithLines($id, $date_debut, $date_fin);
            include __DIR__ . '/../views/PatientHistoryView.php';
        }
        exit;
    }
}