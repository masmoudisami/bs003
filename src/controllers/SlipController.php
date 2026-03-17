<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Slip;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Intervention;

class SlipController
{
    private Slip $model;
    private Patient $patientModel;
    private Doctor $doctorModel;
    private Intervention $interventionModel;

    public function __construct()
    {
        AuthController::requireLogin();
        $this->model = new Slip();
        $this->patientModel = new Patient();
        $this->doctorModel = new Doctor();
        $this->interventionModel = new Intervention();
    }

    public function index(): void
    {
        $slips = $this->model->getAllFiltered(null, null, null, null);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        $interventions = $this->interventionModel->getAll();
        include __DIR__ . '/../views/SlipView.php';
    }

    public function create(): void
    {
        $slip = null;
        $lines = [];
        $slips = $this->model->getAllFiltered(null, null, null, null);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        $interventions = $this->interventionModel->getAll();
        include __DIR__ . '/../views/SlipView.php';
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $slip = $this->model->getById($id);
        
        if (!$slip) {
            $_SESSION['error'] = "Bulletin non trouvé";
            header('Location: index.php?controller=slip&action=index');
            exit;
        }
        
        $lines = $this->model->getLines($id);
        $slips = $this->model->getAllFiltered(null, null, null, null);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        $interventions = $this->interventionModel->getAll();
        include __DIR__ . '/../views/SlipView.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lines_data = [];
            $total = 0.0;
            
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (isset($_POST['lines']) && is_array($_POST['lines'])) {
                foreach ($_POST['lines'] as $i => $line) {
                    $montant = (float)str_replace(',', '.', $line['montant']);
                    $total += $montant;
                    
                    $file_path = null;
                    
                    if (isset($_FILES['lines']) && 
                        isset($_FILES['lines']['name'][$i]['fichier']) && 
                        $_FILES['lines']['name'][$i]['fichier'] !== '') {
                        
                        $file_tmp = $_FILES['lines']['tmp_name'][$i]['fichier'];
                        $file_name = $_FILES['lines']['name'][$i]['fichier'];
                        $file_error = $_FILES['lines']['error'][$i]['fichier'];
                        
                        if ($file_error === UPLOAD_ERR_OK) {
                            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                            $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
                            $destination = $upload_dir . $new_file_name;
                            
                            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
                            if (in_array($file_ext, $allowed_extensions)) {
                                if (move_uploaded_file($file_tmp, $destination)) {
                                    $file_path = $new_file_name;
                                }
                            }
                        }
                    }

                    $lines_data[] = [
                        'intervention_type_id' => (int)$line['intervention_type_id'],
                        'montant' => $montant,
                        'fichier_path' => $file_path
                    ];
                }
            }

            if (empty($lines_data)) {
                $_SESSION['error'] = "Au moins une ligne d'intervention est requise";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }

            $numero_bulletin = isset($_POST['numero_bulletin']) ? trim($_POST['numero_bulletin']) : '';
            
            if (empty($numero_bulletin)) {
                $_SESSION['error'] = "Le numéro de bulletin est obligatoire";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }

            $patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;
            $doctor_id = isset($_POST['doctor_id']) ? (int)$_POST['doctor_id'] : 0;
            
            if ($patient_id <= 0) {
                $_SESSION['error'] = "Le patient est obligatoire";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }
            
            if ($doctor_id <= 0) {
                $_SESSION['error'] = "Le médecin est obligatoire";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }

            $date_soins = $_POST['date_soins'] ?? '';
            if (empty($date_soins)) {
                $_SESSION['error'] = "La date des soins est obligatoire";
                header('Location: index.php?controller=slip&action=create');
                exit;
            }

            $montant_debourse = $total;
            $montant_rembourse = isset($_POST['montant_rembourse']) ? (float)str_replace(',', '.', $_POST['montant_rembourse']) : 0.0;

            $data = [
                'numero_bulletin' => $numero_bulletin,
                'patient_id' => $patient_id,
                'doctor_id' => $doctor_id,
                'date_soins' => $date_soins,
                'date_remboursement' => $_POST['date_remboursement'] ?: null,
                'commentaire' => htmlspecialchars($_POST['commentaire'] ?? ''),
                'total' => $total,
                'montant_debourse' => $montant_debourse,
                'montant_rembourse' => $montant_rembourse,
                'lines' => $lines_data
            ];

            try {
                if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
                    $old_lines = $this->model->getLines((int)$_POST['id']);
                    $existing_files = [];
                    foreach ($old_lines as $old_line) {
                        if (!empty($old_line['fichier_path'])) {
                            $existing_files[$old_line['intervention_type_id']] = $old_line['fichier_path'];
                        }
                    }
                    
                    foreach ($lines_data as &$line_data) {
                        if (empty($line_data['fichier_path']) && 
                            isset($existing_files[$line_data['intervention_type_id']])) {
                            $line_data['fichier_path'] = $existing_files[$line_data['intervention_type_id']];
                        }
                    }
                    
                    $this->model->update((int)$_POST['id'], $data);
                    $_SESSION['success'] = "Bulletin modifié avec succès";
                } else {
                    if ($this->model->exists($numero_bulletin)) {
                        $_SESSION['error'] = "Ce numéro de bulletin existe déjà";
                        header('Location: index.php?controller=slip&action=create');
                        exit;
                    }
                    $this->model->create($data);
                    $_SESSION['success'] = "Bulletin créé avec succès";
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
                header('Location: index.php?controller=slip&action=create');
                exit;
            }
        }
        header('Location: index.php?controller=slip&action=index');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $slip = $this->model->getById($id);
            if ($slip) {
                $lines = $this->model->getLines($id);
                $upload_dir = __DIR__ . '/../uploads/';
                foreach ($lines as $line) {
                    if (!empty($line['fichier_path']) && file_exists($upload_dir . $line['fichier_path'])) {
                        unlink($upload_dir . $line['fichier_path']);
                    }
                }
                
                $this->model->delete($id);
                $_SESSION['success'] = "Bulletin N° " . htmlspecialchars($slip['numero_bulletin']) . " supprimé avec succès";
            } else {
                $_SESSION['error'] = "Bulletin non trouvé";
            }
        }
        header('Location: index.php?controller=slip&action=index');
        exit;
    }

    public function print(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $slip = $this->model->getById($id);
        
        if (!$slip) {
            $_SESSION['error'] = "Bulletin non trouvé";
            header('Location: index.php?controller=slip&action=index');
            exit;
        }
        
        $lines = $this->model->getLines($id);
        $slips = [];
        $patients = [];
        $doctors = [];
        $interventions = [];
        include __DIR__ . '/../views/SlipView.php';
        exit;
    }

    public function viewFile(): void
    {
        $file = $_GET['file'] ?? '';
        
        if (empty($file)) {
            http_response_code(404);
            echo "Fichier non trouvé";
            exit;
        }
        
        $file = basename($file);
        $upload_dir = __DIR__ . '/../uploads/';
        $file_path = $upload_dir . $file;
        
        if (!file_exists($file_path)) {
            http_response_code(404);
            echo "Fichier non trouvé : " . htmlspecialchars($file);
            exit;
        }
        
        $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime_types = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        $mime_type = $mime_types[$file_ext] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . filesize($file_path));
        header('Content-Disposition: inline; filename="' . $file . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        
        readfile($file_path);
        exit;
    }

    public function exportCsv(): void
    {
        $slips = $this->model->getAllFiltered(null, null, null, null);
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bulletins_soins.csv"');
        
        $output = fopen('php://output', 'w');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'N° Bulletin',
            'Patient',
            'Médecin',
            'Date Soins',
            'Date Remboursement',
            'Total (TND)',
            'Montant Déboursé (TND)',
            'Montant Remboursé (TND)',
            'Solde (TND)'
        ], ';');

        foreach ($slips as $s) {
            $solde = (float)$s['montant_debourse'] - (float)$s['montant_rembourse'];
            fputcsv($output, [
                $s['numero_bulletin'],
                $s['patient_nom'],
                $s['doctor_nom'],
                $s['date_soins'],
                $s['date_remboursement'] ?? '',
                number_format((float)$s['total'], 3, '.', ' '),
                number_format((float)$s['montant_debourse'], 3, '.', ' '),
                number_format((float)$s['montant_rembourse'], 3, '.', ' '),
                number_format($solde, 3, '.', ' ')
            ], ';');
        }
        
        fclose($output);
        exit;
    }

    public function exportPdf(): void
    {
        $slips = $this->model->getAllFiltered(null, null, null, null);
        $patients = $this->patientModel->getAll();
        $doctors = $this->doctorModel->getAll();
        $interventions = $this->interventionModel->getAll();
        include __DIR__ . '/../views/SlipView.php';
        exit;
    }
}